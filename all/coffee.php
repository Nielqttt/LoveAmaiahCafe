<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <?php
    // SEO helpers
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'loveamaiahcafe';
    // Normalize homepage path to root for canonical
    $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
    if ($requestUri === '/all/coffee' || $requestUri === '/all/coffee.php') { $requestUri = '/'; }
    $origin = $scheme . '://' . $host;
    $canonical = rtrim($origin, '/') . ($requestUri === '/' ? '/' : $requestUri);
    $siteName = 'Love Amaiah Cafe';
    $pageTitle = 'Love Amaiah Cafe — Coffee & Espresso Drinks | Order Pickup';
    $pageDesc = 'Discover Love Amaiah Cafe’s signature coffee and espresso drinks. Browse favorites like Affogato, Caramel Cloud Latte, Cinnamon Macchiato, and Iced Brownie Espresso. Order online and pick up in-store.';
    $ogImage = $origin . '/images/mainpage_coffee.png';
    $logoUrl = $origin . '/images/logo.png';
  ?>
  <?php $LAConsentShowBanner = true; include __DIR__ . '/../includes/consent-init.php'; ?>
  <?php
    // Load featured Signature drinks data (descriptions and images)
    require_once __DIR__ . '/../classes/database.php';
    $db = new database();
    $allProducts = [];
    try { $allProducts = $db->getAllProductsWithPrice(); } catch (Exception $e) { $allProducts = []; }
    // Map products by name for quick lookup
    $byName = [];
    foreach ($allProducts as $p) { $byName[trim(strtolower($p['ProductName']))] = $p; }
    // Featured order (fallback)
    $featuredOrder = [
      'affogato',
      'caramel cloud latte',
      'cinnamon macchiato',
      'iced brownie espresso'
    ];
    // Try to pull signatures category if present
    $signatures = [];
    foreach ($allProducts as $p) {
      $cat = trim(strtolower($p['ProductCategory'] ?? ''));
      if (strpos($cat, 'signature') !== false) { $signatures[] = $p; }
    }
    // Build featured list (preserve order defined above when possible)
    $featured = [];
    if (!empty($signatures)) {
      // Include ALL signature items; order: preferred names first, then the rest
      $preferred = [];
      foreach ($featuredOrder as $fn) { foreach ($signatures as $p) { if (trim(strtolower($p['ProductName'])) === $fn) { $preferred[] = $p; } } }
      $rest = array_values(array_filter($signatures, function($p) use ($preferred){ return !in_array($p, $preferred, true); }));
      $featured = array_merge($preferred, $rest);
    } else {
      // Fallback by name mapping
      foreach ($featuredOrder as $fn) { if (isset($byName[$fn])) { $featured[] = $byName[$fn]; } }
    }
    // As final fallback if DB empty, synthesize minimal entries with names only
    if (empty($featured)) {
      foreach ($featuredOrder as $nameOnly) { $featured[] = ['ProductName' => ucwords($nameOnly), 'Description' => '', 'ImagePath' => null]; }
    }

    // Helper to build image src (prefer images/signatures/*.png)
    function la_slug($s){
      $s = strtolower($s);
      $s = preg_replace('/[^a-z0-9]+/','_', $s);
      return trim($s, '_');
    }
    function la_feature_img_src($name, $dbImagePath){
      $slug = la_slug($name);
      // handle common alias filename variations
      $aliases = [
        'iced_brownie_espresso' => ['iced_shaken_brownie']
      ];
      $candidates = array_unique(array_merge([$slug], $aliases[$slug] ?? []));
      foreach ($candidates as $s) {
        $sigFs = __DIR__ . '/../images/signatures/' . $s . '.png';
        if (is_file($sigFs)) { return '../images/signatures/' . $s . '.png'; }
      }
      // fallback to previous images folder naming
      foreach ($candidates as $s) {
        $imgFs = __DIR__ . '/../images/' . $s . '.png';
        if (is_file($imgFs)) { return '../images/' . $s . '.png'; }
      }
      // fallback to DB image (uploads)
      if (!empty($dbImagePath)) { return '../uploads/' . ltrim($dbImagePath, '/'); }
      // placeholder
      return 'https://placehold.co/600x400/png?text=' . rawurlencode($name);
    }
  ?>
  <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
  <link rel="icon" href="../images/logo.png" type="image/png" />
  <script>
    // Example: gate analytics by consent
    document.addEventListener('la:consent-updated', (e) => {
      if (window.LAConsent?.allow('analytics')) {
        // load your analytics here if not yet loaded
        if (!window._gaLoaded) {
          window._gaLoaded = true;
          const s = document.createElement('script'); s.async = true; s.src = 'https://www.googletagmanager.com/gtag/js?id=G-XXXXXXX'; document.head.appendChild(s);
          window.dataLayer = window.dataLayer || [];
          function gtag(){dataLayer.push(arguments);} gtag('js', new Date()); gtag('config', 'G-XXXXXXX');
        }
      }
    });
    // On first load, if consent exists and allows analytics, load immediately
    document.addEventListener('DOMContentLoaded', () => {
      if (window.LAConsent?.allow('analytics')) {
        const ev = new Event('la:consent-updated'); document.dispatchEvent(ev);
      }
    });
  </script>
  <meta name="description" content="<?php echo htmlspecialchars($pageDesc, ENT_QUOTES, 'UTF-8'); ?>" />
  <meta name="keywords" content="Love Amaiah Cafe, coffee shop, coffee menu, espresso, latte, cappuccino, affogato, caramel cloud latte, cinnamon macchiato, iced brownie espresso, order coffee online, pickup orders" />
  <meta name="robots" content="index, follow" />
  <link rel="canonical" href="<?php echo htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8'); ?>" />
  <!-- Open Graph -->
  <meta property="og:type" content="website" />
  <meta property="og:site_name" content="<?php echo htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8'); ?>" />
  <meta property="og:title" content="<?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?>" />
  <meta property="og:description" content="<?php echo htmlspecialchars($pageDesc, ENT_QUOTES, 'UTF-8'); ?>" />
  <meta property="og:url" content="<?php echo htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8'); ?>" />
  <meta property="og:image" content="<?php echo htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8'); ?>" />
  <meta property="og:image:alt" content="Coffee drinks at Love Amaiah Cafe" />
  <meta property="og:locale" content="en_US" />
  <script>
    window.LAIsCustomer = <?php echo isset($_SESSION['CustomerID']) ? 'true' : 'false'; ?>;
  </script>

  <!-- Preconnect for CDN -->
  <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin />
  <link rel="dns-prefetch" href="//cdnjs.cloudflare.com" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="stylesheet" href="../assets/css/responsive.css" />
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <!-- Structured Data: LocalBusiness (Cafe) -->
  <script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "CafeOrCoffeeShop",
    "name": "<?php echo htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8'); ?>",
    "url": "<?php echo htmlspecialchars($origin, ENT_QUOTES, 'UTF-8'); ?>",
    "logo": "<?php echo htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8'); ?>",
    "image": "<?php echo htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8'); ?>",
    "servesCuisine": ["Coffee", "Espresso", "Tea", "Pastries"],
    "priceRange": "$$",
    "sameAs": [
      "https://www.facebook.com/share/1CwLmRzYr2/",
      "https://www.instagram.com/loveamaiahcafe?igsh=b3d6djR2eGp4enk5",
      "https://www.tiktok.com/@loveamaiahcafe?_t=ZS-8zGmu07G68F&_r=1"
    ]
  }
  </script>
  <!-- Structured Data: WebPage -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "WebPage",
    "name": "<?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?>",
    "url": "<?php echo htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8'); ?>",
    "description": "<?php echo htmlspecialchars($pageDesc, ENT_QUOTES, 'UTF-8'); ?>",
    "primaryImageOfPage": {
      "@type": "ImageObject",
      "url": "<?php echo htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8'); ?>"
    },
    "isPartOf": {
      "@type": "WebSite",
      "name": "<?php echo htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8'); ?>",
      "url": "<?php echo htmlspecialchars($origin, ENT_QUOTES, 'UTF-8'); ?>"
    }
  }
  </script>
  <style>
    :root {
      --main-color: #a17850;
      --bg-dark: #1e1e1e;
      --white: #fff;
      --spacing: 2rem;
      --card-bg: #2d2d2d;
      --accent: #4B2E0E;
      --light-brown: #C4A07A;
      --soft-bg: rgba(255,255,255,0.85);
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Segoe UI', sans-serif;
      background: url('../images/LAbg.png') no-repeat center center/cover;
      min-height: 100vh;
      color: var(--white);
      overflow-x: hidden;
      scroll-behavior: smooth;
    }

    a {
      text-decoration: none;
      color: inherit;
    }

    /* Smooth fade-in animations */
    .fade-in {
      opacity: 0;
      transform: translateY(30px);
      animation: fadeInUp 1s ease forwards;
    }
    @keyframes fadeInUp {
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* Top bar */
    .top-bar {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem 5vw;
      background-color: rgba(0, 0, 0, 0.5);
      backdrop-filter: blur(8px);
      z-index: 1000;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
      transition: background-color 0.3s ease, padding 0.3s ease;
    }
    .top-bar.scrolled {
      background-color: rgba(0, 0, 0, 0.75);
      padding: 0.5rem 5vw;
    }

    .logo-container {
      display: flex;
      align-items: center;
      gap: 1rem;
    }

    .logo-container img {
      height: 48px;
      width: 48px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid rgba(255, 255, 255, 0.7);
      transition: transform 0.3s ease;
    }
    .logo-container img:hover {
      transform: rotate(8deg) scale(1.05);
    }

    .logo-container span {
      font-size: 1.8rem;
      font-weight: bold;
      background: linear-gradient(to right, var(--main-color), #fff);
  background-clip: text;
  -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .auth-buttons {
      display: flex;
      gap: 1rem;
    }

    .auth-buttons a {
      padding: 0.75rem 1.5rem;
      border: 2px solid var(--white);
      background: transparent;
      color: var(--white);
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }

    /* Burger Menu Toggle (hidden on desktop) */
    .menu-toggle {
      display: none;
      align-items: center;
      justify-content: center;
      width: 44px;
      height: 44px;
      border-radius: 10px;
      border: 1px solid rgba(255,255,255,0.4);
      background: rgba(255,255,255,0.06);
      color: #fff;
      cursor: pointer;
      transition: filter .2s ease, transform .1s ease;
    }
    .menu-toggle:hover { filter: brightness(1.15); }
    .menu-toggle:active { transform: scale(0.98); }

    .auth-buttons a::before {
      content: "";
      position: absolute;
      top: 0; left: -100%;
      width: 100%;
      height: 100%;
      background: rgba(255,255,255,0.15);
      transition: left 0.4s ease;
    }

    .auth-buttons a:hover::before {
      left: 0;
    }

    /* Fullscreen overlay behind the slide-down nav on mobile */
    .nav-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.3);
      z-index: 900; /* below header (1000) */
    }

    .main-content {
      margin-top: 100px;
      padding: var(--spacing) 5vw;
      display: flex;
      flex-direction: column;
      gap: 4rem;
    }

    /* Hero */
    .hero {
      display: flex;
      align-items: center;
      justify-content: center;
      flex-wrap: wrap;
      gap: 4rem;
      padding: 2rem 0;
    }

    .hero img {
      max-width: 800px;
      width: 100%;
      flex: 1 1 45%;
      border-radius: 16px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.3);
      animation: float 6s ease-in-out infinite;
    }

    @keyframes float {
      0% { transform: translateY(0px); }
      50% { transform: translateY(-10px); }
      100% { transform: translateY(0px); }
    }

    .hero-text {
      flex: 1 1 50%;
      padding: 2rem;
      max-width: 700px;
    }

    .hero-text h1 {
      font-size: 4.5rem;
      line-height: 1.2;
      margin-bottom: 1.5rem;
      letter-spacing: -1px;
    }

    .hero-text h1 span {
      color: var(--main-color);
      text-shadow: 2px 2px 6px rgba(0,0,0,0.3);
    }

    .hero-text p {
      font-size: 1.7rem;
      margin-bottom: 2.5rem;
      line-height: 1.6;
    }

    .hero-text button {
      padding: 0.9rem 2rem;
      border: 2px solid var(--white);
      border-radius: 6px;
      background: transparent;
      color: var(--white);
      font-weight: bold;
      font-size: 1.1rem;
      cursor: pointer;
      transition: all 0.3s ease, transform 0.2s ease;
    }

    .hero-text button:hover {
      background-color: rgba(255, 255, 255, 0.2);
      transform: scale(1.05);
    }
    .hero-text button:focus {
      outline: 3px solid var(--main-color);
      outline-offset: 3px;
    }

    /* CTA group under hero copy */
    .hero-cta { display:flex; gap:.6rem; flex-wrap:wrap; margin-top:1rem; }

    /* Hero slideshow inspired by Avenue Cafe (original design) */
    .hero-viewport { position: relative; min-height: min(72vh, 720px); width: 100%; border-radius: 18px; overflow: hidden; }
    .slides { position: absolute; inset: 0; }
    .slides img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; opacity: 0; transition: opacity 900ms ease; }
    .slides img.active { opacity: 1; }
    .hero-gradient { position:absolute; inset:0; background: linear-gradient(90deg, rgba(0,0,0,.7) 0%, rgba(0,0,0,.25) 55%, rgba(0,0,0,0) 100%); z-index: 0; }
    .hero-overlay { position: relative; z-index: 1; display:flex; align-items:center; height: 100%; padding: clamp(1rem, 4.2vw, 3rem); }

    /* Section scaffolding */
  /* Match hero heading sizing/weight for visual consistency */
  .section-title { font-size: clamp(2rem, 4vw, 3.2rem); margin: 0 0 .6rem; font-weight: 800; line-height: 1.08; }
  /* Match hero paragraph sizing/spacing for visual parity */
  .section-sub { color: rgba(255,255,255,.88); margin: 0 0 1.25rem; line-height: 1.6; font-size: 1.7rem; }


    /* Highlights tiles */
    .highlights-grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.2rem; }
    .tile { position: relative; border-radius: 14px; overflow: hidden; background: #111; box-shadow: 0 8px 20px rgba(0,0,0,.25); }
    .tile img { width: 100%; height: 220px; object-fit: cover; filter: brightness(.9); transition: transform .4s ease, filter .4s ease; display:block; }
    .tile:hover img { transform: scale(1.05); filter: brightness(1); }
    .tile .caption { position:absolute; left:0; right:0; bottom:0; padding: .85rem 1rem; background: linear-gradient(180deg, rgba(0,0,0,0) 0%, rgba(0,0,0,.6) 68%, rgba(0,0,0,.82) 100%); font-weight: 800; letter-spacing: .2px; }

    /* Horizontal scroll gallery */
  .scroll-gallery { display:flex; gap: 12px; overflow-x: auto; scroll-snap-type: x proximity; padding: 2px 2px 10px; scrollbar-width: none; }
  .scroll-gallery::-webkit-scrollbar { display: none; }
  .scroll-gallery img { flex: 0 0 auto; width: clamp(240px, 38vw, 480px); height: clamp(160px, 28vw, 320px); object-fit: cover; border-radius: 12px; scroll-snap-align: start; box-shadow: 0 6px 18px rgba(0,0,0,.25); }
  .scroll-gallery.dragging { scroll-snap-type: none; }

    /* Coffee Cards */
    .coffee-cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(260px, 320px));
      gap: 2.5rem;
      justify-content: center; /* center last row when not full */
    }

    .card {
      background-color: var(--card-bg);
      border-radius: 16px;
      overflow: hidden;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
      position: relative;
    }

    .card:hover {
      transform: translateY(-8px);
      box-shadow: 0 8px 20px rgba(0,0,0,0.4);
    }

    .card img {
      width: 100%;
      height: 180px;
      object-fit: cover;
      object-position: center center;
      transition: transform 0.5s ease;
    }

    .card:hover img {
      transform: scale(1.1);
    }

    .card-body {
      padding: 1.2rem;
    }

    .card h3 {
      font-size: 1.3rem;
      margin-bottom: 0.6rem;
      color: var(--main-color);
    }

    .card p {
      font-size: 1rem;
      line-height: 1.5;
    }

    /* --- Footer --- */
    .site-footer {
      background: var(--soft-bg);
      color: #2b2b2b;
      margin-top: 3rem;
      border-top: 1px solid rgba(0,0,0,0.06);
      backdrop-filter: blur(4px);
    }
    .footer-content { padding: 2rem 5vw; }
    .footer-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap: 2rem 2.5rem;
    }
    .footer-col h4 {
      font-size: 1.05rem;
      color: var(--accent);
      margin-bottom: 0.75rem;
    }
    .footer-links { list-style: none; }
    .footer-links li { margin: 0.4rem 0; }
    .footer-links a {
      color: #444;
      text-decoration: none;
      transition: color 0.2s ease;
    }
    .footer-links a:hover { color: var(--accent); }

    .footer-divider { border: none; border-top: 1px solid rgba(0,0,0,0.08); margin: 1.25rem 0; }

    .footer-social { display: flex; gap: 0.75rem; align-items: center; }
    .footer-social .icon {
      width: 36px; height: 36px; border-radius: 9999px;
      display: inline-flex; align-items: center; justify-content: center;
      background: #1f1f1f; color: #fff; transition: transform 0.2s ease, filter 0.2s ease;
    }
    .footer-social .icon:hover { transform: translateY(-2px); filter: brightness(1.1); }

    .legal-links { display: flex; flex-wrap: wrap; gap: 0.8rem 1.25rem; color: #555; font-size: 0.9rem; }
    .legal-links a { color: #555; text-decoration: none; }
    .legal-links a:hover { color: var(--accent); text-decoration: underline; }
  .copyright { margin-top: 1rem; color: #666; font-size: 0.9rem; }

  /* Pickup modal */
  .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(2px); display: none; align-items: center; justify-content: center; z-index: 1050; }
  .modal-overlay.show { display: flex; }
  .modal-card { background: var(--soft-bg); color: #2b2b2b; border-radius: 16px; padding: 1.25rem 1.25rem 1rem; width: min(95vw, 520px); border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 12px 28px rgba(0,0,0,0.25); }
  .modal-card h3 { margin: 0 0 .5rem 0; color: var(--accent); font-size: 1.2rem; }
  .modal-card p { margin: 0.25rem 0 0.75rem; color: #3b3b3b; }
  .modal-actions { display: flex; justify-content: flex-end; gap: .5rem; margin-top: .5rem; }
  .btn { padding: 0.5rem 0.9rem; border-radius: 9999px; font-weight: 600; border: 1px solid transparent; cursor: pointer; }
  .btn-primary { background: var(--accent); color: #fff; }
  .btn-primary:hover { filter: brightness(1.05); }
  .btn-secondary { background: #fff; color: #333; border-color: rgba(0,0,0,0.12); }
  .btn-secondary:hover { background: #f7f7f7; }


    /* Extra responsive polish */
    /* Desktop-specific: split hero into two columns so text sits to the right without overlapping the image */
    @media (min-width: 1025px) {
      /* Two-column layout: media (left) | copy (right) */
      .hero-viewport { display: grid; grid-template-columns: minmax(560px, 58%) 1fr; align-items: stretch; }
      /* Make slides occupy the left column as a normal flow element */
      .slides { position: relative; top: auto; right: auto; bottom: auto; left: auto; height: 100%; }
      /* Keep images absolutely filled within the slides area (unchanged behavior) */
      .slides img { inset: 0; }
      /* Remove dark gradient overlay since copy no longer overlays media */
      .hero-gradient { display: none; }
      /* Position copy on the right column, centered vertically */
      .hero-overlay { position: relative; grid-column: 2; display: flex; align-items: center; justify-content: center; padding: clamp(1rem, 4vw, 3rem); }
      .hero-text { margin: 0; max-width: 680px; text-align: left; padding: 0; }
      .hero-cta { justify-content: flex-start; }
    }

    @media (max-width: 1024px) {
  .top-bar { justify-content: space-between; }
  .logo-container { flex: 0 0 auto; }
  .menu-toggle { flex: 0 0 auto; order: 2; }
  #primary-nav { order: 3; }
      /* Header -> show burger, collapse nav (only when JS is enabled) */
      .menu-toggle { display: inline-flex; }
      .has-js .auth-buttons {
        position: absolute;
        top: calc(100% + 8px);
        right: 5vw;
        background: rgba(0,0,0,0.9);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255,255,255,0.12);
        border-radius: 14px;
        padding: 0.75rem;
        min-width: 230px;
        flex-direction: column;
        gap: 0.5rem;
        box-shadow: 0 12px 28px rgba(0,0,0,0.35);
        /* animated hidden state */
        opacity: 0;
        visibility: hidden;
        transform: translateY(-6px);
        pointer-events: none;
        transition: opacity .2s ease, transform .2s ease, visibility .2s ease;
      }
      .has-js .top-bar.nav-open .auth-buttons {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
        pointer-events: auto;
      }
      .has-js .auth-buttons a {
        display: block;
        width: 100%;
        text-align: center;
        padding: 0.8rem 1rem;
        border-width: 1px;
        background: rgba(255,255,255,0.06);
      }
      .has-js .auth-buttons a:hover { background: rgba(255,255,255,0.14); }
      .hero-text h1 {
        font-size: 3.2rem;
      }
      .hero-text p {
        font-size: 1.4rem;
      }
    }

    @media (max-width: 768px) {
      .hero {
        flex-direction: column;
        gap: 3rem;
        padding: 1rem 0;
      }
      .hero-text {
        padding: 1rem;
        text-align: center;
      }
      .hero-text h1 {
        font-size: 2.8rem;
      }
      .hero-text p {
        font-size: 1.2rem;
      }
      .auth-buttons {
        flex-direction: column;
        gap: 0.5rem;
      }
    }

    @media (max-width: 480px) {
      .top-bar {
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        gap: 0;
        padding: 0.5rem 1rem;
      }
      .logo-container img { height: 40px; width: 40px; }
      .logo-container span {
        font-size: 1.25rem;
      }
      /* Mobile dropdown takes full width with margins */
      .has-js .auth-buttons {
        left: 12px;
        right: 12px;
        top: calc(100% + 10px);
        min-width: 0;
        padding: 0.5rem;
        border-radius: 12px;
      }
      .has-js .auth-buttons a { padding: 0.9rem 1rem; font-size: 1rem; }
      .main-content {
        padding: 1.25rem;
      }
      .coffee-cards {
        grid-template-columns: 1fr;
      }
      .hero-text h1 {
        font-size: 2rem;
      }
  .hero-text p { font-size: 1.05rem; }
  /* Ensure section subtitle scales on small screens to match hero text */
  .section-sub { font-size: 1.05rem; }
      .hero-text button { width: 100%; }
    }
  </style>
</head>
<body>
  <header class="top-bar">
    <a href="#" class="logo-container">
      <img src="../images/logo.png" alt="Love Amaiah Logo" />
      <span>Love Amaiah</span>
    </a>
    <button class="menu-toggle" aria-label="Open menu" aria-expanded="false" aria-controls="primary-nav"><i class="fa-solid fa-bars"></i></button>
    <nav class="auth-buttons" id="primary-nav" aria-label="Primary">
  <a href="#menu" title="Browse featured drinks">Menu</a>
      <a href="#visit" title="Visit our shop"><i class="fa-solid fa-location-dot" style="margin-right:8px;"></i>Find Our Store</a>
      <a href="../all/registration.php">Register</a>
      <a href="../all/login.php">Login</a>
    </nav>
  </header>
  <div id="nav-overlay" class="nav-overlay" aria-hidden="true"></div>
  
  <main class="main-content">
    <!-- Hero (slideshow) -->
    <section class="hero hero-viewport fade-in" id="home">
      <div class="slides" aria-hidden="true">
        <img src="../images/ad1.jpg" alt="" class="active">
        <img src="../images/ad2.jpg" alt="">
        <img src="../images/ad3.jpg" alt="">
        <img src="../images/ad5.jpg" alt="">
        <img src="../images/ad6.jpg" alt="">
        <img src="../images/ad7.jpg" alt="">
        <img src="../images/ad8.jpg" alt="">
        <img src="../images/ad.jpg" alt="">
      </div>
      <div class="hero-gradient" aria-hidden="true"></div>
      <div class="hero-overlay">
        <div class="hero-text">
          <h1>Crafted Coffee,<br><span>Cozy Moments</span></h1>
          <p>Handcrafted espresso, creamy lattes, and seasonal flavors — brewed fresh for your best moments of the day.</p>
          <div class="hero-cta">
            <button onclick="location.href='#menu'" aria-label="View menu">View Menu</button>
            <button onclick="location.href='login.php'" aria-label="Order now">Order Now</button>
          </div>
        </div>
      </div>
    </section>

    <!-- Highlights / About -->
    <section id="story" class="fade-in">
      <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin-bottom:1rem;">
        <div style="flex:1 1 60%;min-width:240px;">
          <h2 class="section-title" style="font-size:clamp(2rem,4vw,3.2rem);margin:0;">What Makes <span style="color:var(--light-brown);">Love</span> <span style="color:var(--main-color);">Amaiah</span> Special</h2>
          <p class="section-sub" style="margin-top:.6rem;max-width:68%;">From ethically sourced beans to warm service, we pour care into every cup. Stop by, slow down, and savor.</p>
        </div>
        <div style="flex:0 0 auto;opacity:.95;">
        </div>
      </div>

      <!-- Rounded horizontal card row like the screenshot -->
      <div class="scroll-gallery" aria-label="Highlights" style="padding:0;">
        <div style="flex:0 0 auto;width:clamp(260px,30vw,420px);border-radius:18px;overflow:hidden;position:relative;background:#000;box-shadow:0 6px 18px rgba(0,0,0,.35);">
          <img src="../images/ad2.jpg" alt="Warm and Inviting" style="width:100%;height:220px;object-fit:cover;display:block;">
          <div style="position:absolute;left:14px;bottom:14px;color:#fff;font-weight:800;padding:.6rem 1rem;background:linear-gradient(180deg,transparent,rgba(0,0,0,.48));border-radius:10px;">Wwarm and Inviting</div>
        </div>

        <div style="flex:0 0 auto;width:clamp(260px,30vw,420px);border-radius:18px;overflow:hidden;position:relative;background:#000;box-shadow:0 6px 18px rgba(0,0,0,.35);">
          <img src="../images/ad6.jpg" alt="Cozy Corners" style="width:100%;height:220px;object-fit:cover;display:block;">
          <div style="position:absolute;left:14px;bottom:14px;color:#fff;font-weight:800;padding:.6rem 1rem;background:linear-gradient(180deg,transparent,rgba(0,0,0,.48));border-radius:10px;">Cozy Corners</div>
        </div>

        <div style="flex:0 0 auto;width:clamp(260px,30vw,420px);border-radius:18px;overflow:hidden;position:relative;background:#000;box-shadow:0 6px 18px rgba(0,0,0,.35);">
          <img src="../images/ad7.jpg" alt="Seasonal Creations" style="width:100%;height:220px;object-fit:cover;display:block;">
          <div style="position:absolute;left:14px;bottom:14px;color:#fff;font-weight:800;padding:.6rem 1rem;background:linear-gradient(180deg,transparent,rgba(0,0,0,.48));border-radius:10px;">Seasonal Creations</div>
        </div>

        <div style="flex:0 0 auto;width:clamp(260px,30vw,420px);border-radius:18px;overflow:hidden;position:relative;background:#000;box-shadow:0 6px 18px rgba(0,0,0,.35);">
          <img src="../images/ad8.jpg" alt="Our Space" style="width:100%;height:220px;object-fit:cover;display:block;">
          <div style="position:absolute;left:14px;bottom:14px;color:#fff;font-weight:800;padding:.6rem 1rem;background:linear-gradient(180deg,transparent,rgba(0,0,0,.48));border-radius:10px;">Our Space</div>
        </div>
      </div>
    </section>

    

    <!-- Coffee Cards -->
    <section class="coffee-cards fade-in" id="menu">
      <h2 class="section-title" style="grid-column:1/-1">Featured drinks</h2>
      <?php foreach ($featured as $prod): 
        $name = $prod['ProductName'] ?? '';
        $desc = trim((string)($prod['Description'] ?? ''));
        $img  = la_feature_img_src($name, $prod['ImagePath'] ?? '');
        $pid  = isset($prod['ProductID']) ? ('product-' . $prod['ProductID']) : '';
        $price = isset($prod['UnitPrice']) ? (float)$prod['UnitPrice'] : 0.0;
        $allergen = trim((string)($prod['Allergen'] ?? 'None'));
      ?>
      <div class="card" role="button" tabindex="0"
           data-id="<?php echo htmlspecialchars($pid, ENT_QUOTES, 'UTF-8'); ?>"
           data-name="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>"
           data-price="<?php echo htmlspecialchars((string)$price, ENT_QUOTES, 'UTF-8'); ?>"
           data-img="<?php echo htmlspecialchars($img, ENT_QUOTES, 'UTF-8'); ?>"
           data-desc="<?php echo htmlspecialchars($desc, ENT_QUOTES, 'UTF-8'); ?>"
           data-allergen="<?php echo htmlspecialchars($allergen, ENT_QUOTES, 'UTF-8'); ?>"
           aria-label="Select quantity and add <?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?> to cart"
           style="cursor:pointer;">
        <img src="<?php echo htmlspecialchars($img, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>" loading="lazy" width="600" height="400">
        <div class="card-body">
          <h3><?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?></h3>
          <p><?php echo $desc !== '' ? htmlspecialchars($desc, ENT_QUOTES, 'UTF-8') : ' ' ; ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </section>
    <!-- Visit us with map preview -->
    <section id="visit" class="fade-in" style="display:grid; gap: .6rem;">
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:.5rem;align-items:stretch;">
        <!-- Map preview -->
        <div style="width:100%;">
          <div style="border-radius:20px;overflow:hidden;box-shadow:0 8px 30px rgba(0,0,0,.4);height:100%;">
            <!-- Google Maps embed (responsive width to minimize inner spacing) -->
            <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d1936.02507423145!2d121.11573586846639!3d13.955619745329125!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x33bd6d0e92625b7f%3A0x5245bd256c2df445!2sLove%2C%20Amaiah%20Cafe!5e0!3m2!1sen!2sph!4v1760377982264!5m2!1sen!2sph" style="border:0;width:100%;height:100%;min-height:420px;max-height:560px;display:block" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
          </div>
        </div>

        <!-- Copy & CTAs -->
  <div style="display:flex;flex-direction:column;gap:.7rem;justify-content:center;">
          <h2 class="section-title">Visit us</h2>
          <p class="section-sub">Come by our cafe for a cozy seat and a freshly brewed cup. We can’t wait to serve you.</p>
          <div style="display:flex;gap:.6rem;flex-wrap:wrap;align-items:center;">
            <a href="https://maps.app.goo.gl/ruZNFNG7NkPm99sz8" target="_blank" rel="noopener" class="btn btn-primary" style="text-decoration:none;">Open in Google Maps</a>
            <a href="../all/login.php" class="btn btn-secondary" style="text-decoration:none;">Order for pickup</a>
          </div>
        </div>
      </div>
    </section>
  </main>

  <!-- Footer -->
  <footer class="site-footer">
    <div class="footer-content">
      <div class="footer-grid">
        <div class="footer-col">
          <h4>About Us</h4>
          <ul class="footer-links">
            <li><a href="../all/about-us.php">Our Company</a></li>
            <li><a href="#">Our Coffee</a></li>
            <li><a href="#">Contact Us</a></li>
          </ul>
        </div>
        
        <div class="footer-col">
          <h4>For Business Partners</h4>
          <ul class="footer-links">
            <li><a href="#">Suppliers</a></li>
            <li><a href="#">Gift Card Sales</a></li>  
          </ul>
        </div>
        <div class="footer-col">
          <h4>Order and Pick Up</h4>
          <ul class="footer-links">
            <li><a href="../all/login.php">Order on the Web</a></li>
            <li><a href="#">Delivery</a></li>
            <li><a href="#" id="pickup-options-link">Order & Pick Up Options</a></li>
          </ul>
        </div>
      </div>

      <hr class="footer-divider" />

      <div class="footer-social" aria-label="Social media links">
        <a class="icon" href="https://www.facebook.com/share/1CwLmRzYr2/" title="Facebook" aria-label="Facebook" target="_blank" rel="noopener"><i class="fa-brands fa-facebook-f"></i></a>
        <a class="icon" href="https://www.instagram.com/loveamaiahcafe?igsh=b3d6djR2eGp4enk5" title="Instagram" aria-label="Instagram" target="_blank" rel="noopener"><i class="fa-brands fa-instagram"></i></a>
        <a class="icon" href="https://www.tiktok.com/@loveamaiahcafe?_t=ZS-8zGmu07G68F&_r=1" title="TikTok" aria-label="TikTok" target="_blank" rel="noopener"><i class="fa-brands fa-tiktok"></i></a>
      </div>

      <hr class="footer-divider" />

      <?php include __DIR__ . '/../includes/legal-footer.php'; ?>
    </div>
  </footer>

  <!-- Pickup modal -->
  <div id="pickup-modal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="pickup-title">
    <div class="modal-card" role="document">
      <h3 id="pickup-title">Order &amp; Pick Up Options</h3>
      <p>You can log in and order from our website, then pick it up later in our store.</p>
      <div class="modal-actions">
        <button type="button" class="btn btn-secondary" id="pickup-close">Close</button>
        <a href="../all/login.php" class="btn btn-primary" id="pickup-login">Login to order</a>
      </div>
    </div>
  </div>

  <!-- Cookie consent banner handled by assets/js/consent.js -->

  <script>
  // Mark document as JS-capable for progressive enhancements
  document.documentElement.classList.add('has-js');
    // Simple hero crossfade slideshow
    (function(){
      const slides = document.querySelectorAll('.slides img');
      if(!slides.length) return;
      let i = 0;
      setInterval(()=>{
        slides[i].classList.remove('active');
        i = (i + 1) % slides.length;
        slides[i].classList.add('active');
      }, 4000);
    })();
    // Auto-scroll for horizontal gallery with user control
    (function(){
      const scroller = document.querySelector('.scroll-gallery');
      if (!scroller) return;

      let isPaused = false;
      // Time-based speed for consistency across refresh rates (px per second)
      const prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      const speed = prefersReduced ? 40 : 160; // slower if user prefers reduced motion

      // Duplicate content to enable seamless looping; ensure we have enough width
      const original = Array.from(scroller.children);
      const cloneOnce = () => original.forEach(node => scroller.appendChild(node.cloneNode(true)));
      cloneOnce();
      // If still not wide enough (small screens), clone again
      if (scroller.scrollWidth <= scroller.clientWidth + 32) cloneOnce();
      let singleWidth = scroller.scrollWidth / 2;

      // Drag handling: pause while dragging
      let startX = 0, startScroll = 0, dragging = false;
      const pageX = (e) => (e.touches ? e.touches[0].pageX : e.pageX);
      const pause = () => { isPaused = true; };
      const resume = () => { isPaused = false; };
      const onDown = (e) => { dragging = true; scroller.classList.add('dragging'); startX = pageX(e); startScroll = scroller.scrollLeft; pause(); };
      const onMove = (e) => { if (!dragging) return; scroller.scrollLeft = startScroll - (pageX(e) - startX); };
      const onUp = () => { if (!dragging) return; dragging = false; scroller.classList.remove('dragging'); setTimeout(resume, 600); };
      scroller.addEventListener('mousedown', onDown);
      scroller.addEventListener('touchstart', onDown, { passive: true });
      window.addEventListener('mousemove', onMove);
      window.addEventListener('touchmove', onMove, { passive: true });
      window.addEventListener('mouseup', onUp);
      window.addEventListener('touchend', onUp);
      scroller.addEventListener('wheel', () => { pause(); clearTimeout(scroller._wheelT); scroller._wheelT = setTimeout(resume, 700); }, { passive: true });

      // Auto-scroll loop using rAF with delta time
      let last = performance.now();
      const loop = (now) => {
        const dt = (now - last) / 1000;
        last = now;
        if (!isPaused) {
          scroller.scrollLeft += speed * dt;
          if (scroller.scrollLeft >= singleWidth) {
            scroller.scrollLeft -= singleWidth;
          }
        }
        requestAnimationFrame(loop);
      };
      requestAnimationFrame((t) => { last = t; loop(t); });
    })();
    // Navbar scroll effect
    window.addEventListener('scroll', function() {
      const topBar = document.querySelector('.top-bar');
      if (window.scrollY > 50) {
        topBar.classList.add('scrolled');
      } else {
        topBar.classList.remove('scrolled');
      }
    });

    // Burger menu toggle
    (function(){
      const header = document.querySelector('.top-bar');
      const toggle = document.querySelector('.menu-toggle');
      const nav = document.getElementById('primary-nav');
      const overlay = document.getElementById('nav-overlay');
      if(!header || !toggle || !nav || !overlay) return;

      function closeNav(){
        header.classList.remove('nav-open');
        toggle.setAttribute('aria-expanded', 'false');
        overlay.style.display = 'none';
      }
      function openNav(){
        header.classList.add('nav-open');
        toggle.setAttribute('aria-expanded', 'true');
        overlay.style.display = 'block';
        document.body.style.overflow = 'hidden';
        // swap icon to close
        toggle.innerHTML = '<i class="fa-solid fa-xmark"></i>';
      }
      function toggleNav(){
        if(header.classList.contains('nav-open')){ closeNav(); } else { openNav(); }
      }
      function closeNav(){
        header.classList.remove('nav-open');
        toggle.setAttribute('aria-expanded', 'false');
        overlay.style.display = 'none';
        document.body.style.overflow = '';
        // swap icon back to burger
        toggle.innerHTML = '<i class="fa-solid fa-bars"></i>';
      }
      toggle.addEventListener('click', toggleNav);
      overlay.addEventListener('click', closeNav);
      nav.addEventListener('click', (e)=>{
        const t = e.target;
        if(t && t.closest('a')) closeNav();
      });
      window.addEventListener('keydown', (e)=>{
        if(e.key === 'Escape') closeNav();
      });
      // Close menu if resizing to desktop
      window.addEventListener('resize', ()=>{
        if(window.innerWidth > 1024) closeNav();
      });
    })();

    // Pickup modal wiring
    (function(){
      const trigger = document.getElementById('pickup-options-link');
      const overlay = document.getElementById('pickup-modal');
      const btnClose = document.getElementById('pickup-close');
      const btnLogin = document.getElementById('pickup-login');
      if (!trigger || !overlay) return;

      function openModal(e){ if(e) e.preventDefault(); overlay.classList.add('show'); document.body.style.overflow = 'hidden'; btnLogin && btnLogin.focus(); }
      function closeModal(){ overlay.classList.remove('show'); document.body.style.overflow = ''; }

      trigger.addEventListener('click', openModal);
      btnClose && btnClose.addEventListener('click', closeModal);
      overlay.addEventListener('click', (ev) => { if (ev.target === overlay) closeModal(); });
      document.addEventListener('keydown', (ev) => { if (ev.key === 'Escape' && overlay.classList.contains('show')) closeModal(); });
    })();

    // Consent banner is managed by consent.js; no inline banner code needed here.
  </script>
  <script>
    // Featured cards -> Product Information popup -> Add requires login/registration
    (function(){
      const container = document.getElementById('menu');
      if(!container) return;
      function openPopupFrom(el){
        const id = el.getAttribute('data-id') || '';
        const name = el.getAttribute('data-name') || '';
        const price = parseFloat(el.getAttribute('data-price') || '0') || 0;
        const img = el.getAttribute('data-img') || '';
        const desc = el.getAttribute('data-desc') || '';
        const allergen = el.getAttribute('data-allergen') || 'None';
        let qty = 1;
        Swal.fire({
          title: 'Product Information',
          html: `
            <div style="text-align:left">
              <div style="margin-bottom:12px;border-radius:24px;background:#ffffff;padding:10px;box-shadow:0 4px 16px rgba(0,0,0,0.08)">
                <img src="${img}" alt="${name}" style="width:100%;height:auto;border-radius:18px;object-fit:cover;display:block" />
              </div>
              <h3 style="font-size:22px;line-height:1.2;margin:0 0 6px 0;font-weight:800;color:#111827">${name}</h3>
              <div style="font-weight:800;color:#C4A07A;margin-bottom:8px">₱ ${price.toFixed(2)}</div>
              ${desc ? `<p style=\"margin:0 0 8px 0;color:#6b7280;font-size:14px\">${desc}</p>` : ''}
              <p style="margin:0;color:#374151;font-size:13px"><strong>Allergens:</strong> ${allergen}</p>
              <div style="display:flex;justify-content:space-between;align-items:center;margin-top:14px;gap:12px">
                <div style="display:inline-flex;align-items:center;gap:10px;background:#e5e7eb;border-radius:9999px;padding:6px 10px">
                  <button id="cf-minus" type="button" style="width:28px;height:28px;border-radius:9999px;border:1px solid #111827;background:#ffffff;color:#111827;display:flex;align-items:center;justify-content:center;font-weight:700;line-height:1">-</button>
                  <span id="cf-qty" style="min-width:18px;text-align:center;font-weight:700;color:#111827">1</span>
                  <button id="cf-plus" type="button" style="width:28px;height:28px;border-radius:9999px;border:1px solid #111827;background:#ffffff;color:#111827;display:flex;align-items:center;justify-content:center;font-weight:700;line-height:1">+</button>
                </div>
                <button id="cf-add" type="button" style="flex:1;background:#C4A07A;color:#ffffff;border:none;border-radius:9999px;padding:10px 16px;font-weight:700">Add Item</button>
              </div>
            </div>
          `,
          background: '#ffffff',
          confirmButtonText: 'Close',
          confirmButtonColor: '#4B2E0E',
          width: 520,
          backdrop: false,
          heightAuto: false,
          scrollbarPadding: false,
          didOpen: () => {
            let qEl = document.getElementById('cf-qty');
            const mEl = document.getElementById('cf-minus');
            const pEl = document.getElementById('cf-plus');
            const aEl = document.getElementById('cf-add');
            mEl?.addEventListener('click', () => { if (qty > 1) { qty--; if(qEl) qEl.textContent = String(qty); } });
            pEl?.addEventListener('click', () => { qty++; if(qEl) qEl.textContent = String(qty); });
            aEl?.addEventListener('click', () => {
              // Save intended addition to localStorage and redirect to login/register
              try {
                const key = 'pending_cart_add';
                localStorage.setItem(key, JSON.stringify({ id, name, price, img, qty }));
              } catch(e) {}
              Swal.close();
              // Prefer registration/login; add a hint query so we know where to continue
              const dest = '../all/login.php?continue=customer_cart&from=coffee';
              window.location.href = dest;
            });
          }
        });
      }
      container.addEventListener('click', (e) => {
        const card = e.target.closest('.card[data-id]');
        if (card) { openPopupFrom(card); }
      });
      container.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
          const card = e.target.closest('.card[data-id]');
          if (card) { e.preventDefault(); openPopupFrom(card); }
        }
      });
    })();

    // After login/register, if a pending add exists, merge into cart and forward to cart page
    (function(){
      try {
        const params = new URLSearchParams(window.location.search);
        const cont = params.get('continue');
        if (cont === 'customer_cart') {
          const key = 'pending_cart_add';
          const saved = localStorage.getItem(key);
          if (saved) {
            const item = JSON.parse(saved);
            const cartKey = 'customer_cart';
            const raw = localStorage.getItem(cartKey);
            let cart = raw ? JSON.parse(raw) : {};
            if (!cart || typeof cart !== 'object') cart = {};
            const id = item.id;
            if (id) {
              if (!cart[id]) {
                cart[id] = { id, name: item.name, price: item.price, quantity: 0, img: item.img, alt: item.name };
              }
              cart[id].quantity += (item.qty || 1);
              localStorage.setItem(cartKey, JSON.stringify(cart));
            }
            localStorage.removeItem(key);
            // Redirect to cart page
            window.location.href = '../Customer/customerpage.php';
          }
        }
      } catch(e) {}
    })();
  </script>
</body>
</html>
