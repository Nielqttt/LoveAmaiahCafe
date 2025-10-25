<?php
// Minimal ESC/POS over raw TCP (port 9100) for LAN receipt printers.
// No external dependencies; provides a few helpers for common tasks.

require_once(__DIR__ . '/printer_config.php');

class EscposLanPrinter {
    private string $ip;
    private int $port;
    private $socket = null;

    public function __construct(?string $ip = null, ?int $port = null) {
        $this->ip = $ip ?: PrinterConfig::ip();
        $this->port = $port ?: PrinterConfig::port();
    }

    public function connect(int $timeoutSeconds = 3): void {
        $errNo = 0; $errStr = '';
        $ctx = stream_context_create([
            'socket' => ['tcp_nodelay' => true],
        ]);
        $this->socket = @stream_socket_client(
            'tcp://' . $this->ip . ':' . $this->port,
            $errNo,
            $errStr,
            $timeoutSeconds,
            STREAM_CLIENT_CONNECT,
            $ctx
        );
        if (!$this->socket) {
            throw new Exception("Printer connection failed: $errStr ($errNo)");
        }
        stream_set_timeout($this->socket, 2);
        $this->write("\x1B@\x1B\x52\x09"); // init + set international (Philippines falls under 'Philippines/USA' cp437-ish)
    }

    public function close(): void {
        if ($this->socket) {
            @fclose($this->socket);
            $this->socket = null;
        }
    }

    public function write(string $data): void {
        if (!$this->socket) { throw new Exception('Printer not connected'); }
        $len = strlen($data);
        $written = 0;
        while ($written < $len) {
            $n = @fwrite($this->socket, substr($data, $written));
            if ($n === false || $n === 0) { throw new Exception('Write to printer failed'); }
            $written += $n;
        }
    }

    public function text(string $text): void {
        // Convert to desired encoding if needed
        $enc = PrinterConfig::encoding();
        if (strtoupper($enc) !== 'UTF-8') {
            $text = @iconv('UTF-8', $enc . '//TRANSLIT', $text) ?: $text;
        }
        $this->write($text);
    }

    public function lf(int $n = 1): void { $this->write(str_repeat("\n", max(1, $n))); }
    public function align(string $mode = 'left'): void {
        $map = ['left' => 0, 'center' => 1, 'right' => 2];
        $v = $map[strtolower($mode)] ?? 0;
        $this->write("\x1Ba" . chr($v));
    }
    public function bold(bool $on): void { $this->write("\x1B" . ($on ? "E\x01" : "E\x00")); }
    public function cut(bool $partial = true): void { $this->write("\x1D" . "V" . ($partial ? "\x41\x03" : "\x00")); }
    public function drawerKick(): void { $this->write("\x1B\x70\x00\x19\xFA"); }
}

// Helper: build a simple 58mm text receipt with ~32 chars width.
function la_build_text_receipt(array $data): string {
    $width = 32;
    $lines = [];
    $brand = $data['brand'] ?? 'Love Amaiah Cafe';
    $subtitle = $data['subtitle'] ?? '';
    $divider = str_repeat('-', $width);
    $lines[] = la_center($brand, $width);
    if ($subtitle) { $lines[] = la_center($subtitle, $width); }
    $lines[] = $divider;
    if (!empty($data['OrderID'])) { $lines[] = 'Order: #' . $data['OrderID']; }
    if (!empty($data['ReferenceNo'])) { $lines[] = 'Ref: ' . $data['ReferenceNo']; }
    if (!empty($data['OrderDate'])) { $lines[] = 'Date: ' . $data['OrderDate']; }
    if (!empty($data['PaymentMethod'])) { $lines[] = 'Pay: ' . $data['PaymentMethod']; }
    $lines[] = $divider;
    foreach (($data['Items'] ?? []) as $it) {
        $name = (string)($it['name'] ?? '');
        $qty  = (int)($it['qty'] ?? 0);
        $unit = (float)($it['unit'] ?? 0);
        $subtotal = (float)($it['subtotal'] ?? ($qty * $unit));
        $priceStr = la_peso($subtotal);
        foreach (la_split_lines($name, $width) as $i => $nl) {
            if ($i === 0) {
                $left = la_pad_right($nl, $width - strlen($priceStr));
                $lines[] = $left . $priceStr;
            } else {
                $lines[] = la_pad_right($nl, $width);
            }
        }
        $lines[] = la_pad_right('  ' . $qty . ' x ' . la_peso($unit), $width);
    }
    $lines[] = $divider;
    $totalStr = la_peso((float)($data['TotalAmount'] ?? 0));
    $lines[] = la_pad_right('TOTAL', $width - strlen($totalStr)) . $totalStr;
    $lines[] = $divider;
    $lines[] = la_center('Thank you for your order!', $width);
    if (!empty($data['footer'])) {
        foreach (preg_split("/\r?\n/", (string)$data['footer']) as $f) {
            $lines[] = la_center($f, $width);
        }
    }
    $lines[] = '';
    $lines[] = '';
    return implode("\n", $lines) . "\n";
}

function la_split_lines(string $s, int $w): array {
    $out = [];
    while ($s !== '') { $out[] = substr($s, 0, $w); $s = substr($s, $w); }
    return $out ?: [''];
}
function la_center(string $s, int $w): string {
    if (strlen($s) >= $w) { return substr($s, 0, $w); }
    $pad = intdiv($w - strlen($s), 2);
    return str_repeat(' ', $pad) . $s;
}
function la_pad_right(string $s, int $w): string { return strlen($s) >= $w ? substr($s, 0, $w) : $s . str_repeat(' ', $w - strlen($s)); }
function la_peso(float $n): string { return '₱' . number_format($n, 2, '.', ','); }

?>
