<?php
/**
 * Love Amaiah Cafe – Reusable HTML email template
 *
 * Purpose: Produce Gmail/Outlook‑friendly HTML (tables + inline styles) that
 * matches the site's warm cafe theme. Returns both HTML and plain‑text.
 */

if (!function_exists('la_email_template')) {
    /**
     * Build a branded HTML email.
     *
     * @param array $o {
     *   @type string $title       Title in header and <title>
     *   @type string $preheader   Hidden preview text
     *   @type string $greeting    Leading line (e.g., "Hi there,")
     *   @type string $body        Main HTML paragraphs (already escaped/safe)
     *   @type string $code        Optional big code (OTP)
     *   @type string $cta_text    Optional call‑to‑action button text
     *   @type string $cta_url     CTA link URL
     *   @type string $footer      Small print/footer text
     *   @type string $logo_cid    Optional CID (e.g., 'cid:logoimg')
     * }
     * @return array{html:string,text:string}
     */
    function la_email_template(array $o): array
    {
        // Brand palette (align with app styles)
        $c_bg        = '#F7F2EC'; // parchment background
        $c_card      = '#FFFFFF';
        $c_header    = '#7C573A'; // deep mocha
        $c_accent    = '#A1764E'; // latte accent
        $c_text      = '#21160E';
        $c_muted     = '#6b5a4d';
        $c_code_bg   = '#FFF8F0';
        $c_code_brdr = '#E0C9AC';

        $title     = trim($o['title']     ?? 'Love Amaiah Cafe');
        $preheader = trim($o['preheader'] ?? '');
        $greeting  = trim($o['greeting']  ?? '');
        $bodyHtml  = (string)($o['body']  ?? '');
        $code      = isset($o['code']) ? preg_replace('/\s+/', '', (string)$o['code']) : null;
        $ctaText   = trim($o['cta_text']  ?? '');
        $ctaUrl    = trim($o['cta_url']   ?? '');
        $footer    = trim($o['footer']    ?? 'If you didn’t request this, you can ignore this email.');
        $logoCid   = trim($o['logo_cid']  ?? ''); // e.g. 'cid:logoimg'

        // Basic escapes for text parts used inside HTML attributes/nodes
        $esc = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); };

        $preheaderHtml = $preheader !== ''
            ? '<div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">' . $esc($preheader) . str_repeat(' &nbsp; ', 20) . '</div>'
            : '';

        $logoHtml = $logoCid !== ''
            ? '<img src="' . $esc($logoCid) . '" width="64" height="64" style="display:block;border-radius:50%;border:4px solid #fff;background:#fff;" alt="Love Amaiah Cafe logo"/>'
            : '';

        $codeHtml = '';
        if ($code !== null && $code !== '') {
            $codeHtml = '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:16px 0 8px 0;">'
                . '<tr><td style="background:' . $c_code_bg . ';border:2px dashed ' . $c_code_brdr . ';border-radius:12px;padding:20px 16px;text-align:center;">'
                . '<div style="font-size:32px;line-height:1.2;font-weight:800;letter-spacing:6px;color:' . $c_text . ';font-family:Segoe UI, Arial, sans-serif;">'
                . $esc($code)
                . '</div>'
                . '<div style="font-size:12px;color:' . $c_muted . ';margin-top:8px;">This code expires in 5 minutes.</div>'
                . '</td></tr></table>';
        }

        $ctaHtml = '';
        if ($ctaText !== '' && $ctaUrl !== '') {
            $ctaHtml = '<table role="presentation" cellpadding="0" cellspacing="0" style="margin-top:16px;">'
                . '<tr><td bgcolor="' . $c_accent . '" style="border-radius:9999px;">'
                . '<a href="' . $esc($ctaUrl) . '" style="display:inline-block;padding:12px 20px;font-weight:700;color:#fff;text-decoration:none;font-family:Segoe UI, Arial, sans-serif;">'
                . $esc($ctaText)
                . '</a></td></tr></table>';
        }

        $html = '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
            . '<title>' . $esc($title) . '</title></head>'
            . '<body style="margin:0;background:' . $c_bg . ';font-family:Segoe UI, Arial, sans-serif;color:' . $c_text . ';">'
            . $preheaderHtml
            . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0">'
            . '<tr><td align="center" style="padding:24px;">'
            . '<table role="presentation" width="640" cellpadding="0" cellspacing="0" style="max-width:640px;background:' . $c_card . ';border-radius:16px;box-shadow:0 6px 24px rgba(75,46,14,0.15);overflow:hidden;">'
            . '<tr><td style="background:' . $c_header . ';padding:20px 24px;color:#fff;">'
            . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>'
            . ($logoHtml !== '' ? '<td width="64" valign="middle">' . $logoHtml . '</td>' : '')
            . '<td valign="middle" style="' . ($logoHtml !== '' ? 'padding-left:12px;' : '') . '">'
            . '<div style="font-size:20px;font-weight:800;">Love Amaiah Cafe</div>'
            . '<div style="font-size:13px;opacity:0.9;">' . $esc($title) . '</div>'
            . '</td></tr></table>'
            . '</td></tr>'
            . '<tr><td style="padding:24px 24px 10px;">'
            . ($greeting !== '' ? '<p style="margin:0 0 10px 0;font-size:16px;">' . $esc($greeting) . '</p>' : '')
            . '<div style="font-size:15px;line-height:1.6;">' . $bodyHtml . '</div>'
            . $codeHtml
            . $ctaHtml
            . '</td></tr>'
            . '<tr><td style="padding:16px 24px 24px;">'
            . '<div style="font-size:12px;color:' . $c_muted . ';">' . $esc($footer) . '</div>'
            . '</td></tr>'
            . '</table>'
            . '<div style="font-size:11px;color:' . $c_muted . ';margin-top:12px;">&copy; ' . date('Y') . ' Love Amaiah Cafe</div>'
            . '</td></tr></table></body></html>';

        // Plain text fallback
        $textParts = [];
        if ($greeting !== '') $textParts[] = $greeting;
        $textParts[] = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $bodyHtml));
        if ($code !== null && $code !== '') {
            $textParts[] = 'Code: ' . $code . ' (expires in 5 minutes)';
        }
        if ($ctaText !== '' && $ctaUrl !== '') {
            $textParts[] = $ctaText . ': ' . $ctaUrl;
        }
        $textParts[] = $footer;
        $text = implode("\n\n", array_filter($textParts));

        return ['html' => $html, 'text' => $text];
    }
}

