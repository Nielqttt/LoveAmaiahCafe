<?php
// Consent initialization include. Usage: include __DIR__ . '/../includes/consent-init.php';
// Set this to true to show the cookie banner on the page where it's included.
if (!isset($LAConsentShowBanner)) { $LAConsentShowBanner = true; }
?>
<script>
  window.LAConsentShowBanner = <?php echo $LAConsentShowBanner ? 'true' : 'false'; ?>;
</script>
<script defer src="<?php
  // Compute relative path to assets/js/consent.js based on current script location
  // We derive depth by counting slashes and stepping up to project root.
  $script = $_SERVER['SCRIPT_NAME'] ?? '';
  // Try common locations: /all, /Owner, /Employee, /Customer, /terms, /
  $prefix = '';
  if (strpos($script, '/all/') !== false || strpos($script, '/Owner/') !== false || strpos($script, '/Employee/') !== false || strpos($script, '/Customer/') !== false || strpos($script, '/terms/') !== false) {
    $prefix = '..';
  } else {
    $prefix = '.';
  }
  echo htmlspecialchars($prefix . '/assets/js/consent.js', ENT_QUOTES, 'UTF-8');
?>"></script>
