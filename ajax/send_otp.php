<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../Mailer/class.phpmailer.php';
require_once __DIR__ . '/../Mailer/class.smtp.php';
require_once __DIR__ . '/../classes/email_template.php';
// Centralized mail config (branding + SMTP)
$mailConfig = require __DIR__ . '/../classes/mail_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']); exit;
}

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);
$email = isset($data['email']) ? trim((string)$data['email']) : ($_SESSION['mail'] ?? '');
$purpose = isset($data['purpose']) ? strtolower(trim((string)$data['purpose'])) : 'registration'; // 'registration' | 'password-reset'

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Enter a valid email.']); exit;
}

// Throttle resend (30s)
$now = time();
$cooldown = 30;
if (!empty($_SESSION['last_otp_sent_at']) && ($now - $_SESSION['last_otp_sent_at']) < $cooldown) {
    $remain = $cooldown - ($now - (int)$_SESSION['last_otp_sent_at']);
    echo json_encode(['success' => false, 'message' => "Please wait {$remain}s before requesting a new code.", 'cooldown' => $remain]); exit;
}

// Generate and store OTP
$otp = random_int(100000, 999999);
$_SESSION['otp'] = (string)$otp;
$_SESSION['mail'] = $email;
$_SESSION['otp_expires'] = $now + 5 * 60; // 5 minutes
$_SESSION['otp_attempts'] = 0;
unset($_SESSION['otp_locked_until']);

$mail = new PHPMailer;
$mail->CharSet    = 'UTF-8';
$mail->isSMTP();
$mail->Host       = $mailConfig['smtp']['host'];
$mail->Port       = (int)$mailConfig['smtp']['port'];
$mail->SMTPAuth   = true;
$mail->SMTPSecure = $mailConfig['smtp']['secure'];

// SMTP Debug for troubleshooting
$mail->SMTPDebug = (int)$mailConfig['smtp']['debug'];
$mail->Debugoutput = function ($str, $level) {
    error_log("PHPMailer [$level]: $str");
};
$mail->Timeout = (int)$mailConfig['smtp']['timeout'];

// Consistent SMTPOptions with register.php
$mail->SMTPOptions = [
    'ssl' => $mailConfig['smtp']['ssl_options'],
];

$mail->Username = $mailConfig['smtp']['username'];
$mail->Password = $mailConfig['smtp']['password'];

$mail->setFrom($mailConfig['from_email'], $mailConfig['from_name']);
$mail->addReplyTo($mailConfig['reply_to'], $mailConfig['from_name']);
$mail->addAddress($email);

// Prepare content per requested copy; omit logo image
$mail->isHTML(true);
if ($purpose === 'password-reset') {
    // Different subject and body for password reset; still sending OTP code
    $subject = "☕Love Amaiah Café Password Reset Code";
    $greeting = 'Hi there!👋';
    $body = '<p>Here’s your One-Time Password (OTP) to reset your password with Love Amaiah Cafe:</p>'
        . '<p>Please enter this code within 5 minutes to continue.</p>'
        . '<p>If you didn’t request this, please ignore this email.</p>'
        . '<p>With love,<br>Love Amaiah Cafe</p>';
    $built = la_email_template([
        'title'     => 'Password Reset Code',
        'preheader' => 'Your OTP code is ' . $otp . '. It expires in 5 minutes.',
        'greeting'  => $greeting,
        'body'      => $body,
        'footer'    => 'If you didn’t request this, you can ignore this email or contact support.',
        'logo_cid'  => '',
        'logo_text' => 'LA',
        'show_logo' => false
    ]);
} else {
    // Registration
    $subject = "☕Love Amaiah Café OTP Code";
    $greeting = 'Hi there!👋';
    $body = '<p>Here’s your One-Time Password (OTP) to verify your account with Love Amaiah Cafe:</p>'
        . '<p>Please enter this code within 5 minutes to complete your verification.</p>'
        . '<p>If you didn’t request this, please ignore this email.</p>'
        . '<p>With love,<br>Love Amaiah Cafe</p>';
    $built = la_email_template([
        'title'     => 'Verification Code',
        'preheader' => 'Your OTP code is ' . $otp . '. It expires in 5 minutes.',
        'greeting'  => $greeting,
        'body'      => $body,
        'footer'    => 'If you didn’t request this, you can ignore this email or contact support.',
        'logo_cid'  => '',
        'logo_text' => 'LA',
        'show_logo' => false
    ]);
}

$mail->Subject = $subject;
$mail->Body    = $built['html'];
$mail->AltBody = $built['text'];

if ($mail->send()) {
    $_SESSION['last_otp_sent_at'] = $now;
    echo json_encode([
        'success'     => true,
        'message'     => 'Verification code sent.',
        'email'       => $email,
        'expires_at'  => $_SESSION['otp_expires'],
        'cooldown'    => $cooldown
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to send verification email. Please try again later.',
        'error'   => $mail->ErrorInfo
    ]);
}