<?php
// Centralized mail configuration for Love Amaiah Cafe
// You can change these values to your own SMTP or use environment variables.

// If a local override file exists, load it instead (keep secrets out of git)
if (file_exists(__DIR__ . '/mail_config.local.php')) {
    return require __DIR__ . '/mail_config.local.php';
}

return [
    // Branding
    'from_email' => getenv('MAIL_FROM_EMAIL') ?: 'welcomehome@loveamaiahcafe.shop',
    'from_name'  => getenv('MAIL_FROM_NAME') ?: 'Love Amaiah Cafe',
    'reply_to'   => getenv('MAIL_REPLY_TO') ?: 'welcomehome@loveamaiahcafe.shop',

    // Optional brand theme (used by email templates)
    'brand' => [
        // Path to a local logo to embed in emails (CID). Leave null to skip.
        'logo_path'    => file_exists(__DIR__ . '/../images/logo.png') ? (__DIR__ . '/../images/logo.png') : null,
        // Colors aligned with the site's look-and-feel (coffee tones)
        'primary_color' => getenv('MAIL_BRAND_PRIMARY') ?: '#7C573A', // deep coffee
        'accent_color'  => getenv('MAIL_BRAND_ACCENT')  ?: '#A1764E', // warm brown
        'bg_color'      => getenv('MAIL_BRAND_BG')      ?: '#F7F2EC', // latte background
        'text_color'    => getenv('MAIL_BRAND_TEXT')    ?: '#21160E', // dark text
    ],

    // SMTP server (use your provider or Gmail SMTP with App Password)
    'smtp' => [
        'host'       => getenv('MAIL_SMTP_HOST') ?: 'smtp.hostinger.com',
        'port'       => (int)(getenv('MAIL_SMTP_PORT') ?: 587),
        'username'   => getenv('MAIL_SMTP_USER') ?: 'welcomehome@loveamaiahcafe.shop',
        'password'   => getenv('MAIL_SMTP_PASS') ?: 'home@Amaiah23',
        'secure'     => getenv('MAIL_SMTP_SECURE') ?: 'tls', // 'tls' or 'ssl'
        'timeout'    => (int)(getenv('MAIL_SMTP_TIMEOUT') ?: 20),
        'debug'      => (int)(getenv('MAIL_SMTP_DEBUG') ?: 0), // 0 in production
        'ssl_options' => [
            'verify_peer'       => false,
            'verify_peer_name'  => false,
            'allow_self_signed' => true,
            'crypto_method'     => defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT') ? STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT : 0,
        ],
    ],
];
