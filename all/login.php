<?php
session_start();
require_once('../classes/database.php');
$sweetAlertConfig = "";
$con = new database();

if (isset($_SESSION['CustomerID'])) {
    header('Location: ../Customer/advertisement.php');
    exit();
}
if (isset($_SESSION['EmployeeID'])) {
    header('Location: ../Employee/employesmain.php');
    exit();
}
if (isset($_SESSION['OwnerID'])) {
    header('Location: ../Owner/dashboard.php');
    exit();
}

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $user = $con->loginCustomer($username, $password);
    if ($user) {
        $_SESSION['CustomerID'] = $user['CustomerID'];
        $_SESSION['CustomerFN'] = $user['CustomerFN'];
        $sweetAlertConfig = "
        <script>
        Swal.fire({
          icon: 'success',
          title: 'Login Successful',
          text: 'Welcome Home, " . addslashes(htmlspecialchars($user['CustomerFN'])) . "!',
          confirmButtonText: 'Continue'
        }).then(() => {
          window.location.href = '../Customer/advertisement.php';
        });
        </script>";
    } else {
        $emp = $con->loginEmployee($username, $password);
        if ($emp) {
            $_SESSION['EmployeeID'] = $emp['EmployeeID'];
            $_SESSION['EmployeeFN'] = $emp['EmployeeFN'];
            $sweetAlertConfig = "
            <script>
            Swal.fire({
              icon: 'success',
              title: 'Login Successful',
              text: 'Welcome Home, " . addslashes(htmlspecialchars($emp['EmployeeFN'])) . "!',
              confirmButtonText: 'Continue'
            }).then(() => {
              window.location.href = '../Employee/employesmain.php';
            });
            </script>";
        } else {
            $own = $con->loginOwner($username, $password);
            if ($own) {
                $_SESSION['OwnerID'] = $own['OwnerID'];
                $_SESSION['OwnerFN'] = $own['OwnerFN'];
                $sweetAlertConfig = "
                <script>
                Swal.fire({
                  icon: 'success',
                  title: 'Login Successful',
                  text: 'Welcome, " . addslashes(htmlspecialchars($own['OwnerFN'])) . "!',
                  confirmButtonText: 'Continue'
                }).then(() => {
                  window.location.href = '../Owner/dashboard.php';
                });
                </script>";
            } else {

                $sweetAlertConfig = "
                <script>
                Swal.fire({
                  icon: 'error',
                  title: 'Login Failed',
                  text: 'Invalid username or password.',
                  confirmButtonText: 'Try Again'
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
  <title>Login - Amaiah</title>
  <link rel="stylesheet" href="../bootstrap-5.3.3-dist/css/bootstrap.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="stylesheet" href="../package/dist/sweetalert2.css">
  <link rel="stylesheet" href="../assets/css/responsive.css">
  <style>
    /* SweetAlert theme aligned with registration */
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
<div class="login-container">
  <div class="logo">
    <img src="../images/logo.png" alt="Amaiah logo" />
  </div>
  <h2>Login</h2>
  <form method="POST" action="">
    <div class="mb-3">
      <input type="text" name="username" class="form-control" placeholder="Enter your username" required>
    </div>
    <div class="mb-3 position-relative password-wrapper">
      <input type="password" id="login-password" name="password" class="form-control pe-5" placeholder="Enter your password" required>
      <button type="button" class="password-toggle" tabindex="-1" aria-label="Show password" data-target="login-password">
        <i class="fa-solid fa-eye"></i>
      </button>
    </div>
    <button type="submit" name="login" class="btn btn-primary">Login</button>
    <div class="text-center mt-3">
      Don't have an account? <a href="registration.php">Register</a>
    </div>
    <div class="text-center mt-2">
      <button type="button" id="forgot-password-btn" class="btn btn-link text-decoration-underline p-0" style="color:#fff;font-weight:bold;">Forgot password?</button>
    </div>
  </form>
</div>

<script src="../bootstrap-5.3.3-dist/js/bootstrap.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php echo $sweetAlertConfig; ?>
</body>

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
  align-items: center;
  justify-content: center;
}

.login-container {
  background-color: rgba(255, 255, 255, 0.3);
  backdrop-filter: blur(10px);
  border-radius: 15px;
  padding: 80px 24px 36px; 
  width: min(92vw, 450px);
  color: white;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
  position: relative;
  text-align: center;
}

.logo {
  position: absolute;
  top: -55px;
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

h2 {
  margin-top: 10px;
  margin-bottom: 30px;
  font-weight: bold;
}

.form-control {
  border-radius: 25px;
  padding: 14px 16px;
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
  padding: 12px 16px;
  border-radius: 8px;
  font-weight: bold;
  font-size: 16px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.btn-primary:hover {
  background-color: #a17850;
}

.text-center a {
  color: #fff;
  font-weight: bold;
  text-decoration: underline;
}

.text-center a:hover {
  color: #e0b083;
}

/* Password visibility toggle */
.password-wrapper { position: relative; }
.password-toggle { position: absolute; top: 50%; right: 16px; transform: translateY(-50%); background: none; border: none; color: #4B2E0E; cursor: pointer; padding: 4px; }
.password-toggle:focus { outline: none; }
 .password-toggle i { font-size: 1rem; }
  </style>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    // Password toggle
    document.querySelectorAll('.password-toggle').forEach(btn => {
      btn.addEventListener('click', () => {
        const targetId = btn.getAttribute('data-target');
        const input = document.getElementById(targetId);
        if (!input) return;
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        const icon = btn.querySelector('i');
        if (icon) {
          icon.classList.toggle('fa-eye');
          icon.classList.toggle('fa-eye-slash');
        }
        btn.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
      });
    });

    // Forgot password flow helpers
    const fpSendOtp = async (email) => {
      try {
        const resp = await fetch('../ajax/send_otp.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ email }) });
        const data = await resp.json();
        if (!data.success) {
          await Swal.fire({ icon: 'error', title: 'Unable to send code', text: data.message || 'Please try again later.', customClass: { popup: 'ae-ap-popup ae-narrow' } });
          return false;
        }
        return true;
      } catch (e) {
        await Swal.fire({ icon: 'error', title: 'Network error', text: 'Please try again.', customClass: { popup: 'ae-ap-popup ae-narrow' } });
        return false;
      }
    };
    const fpVerifyOtp = async (otp) => {
      try {
        const resp = await fetch('../ajax/verify_otp.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ otp }) });
        const data = await resp.json();
        return !!data.success;
      } catch (e) { return false; }
    };
    const fpPromptOtp = async (email) => {
      while (true) {
        const res = await Swal.fire({
          title: 'Verify your email',
          html: '<p class="mb-2">Enter the 6-digit code we sent to <b>' + email.replace(/</g,'&lt;') + '</b>.</p>',
          input: 'text', inputPlaceholder: 'Enter 6-digit code',
          inputAttributes: { maxlength: 6, inputmode: 'numeric', autocapitalize:'off', autocorrect:'off' },
          showCancelButton: true, showDenyButton: true,
          confirmButtonText: 'Verify', denyButtonText: 'Resend', cancelButtonText: 'Cancel',
          icon: 'info', customClass: { popup: 'ae-ap-popup ae-narrow' }, allowOutsideClick: false,
          didOpen: () => { const i = Swal.getInput(); if (i) { i.focus(); i.select && i.select(); } },
          preConfirm: async (value) => {
            const code = (value || '').replace(/\D+/g,'');
            if (code.length !== 6) { Swal.showValidationMessage('Enter the 6-digit code.'); return false; }
            const ok = await fpVerifyOtp(code);
            if (!ok) { Swal.showValidationMessage('Incorrect or expired code.'); return false; }
            return true;
          }
        });
        if (res.isConfirmed) return true;
        if (res.isDenied) { const sent = await fpSendOtp(email); if (sent) { await Swal.fire({ icon:'success', title:'Code sent', text:'We emailed you a new code.', customClass:{ popup:'ae-ap-popup ae-narrow' } }); } continue; }
        return false;
      }
    };

    // Forgot password flow (email -> OTP -> new password)
    document.getElementById('forgot-password-btn')?.addEventListener('click', async () => {
      try {
        const { value: email } = await Swal.fire({
          title: 'Reset your password',
          html: '<p class="mb-2">Enter your account email to receive a verification code.</p>',
          input: 'email',
          inputLabel: 'Email address',
          inputPlaceholder: 'you@example.com',
          confirmButtonText: 'Send code',
          showCancelButton: true,
          icon: 'info',
          customClass: { popup: 'ae-ap-popup ae-narrow' },
          inputValidator: (v) => {
            if (!v) return 'Please enter your email.';
            const ok = /.+@.+\..+/.test(v);
            return ok ? undefined : 'Enter a valid email address.';
          }
        });
        if (!email) return;

        const sent = await fpSendOtp(email);
        if (!sent) return;

        const verified = await fpPromptOtp(email);
        if (!verified) return;

        const { value: pass1 } = await Swal.fire({
          title: 'Create new password',
          input: 'password',
          inputLabel: 'New password',
          inputPlaceholder: 'At least 6 characters',
          confirmButtonText: 'Continue',
          showCancelButton: true,
          customClass: { popup: 'ae-ap-popup ae-narrow' },
          inputValidator: (v) => {
            if (!v || v.length < 6) return 'Use at least 6 characters.';
            return undefined;
          }
        });
        if (!pass1) return;
        const { value: pass2 } = await Swal.fire({
          title: 'Confirm password',
          input: 'password',
          inputLabel: 'Re-enter new password',
          confirmButtonText: 'Reset password',
          showCancelButton: true,
          customClass: { popup: 'ae-ap-popup ae-narrow' },
          inputValidator: (v) => {
            if (v !== pass1) return 'Passwords do not match.';
            return undefined;
          }
        });
        if (!pass2) return;

        const resetResp = await fetch('../ajax/reset_password.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ email, new_password: pass1 }) });
        const resetData = await resetResp.json();
        if (resetData && resetData.success) {
          await Swal.fire({ icon: 'success', title: 'Password updated', text: 'You can now log in with your new password.', customClass: { popup: 'ae-ap-popup ae-narrow' } });
        } else {
          await Swal.fire({ icon: 'error', title: 'Reset failed', text: resetData.message || 'Please try again later.', customClass: { popup: 'ae-ap-popup ae-narrow' } });
        }
      } catch (err) {
        console.error(err);
        Swal.fire({ icon: 'error', title: 'Something went wrong', text: 'Please try again.', customClass: { popup: 'ae-ap-popup ae-narrow' } });
      }
    });
  });
</script>
</html>