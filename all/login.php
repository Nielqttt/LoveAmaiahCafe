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
  <link rel="stylesheet" href="./package/dist/sweetalert2.css">
  <link rel="stylesheet" href="../assets/css/responsive.css">
  
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

    // Forgot password flow (email -> OTP -> new password)
    const forgotBtn = document.getElementById('forgot-password-btn');
    forgotBtn?.addEventListener('click', async () => {
      try {
        // Step 1: Collect email
        const { value: email } = await Swal.fire({
          title: 'Reset your password',
          input: 'email',
          inputLabel: 'Enter your account email',
          inputPlaceholder: 'you@example.com',
          confirmButtonText: 'Send code',
          showCancelButton: true,
          inputValidator: (v) => {
            if (!v) return 'Please enter your email.';
            const ok = /.+@.+\..+/.test(v);
            return ok ? undefined : 'Enter a valid email address.';
          }
        });
        if (!email) return; // canceled

        // Step 2: Send OTP
        const sendResp = await fetch('../ajax/send_otp.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ email }) });
        const sendData = await sendResp.json();
        if (!sendData.success) {
          await Swal.fire({ icon: 'error', title: 'Unable to send code', text: sendData.message || 'Please try again later.' });
          return;
        }

        // Step 3: Verify OTP
        const verify = async () => {
          const { value: otp } = await Swal.fire({
            title: 'Enter verification code',
            input: 'text',
            inputLabel: 'We sent a 6-digit code to your email',
            inputPlaceholder: '123456',
            confirmButtonText: 'Verify',
            showCancelButton: true,
            inputAttributes: { maxlength: 6, inputmode: 'numeric', autocomplete: 'one-time-code' },
            inputValidator: (v) => {
              if (!v || !/^\d{6}$/.test(v)) return 'Enter the 6-digit code.';
              return undefined;
            },
            footer: `<button id="resend-otp" class="swal2-styled" style="background:#c19a6b;margin-top:8px;">Resend code</button>`
          });
          if (!otp) return { canceled: true };
          const resp = await fetch('../ajax/verify_otp.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ otp }) });
          const data = await resp.json();
          if (!data.success) {
            await Swal.fire({ icon: 'error', title: 'Verification failed', text: data.message || 'Please try again.' });
            return { success: false };
          }
          return { success: true };
        };

        // Attach resend while the OTP dialog is open
        document.addEventListener('click', async function resendHandler(e){
          if (e.target && e.target.id === 'resend-otp') {
            e.preventDefault();
            const r = await fetch('../ajax/send_otp.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ email }) });
            const d = await r.json();
            if (d.success) {
              Swal.fire({ icon: 'success', title: 'Code sent', text: 'We sent a new code to your email.' });
            } else {
              Swal.fire({ icon: 'error', title: 'Resend failed', text: d.message || 'Please try again later.' });
            }
          }
        }, { once: true });

        const verifyResult = await verify();
        if (!verifyResult || verifyResult.canceled) return;
        if (!verifyResult.success) return;

        // Step 4: New password
        const { value: pass1 } = await Swal.fire({
          title: 'Create new password',
          input: 'password',
          inputLabel: 'New password',
          inputPlaceholder: 'At least 6 characters',
          confirmButtonText: 'Continue',
          showCancelButton: true,
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
          inputValidator: (v) => {
            if (v !== pass1) return 'Passwords do not match.';
            return undefined;
          }
        });
        if (!pass2) return;

        // Step 5: Reset via backend
        const resetResp = await fetch('../ajax/reset_password.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ email, new_password: pass1 }) });
        const resetData = await resetResp.json();
        if (resetData && resetData.success) {
          await Swal.fire({ icon: 'success', title: 'Password updated', text: 'You can now log in with your new password.' });
        } else {
          await Swal.fire({ icon: 'error', title: 'Reset failed', text: resetData.message || 'Please try again later.' });
        }
      } catch (err) {
        console.error(err);
        Swal.fire({ icon: 'error', title: 'Something went wrong', text: 'Please try again.' });
      }
    });
  });
</script>
</html>