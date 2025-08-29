<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>LoveAmiah - Advertisement</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
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

    /* Coffee Cards */
    .coffee-cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
      gap: 2.5rem;
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
    @media (max-width: 1024px) {
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
        flex-direction: column;
        gap: 0.5rem;
        padding: 0.75rem 1rem;
      }
      .logo-container span {
        font-size: 1.3rem;
      }
      .main-content {
        padding: 2rem;
      }
      .coffee-cards {
        grid-template-columns: 1fr;
      }
      .hero-text h1 {
        font-size: 2rem;
      }
    }
  </style>
</head>
<body>
  <header class="top-bar">
    <a href="#" class="logo-container">
      <img src="../images/logo.png" alt="Love Amaiah Logo" />
      <span>Love Amaiah</span>
    </a>
    <div class="auth-buttons">
      <a href="https://maps.app.goo.gl/ruZNFNG7NkPm99sz8" target="_blank" rel="noopener" title="Find our store on Google Maps"><i class="fa-solid fa-location-dot" style="margin-right:8px;"></i>Find Our Store</a>
      <a href="../all/registration.php">Register</a>
      <a href="../all/login.php">Login</a>
    </div>
  </header>
  
  <main class="main-content">
    <!-- Hero -->
    <section class="hero fade-in">
      <img src="../images/mainpage_coffee.png" alt="Latte Art">
      <div class="hero-text">
        <h1>Sip Happiness<br><span>One Cup at a Time</span></h1>
        <p>Begin your day with a cup of coffee—boost your energy, sharpen your focus, and set the tone for a productive, positive day ahead.</p>
        <button onclick="window.location.href='login.php'">Order Coffee</button>
      </div>
    </section>

    <!-- Coffee Cards -->
    <section class="coffee-cards fade-in">
      <div class="card">
        <img src="../images/affogato.png" alt="Affogato">
        <div class="card-body">
          <h3>Affogato</h3>
          <p>Espresso poured over vanilla ice cream — bold, creamy, and decadent.</p>
        </div>
      </div>
      <div class="card">
        <img src="../images/caramel_cloud_latte.png" alt="Caramel Cloud Latte">
        <div class="card-body">
          <h3>Caramel Cloud Latte</h3>
          <p>Fluffy foam, bold espresso, and silky caramel — heavenly in every sip.</p>
        </div>
      </div>
      <div class="card">
        <img src="../images/cinnamon_macchiato.png" alt="Cinnamon Macchiato">
        <div class="card-body">
          <h3>Cinnamon Macchiato</h3>
          <p>Warm cinnamon meets espresso and milk — sweet, spicy, and smooth.</p>
        </div>
      </div>
      <div class="card">
        <img src="../images/iced_shaken_brownie.png" alt="Iced Brownie Espresso">
        <div class="card-body">
          <h3>Iced Brownie Espresso</h3>
          <p>Shaken espresso with rich brownie flavor — bold, cold, and energizing.</p>
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

      <div class="legal-links">
  <a href="../terms/privacy-notice.php">Privacy Notice</a>
  <a href="../terms/privacy-notice.php#health">Consumer Health Privacy Notice</a>
  <a href="../terms/loveamaiah-terms-of-use.php">Terms of Use</a>
  <a href="../terms/personal-information.php">Do Not Share My Personal Information</a>
  <a href="../terms/accessibility.php">Accessibility</a>
  <a href="../terms/cookie-preferences.php">Cookie Preferences</a>
      </div>
  <div class="copyright">© 2025 Love Amaiah Cafe. All rights reserved.</div>
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

  <!-- Cookie consent banner -->
  <div id="cookie-banner" style="position:fixed;z-index:1100;left:16px;right:16px;bottom:16px;display:none;background:rgba(255,255,255,0.95);color:#1f2937;border:1px solid rgba(0,0,0,0.08);box-shadow:0 8px 24px rgba(0,0,0,.2);border-radius:14px;padding:12px 14px;backdrop-filter:blur(4px)">
    <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;justify-content:space-between">
      <div style="max-width:720px">
        <strong style="color:#111827">We use cookies</strong>
        <div style="font-size:.95rem;color:#374151">We use necessary cookies to make our site work and optional cookies to improve your experience. You can manage preferences anytime.</div>
        <a href="../terms/cookie-preferences.php" style="color:#4B2E0E;text-decoration:underline;font-weight:600">Cookie preferences</a>
      </div>
      <div style="display:flex;gap:8px;flex-wrap:wrap">
        <button id="cb-accept" style="background:#4B2E0E;color:#fff;border:none;border-radius:9999px;padding:8px 14px;font-weight:700;cursor:pointer">Accept all</button>
        <button id="cb-reject" style="background:#fff;color:#1f2937;border:1px solid rgba(0,0,0,0.12);border-radius:9999px;padding:8px 14px;font-weight:600;cursor:pointer">Reject non-essential</button>
      </div>
    </div>
  </div>

  <script>
    // Navbar scroll effect
    window.addEventListener('scroll', function() {
      const topBar = document.querySelector('.top-bar');
      if (window.scrollY > 50) {
        topBar.classList.add('scrolled');
      } else {
        topBar.classList.remove('scrolled');
      }
    });

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

    // Cookie banner behavior (syncs with preferences page cookie format)
    (function(){
      const COOKIE_NAME = 'la_cookie_consent';
      function readConsent(){
        const m = document.cookie.match(new RegExp('(?:^|; )' + COOKIE_NAME + '=([^;]*)'));
        if(!m) return null;
        try { return JSON.parse(decodeURIComponent(m[1])); } catch { return null; }
      }
      function writeConsent(obj){
        const oneYear = 60*60*24*365;
        const val = encodeURIComponent(JSON.stringify(obj));
        document.cookie = COOKIE_NAME + '=' + val + '; max-age=' + oneYear + '; path=/; SameSite=Lax';
      }
      const banner = document.getElementById('cookie-banner');
      const existing = readConsent();
      if(!existing){ banner.style.display = 'block'; }
      function set(consent){ writeConsent(consent); banner.style.display = 'none'; }
      document.getElementById('cb-accept').addEventListener('click', ()=> set({necessary:true,functional:true,analytics:true,marketing:true,ts:new Date().toISOString(),v:1}));
      document.getElementById('cb-reject').addEventListener('click', ()=> set({necessary:true,functional:false,analytics:false,marketing:false,ts:new Date().toISOString(),v:1}));
    })();
  </script>
</body>
</html>