if (!function_exists('la_build_otp_email')) {
    /**
     * Helper for OTP email.
     *
     * @param string $code 6‑digit code
     * @param array  $extras Extra keys accepted by la_email_template
     * @return array{html:string,text:string}
     */
    function la_build_otp_email(string $code, array $extras = []): array
    {
        $intro = '<p>Use the verification code below to continue your request.</p>'
               . '<p>For your security, never share this code with anyone.</p>';

        $payload = array_merge([
            'title'      => 'Verification Code',
            'preheader'  => 'Your verification code is ' . $code . ' (expires in 5 minutes).',
            'greeting'   => 'Hi there,',
            'body'       => $intro,
            'code'       => $code,
            'footer'     => 'If you didn’t request this, you can ignore this email or contact support.',
        ], $extras);

        return la_email_template($payload);
    }
}

if (!function_exists('la_build_password_reset_email')) {
    /**
     * Helper for password reset email (no logo), with friendly copy.
     *
     * @return array{html:string,text:string}
     */
    function la_build_password_reset_email(): array
    {
        $body = '<p>We received a request to reset your password for your Love Amaiah Cafe account.</p>'
              . '<p>Click the button below to create a new password. This link will be valid for a short time.</p>';

        return la_email_template([
            'title'     => 'Password Reset Request',
            'preheader' => 'Create a new password for your Love Amaiah Cafe account.',
            'greeting'  => 'Hi there! 👋',
            'body'      => $body,
            'cta_text'  => 'Reset your password',
            'cta_url'   => '{{RESET_URL}}',
            'footer'    => 'If you didn’t request this, you can safely ignore this email.',
            'logo_cid'  => '' // explicit: no logo requested
        ]);
    }
}

?>
