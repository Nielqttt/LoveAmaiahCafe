<?php

class database {

    function opencon() {
        return new PDO('mysql:host=localhost;dbname=amaihatest', 'root', '');
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
    
 
}