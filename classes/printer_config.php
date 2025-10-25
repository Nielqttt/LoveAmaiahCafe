<?php
// LAN ESC/POS printer configuration
// Set your receipt printer's IP address and port (usually 9100 for raw TCP printing)
// IMPORTANT: Do NOT commit real IPs for public repos; this is a placeholder.

class PrinterConfig {
    // Printer IPv4 address or hostname reachable from the PHP server (NOT the iPad)
    public static function ip(): string {
        // Example: return '192.168.1.50';
        $ip = getenv('LA_PRINTER_IP');
        return $ip !== false && $ip !== '' ? $ip : '192.168.1.50';
    }

    // Raw TCP port for ESC/POS printing (most LAN printers use 9100)
    public static function port(): int {
        $port = getenv('LA_PRINTER_PORT');
        if ($port !== false && (int)$port > 0) { return (int)$port; }
        return 9100;
    }

    // Character encoding to send (UTF-8 often works; some printers expect cp437/936 etc.)
    public static function encoding(): string { return 'UTF-8'; }
}

?>
