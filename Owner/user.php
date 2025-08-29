
<?php
session_start();
 
if (!isset($_SESSION['OwnerID'])) {
  header('Location: ../all/login.php');
  exit();
}
 
require_once('../classes/database.php'); 
$con = new database();
$sweetAlertConfig = "";

if (isset($_POST['add_employee'])) {
  $owerID = $_SESSION['OwnerID'];
  $firstF = $_POST['firstF'];
  $firstN = $_POST['firstN'];
  $role = $_POST['role'];
  $number = $_POST['number'];
  $emailN = $_POST['email'];
  $Euser = $_POST['username'];
  $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
 
  $userID = $con->addEmployee($firstF, $firstN, $Euser, $password, $role, $emailN,  $number, $owerID);
 
  if ($userID) {
    $sweetAlertConfig = "<script>document.addEventListener('DOMContentLoaded', () => Swal.fire('Success', 'Employee added.', 'success').then(() => window.location.href = 'user.php'));</script>";
  } else {
    $sweetAlertConfig = "<script>document.addEventListener('DOMContentLoaded', () => Swal.fire('Error', 'Failed to add employee.', 'error'));</script>";
  }
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Employee List</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    body { font-family: 'Inter', sans-serif; }
    .swal-feedback { color: #dc3545; font-size: 13px; text-align: left; display: block; margin-top: 5px; }
    .swal2-input.is-valid { border-color: #198754 !important; }
    .swal2-input.is-invalid { border-color: #dc3545 !important; }
    .pagination-bar {
      position: absolute;
      bottom: 1rem;
      left: 0;
      right: 0;
      display: flex;
      justify-content: center;
      flex-wrap: wrap;
      gap: 0.5rem;
    }

  /* Add Employee modal custom layout */
  .ae-grid { display: grid; grid-template-columns: 1.3fr 1fr; gap: 20px; align-items: start; }
  .ae-input { width: 100%; padding: 12px 16px; border-radius: 16px; border: 2px solid #ddd; outline: none; font-size: 14px; }
  .ae-input:focus { border-color: #C4A07A; box-shadow: 0 0 0 3px rgba(196,160,122,0.2); }
  .ae-section-title { font-weight: 800; font-size: 20px; color: #1f2937; margin: 10px 0; }
  .ae-soft { background: #f3f4f6; padding: 16px; border-radius: 20px; }
  .ae-label { font-weight: 700; font-size: 14px; color: #111827; margin-bottom: 6px; display: block; }
  .ae-avatar-wrap { background: #f3f4f6; padding: 14px; border-radius: 24px; border: 1px solid #e5e7eb; }
  .ae-avatar { width: 100%; max-width: 280px; height: 280px; object-fit: cover; display: block; border-radius: 20px; margin: 0 auto; background: #e5e7eb; }
  .ae-select { width: 100%; padding: 10px 14px; border-radius: 12px; border: 2px solid #ddd; outline: none; }
  .ae-select:focus { border-color: #C4A07A; box-shadow: 0 0 0 3px rgba(196,160,122,0.2); }
  .swal2-popup.ae-wide { width: 980px !important; }
  .swal-feedback { color: #dc3545; font-size: 13px; text-align: left; display: block; margin-top: 5px; }
  .swal2-title { color: #21160E; font-weight: 800; }

  /* Reuse Add Product modal look-and-feel */
  .ap-section-title { color: #21160E; font-weight: 800; }
  .ap-label { color: #4B2E0E; font-weight: 600; font-size: 0.9rem; }
  .ap-soft-field { background: #EEE6DC; border-radius: 16px; padding: 14px; border: 1px solid rgba(196,160,122,0.28); }
  .ap-image-wrap { background: #E8E0D7; border-radius: 18px; padding: 12px; border: 1px solid rgba(196,160,122,0.28); }
  .ap-image { border-radius: 14px; width: 100%; height: 300px; object-fit: cover; }
  .swal2-popup.ae-ap-popup { background: #F7F2EC; box-shadow: 0 12px 32px rgba(75,46,14,0.18), inset 0 1px 0 rgba(255,255,255,0.65); border-radius: 24px; }
  .btn-soft-hollow { background: #CFCAC4; color: #21160E; border-radius: 9999px; padding: 0.5rem 1rem; border: 3px solid rgba(255,255,255,0.85); font-weight: 700; }
  .btn-soft-gray { background: linear-gradient(180deg, #A1764E 0%, #7C573A 100%); color: #FFFFFF; border-radius: 9999px; padding: 0.5rem 1.25rem; font-weight: 700; border: 1px solid rgba(255,255,255,0.75); box-shadow: inset 0 2px 0 rgba(255,255,255,0.6), inset 0 -2px 0 rgba(0,0,0,0.06), 0 4px 12px rgba(75,46,14,0.25); }
  .btn-soft-gray:hover { filter: brightness(1.02); }
  .btn-soft-gray:active { transform: translateY(1px); }
  /* Stronger specificity so SweetAlert defaults don't override our button styles */
  .swal2-styled.btn-soft-gray { background: linear-gradient(180deg, #A1764E 0%, #7C573A 100%) !important; color: #FFFFFF !important; border-radius: 9999px !important; border: 1px solid rgba(255,255,255,0.75) !important; box-shadow: inset 0 2px 0 rgba(255,255,255,0.6), inset 0 -2px 0 rgba(0,0,0,0.06), 0 4px 12px rgba(75,46,14,0.25) !important; }
  .swal2-styled.btn-soft-hollow { background: #CFCAC4 !important; color: #21160E !important; border-radius: 9999px !important; border: 3px solid rgba(255,255,255,0.85) !important; box-shadow: none !important; }
  .swal2-styled:focus { box-shadow: none !important; }
  </style>
</head>
<body class="bg-[rgba(255,255,255,0.7)] min-h-screen flex">
 
<!-- Sidebar -->
<aside class="bg-white bg-opacity-90 backdrop-blur-sm w-16 flex flex-col items-center py-6 space-y-8 shadow-lg">
    <img src="../images/logo.png" alt="Logo" class="w-10 h-10 rounded-full mb-4" />
    <?php $current = basename($_SERVER['PHP_SELF']); ?>   
    <button title="Dashboard" onclick="window.location.href='../Owner/dashboard.php'">
        <i class="fas fa-chart-line text-xl <?= $current == 'dashboard.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
    </button>
    <button title="Home" onclick="window.location.href='../Owner/mainpage.php'">
        <i class="fas fa-home text-xl <?= $current == 'mainpage.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
    </button>
    <button title="Cart" onclick="window.location.href='../Owner/page.php'">
        <i class="fas fa-shopping-cart text-xl <?= $current == 'page.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
    </button>
    <button title="Order List" onclick="window.location.href='../all/tranlist.php'">
        <i class="fas fa-list text-xl <?= $current == 'tranlist.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
    </button>
    <button title="Product List" onclick="window.location.href='../Owner/product.php'">
        <i class="fas fa-box text-xl <?= $current == 'product.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
    </button>
    <button title="Employees" onclick="window.location.href='../Owner/user.php'">
        <i class="fas fa-users text-xl <?= $current == 'user.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
    </button>
    <button title="Settings" onclick="window.location.href='../all/setting.php'">
        <i class="fas fa-cog text-xl <?= $current == 'setting.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i>
    </button>
    <button id="logout-btn" title="Logout">
        <i class="fas fa-sign-out-alt text-xl text-[#4B2E0E]"></i>
    </button>
</aside>
 
<!-- Main content -->
<main class="flex-1 p-6 relative flex flex-col">
  <header class="mb-4 flex items-center justify-between">
    <div>
      <h1 class="text-[#4B2E0E] font-semibold text-xl mb-1">Employee List</h1>
      <p class="text-xs text-gray-400">Manage your employees here</p>
    </div>
    <a href="#" id="add-employee-btn" class="bg-[#4B2E0E] text-white rounded-full px-5 py-2 text-sm font-semibold shadow-md hover:bg-[#6b3e14] transition flex items-center">
      <i class="fas fa-user-plus mr-2"></i>Add Employee
    </a>
  </header>
 
  <section class="bg-white rounded-xl p-4 w-full shadow-lg flex-1 overflow-x-auto relative">
    <table class="w-full text-sm">
      <thead>
        <tr class="text-left text-[#4B2E0E] border-b">
          <th class="py-2 px-3 w-[5%]">#</th>
          <th class="py-2 px-3 w-[20%]">Name</th>
          <th class="py-2 px-3 w-[15%]">Role</th>
          <th class="py-2 px-3 w-[10%]">Status</th>
          <th class="py-2 px-3 w-[15%]">Phone</th>
          <th class="py-2 px-3 w-[20%]">Email</th>
          <th class="py-2 px-3 w-[15%]">Username</th>
          <th class="py-2 px-3 w-[10%] text-center">Actions</th>
        </tr>
      </thead>
      <tbody id="employee-body">
        <?php
        $employees = array_reverse($con->getEmployee());
        foreach ($employees as $employee) {
        ?>
        <tr class="border-b hover:bg-gray-50 <?= $employee['is_active'] == 0 ? 'bg-red-50 text-gray-500' : '' ?>">
          <td class="py-2 px-3"><?= htmlspecialchars($employee['EmployeeID']) ?></td>
          <td class="py-2 px-3 font-semibold <?= $employee['is_active'] == 0 ? 'line-through' : '' ?>"><?= htmlspecialchars($employee['EmployeeFN'] . ' ' . $employee['EmployeeLN']) ?></td>
          <td class="py-2 px-3"><?= htmlspecialchars($employee['Role']) ?></td>
          <td class="py-2 px-3">
            <?php if ($employee['is_active'] == 1): ?>
              <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-green-600 bg-green-200">Active</span>
            <?php else: ?>
              <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-red-600 bg-red-200">Archived</span>
            <?php endif; ?>
          </td>
          <td class="py-2 px-3"><?= htmlspecialchars($employee['E_PhoneNumber']) ?></td>
          <td class="py-2 px-3"><?= htmlspecialchars($employee['E_Email']) ?></td>
          <td class="py-2 px-3"><?= htmlspecialchars($employee['E_Username']) ?></td>
          <td class="py-2 px-3 text-center">
            <?php if ($employee['is_active'] == 1): ?>
              <button class="text-red-600 hover:underline text-lg archive-employee-btn" title="Archive"
                 data-employee-id="<?= htmlspecialchars($employee['EmployeeID']) ?>"
                 data-employee-name="<?= htmlspecialchars($employee['EmployeeFN'] . ' ' . $employee['EmployeeLN']) ?>">
                <i class="fas fa-archive"></i>
              </button>
            <?php else: ?>
              <button class="text-green-600 hover:underline text-lg restore-employee-btn" title="Restore"
                 data-employee-id="<?= htmlspecialchars($employee['EmployeeID']) ?>"
                 data-employee-name="<?= htmlspecialchars($employee['EmployeeFN'] . ' ' . $employee['EmployeeLN']) ?>">
                <i class="fas fa-undo-alt"></i>
              </button>
            <?php endif; ?>
          </td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
    <div id="pagination" class="pagination-bar"></div>
  </section>

  <!-- Hidden form for add employee -->
  <form id="add-employee-form" method="POST" style="display:none;">
    <input type="hidden" name="firstF" id="form-firstF">
    <input type="hidden" name="firstN" id="form-firstN">
    <input type="hidden" name="role" id="form-role">
    <input type="hidden" name="number" id="form-number">
    <input type="hidden" name="email" id="form-email">
    <input type="hidden" name="username" id="form-username">
    <input type="hidden" name="password" id="form-password">
    <input type="hidden" name="add_employee" value="1">
  </form>

  <?= $sweetAlertConfig ?>
</main>

<script>
const isNotEmpty = (value) => value.trim() !== '';
const isPasswordValid = (value) => /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{6,}$/.test(value);
const isPhoneValid = (value) => /^09\d{9}$/.test(value);

function setSwalFieldState(field, isValid, message) {
  if (isValid) {
    field.classList.remove('is-invalid');
    field.classList.add('is-valid');
    field.style.borderColor = '#198754';
    if(field.nextElementSibling) field.nextElementSibling.textContent = '';
  } else {
    field.classList.remove('is-valid');
    field.classList.add('is-invalid');
    field.style.borderColor = '#dc3545';
    if(field.nextElementSibling) field.nextElementSibling.textContent = message;
  }
}

document.getElementById('add-employee-btn').addEventListener('click', function (e) {
  e.preventDefault();
  Swal.fire({
    title: 'Add Employee',
    customClass: { popup: 'ae-wide ae-ap-popup', confirmButton: 'btn-soft-gray rounded-full', cancelButton: 'btn-soft-hollow rounded-full' },
    html:
      `<div class="ae-grid">
         <div>
           <div class="ap-section-title">Employee</div>
           <div class="ap-soft-field">
             <label class="ap-label" for="swal-emp-fname">First Name</label>
             <input id="swal-emp-fname" class="ae-input" placeholder="First Name" />
             <div style="height:10px"></div>
             <label class="ap-label" for="swal-emp-lname">Last Name</label>
             <input id="swal-emp-lname" class="ae-input" placeholder="Last Name" />
           </div>
           <div style="height:14px"></div>
           <div class="ap-section-title">Contacts</div>
           <div class="ap-soft-field">
             <label class="ap-label" for="swal-emp-phone">Phone Number</label>
             <input id="swal-emp-phone" class="ae-input" placeholder="(09xxxxxxxxx)" />
             <span class="swal-feedback"></span>
             <div style="height:12px"></div>
             <label class="ap-label" for="swal-emp-email">Email</label>
             <input id="swal-emp-email" class="ae-input" type="email" placeholder="Email" />
             <span class="swal-feedback"></span>
           </div>
           <div style="height:14px"></div>
           <div class="ap-section-title">Account</div>
           <div class="ap-soft-field">
             <label class="ap-label" for="swal-emp-username">Username</label>
             <input id="swal-emp-username" class="ae-input" placeholder="Username" />
             <span class="swal-feedback"></span>
             <div style="height:12px"></div>
             <label class="ap-label" for="swal-emp-password">Password</label>
             <input id="swal-emp-password" class="ae-input" type="password" placeholder="Password" />
             <span class="swal-feedback"></span>
           </div>
         </div>
         <div>
           <div class="ap-section-title">Role</div>
           <div class="ap-soft-field">
             <div class="ap-image-wrap">
               <img id="swal-emp-avatar" class="ap-image" alt="Role avatar" />
             </div>
             <div style="height:14px"></div>
             <select id="swal-emp-role" class="ae-select">
               <option value="" selected disabled>Select Role</option>
               <option value="Barista">Barista</option>
               <option value="Cashier">Cashier</option>
             </select>
           </div>
         </div>
       </div>`,
  showCancelButton: true,
  confirmButtonText: 'Add',
  cancelButtonText: 'Cancel',
    focusConfirm: false,
  heightAuto: false,
  scrollbarPadding: false,
    preConfirm: () => {
      const firstF = document.getElementById('swal-emp-fname').value.trim();
      const firstN = document.getElementById('swal-emp-lname').value.trim();
      const role = document.getElementById('swal-emp-role').value;
      const number = document.getElementById('swal-emp-phone').value.trim();
      const email = document.getElementById('swal-emp-email').value.trim();
      const username = document.getElementById('swal-emp-username').value.trim();
      const password = document.getElementById('swal-emp-password').value;

      if (!firstF || !firstN || !role || !isPhoneValid(number) || !email || !username || !isPasswordValid(password)) {
        Swal.showValidationMessage('Please fix all errors before submitting.');
        return false;
      }

      document.getElementById('form-firstF').value = firstF;
      document.getElementById('form-firstN').value = firstN;
      document.getElementById('form-role').value = role;
      document.getElementById('form-number').value = number;
      document.getElementById('form-email').value = email;
      document.getElementById('form-username').value = username;
      document.getElementById('form-password').value = password;
      return true;
    },
    didOpen: () => {
      const phoneField = document.getElementById('swal-emp-phone');
      phoneField.addEventListener('input', () => setSwalFieldState(phoneField, isPhoneValid(phoneField.value), 'Invalid PH phone number (e.g., 09xxxxxxxxx)'));

      const passwordField = document.getElementById('swal-emp-password');
      passwordField.addEventListener('input', () => setSwalFieldState(passwordField, isPasswordValid(passwordField.value), 'Min. 6 chars, 1 uppercase, 1 number, 1 special char.'));

      // Role-based avatar switching using uploaded images
      const roleSelect = document.getElementById('swal-emp-role');
      const avatarImg = document.getElementById('swal-emp-avatar');
      const AVATARS = {
        'Barista': '../images/barista.png',
        'Cashier': '../images/cashier.png'
      };
      const DEFAULT_AVATAR = '../images/employ.png';
      const updateAvatar = () => {
        const v = roleSelect.value;
        avatarImg.src = AVATARS[v] || DEFAULT_AVATAR;
        avatarImg.alt = v ? `${v} avatar` : 'Employee avatar';
      };
      updateAvatar();
      roleSelect.addEventListener('change', updateAvatar);
    }
  }).then((result) => {
    if (result.isConfirmed) {
      document.getElementById('add-employee-form').submit();
    }
  });
});

function initializeActionButtons() {
  document.querySelectorAll('.archive-employee-btn').forEach(button => {
    button.addEventListener('click', function(e) {
      e.preventDefault();
      const employeeId = this.dataset.employeeId;
      const employeeName = this.dataset.employeeName;
      Swal.fire({
        title: 'Are you sure?',
        text: `You are about to archive "${employeeName}". They will not be able to log in.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, archive them!',
        cancelButtonText: 'Cancel'
      }).then((result) => {
        if (result.isConfirmed) {
          const formData = new FormData();
          formData.append('employee_id', employeeId);
          fetch('archive_employee.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                Swal.fire('Archived!', `${employeeName} has been archived.`, 'success').then(() => window.location.reload());
              } else {
                Swal.fire('Error!', data.message || 'Failed to archive.', 'error');
              }
            })
            .catch(() => Swal.fire('Error!', 'An error occurred.', 'error'));
        }
      });
    });
  });

  document.querySelectorAll('.restore-employee-btn').forEach(button => {
    button.addEventListener('click', function(e) {
      e.preventDefault();
      const employeeId = this.dataset.employeeId;
      const employeeName = this.dataset.employeeName;
      Swal.fire({
        title: 'Are you sure?',
        text: `You are about to restore "${employeeName}". They will be able to log in again.`,
        icon: 'info',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, restore them!',
        cancelButtonText: 'Cancel'
      }).then((result) => {
        if (result.isConfirmed) {
          const formData = new FormData();
          formData.append('employee_id', employeeId);
          fetch('restore_employee.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                Swal.fire('Restored!', `${employeeName} has been restored.`, 'success').then(() => window.location.reload());
              } else {
                Swal.fire('Error!', data.message || 'Failed to restore.', 'error');
              }
            })
            .catch(() => Swal.fire('Error!', 'An error occurred.', 'error'));
        }
      });
    });
  });
}

function paginateTable(containerId, paginationId, rowsPerPage = 15) {
  const tbody = document.getElementById(containerId);
  const pagination = document.getElementById(paginationId);
  if (!tbody || !pagination) return;
  const rows = Array.from(tbody.children);
  const pageCount = Math.ceil(rows.length / rowsPerPage);
  let currentPage = 1;

  function showPage(page) {
    rows.forEach((row, i) => {
      row.style.display = (i >= (page - 1) * rowsPerPage && i < page * rowsPerPage) ? '' : 'none';
    });
    renderPagination();
  }

  function renderPagination() {
    pagination.innerHTML = '';
    const createButton = (text, onClick, isDisabled = false) => {
        const btn = document.createElement('button');
        btn.textContent = text;
        btn.disabled = isDisabled;
        btn.onclick = onClick;
        btn.className = "px-3 py-1 border rounded disabled:opacity-50";
        return btn;
    };
    
    pagination.appendChild(createButton('Prev', () => { if (currentPage > 1) { currentPage--; showPage(currentPage); } }, currentPage === 1));
    for (let i = 1; i <= pageCount; i++) {
        const btn = createButton(i, () => { currentPage = i; showPage(currentPage); });
        if (i === currentPage) btn.className += ' bg-[#4B2E0E] text-white';
        pagination.appendChild(btn);
    }
    pagination.appendChild(createButton('Next', () => { if (currentPage < pageCount) { currentPage++; showPage(currentPage); } }, currentPage === pageCount));
  }
  if (pageCount > 1) { showPage(currentPage); }
}

window.addEventListener('DOMContentLoaded', () => {
  paginateTable('employee-body', 'pagination');
  initializeActionButtons();
});

document.getElementById('logout-btn').addEventListener('click', () => {
    Swal.fire({
        title: 'Are you sure you want to log out?', icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#4B2E0E', cancelButtonColor: '#d33', confirmButtonText: 'Yes, log out'
    }).then((result) => {
        if (result.isConfirmed) { window.location.href = "../all/logout.php"; }
    });
});

</script>
</body>
</html>
