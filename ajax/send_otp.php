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
$purpose = isset($data['purpose']) ? strtolower(trim((string)$data['purpose'])) : 'generic';
if (!in_array($purpose, ['register','reset','generic'], true)) { $purpose = 'generic'; }

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

// Select copy based on purpose
$siteName = $mailConfig['from_name'] ?? 'Love Amaiah Cafe';
$subject = 'Your verification code';
$actionTitle = 'Email Verification';
$intro = "Use the code below to continue.";
if ($purpose === 'reset') {
    $subject = 'Password reset code';
    $actionTitle = 'Password Reset Request';
    $intro = "We received a request to reset your password. Here's your verification code:";
} elseif ($purpose === 'register') {
    $subject = 'Verify your email';
    $actionTitle = 'Verify your email';
    $intro = 'Use this verification code to complete your registration at ' . $siteName . ':';
}
$mail->Subject = $subject;

// Embed logo if present
$logoPath = realpath(__DIR__ . '/../images/logo.png');
$logoCid = '';
if ($logoPath && file_exists($logoPath)) {
    // The second parameter is CID name (without 'cid:' prefix in PHPMailer)
    $cid = $mail->addEmbeddedImage($logoPath, 'la-logo', 'logo.png');
    // PHPMailer returns boolean; we still know the CID we provided
    $logoCid = 'cid:la-logo';
}

$html = EmailTemplate::otpHtml([
    'code'           => (string)$otp,
    'siteName'       => $siteName,
    'actionTitle'    => $actionTitle,
    'introText'      => $intro,
    'expiresMinutes' => 5,
    'supportEmail'   => $mailConfig['reply_to'] ?? '',
    'brandColor'     => '#7C573A',
    'brandLight'     => '#C4A07A',
    'bgColor'        => '#F7F2EC',
    'textColor'      => '#21160E',
    'logoCid'        => $logoCid,
]);
$text = EmailTemplate::otpText([
    'code'           => (string)$otp,
    'siteName'       => $siteName,
    'actionTitle'    => $actionTitle,
    'introText'      => $intro,
    'expiresMinutes' => 5,
    'supportEmail'   => $mailConfig['reply_to'] ?? '',
]);

$mail->Body    = $html;
$mail->AltBody = $text;

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