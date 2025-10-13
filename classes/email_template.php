<?php
// Centralized HTML Email Templates for Love Amaiah Cafe
// Use inline styles and table-based layout for broad email-client support.

class EmailTemplate
{
		/**
		 * Build HTML for a brand-styled OTP / verification code email.
		 * Options (array keys):
		 * - code: string (required) – the 6-digit code
		 * - siteName: string – default 'Love Amaiah Cafe'
		 * - actionTitle: string – default 'Password Reset Request'
		 * - introText: string – short paragraph above the code
		 * - expiresMinutes: int – default 5
		 * - supportEmail: string – optional contact email
		 * - brandColor: string – hex e.g. '#7C573A' (header/bg and accents)
		 * - brandLight: string – hex e.g. '#C4A07A' (borders/lighter ui)
		 * - bgColor: string – email background, default '#F7F2EC'
		 * - textColor: string – default '#21160E'
		 * - logoCid: string – e.g. 'cid:la-logo' if embedded with PHPMailer
		 * - logoUrl: string – absolute https URL fallback
		 */
		public static function otpHtml(array $o): string
		{
				$siteName      = $o['siteName']      ?? 'Love Amaiah Cafe';
				$actionTitle   = $o['actionTitle']   ?? 'Password Reset Request';
				$introText     = $o['introText']     ?? "We received a request to reset your password. Here's your verification code:";
				$code          = isset($o['code']) ? trim((string)$o['code']) : '';
				$expires       = (int)($o['expiresMinutes'] ?? 5);
				$supportEmail  = $o['supportEmail']  ?? '';

				// Brand palette aligned with the site's coffee theme
				$brandColor = $o['brandColor'] ?? '#7C573A';
				$brandLight = $o['brandLight'] ?? '#C4A07A';
				$bgColor    = $o['bgColor']    ?? '#F7F2EC';
				$textColor  = $o['textColor']  ?? '#21160E';

				$logoSrc = '';
				if (!empty($o['logoCid'])) {
						$logoSrc = $o['logoCid'];
				} elseif (!empty($o['logoUrl'])) {
						$logoSrc = $o['logoUrl'];
				}

				// Fallback code guard
				if ($code === '') {
						$code = '••••••';
				}

				// Basic sanitize (emails are mostly trusted content we generate)
				$esc = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

				$logoHtml = $logoSrc
						? '<img alt="'.$esc($siteName).'" src="'.$esc($logoSrc).'" width="48" height="48" style="display:block;border:0;outline:none;text-decoration:none;border-radius:50%;background:#ffffff;">'
						: '';

				// Email structure uses nested tables and inline CSS for better client support
				return '<!doctype html>
<html>
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title>'.$esc($siteName).' Verification Code</title>
	</head>
	<body style="margin:0;padding:0;background:'.$bgColor.';color:'.$textColor.';">
		<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background:'.$bgColor.';">
			<tr>
				<td align="center" style="padding:24px 16px;">
					<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width:640px;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(75,46,14,0.08);">
						<tr>
							<td align="left" style="background:'.$brandColor.';padding:20px 24px;">
								<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
									<tr>
										<td valign="middle" width="56" style="padding-right:12px;">'.$logoHtml.'</td>
										<td valign="middle">
											<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;font-size:20px;line-height:1.2;font-weight:800;color:#ffffff;">'.$esc($siteName).'</div>
											<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;font-size:14px;color:#F5EDE6;opacity:0.95;margin-top:2px;">'.$esc($actionTitle).'</div>
										</td>
									</tr>
								</table>
							</td>
						</tr>

						<tr>
							<td style="padding:28px 24px 12px 24px;">
								<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;font-size:14px;color:'.$textColor.';">'.$esc($introText).'</div>
							</td>
						</tr>

						<tr>
							<td align="center" style="padding:8px 24px 20px 24px;">
								<div style="display:inline-block;border:2px dashed '.$brandLight.';border-radius:12px;background:#FFF7F0;padding:18px 20px;">
									<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;font-size:36px;line-height:1;font-weight:800;letter-spacing:8px;color:'.$brandColor.';">'.$esc($code).'</div>
								</div>
								<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;font-size:12px;color:#6B5B4A;margin-top:10px;">This code expires in '.($expires > 0 ? $esc($expires) : 'a few').' minute'.($expires === 1 ? '' : 's').'.</div>
							</td>
						</tr>

						<tr>
							<td style="padding:0 24px 8px 24px;">
								<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;font-size:13px;color:#3E2B1C;background:#FFF2E1;border:1px solid '.$brandLight.';border-radius:10px;padding:12px 14px;">
									<strong style="display:block;margin-bottom:4px;color:'.$brandColor.';">Security notice</strong>
									If you did not request this, you can safely ignore this email. Your password will remain unchanged.
								</div>
							</td>
						</tr>

						<tr>
							<td style="padding:8px 24px 24px 24px;">
								<div style="font-family:Segoe UI,Arial,Helvetica,sans-serif;font-size:12px;color:#7A6B5B;line-height:1.5;">
									Need help?'.($supportEmail ? ' Contact us at <a href="mailto:'.$esc($supportEmail).'" style="color:'.$brandColor.';text-decoration:underline;">'.$esc($supportEmail).'</a>.' : '').'
								</div>
							</td>
						</tr>

					</table>
				</td>
			</tr>
		</table>
	</body>
</html>';
		}

		/** Build a minimal plain-text version for clients that do not render HTML */
		public static function otpText(array $o): string
		{
				$siteName     = $o['siteName']     ?? 'Love Amaiah Cafe';
				$actionTitle  = $o['actionTitle']  ?? 'Password Reset Request';
				$introText    = $o['introText']    ?? "We received a request to reset your password. Here's your verification code:";
				$code         = isset($o['code']) ? trim((string)$o['code']) : '';
				$expires      = (int)($o['expiresMinutes'] ?? 5);
				$supportEmail = $o['supportEmail'] ?? '';

				$lines = [];
				$lines[] = $siteName.' — '.$actionTitle;
				$lines[] = '';
				$lines[] = $introText;
				$lines[] = 'Code: '.$code;
				$lines[] = 'This code expires in '.($expires > 0 ? $expires : 'a few').' minute'.($expires === 1 ? '' : 's').'.';
				$lines[] = '';
				$lines[] = 'Security notice: If you did not request this, ignore this email. Your password will remain unchanged.';
				if ($supportEmail) {
						$lines[] = 'Help: '.$supportEmail;
				}
				return implode("\n", $lines);
		}
}

?>

