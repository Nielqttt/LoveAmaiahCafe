<?php
// Reusable legal links footer block
// Computes a base path for linking to the terms folder, whether included from /all, /Owner, /Employee, /Customer, or /terms

$script = $_SERVER['SCRIPT_NAME'] ?? '';
// If the current script is already under /terms/, links can be relative ('./')
$termsBase = (strpos($script, '/terms/') !== false) ? './' : '../terms/';
?>

<div class="legal-links">
  <a href="<?php echo $termsBase; ?>privacy-notice.php">Privacy Notice</a>
  <a href="<?php echo $termsBase; ?>privacy-notice.php#health">Consumer Health Privacy Notice</a>
  <a href="<?php echo $termsBase; ?>loveamaiah-terms-of-use.php">Terms of Use</a>
  <a href="<?php echo $termsBase; ?>personal-information.php">Do Not Share My Personal Information</a>
  <a href="<?php echo $termsBase; ?>accessibility.php">Accessibility</a>
  <a href="<?php echo $termsBase; ?>cookie-preferences.php">Cookie Preferences</a>
</div>
<div class="copyright">© <?php echo date('Y'); ?> Love Amaiah Cafe. All rights reserved.</div>
