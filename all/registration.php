<?php
// No direct DB insert here. We now use AJAX endpoints to start pending registration,
// send/verify OTP, and complete registration only after verification.
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Register - Amaiah</title>
  <link rel="stylesheet" href="../bootstrap-5.3.3-dist/css/bootstrap.css">
  <link rel="stylesheet" href="../package/dist/sweetalert2.css">
  <link rel="stylesheet" href="../assets/css/responsive.css">
</head>
<body>

<div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh; padding-top: 24px; padding-bottom: 24px;">
  <div class="login-container">
    <div class="logo">
      <img src="../images/logo.png" alt="Amaiah logo"/>
    </div>
    <h2 class="text-center mb-4" style="margin-top: 30px" >Register</h2>
  <form id="registrationForm" novalidate>
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
      <button type="submit" id="registerButton" class="btn btn-primary w-100">Register</button>
      <div class="login-link">
  Already have an account? <a href="login">Login</a>
      </div>
    </form>
  </div>
</div>
<!-- OTP Modal -->
<div class="modal fade" id="otpModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content otp-modal">
      <div class="modal-header">
        <h5 class="modal-title">Email Verification</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>We sent a 6-digit code to <b id="otpEmail"></b>.</p>
        <div class="mb-2">
          <label for="otpInput" class="form-label">Enter OTP</label>
          <input type="text" id="otpInput" class="form-control" maxlength="6" placeholder="e.g. 123456" inputmode="numeric" autocomplete="one-time-code">
          <div class="invalid-feedback" id="otpError">Please enter a valid 6-digit code.</div>
        </div>
      </div>
      <div class="modal-footer d-flex justify-content-between align-items-center w-100">
        <div>
          <button class="btn btn-outline-secondary" id="resendBtn" disabled>Resend in 30s</button>
        </div>
        <div>
          <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-primary" id="verifyBtn">Verify</button>
        </div>
      </div>
    </div>
  </div>
 </div>
