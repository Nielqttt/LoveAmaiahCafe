<?php
// QZ Tray security configuration
// Place your certificate and private key under classes/secure/ and keep them OUT of git.
// - Public cert (X.509 PEM): classes/secure/qz-public.crt
// - Private key (PKCS#8 or RSA PEM): classes/secure/qz-private.pem (optionally encrypted)

class QZConfig {
    // Absolute paths for safety
    public static function publicCertPath(): string {
        return __DIR__ . DIRECTORY_SEPARATOR . 'secure' . DIRECTORY_SEPARATOR . 'qz-public.crt';
    }

    public static function privateKeyPath(): string {
        return __DIR__ . DIRECTORY_SEPARATOR . 'secure' . DIRECTORY_SEPARATOR . 'qz-private.pem';
    }

    // Optional private key passphrase; prefer to load from env if set
    public static function privateKeyPassphrase(): ?string {
        $pass = getenv('QZ_PRIVATE_KEY_PASSPHRASE');
        return $pass === false ? null : $pass;
    }
}
?>