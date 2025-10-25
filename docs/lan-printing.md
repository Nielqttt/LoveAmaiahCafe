# LAN auto-printing (ESC/POS over TCP 9100)

This project now includes optional server-side printing to a LAN ESC/POS receipt printer (e.g., GOOJPRT / generic 58mm) that exposes raw TCP on port 9100.

Works from iPad/Safari because the iPad does NOT talk to the printer; your PHP server does.

## What was added

- classes/printer_config.php — set your printer IP/port here (or via env `LA_PRINTER_IP`, `LA_PRINTER_PORT`).
- classes/escpos_lan_printer.php — minimal TCP 9100 ESC/POS client and text receipt builder.
- ajax/print_receipt_lan.php — endpoint that prints a receipt for a given `order_id`.
- assets/js/print-receipt.js — optional call to the LAN endpoint when `window.LA_USE_LAN_PRINTER = true`.

No existing print paths were removed (QZ Tray, RawBT, browser print remain).

## Setup

1. Connect a LAN/Ethernet or Wi‑Fi ESC/POS printer that supports raw TCP printing on port 9100.
2. Find its IP address (e.g., from the printer test page or router DHCP lease).
3. Configure the app:
   - Edit `classes/printer_config.php` and set the IP `return '192.168.1.50';` (or set env `LA_PRINTER_IP`).
   - Port defaults to `9100`. Override via env `LA_PRINTER_PORT` if needed.
4. In your page where you trigger printing, set the flag before loading `assets/js/print-receipt.js` or before calling `LA_onOrderCompleted`:

```html
<script>
  window.LA_USE_LAN_PRINTER = true; // enable server-side LAN printing
</script>
```

Now, when an order completes, the browser posts `order_id` to `ajax/print_receipt_lan.php`. The PHP server formats a 58mm text receipt and sends it directly to the printer.

## Notes

- This is a minimal ESC/POS implementation. If you need barcodes, logos, or QR codes, consider using `mike42/escpos-php` in the future.
- The printer must be reachable from the machine running Apache/PHP (XAMPP). If you’re developing on Windows, ensure Windows Firewall allows outbound TCP to the printer IP:9100.
- Character encoding defaults to UTF‑8 in `PrinterConfig::encoding()`. If your printer shows garbled symbols, change it to a code page your printer supports (e.g., `CP437`, `CP936`, etc.).
- Cash drawer pulse is sent safely; if no drawer is attached, it’s ignored.
- iPad/Safari users don’t need any special app for this flow.

## Troubleshooting

- Nothing prints: Verify the printer IP and that you can `ping` it from the server. Also try `telnet <ip> 9100`.
- Random characters: change `PrinterConfig::encoding()`.
- Multiple copies or cut behavior: adjust `cut(true/false)` and the extra `lf()` feeds in the endpoint.