<script src="../bootstrap-5.3.3-dist/js/bootstrap.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
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

  // OTP modal elements
  const otpModalEl = document.getElementById('otpModal');
  const otpModal = new bootstrap.Modal(otpModalEl);
  const otpEmailEl = document.getElementById('otpEmail');
  const otpInput = document.getElementById('otpInput');
  const otpError = document.getElementById('otpError');
  const resendBtn = document.getElementById('resendBtn');
  const verifyBtn = document.getElementById('verifyBtn');

  let cooldown = 30;
  let timerHandle = null;
  let currentEmail = '';

  function startCooldown(seconds) {
    cooldown = seconds || 30;
    resendBtn.disabled = true;
    resendBtn.textContent = 'Resend in ' + cooldown + 's';
    if (timerHandle) clearInterval(timerHandle);
    timerHandle = setInterval(() => {
      cooldown -= 1;
      if (cooldown <= 0) {
        clearInterval(timerHandle);
        resendBtn.disabled = false;
        resendBtn.textContent = 'Resend code';
      } else {
        resendBtn.textContent = 'Resend in ' + cooldown + 's';
      }
    }, 1000);
  }

  async function sendOtp(emailAddr) {
    try {
      const resp = await fetch('../ajax/send_otp.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify({ email: emailAddr })
      });
      const data = await resp.json();
      if (data.success) {
        startCooldown(data.cooldown || 30);
        return true;
      } else {
        await Swal.fire({ icon: 'error', title: 'OTP not sent', text: data.message || 'Please try again later.' });
        return false;
      }
    } catch (e) {
      console.error('sendOtp error', e);
      await Swal.fire({ icon: 'error', title: 'Network error', text: 'Please try again.' });
      return false;
    }
  }

  async function verifyOtp(code) {
    try {
      const resp = await fetch('../ajax/verify_otp.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify({ otp: String(code).trim() })
      });
      return await resp.json();
    } catch (e) {
      console.error('verifyOtp error', e);
      return { success: false, message: 'Network error' };
    }
  }

  async function completeRegistration() {
    try {
      const resp = await fetch('../ajax/complete_registration.php', {
        method: 'POST',
        credentials: 'same-origin'
      });
      return await resp.json();
    } catch (e) {
      console.error('completeRegistration error', e);
      return { success: false, message: 'Network error' };
    }
  }

  resendBtn.addEventListener('click', async () => {
    if (resendBtn.disabled) return;
    await sendOtp(currentEmail);
  });

  verifyBtn.addEventListener('click', async () => {
    const code = otpInput.value.trim();
    if (!/^\d{6}$/.test(code)) {
      otpInput.classList.add('is-invalid');
      otpError.textContent = 'Please enter a valid 6-digit code.';
      return;
    }
    otpInput.classList.remove('is-invalid');
    verifyBtn.disabled = true;
    const result = await verifyOtp(code);
    verifyBtn.disabled = false;
    if (result.success) {
      // Proceed to finalize account creation
      const done = await completeRegistration();
      if (done.success) {
        otpModal.hide();
        await Swal.fire({ icon: 'success', title: 'Registration complete', text: 'Your email was verified.' });
        window.location.href = 'login';
      } else {
        await Swal.fire({ icon: 'error', title: 'Registration failed', text: done.message || 'Please try again.' });
      }
    } else {
      otpInput.classList.add('is-invalid');
      otpError.textContent = result.message || 'Invalid code. Please try again.';
    }
  });

  // Handle form submit: start pending registration, then show OTP modal and send initial OTP
  document.getElementById('registrationForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    let ok = true;
    [firstname, lastname, username, email, password, phonenum].forEach(field => {
      if (!field.classList.contains('is-valid')) {
        field.classList.add('is-invalid');
        ok = false;
      }
    });
    if (!ok) return;

    const payload = {
      firstname: firstname.value.trim(),
      lastname: lastname.value.trim(),
      email: email.value.trim(),
      username: username.value.trim(),
      phonenum: phonenum.value.trim(),
      password: password.value
    };

    try {
      const resp = await fetch('../ajax/start_registration.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify(payload)
      });
      const data = await resp.json();
      if (!data.success) {
        await Swal.fire({ icon: 'error', title: 'Cannot proceed', text: data.message || 'Please fix errors and try again.' });
        return;
      }
      currentEmail = data.email || payload.email;
      otpEmailEl.textContent = currentEmail;
      otpInput.value = '';
      otpInput.classList.remove('is-invalid');
      otpModal.show();
      // Send the first OTP immediately
      await sendOtp(currentEmail);
    } catch (err) {
      console.error('start_registration error:', err);
      await Swal.fire({ icon: 'error', title: 'Unexpected error', text: 'Please try again later.' });
    }
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

/* OTP Modal - match frosted glass theme */
.otp-modal {
  background-color: rgba(255, 255, 255, 0.3);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border-radius: 15px;
  border: 1px solid rgba(255,255,255,0.4);
  color: #fff;
}
.otp-modal .modal-header,
.otp-modal .modal-footer {
  border: none;
  background: transparent;
}
.otp-modal .modal-title,
.otp-modal p,
.otp-modal .form-label {
  color: #fff;
}
.otp-modal .btn-close {
  filter: invert(1) grayscale(1);
}
.otp-modal .form-control {
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.6);
  background-color: rgba(255, 255, 255, 0.3);
  color: black;
}
.otp-modal .form-control::placeholder {
  color: rgba(255, 255, 255, 0.8);
}
.otp-modal .btn-secondary {
  background: transparent;
  color: #fff;
  border: 1px solid rgba(255, 255, 255, 0.6);
}
.otp-modal .btn-secondary:hover {
  background: rgba(255, 255, 255, 0.15);
}
#resendBtn.btn-outline-secondary {
  border-color: rgba(255, 255, 255, 0.6);
  color: #fff;
}
#resendBtn.btn-outline-secondary:hover {
  background: rgba(255, 255, 255, 0.15);
  color: #fff;
}
#resendBtn.btn-outline-secondary:disabled {
  opacity: 0.6;
  color: #fff;
}

</style>
</body>
</html>