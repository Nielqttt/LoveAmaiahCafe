
<?php
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

  $userID = $con->signupCustomer($firstname, $lastname, $phonenum, $email, $username, $password);

  if ($userID) {
    $sweetAlertConfig = "
    <script>
    Swal.fire({
      icon: 'success',
      title: 'Registration Successful',
      text: 'Your account has been created successfully',
      confirmButtonText: 'OK'
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = 'login.php';
      }
    });
    </script>";
  } else {
    $sweetAlertConfig = "
    <script>
    Swal.fire({
      icon: 'error',
      title: 'Registration Failed',
      text: 'Please try again later',
      confirmButtonText: 'OK'
    });
    </script>";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Register - Amaiah</title>
  <link rel="stylesheet" href="../bootstrap-5.3.3-dist/css/bootstrap.css">
  <link rel="stylesheet" href="../package/dist/sweetalert2.css">
</head>
<body>

<div class="container d-flex justify-content-center align-items-center" style="height: 100vh;">
  <div class="login-container">
    <div class="logo">
      <img src="../images/logo.png" alt="Amaiah logo"/>
    </div>
    <h2 class="text-center mb-4" style="margin-top: 30px" >Register</h2>
    <form id="registrationForm" method="POST" action="">
      <div class="login-box">
        <div class="row mb-3">
          <div class="col-md-6">
            <input type="text" name="firstname" id="firstname" class="form-control" placeholder="Enter your first name" required>
            <div class="invalid-feedback">First name is required.</div>
          </div>
          <div class="col-md-6">
            <input type="text" name="lastname" id="lastname" class="form-control" placeholder="Enter your last name" required>
            <div class="invalid-feedback">Last name is required.</div>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-12">
            <input type="email" name="email" id="email" class="form-control" placeholder="Enter your email" required>
            <div class="invalid-feedback">Email is required.</div>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-md-6">
            <input type="text" name="username" id="username" class="form-control" placeholder="Enter your username" required>
            <div class="invalid-feedback">Username is required.</div>
          </div>
          <div class="col-md-6">
            <input type="tel" name="phonenum" id="phonenum" class="form-control" placeholder="Enter your phone number" pattern="^09\d{9}$" required>
            <div class="invalid-feedback">Enter a valid Philippine number starting with 09.</div>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-12">
            <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
            <div class="invalid-feedback">Password must be at least 6 characters long, include one uppercase letter, one number, and one special character.</div>
          </div>
        </div>
      </div>
      <button type="submit" id="registerButton" name="register" class="btn-primary">Register</button>
      <div class="login-link">
        Already have an account? <a href="login.php">Login</a>
      </div>
    </form>
  </div>
</div>
<script src="../bootstrap-5.3.3-dist/js/bootstrap.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php echo $sweetAlertConfig; ?>

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

  // Form submission validation
  document.getElementById('registrationForm').addEventListener('submit', function (e) {
    let isValid = true;
    [firstname, lastname, username, email, password, phonenum].forEach(field => {
      if (!field.classList.contains('is-valid')) {
        field.classList.add('is-invalid');
        isValid = false;
      }
    });
    if (!isValid) {
      e.preventDefault();
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

</style>
</body>
</html>