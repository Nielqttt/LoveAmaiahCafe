<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../Mailer/class.phpmailer.php';
require_once __DIR__ . '/../Mailer/class.smtp.php';
// Centralized mail config (branding + SMTP)
$mailConfig = require __DIR__ . '/../classes/mail_config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']); exit;
}

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);
$email = isset($data['email']) ? trim((string)$data['email']) : ($_SESSION['mail'] ?? '');

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

$mail->isHTML(true);
$mail->Subject = "Your verification code";
$mail->Body    = "<p>Your OTP code is <b>{$otp}</b></p><p>This code expires in 5 minutes.</p>";
$mail->AltBody = "Your OTP code is {$otp}. It expires in 5 minutes.";

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