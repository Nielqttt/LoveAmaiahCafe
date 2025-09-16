<?php

class database {

    function opencon() {
        // Ensure PHP uses Philippine Standard Time
        if(function_exists('date_default_timezone_set')){
            date_default_timezone_set('Asia/Manila');
        }
        $pdo = new PDO('mysql:host=mysql.hostinger.com;dbname=u130699935_amaiah', 'u130699935_loveamaiah', 'iLoveAmaiah?143');
        // Set MySQL session timezone to match (UTC+8, no DST)
        try { $pdo->exec("SET time_zone = '+08:00'"); } catch (Exception $e) { /* ignore */ }
        return $pdo;
    }

    function getOrdersForOwnerOrEmployee($loggedInID, $userType) {
        $con = $this->opencon();

        $sql = "
            SELECT
                o.OrderID, o.OrderDate, o.TotalAmount, os.UserTypeID, c.C_Username AS CustomerUsername,
                e.EmployeeFN AS EmployeeFirstName, e.EmployeeLN AS EmployeeLastName,
                ow.OwnerFN AS OwnerFirstName, ow.OwnerLN AS OwnerLastName,
                p.PaymentMethod, p.ReferenceNo,
                GROUP_CONCAT(CONCAT(prod.ProductName, ' x', od.Quantity, ' (₱', FORMAT(pp.UnitPrice, 2), ')') ORDER BY od.OrderDetailID SEPARATOR '; ') AS OrderItems
            FROM orders o
            JOIN ordersection os ON o.OrderSID = os.OrderSID
            LEFT JOIN customer c ON os.CustomerID = c.CustomerID
            LEFT JOIN employee e ON os.EmployeeID = e.EmployeeID
            LEFT JOIN owner ow ON os.OwnerID = ow.OwnerID
            LEFT JOIN payment p ON o.OrderID = p.OrderID
            LEFT JOIN orderdetails od ON o.OrderID = od.OrderID
            LEFT JOIN product prod ON od.ProductID = prod.ProductID
            LEFT JOIN productprices pp ON od.PriceID = pp.PriceID
            GROUP BY o.OrderID 
            ORDER BY o.OrderDate DESC
        ";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // --- ALL OTHER FUNCTIONS ---

     function archiveProduct($productID): bool {
        $con = $this->opencon();
        try {
            $stmt = $con->prepare("UPDATE product SET is_available = 0 WHERE ProductID = ?");
            return $stmt->execute([$productID]);
        } catch (PDOException $e) {
            error_log("Archive Product Error: " . $e->getMessage());
            return false;
        }
    }

    function restoreProduct($productID): bool {
        $con = $this->opencon();
        try {
            $stmt = $con->prepare("UPDATE product SET is_available = 1 WHERE ProductID = ?");
            return $stmt->execute([$productID]);
        } catch (PDOException $e) {
            error_log("Restore Product Error: " . $e->getMessage());
            return false;
        }
    }

    function archiveEmployee($employeeID): bool {
        $con = $this->opencon();
        try {
            $stmt = $con->prepare("UPDATE employee SET is_active = 0 WHERE EmployeeID = ?");
            return $stmt->execute([$employeeID]);
        } catch (PDOException $e) {
            error_log("Archive Employee Error: " . $e->getMessage());
            return false;
        }
    }

    function restoreEmployee($employeeID): bool {
        $con = $this->opencon();
        try {
            $stmt = $con->prepare("UPDATE employee SET is_active = 1 WHERE EmployeeID = ?");
            return $stmt->execute([$employeeID]);
        } catch (PDOException $e) {
            error_log("Restore Employee Error: " . $e->getMessage());
            return false;
        }
    }

    function getEmployee() {
        $con = $this->opencon();
        return $con->query("SELECT * FROM employee ORDER BY EmployeeID DESC")->fetchAll(PDO::FETCH_ASSOC);
    }

    function getJoinedProductData() {
        $con = $this->opencon();
        $stmt = $con->prepare("
            SELECT 
                    p.ProductID, p.ProductName, p.ProductCategory, p.is_available, p.Created_AT, p.Allergen,
                pp.UnitPrice, pp.Effective_From, pp.Effective_To, pp.PriceID
            FROM product p
            JOIN productprices pp ON p.ProductID = pp.ProductID
            ORDER BY p.ProductID DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function processOrder($orderData, $paymentMethod, $userID, $userType) {
        $db = $this->opencon();
        $ownerID = null; $employeeID = null; $customerID = null; $userTypeID = null; $referencePrefix = 'ORD';
        switch ($userType) {
            case 'owner':
                $ownerID = $userID;
                $userTypeID = 1;
                $referencePrefix = 'LA';
                break;
            case 'employee':
                $employeeID = $userID;
                $ownerID = $this->getEmployeeOwnerID($employeeID);
                if ($ownerID === null) { return ['success' => false, 'message' => "Order failed: Employee not linked to an owner."]; }
                $userTypeID = 2;
                $referencePrefix = 'EMP';
                break;
            case 'customer':
                $customerID = $userID;
                $ownerID = $this->getAnyOwnerId(); 
                if ($ownerID === null) { return ['success' => false, 'message' => "Order failed: No owner account is configured to handle orders."]; }
                $userTypeID = 3;
                $referencePrefix = 'CUST';
                break;
            default:
                return ['success' => false, 'message' => "Invalid user type."];
        }
        $totalAmount = 0;
        foreach ($orderData as $item) { $totalAmount += $item['price'] * $item['quantity']; }
        try {
            $db->beginTransaction();
            $stmt = $db->prepare("INSERT INTO ordersection (CustomerID, EmployeeID, OwnerID, UserTypeID) VALUES (?, ?, ?, ?)");
            $stmt->execute([$customerID, $employeeID, $ownerID, $userTypeID]);
            $orderSID = $db->lastInsertId();
            $stmt = $db->prepare("INSERT INTO orders (OrderDate, TotalAmount, OrderSID) VALUES (NOW(), ?, ?)");
            $stmt->execute([$totalAmount, $orderSID]);
            $orderID = $db->lastInsertId();
            foreach ($orderData as $item) {
                $productID = intval(str_replace('product-', '', $item['id']));
                $priceID = isset($item['price_id']) ? $item['price_id'] : null;
                if ($priceID === null) { throw new Exception("Price ID is missing."); }
                $stmt = $db->prepare("INSERT INTO orderdetails (OrderID, ProductID, PriceID, Quantity, Subtotal) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$orderID, $productID, $priceID, $item['quantity'], $item['price'] * $item['quantity']]);
            }
            $referenceNo = strtoupper($referencePrefix . uniqid() . mt_rand(1000, 9999));
            $this->addPaymentRecord($db, $orderID, $paymentMethod, $totalAmount, $referenceNo, 1);
            $db->commit();
            return ['success' => true, 'message' => 'Transaction successful!', 'order_id' => $orderID, 'ref_no' => $referenceNo];
        } catch (Exception $e) {
            $db->rollBack(); 
            error_log("Order Save Error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    function getUserData($userID, $userType) {
        $con = $this->opencon();
        $sql = ''; $fieldMap = [];
        switch ($userType) {
            case 'customer':
                $sql = "SELECT * FROM customer WHERE CustomerID = ?";
                $fieldMap = [ 'username' => 'C_Username', 'name' => 'CustomerFN', 'email' => 'C_Email', 'phone' => 'C_PhoneNumber' ];
                break;
            case 'employee':
                $sql = "SELECT * FROM employee WHERE EmployeeID = ?";
                 $fieldMap = [ 'username' => 'E_Username', 'name' => 'EmployeeFN', 'email' => 'E_Email', 'phone' => 'E_PhoneNumber' ];
                break;
            case 'owner':
                $sql = "SELECT * FROM owner WHERE OwnerID = ?";
                 $fieldMap = [ 'username' => 'Username', 'name' => 'OwnerFN', 'email' => 'O_Email', 'phone' => 'O_PhoneNumber' ];
                break;
            default: return [];
        }
        $stmt = $con->prepare($sql);
        $stmt->execute([$userID]);
        $dbData = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$dbData) { return []; }
        $standardizedData = [];
        foreach ($fieldMap as $standardKey => $dbKey) {
            $standardizedData[$standardKey] = $dbData[$dbKey] ?? '';
        }
        return $standardizedData;
    }

       function updateUserData($userID, $userType, $data) {
        $con = $this->opencon();
        $table = ''; $idColumn = ''; $fieldMap = [];

        switch ($userType) {
            case 'customer':
                $table = 'customer'; $idColumn = 'CustomerID';
                $fieldMap = ['username' => 'C_Username', 'name' => 'CustomerFN', 'email' => 'C_Email', 'phone' => 'C_PhoneNumber', 'password' => 'C_Password'];
                break;
            case 'employee':
                $table = 'employee'; $idColumn = 'EmployeeID';
                $fieldMap = ['username' => 'E_Username', 'name' => 'EmployeeFN', 'email' => 'E_Email', 'phone' => 'E_PhoneNumber', 'password' => 'E_Password'];
                break;
            case 'owner':
                $table = 'owner'; $idColumn = 'OwnerID';
                $fieldMap = ['username' => 'Username', 'name' => 'OwnerFN', 'email' => 'O_Email', 'phone' => 'O_PhoneNumber', 'password' => 'O_Password'];
                break;
            default:
                return ['success' => false, 'message' => 'Invalid user type.'];
        }

        $sqlParts = []; $params = [];
        $map = ['username', 'name', 'email', 'phone'];
        foreach($map as $key) {
            if (isset($data[$key])) {
                $sqlParts[] = "`{$fieldMap[$key]}` = ?";
                $params[] = $data[$key];
            }
        }
        
      
        if (!empty($data['new_password'])) {
            
            
            if (empty($data['current_password'])) {
                return ['success' => false, 'message' => 'To set a new password, you must provide your current password.'];
            }

            $stmt_check = $con->prepare("SELECT `{$fieldMap['password']}` FROM `{$table}` WHERE `{$idColumn}` = ?");
            $stmt_check->execute([$userID]);
            $user = $stmt_check->fetch(PDO::FETCH_ASSOC);

            if (!$user || !password_verify($data['current_password'], $user[$fieldMap['password']])) {
                return ['success' => false, 'message' => 'Incorrect current password. Password was not changed.'];
            }

            $sqlParts[] = "`{$fieldMap['password']}` = ?";
            $params[] = password_hash($data['new_password'], PASSWORD_DEFAULT);
        }

        if (empty($sqlParts)) {
            return ['success' => true, 'message' => 'No changes were made.'];
        }

        $sql = "UPDATE `{$table}` SET " . implode(', ', $sqlParts) . " WHERE `{$idColumn}` = ?";
        $params[] = $userID;
        $stmt = $con->prepare($sql);
        
        if ($stmt->execute($params)) {
             return ['success' => true, 'message' => 'Profile updated successfully!'];
        } else {
             return ['success' => false, 'message' => 'An error occurred while updating the profile.'];
        }
    }

    function signupCustomer($firstname, $lastname, $phonenum, $email, $username, $password) {
        $con = $this->opencon();
        try {
            $con->beginTransaction();
            $stmt = $con->prepare("INSERT INTO customer (CustomerFN, CustomerLN, C_PhoneNumber, C_Email, C_Username, C_Password) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$firstname, $lastname, $phonenum, $email, $username, $password]);
            $userID = $con->lastInsertId();
            $con->commit();
            return $userID;
        } catch (PDOException $e) {
            $con->rollBack(); return false;
        }
    }

    function isUsernameExists($username) {
        $con = $this->opencon();
        $stmt1 = $con->prepare("SELECT COUNT(*) FROM customer WHERE C_Username = ?");
        $stmt1->execute([$username]);
        $count1 = $stmt1->fetchColumn();
        $stmt2 = $con->prepare("SELECT COUNT(*) FROM employee WHERE E_Username = ?");
        $stmt2->execute([$username]);
        $count2 = $stmt2->fetchColumn();
        return ($count1 > 0 || $count2 > 0);
    }

    function isEmailExists($email) {
        $con = $this->opencon();
        $stmt = $con->prepare("SELECT COUNT(*) FROM customer WHERE C_Email = ?");
        $stmt->execute([$email]);
        return $stmt->fetchColumn() > 0;
    }

    function loginCustomer($username, $password) {
        $con = $this->opencon();
        $stmt = $con->prepare("SELECT * FROM customer WHERE C_Username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['C_Password'])) { return $user; }
        return false;
    }

    function loginOwner($username, $password) {
        $con = $this->opencon();
        $stmt = $con->prepare("SELECT * FROM owner WHERE Username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['Password'])) { return $user; }
        return false;
    }

    function loginEmployee($username, $password) {
        $con = $this->opencon();
        $stmt = $con->prepare("SELECT * FROM employee WHERE E_Username = ? AND is_active = 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['E_Password'])) { return $user; }
        return false;
    }

    function addEmployee($firstF, $firstN, $Euser, $password, $role, $emailN, $number, $owerID): bool|string {
        $con = $this->opencon();
        try {
            $con->beginTransaction();
            $stmt = $con->prepare("INSERT INTO employee (EmployeeFN, EmployeeLN, E_Username, E_Password, Role, E_PhoneNumber, E_Email, OwnerID) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$firstF, $firstN, $Euser, $password, $role, $number, $emailN, $owerID]);
            $userID = $con->lastInsertId();
            $con->commit();
            return $userID;
        } catch (PDOException $e) {
            $con->rollBack(); error_log("AddEmployee Error: " . $e->getMessage());
            return false;
        }
    }
    
    function updateProductPrice($priceID, $unitPrice, $effectiveFrom, $effectiveTo): bool {
        $con = $this->opencon();
        try {
            $stmt = $con->prepare("UPDATE productprices SET UnitPrice = ?, Effective_From = ?, Effective_To = ? WHERE PriceID = ?");
            $effectiveTo = empty($effectiveTo) ? NULL : $effectiveTo;
            return $stmt->execute([$unitPrice, $effectiveFrom, $effectiveTo, $priceID]);
        } catch (PDOException $e) {
            error_log("UpdateProductPrice Error: " . $e->getMessage());
            return false;
        }
    }

    function addProduct($productName, $category, $price, $createdAt, $effectiveFrom, $effectiveTo, $ownerID, $description = null, $allergens = null) {
        $con = $this->opencon();
        try {
            $con->beginTransaction();
                // normalize allergens string (default to 'None' if empty)
                $allergenValue = trim((string)$allergens);
                if ($allergenValue === '') { $allergenValue = 'None'; }
                $stmt = $con->prepare("INSERT INTO product (ProductName, ProductCategory, Allergen, Description) VALUES (?, ?, ?, ?)");
                $stmt->execute([$productName, $category, $allergenValue, $description]);
            $productID = $con->lastInsertId();
            $stmt2 = $con->prepare("INSERT INTO productprices (ProductID, UnitPrice, Effective_From, Effective_To) VALUES (?, ?, ?, ?)");
            $stmt2->execute([$productID, $price, $effectiveFrom, $effectiveTo]);
            $con->commit();
            return $productID;
        } catch (PDOException $e) {
            $con->rollBack(); error_log("AddProduct Error: " . $e->getMessage());
            return false;
        }
    }

    function isEmployeEmailExists($emailN) {
        $con = $this->opencon();
        $stmt = $con->prepare("SELECT COUNT(*) FROM employee WHERE E_Email = ?");
        $stmt->execute([$emailN]);
        return $stmt->fetchColumn() > 0;
    }

    function isEmployeeUserExists($Euser) {
        $con = $this->opencon();
        $stmt = $con->prepare("SELECT COUNT(*) FROM employee WHERE E_Username = ?");
        $stmt->execute([$Euser]);
        return $stmt->fetchColumn() > 0;
    }

    function getAllProductsWithPrice() {
    $con = $this->opencon();
            $stmt = $con->prepare("
                SELECT 
                    p.ProductID, 
                    p.ProductName, 
                    p.ProductCategory, 
                    p.Created_AT, 
                    p.ImagePath, 
                    p.Description,
                    p.Allergen,
                    pp.UnitPrice, 
                    pp.PriceID 
                FROM product p 
                LEFT JOIN productprices pp ON p.ProductID = pp.ProductID 
                WHERE p.is_available = 1 
                GROUP BY p.ProductID
            ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


    function getAllCategories() {
        $con = $this->opencon();
        $stmt = $con->prepare("SELECT DISTINCT ProductCategory FROM product WHERE is_available = 1");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    function addPaymentRecord(PDO $pdo, $orderID, $paymentMethod, $paymentAmount, $referenceNo, $paymentStatus = 1): bool {
        try {
            $stmt = $pdo->prepare("INSERT INTO payment (OrderID, PaymentMethod, PaymentAmount, PaymentStatus, ReferenceNo) VALUES (?, ?, ?, ?, ?)");
            return $stmt->execute([$orderID, $paymentMethod, $paymentAmount, $paymentStatus, $referenceNo]);
        } catch (PDOException $e) {
            error_log("ERROR: AddPaymentRecord Error: " . $e->getMessage());
            return false;
        }
    }

    function getFullOrderDetails($orderID, $referenceNo) {
        $con = $this->opencon();
        $stmt = $con->prepare("SELECT o.OrderID, o.OrderDate, o.TotalAmount, os.UserTypeID, os.CustomerID, os.EmployeeID, os.OwnerID, p.PaymentMethod, p.ReferenceNo, p.PaymentStatus FROM orders o JOIN ordersection os ON o.OrderSID = os.OrderSID LEFT JOIN payment p ON o.OrderID = p.OrderID WHERE o.OrderID = ? AND p.ReferenceNo = ?");
        $stmt->execute([$orderID, $referenceNo]);
        $orderHeader = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($orderHeader) {
            $stmtDetails = $con->prepare("SELECT od.Quantity, od.Subtotal, prod.ProductName, pp.UnitPrice FROM orderdetails od JOIN product prod ON od.ProductID = prod.ProductID JOIN productprices pp ON od.PriceID = pp.PriceID WHERE od.OrderID = ?");
            $stmtDetails->execute([$orderID]);
            $orderDetails = $stmtDetails->fetchAll(PDO::FETCH_ASSOC);
            $orderHeader['Details'] = $orderDetails;
            return $orderHeader;
        }
        return false;
    }

    function getEmployeeOwnerID($employeeID) {
        $con = $this->opencon();
        $stmt = $con->prepare("SELECT OwnerID FROM employee WHERE EmployeeID = ?");
        $stmt->execute([$employeeID]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['OwnerID'] ?? null;
    }

    function getAnyOwnerId() {
        $con = $this->opencon();
        try {
            $stmt = $con->prepare("SELECT OwnerID FROM owner LIMIT 1");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['OwnerID'] ?? null;
        } catch (PDOException $e) {
            error_log("ERROR: getAnyOwnerId() failed: " . $e->getMessage());
            return null;
        }
    }
    
    function getOrdersForCustomer($customerID) {
        $con = $this->opencon();
        $stmt = $con->prepare("
            SELECT
                o.OrderID, o.OrderDate, o.TotalAmount, p.PaymentMethod, p.ReferenceNo,
                GROUP_CONCAT(CONCAT(prod.ProductName, ' x', od.Quantity, ' (₱', FORMAT(pp.UnitPrice, 2), ')') ORDER BY od.OrderDetailID SEPARATOR '; ') AS OrderItems
            FROM orders o
            JOIN ordersection os ON o.OrderSID = os.OrderSID
            LEFT JOIN payment p ON o.OrderID = p.OrderID
            LEFT JOIN orderdetails od ON o.OrderID = od.OrderID
            LEFT JOIN product prod ON od.ProductID = prod.ProductID
            LEFT JOIN productprices pp ON od.PriceID = pp.PriceID
            WHERE os.CustomerID = ?
            GROUP BY o.OrderID
            ORDER BY o.OrderDate DESC
        ");
        $stmt->execute([$customerID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

     function getSystemTotalSales($days) {
        $con = $this->opencon();
        $stmt = $con->prepare("SELECT SUM(TotalAmount) FROM orders WHERE OrderDate >= DATE_SUB(NOW(), INTERVAL ? DAY)");
        $stmt->execute([$days]);
        return (float)$stmt->fetchColumn();
    }
    
    function getSystemTotalOrders($days) {
        $con = $this->opencon();
        $stmt = $con->prepare("SELECT COUNT(OrderID) FROM orders WHERE OrderDate >= DATE_SUB(NOW(), INTERVAL ? DAY)");
        $stmt->execute([$days]);
        return (int)$stmt->fetchColumn();
    }
    
    function getSystemTotalTransactions() {
        $con = $this->opencon();
        $stmt = $con->prepare("SELECT COUNT(OrderID) FROM orders");
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    function getSystemSalesData($days) {
        $con = $this->opencon();
        $stmt = $con->prepare("SELECT DATE(OrderDate) as date, SUM(TotalAmount) as total FROM orders WHERE OrderDate >= DATE_SUB(NOW(), INTERVAL ? DAY) GROUP BY DATE(OrderDate) ORDER BY date ASC");
        $stmt->execute([$days]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $labels = []; $data = [];
        foreach($results as $row) {
            $labels[] = date("M d", strtotime($row['date']));
            $data[] = $row['total'];
        }
        return ['labels' => $labels, 'data' => $data];
    }
    
     function getSystemTopProducts($days) {
        $con = $this->opencon();
        $stmt = $con->prepare("SELECT p.ProductName, SUM(od.Quantity) as total_quantity FROM orderdetails od JOIN product p ON od.ProductID = p.ProductID JOIN orders o ON od.OrderID = o.OrderID WHERE o.OrderDate >= DATE_SUB(NOW(), INTERVAL ? DAY) GROUP BY p.ProductID, p.ProductName ORDER BY total_quantity DESC LIMIT 5");
        $stmt->execute([$days]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $labels = []; $data = [];
        foreach($results as $row) {
            $labels[] = $row['ProductName'];
            $data[] = $row['total_quantity'];
        }
        return ['labels' => $labels, 'data' => $data];
    }
    
    // ================= Attendance / Time Logs =================
    function ensureTimeLogsTable() {
        $con = $this->opencon();
        $sql = "CREATE TABLE IF NOT EXISTS time_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            EmployeeID INT NOT NULL,
            log_date DATE NOT NULL,
            clock_in DATETIME DEFAULT NULL,
            clock_out DATETIME DEFAULT NULL,
            break_start DATETIME DEFAULT NULL,
            break_end DATETIME DEFAULT NULL,
            clock_in_lat DECIMAL(10,7) DEFAULT NULL,
            clock_in_lng DECIMAL(10,7) DEFAULT NULL,
            clock_in_acc DECIMAL(10,2) DEFAULT NULL,
            clock_out_lat DECIMAL(10,7) DEFAULT NULL,
            clock_out_lng DECIMAL(10,7) DEFAULT NULL,
            clock_out_acc DECIMAL(10,2) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_emp_date (EmployeeID, log_date),
            FOREIGN KEY (EmployeeID) REFERENCES employee(EmployeeID) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        $con->exec($sql);
        // Attempt to add new columns if table already existed (ignore errors)
        $maybeCols = [
            'clock_in_lat DECIMAL(10,7) NULL',
            'clock_in_lng DECIMAL(10,7) NULL',
            'clock_in_acc DECIMAL(10,2) NULL',
            'clock_out_lat DECIMAL(10,7) NULL',
            'clock_out_lng DECIMAL(10,7) NULL',
            'clock_out_acc DECIMAL(10,2) NULL'
        ];
        foreach($maybeCols as $def){
            try { $con->exec("ALTER TABLE time_logs ADD COLUMN $def"); } catch (PDOException $e) { /* ignore */ }
        }
    }

    function getTodayAttendance($employeeID) {
        $con = $this->opencon();
        $stmt = $con->prepare("SELECT * FROM time_logs WHERE EmployeeID = ? AND log_date = CURDATE() LIMIT 1");
        $stmt->execute([$employeeID]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return [
                'clock_in' => null,
                'clock_out' => null,
                'break_start' => null,
                'break_end' => null
            ];
        }
        return [
            'clock_in' => $row['clock_in'],
            'clock_out' => $row['clock_out'],
            'break_start' => $row['break_start'],
            'break_end' => $row['break_end']
        ];
    }

    function clockIn($employeeID) {
        $this->ensureTimeLogsTable();
        $con = $this->opencon();
        // If already has a time log today with clock_in, block
        $stmt = $con->prepare("SELECT clock_in FROM time_logs WHERE EmployeeID = ? AND log_date = CURDATE() LIMIT 1");
        $stmt->execute([$employeeID]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($existing && $existing['clock_in']) {
            return ['success' => false, 'message' => 'Already clocked in.'];
        }
        if ($existing) {
            $stmt = $con->prepare("UPDATE time_logs SET clock_in = NOW(), clock_in_lat = COALESCE(?, clock_in_lat), clock_in_lng = COALESCE(?, clock_in_lng), clock_in_acc = COALESCE(?, clock_in_acc) WHERE EmployeeID = ? AND log_date = CURDATE()");
            $stmt->execute([$_POST['lat'] ?? null, $_POST['lng'] ?? null, $_POST['acc'] ?? null, $employeeID]);
        } else {
            $stmt = $con->prepare("INSERT INTO time_logs (EmployeeID, log_date, clock_in, clock_in_lat, clock_in_lng, clock_in_acc) VALUES (?, CURDATE(), NOW(), ?, ?, ?)");
            $stmt->execute([$employeeID, $_POST['lat'] ?? null, $_POST['lng'] ?? null, $_POST['acc'] ?? null]);
        }
        return ['success' => true, 'message' => 'Clocked in.'];
    }

    function startBreak($employeeID) {
        $con = $this->opencon();
        $stmt = $con->prepare("SELECT clock_in, break_start, break_end, clock_out FROM time_logs WHERE EmployeeID = ? AND log_date = CURDATE() LIMIT 1");
        $stmt->execute([$employeeID]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row || !$row['clock_in']) {
            return ['success' => false, 'message' => 'Clock in first.'];
        }
        if ($row['clock_out']) {
            return ['success' => false, 'message' => 'Already clocked out.'];
        }
        if ($row['break_start'] && !$row['break_end']) {
            return ['success' => false, 'message' => 'Break already started.'];
        }
        $stmt = $con->prepare("UPDATE time_logs SET break_start = NOW(), break_end = NULL WHERE EmployeeID = ? AND log_date = CURDATE()");
        $stmt->execute([$employeeID]);
        return ['success' => true, 'message' => 'Break started.'];
    }

    function endBreak($employeeID) {
        $con = $this->opencon();
        $stmt = $con->prepare("SELECT break_start, break_end FROM time_logs WHERE EmployeeID = ? AND log_date = CURDATE() LIMIT 1");
        $stmt->execute([$employeeID]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row || !$row['break_start']) {
            return ['success' => false, 'message' => 'No active break.'];
        }
        if ($row['break_end']) {
            return ['success' => false, 'message' => 'Break already ended.'];
        }
        $stmt = $con->prepare("UPDATE time_logs SET break_end = NOW() WHERE EmployeeID = ? AND log_date = CURDATE()");
        $stmt->execute([$employeeID]);
        return ['success' => true, 'message' => 'Break ended.'];
    }

    function clockOut($employeeID) {
        $con = $this->opencon();
        $stmt = $con->prepare("SELECT clock_in, clock_out FROM time_logs WHERE EmployeeID = ? AND log_date = CURDATE() LIMIT 1");
        $stmt->execute([$employeeID]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row || !$row['clock_in']) {
            return ['success' => false, 'message' => 'Clock in first.'];
        }
        if ($row['clock_out']) {
            return ['success' => false, 'message' => 'Already clocked out.'];
        }
        $stmt = $con->prepare("UPDATE time_logs SET clock_out = NOW(), clock_out_lat = COALESCE(?, clock_out_lat), clock_out_lng = COALESCE(?, clock_out_lng), clock_out_acc = COALESCE(?, clock_out_acc) WHERE EmployeeID = ? AND log_date = CURDATE()");
        $stmt->execute([$_POST['lat'] ?? null, $_POST['lng'] ?? null, $_POST['acc'] ?? null, $employeeID]);
        return ['success' => true, 'message' => 'Clocked out.'];
    }

    // Fetch today's logs with geolocation for all employees (owner/admin view)
    function getTodayLogsWithGeo($employeeID = null) {
        $con = $this->opencon();
        if ($employeeID) {
            $stmt = $con->prepare("SELECT tl.*, e.EmployeeFN, e.EmployeeLN FROM time_logs tl JOIN employee e ON tl.EmployeeID = e.EmployeeID WHERE tl.log_date = CURDATE() AND tl.EmployeeID = ? ORDER BY tl.clock_in ASC");
            $stmt->execute([$employeeID]);
        } else {
            $stmt = $con->prepare("SELECT tl.*, e.EmployeeFN, e.EmployeeLN FROM time_logs tl JOIN employee e ON tl.EmployeeID = e.EmployeeID WHERE tl.log_date = CURDATE() ORDER BY tl.clock_in ASC");
            $stmt->execute();
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function getTodayAttendanceSummaryForEmployees() {
        $this->ensureTimeLogsTable();
        $con = $this->opencon();
        $stmt = $con->prepare("SELECT e.EmployeeID, e.EmployeeFN, e.EmployeeLN, tl.clock_in, tl.clock_out, tl.break_start, tl.break_end
            FROM employee e
            LEFT JOIN time_logs tl ON tl.EmployeeID = e.EmployeeID AND tl.log_date = CURDATE()
            ORDER BY e.EmployeeID DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Map of employeeID => today's attendance (aliased field names)
    function getOwnerEmployeesTodayAttendance($ownerID) {
        $this->ensureTimeLogsTable();
        $con = $this->opencon();
        $stmt = $con->prepare("SELECT 
                e.EmployeeID,
                tl.clock_in  AS clock_in_time,
                tl.clock_out AS clock_out_time,
                tl.break_start AS break_start_time,
                tl.break_end AS break_end_time,
                tl.clock_in_lat, tl.clock_in_lng, tl.clock_out_lat, tl.clock_out_lng,
                CASE 
                    WHEN tl.break_start IS NOT NULL AND (tl.break_end IS NULL OR tl.break_end < tl.break_start) THEN 1 
                    ELSE 0 
                END AS on_break
            FROM employee e
            LEFT JOIN time_logs tl 
                ON tl.EmployeeID = e.EmployeeID AND tl.log_date = CURDATE()
            WHERE e.OwnerID = ?
            ORDER BY e.EmployeeID DESC");
        $stmt->execute([$ownerID]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $map = [];
        foreach ($rows as $r) { $map[$r['EmployeeID']] = $r; }
        return $map;
    }

    // Owner manual reset of today's attendance for a given employee
    function resetTodayAttendance($ownerID, $employeeID){
        $con = $this->opencon();
        // Verify employee belongs to owner
        $stmt = $con->prepare("SELECT 1 FROM employee WHERE EmployeeID = ? AND OwnerID = ? LIMIT 1");
        $stmt->execute([$employeeID, $ownerID]);
        if(!$stmt->fetch()){
            return ['success'=>false,'message'=>'Unauthorized employee.'];
        }
        // Delete today's time log (removes clock in/out/breaks). Could also UPDATE to null; deletion simpler.
        $stmt = $con->prepare("DELETE FROM time_logs WHERE EmployeeID = ? AND log_date = CURDATE()");
        $stmt->execute([$employeeID]);
        return ['success'=>true,'message'=>'Attendance reset for today.'];
    }
 
}