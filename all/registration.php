<?php
session_start();
require_once('../classes/database.php');
$con = new database();
$sweetAlertConfig = "";

if (isset($_POST['register'])) {
  $firstname = $_POST['firstname'];
  $lastname = $_POST['lastname'];
  $email = $_POST['email'];
  $username = $_POST['username'];
  $phonenum = $_POST['phonenum'];
  $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

  // Require OTP verified in session for the same email
  $otpVerified = isset($_SESSION['otp_verified']) && $_SESSION['otp_verified'] === true;
  $verifiedEmail = isset($_SESSION['mail']) ? (string)$_SESSION['mail'] : '';
  if (!$otpVerified || strcasecmp($verifiedEmail, $email) !== 0) {
    $sweetAlertConfig = "
    <script>
    Swal.fire({
      icon: 'warning',
      title: 'Verify your email',
      text: 'Please enter the OTP sent to your email to complete registration.',
      confirmButtonText: 'OK',
      customClass: { popup: 'ae-ap-popup ae-narrow' }
    });
    </script>";
  } else {
    // Clear OTP flags then save
    unset($_SESSION['otp_verified']);
    unset($_SESSION['mail']);
    unset($_SESSION['last_otp_sent_at']);

    // Server-side duplicate checks to avoid silent DB failures
    if ($con->isUsernameExists($username)) {
      $sweetAlertConfig = "
      <script>
      Swal.fire({ icon:'error', title:'Username taken', text:'Please choose a different username.', confirmButtonText:'OK', customClass:{popup:'ae-ap-popup ae-narrow'} });
      </script>";
    } else if ($con->isEmailExists($email)) {
      $sweetAlertConfig = "
      <script>
      Swal.fire({ icon:'error', title:'Email in use', text:'Please use another email address.', confirmButtonText:'OK', customClass:{popup:'ae-ap-popup ae-narrow'} });
      </script>";
    } else {
      $userID = $con->signupCustomer($firstname, $lastname, $phonenum, $email, $username, $password);

      if ($userID) {
        // Persist verified state like ajax/complete_registration.php
        try {
          $con->ensureCustomerEmailVerified();
          $con->markCustomerEmailVerified((int)$userID);
        } catch (Throwable $e) { /* ignore */ }
        $sweetAlertConfig = "
        <script>
        Swal.fire({
          icon: 'success',
          title: 'Account created!',
          text: 'Redirecting to login…',
          timer: 1600,
          showConfirmButton: false,
          customClass: { popup: 'ae-ap-popup ae-narrow' }
        }).then(()=>{ window.location.href = 'login'; });
        </script>";
      } else {
        $sweetAlertConfig = "
        <script>
        Swal.fire({
          icon: 'error',
          title: 'Registration Failed',
          text: 'Please try again later',
          confirmButtonText: 'OK',
          customClass: { popup: 'ae-ap-popup ae-narrow' }
        });
        </script>";
      }
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <link rel="icon" href="../images/logo.png" type="image/png"/>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Register - Amaiah</title>
  <link rel="stylesheet" href="../bootstrap-5.3.3-dist/css/bootstrap.css">
  <link rel="stylesheet" href="../package/dist/sweetalert2.css">
  <link rel="stylesheet" href="../assets/css/responsive.css">
  <style>
    /* SweetAlert theme matching product popup */
    .swal2-popup.ae-ap-popup { background: #F7F2EC; box-shadow: 0 12px 32px rgba(75,46,14,0.18), inset 0 1px 0 rgba(255,255,255,0.65); border-radius: 24px; padding: 24px 28px !important; }
    .swal2-popup.ae-narrow { width: min(520px, 92vw) !important; }
    .swal2-title { color: #21160E; font-weight: 800; }
    .swal2-confirm { background: linear-gradient(180deg, #A1764E 0%, #7C573A 100%) !important; color: #fff !important; border-radius: 9999px !important; border: 1px solid rgba(255,255,255,0.75) !important; box-shadow: inset 0 2px 0 rgba(255,255,255,0.6), inset 0 -2px 0 rgba(0,0,0,0.06), 0 4px 12px rgba(75,46,14,0.25) !important; }
    .swal2-deny { background: #CFCAC4 !important; color: #21160E !important; border-radius: 9999px !important; border: 3px solid rgba(255,255,255,0.85) !important; }
    .swal2-cancel { border-radius: 9999px !important; }
    .swal2-input { box-sizing: border-box !important; width: 100% !important; max-width: 100% !important; padding: 12px 16px !important; border-radius: 16px !important; border: 2px solid #ddd !important; outline: none !important; font-size: 14px !important; margin: 8px 6px !important; }
    .swal2-input:focus { border-color: #C4A07A !important; box-shadow: 0 0 0 3px rgba(196,160,122,0.2) !important; }
    .ae-ap-popup .swal2-html-container, .ae-ap-popup .swal2-actions { padding: 0 6px !important; }
  </style>
</head>
<body>

<div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh; padding-top: 24px; padding-bottom: 24px;">
  <div class="login-container">
    <div class="logo">
      <img src="../images/logo.png" alt="Amaiah logo"/>
    </div>
    <h2 class="text-center mb-4" style="margin-top: 30px" >Register</h2>
    <form id="registrationForm" method="POST" action="" novalidate>
      <div class="login-box">
        <div class="row g-3 mb-3">
          <div class="col-md-6 col-12">
            <input type="text" name="firstname" id="firstname" class="form-control" placeholder="Enter your first name" required>
            <div class="invalid-feedback">First name is required.</div>
          </div>
          <div class="col-md-6 col-12">
            <input type="text" name="lastname" id="lastname" class="form-control" placeholder="Enter your last name" required>
            <div class="invalid-feedback">Last name is required.</div>
          </div>
        </div>
        <div class="row g-3 mb-3">
          <div class="col-12">
            <input type="email" name="email" id="email" class="form-control" placeholder="Enter your email" required>
            <div class="invalid-feedback">Email is required.</div>
          </div>
        </div>
        <div class="row g-3 mb-3">
          <div class="col-md-6 col-12">
            <input type="text" name="username" id="username" class="form-control" placeholder="Enter your username" required>
            <div class="invalid-feedback">Username is required.</div>
          </div>
          <div class="col-md-6 col-12">
            <input type="tel" name="phonenum" id="phonenum" class="form-control" placeholder="Enter your phone number" pattern="^09\d{9}$" required>
            <div class="invalid-feedback">Enter a valid Philippine number starting with 09.</div>
          </div>
        </div>
        <div class="row g-3 mb-3">
          <div class="col-12">
            <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
            <div class="invalid-feedback">Password must be at least 6 characters long, include one uppercase letter, one number, and one special character.</div>
          </div>
        </div>
      </div>
      <button type="submit" id="registerButton" name="register" class="btn btn-primary w-100">Register</button>
      <div class="login-link">
  Already have an account? <a href="login">Login</a>
      </div>
    </form>
  </div>
</div>
<script src="../bootstrap-5.3.3-dist/js/bootstrap.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php echo $sweetAlertConfig; ?>

<script>
  // OTP helpers using existing endpoints with PHPMailer
  async function sendOtp(email) {
    try {
      const resp = await fetch('../ajax/send_otp.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ email, purpose: 'registration' }) });
      const data = await resp.json();
      if (!data.success) { await Swal.fire({ icon:'error', title:'Error', text: data.message || 'Could not send code.', customClass:{popup:'ae-ap-popup ae-narrow'} }); return false; }
      return true;
    } catch (e) { await Swal.fire({ icon:'error', title:'Network error', text:'Please try again.', customClass:{popup:'ae-ap-popup ae-narrow'} }); return false; }
  }
  async function verifyOtp(otp) {
    try {
      const resp = await fetch('../ajax/verify_otp.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ otp }) });
      const data = await resp.json();
      return !!data.success;
    } catch (e) { return false; }
  }
  async function promptOtp(email) {
    while (true) {
      const res = await Swal.fire({
        title: 'Verify your email',
        html: '<p class="mb-2">Enter the 6-digit code we sent to <b>'+email.replace(/</g,'&lt;')+'</b>.</p>',
        input: 'text', inputPlaceholder: 'Enter 6-digit code',
        inputAttributes: { maxlength: 6, inputmode: 'numeric', autocapitalize:'off', autocorrect:'off' },
        showCancelButton: true, showDenyButton: true,
        confirmButtonText: 'Verify', denyButtonText: 'Resend', cancelButtonText: 'Cancel',
        icon: 'info', customClass: { popup: 'ae-ap-popup ae-narrow' }, allowOutsideClick: false,
        didOpen: () => { const inpt = Swal.getInput(); if (inpt) { inpt.focus(); inpt.select && inpt.select(); } },
        preConfirm: async (value) => {
          const code = (value || '').replace(/\D+/g,'');
          if (code.length !== 6) { Swal.showValidationMessage('Enter the 6-digit code.'); return false; }
          const ok = await verifyOtp(code);
          if (!ok) { Swal.showValidationMessage('Incorrect or expired code.'); return false; }
          return true;
        }
      });
      if (res.isConfirmed) return true;
      if (res.isDenied) { const sent = await sendOtp(email); if (sent) { await Swal.fire({icon:'success', title:'Code sent', text:'We emailed you a new code.', customClass:{popup:'ae-ap-popup ae-narrow'}}); } continue; }
      return false;
    }
  }
  // Function to validate individual fields
  function validateField(field, validationFn) {
    field.addEventListener('input', () => {
      if (validationFn(field.value)) {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
      } else {
        field.classList.remove('is-valid');
        field.classList.add('is-invalid');
      }
    });
  }

  // Validation functions for each field
  const isNotEmpty = (value) => value.trim() !== '';
  const isPasswordValid = (value) => {
    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{6,}$/;
    return passwordRegex.test(value);
  };
  const isPhoneValid = (value) => {
    const phoneRegex = /^09\d{9}$/;
    return phoneRegex.test(value);
  };

  // Real-time username validation using AJAX
  const checkUsernameAvailability = (usernameField) =>{
    usernameField.addEventListener('input',()=>{
      const username = usernameField.value.trim();
      if (username ===''){
        usernameField.classList.remove('is-valid');
        usernameField.classList.add('is-invalid');
        usernameField.nextElementSibling.textContent = 'Username is required.';
        registerButton.disabled = true;
        return;
      }
      fetch('../ajax/check_username.php',{
        method: 'POST',
        headers:{
          'Content-Type':'application/x-www-form-urlencoded',
        },
        body:`username=${encodeURIComponent(username)}`,
      })
        .then((response)=>response.json())
        .then((data)=>{
          if (data.exists){
            usernameField.classList.remove('is-valid');
            usernameField.classList.add('is-invalid');
            usernameField.nextElementSibling.textContent = 'Username is already taken.';
            registerButton.disabled = true;
          }else {
            usernameField.classList.remove('is-invalid');
            usernameField.classList.add('is-valid');
            usernameField.nextElementSibling.textContent = '';
            registerButton.disabled = false;
          }
        })
        .catch((error)=>{
          console.error('Error:', error);
          registerButton.disabled = true;
        });
    });
  };

  // Real-time email validation using AJAX
  const registerButton = document.getElementById('registerButton');
  const checkEmailAvailability = (emailField) => {
    emailField.addEventListener('input', () => {
      const email = emailField.value.trim();
      if (email === '') {
        emailField.classList.remove('is-valid');
        emailField.classList.add('is-invalid');
        emailField.nextElementSibling.textContent = 'Email is required.';
        registerButton.disabled = true;
        return;
      }
      fetch('../ajax/check_email.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `email=${encodeURIComponent(email)}`,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.exists) {
            emailField.classList.remove('is-valid');
            emailField.classList.add('is-invalid');
            emailField.nextElementSibling.textContent = 'Email is already taken.';
            registerButton.disabled = true;
          } else {
            emailField.classList.remove('is-invalid');
            emailField.classList.add('is-valid');
            emailField.nextElementSibling.textContent = '';
            registerButton.disabled = false;
          }
        })
        .catch((error) => {
          console.error('Error:', error);
          registerButton.disabled = true;
        });
    });
  };

  // Get form fields
  const firstname = document.getElementById('firstname');
  const lastname = document.getElementById('lastname');
  const username = document.getElementById('username');
  const email = document.getElementById('email');
  const password = document.getElementById('password');
  const phonenum = document.getElementById('phonenum');

  // Attach real-time validation to each field
  validateField(firstname, isNotEmpty);
  validateField(lastname, isNotEmpty);
  validateField(phonenum, isPhoneValid);
  validateField(password, isPasswordValid);
  checkUsernameAvailability(username);
  checkEmailAvailability(email);

  // Form submission validation + OTP modal
  document.getElementById('registrationForm').addEventListener('submit', async function (e) {
    let isValid = true;
    [firstname, lastname, username, email, password, phonenum].forEach(field => {
      if (!field.classList.contains('is-valid')) { field.classList.add('is-invalid'); isValid = false; }
    });
    e.preventDefault();
    if (!isValid) return;
    const btn = document.getElementById('registerButton');
    const original = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Verifying...';
    const emailVal = email.value.trim();
    // send in background for quick popup
    sendOtp(emailVal);
    const ok = await promptOtp(emailVal);
    if (ok) {
      // Ensure the server sees the 'register' flag when submitting programmatically
      if (!this.querySelector('#registerHidden')) {
        const h = document.createElement('input');
        h.type = 'hidden'; h.name = 'register'; h.value = '1'; h.id = 'registerHidden';
        this.appendChild(h);
      }
      this.submit();
      return;
    }
    btn.disabled = false; btn.innerHTML = original;
  });
</script>

<style>
  body {
  margin: 0;
  font-family: 'Segoe UI', sans-serif;
  background-image: url('../images/LAbg.png');
  background-size: cover;
  background-position: center;
  background-attachment: fixed;
  height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.login-container {
  background-color: rgba(255, 255, 255, 0.3);
  backdrop-filter: blur(10px);
  border-radius: 15px;
  padding: 50px 40px;
  width: 450px;
  height: auto; 
  text-align: center;
  color: white;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}



.logo {
  position: absolute;
  top: -45px;
  left: 50%;
  transform: translateX(-50%);
}

.logo img {
  width: 110px;
  height: 110px;
  border-radius: 50%;
  background-color: white;
  object-fit: contain;
  border: 6px solid white;
}


.input-row {
  display: flex;
  gap: 15px;
  margin-bottom: 15px;
}

.input-row input {
  flex: 1;
}

.form-control {
  border-radius: 25px;
  padding: 14px;
  border: 1px solid rgba(255, 255, 255, 0.5);
  background-color: rgba(255, 255, 255, 0.3);
  color: black;
}


.form-control::placeholder {
  color: rgba(255, 255, 255, 0.7);
}


.btn-primary {
  background-color: #c19a6b;
  border: none;
  color: white;
  padding: 12px;
  width: 100%;
  margin-top: 15px;
  border-radius: 8px;
  font-weight: bold;
  font-size: 16px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.btn-primary:hover {
  background-color: #a17850;
}


.login-link {
  margin-top: 20px;
  font-size: 14px;
  color: #ffffff;
}

.login-link a {
  color: #ffffff;
  font-weight: bold;
  text-decoration: underline;
  margin-left: 5px;
  transition: color 0.3s;
}

.login-link a:hover {
  color: #e0b083;
}

</style>
</body>
</html>