<?php
session_start();
header('Content-Type: application/json');
@ini_set('display_errors', '0');
@ini_set('log_errors', '1');
ob_start();

require_once __DIR__ . '/../Mailer/class.phpmailer.php';
require_once __DIR__ . '/../Mailer/class.smtp.php';
require_once __DIR__ . '/../classes/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $resp = json_encode(['success' => false, 'message' => 'Method not allowed.']);
    if (ob_get_length()) { ob_clean(); }
    echo $resp; exit;
}

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

// Expect full form in this call, so verify accepts only OTP later
$firstname = isset($data['firstname']) ? trim((string)$data['firstname']) : '';
$lastname  = isset($data['lastname']) ? trim((string)$data['lastname']) : '';
$email     = isset($data['email']) ? trim((string)$data['email']) : '';
$username  = isset($data['username']) ? trim((string)$data['username']) : '';
$phonenum  = isset($data['phonenum']) ? trim((string)$data['phonenum']) : '';
$password  = isset($data['password']) ? (string)$data['password'] : '';

if ($firstname === '' || $lastname === '' || $username === '' || $phonenum === '' || $password === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $resp = json_encode(['success' => false, 'message' => 'Please complete the form with valid information.']);
    if (ob_get_length()) { ob_clean(); }
    echo $resp; exit;
}

// Uniqueness checks
$db = new database();
if ($db->isEmailExists($email)) { $resp = json_encode(['success' => false, 'message' => 'Email is already registered.']); if (ob_get_length()) { ob_clean(); } echo $resp; exit; }
if ($db->isUsernameExists($username)) { $resp = json_encode(['success' => false, 'message' => 'Username is already taken.']); if (ob_get_length()) { ob_clean(); } echo $resp; exit; }

// Throttle resend (30s)
$now = time();
$cooldown = 30;
if (!empty($_SESSION['last_otp_sent_at']) && ($now - (int)$_SESSION['last_otp_sent_at']) < $cooldown) {
    $remain = $cooldown - ($now - (int)$_SESSION['last_otp_sent_at']);
    $resp = json_encode(['success' => false, 'message' => "Please wait {$remain}s before requesting a new code.", 'cooldown' => $remain]);
    if (ob_get_length()) { ob_clean(); }
    echo $resp; exit;
}

// Generate OTP but only store after successful send
$otp = random_int(100000, 999999);

$mail = new PHPMailer;
$mail->CharSet    = 'UTF-8';
$mail->isSMTP();
$mail->Host       = 'smtp.gmail.com';
$mail->Port       = 587;
$mail->SMTPAuth   = true;
$mail->SMTPSecure = 'tls';
$mail->SMTPDebug  = 0;
$mail->Debugoutput = function ($str, $level) { error_log("PHPMailer [$level]: $str"); };
$mail->Timeout = 20;
$mail->SMTPOptions = [
    'ssl' => [
        'verify_peer'       => false,
        'verify_peer_name'  => false,
        'allow_self_signed' => true,
        'crypto_method'     => STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT,
    ],
];

// NOTE: Credentials should be moved to env/config in production
$mail->Username = 'ahmadpaguta2005@gmail.com';
$mail->Password = 'unwr kdad ejcd rysq';

$mail->setFrom($mail->Username, 'Cups & Cuddles');
$mail->addReplyTo('no-reply@cupscuddles.local', 'Cups & Cuddles');
$mail->addAddress($email);

$mail->isHTML(true);
$mail->Subject = "Your verification code";
$mail->Body    = "<p>Your OTP code is <b>{$otp}</b></p><p>This code expires in 5 minutes.</p>";
$mail->AltBody = "Your OTP code is {$otp}. It expires in 5 minutes.";

if ($mail->send()) {
    // Store OTP and pending registration details
    $_SESSION['otp'] = (string)$otp;
    $_SESSION['mail'] = $email;
    $_SESSION['otp_expires'] = $now + 5 * 60; // 5 minutes
    $_SESSION['otp_attempts'] = 0;
    unset($_SESSION['otp_locked_until']);
    $_SESSION['last_otp_sent_at'] = $now;
    $_SESSION['pending_registration'] = [
        'firstname' => $firstname,
        'lastname'  => $lastname,
        'phonenum'  => $phonenum,
        'email'     => $email,
        'username'  => $username,
        'password'  => password_hash($password, PASSWORD_BCRYPT),
    ];

    $resp = json_encode([
        'success'     => true,
        'message'     => 'Verification code sent.',
        'email'       => $email,
        'expires_at'  => $_SESSION['otp_expires'],
        'cooldown'    => $cooldown
    ]);
    if (ob_get_length()) { ob_clean(); }
    echo $resp; exit;
} else {
    $resp = json_encode([
        'success' => false,
        'message' => 'Failed to send verification email. Please try again later.'
    ]);
    if (ob_get_length()) { ob_clean(); }
    echo $resp; exit;
}