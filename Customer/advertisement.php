<?php
session_start();
$current = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <?php
    // SEO helpers (from coffee.php)
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'loveamaiahcafe';
    $requestUri = $_SERVER['REQUEST_URI'] ?? '/Customer/advertisement.php';
    $origin = $scheme . '://' . $host;
    $canonical = $origin . $requestUri;
    $siteName = 'Love Amaiah Cafe';
    $pageTitle = 'Love Amaiah Cafe — Coffee & Espresso Drinks | Order Pickup';
    $pageDesc = 'Discover Love Amaiah Cafe’s signature coffee and espresso drinks. Browse favorites like Affogato, Caramel Cloud Latte, Cinnamon Macchiato, and Iced Brownie Espresso. Order online and pick up in-store.';
    $ogImage = $origin . '/images/mainpage_coffee.png';
    $logoUrl = $origin . '/images/logo.png';
  ?>
  <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
  <link rel="icon" href="../images/logo.png" type="image/png" />
  <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin />
  <link rel="dns-prefetch" href="//cdnjs.cloudflare.com" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="../assets/css/responsive.css" />
  <meta name="description" content="<?php echo htmlspecialchars($pageDesc, ENT_QUOTES, 'UTF-8'); ?>" />
  <link rel="canonical" href="<?php echo htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8'); ?>" />
  <meta property="og:type" content="website" />
  <meta property="og:site_name" content="<?php echo htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8'); ?>" />
  <meta property="og:title" content="<?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?>" />
  <meta property="og:description" content="<?php echo htmlspecialchars($pageDesc, ENT_QUOTES, 'UTF-8'); ?>" />
  <meta property="og:url" content="<?php echo htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8'); ?>" />
  <meta property="og:image" content="<?php echo htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8'); ?>" />
  <meta property="og:image:alt" content="Coffee drinks at Love Amaiah Cafe" />
  <meta property="og:locale" content="en_US" />

          <style>
            :root {
              --main-color: #a17850;
              --white: #fff;
              --spacing: 2rem;
              --card-bg: #2d2d2d;
              --accent: #4B2E0E;
              --light-brown: #C4A07A;
              --soft-bg: rgba(255,255,255,0.85);
            }
            * { box-sizing: border-box; margin: 0; padding: 0; }
            body {
              font-family: 'Segoe UI', sans-serif;
              background: url('../images/LAbg.png') no-repeat center center/cover;
              min-height: 100vh; color: var(--white); overflow-x: hidden; scroll-behavior: smooth;
            }
            a { text-decoration: none; color: inherit; }

            /* Sidebar layout */
            .page-shell { display:flex; min-height:100vh; }
            .la-sidebar { width:70px; min-width:70px; background:#fff; color:#4B2E0E; box-shadow:0 4px 18px rgba(0,0,0,.15); display:none; flex-direction:column; align-items:center; padding:1rem .5rem; gap:1rem; }
            @media (min-width:768px){ .la-sidebar{ display:flex; } }
            .la-sidebar img{ width:48px; height:48px; border-radius:50%; object-fit:cover; margin-bottom:.5rem; }
            .la-sidebar button{ background:transparent; border:none; cursor:pointer; padding:.5rem; color:#4B2E0E; }
            .la-sidebar button i{ font-size:20px; }

            /* Content shell */
            .main-content { padding: var(--spacing) 5vw; display:flex; flex-direction:column; gap:4rem; width:100%; }

            /* Animations */
            .fade-in { opacity:0; transform:translateY(30px); animation: fadeInUp 1s ease forwards; }
            @keyframes fadeInUp { to { opacity:1; transform:translateY(0); } }

            /* Hero (from coffee.php) */
            .hero { display:flex; align-items:center; justify-content:center; flex-wrap:wrap; gap:4rem; padding:2rem 0; }
            .hero img { max-width:800px; width:100%; flex:1 1 45%; border-radius:16px; box-shadow:0 4px 20px rgba(0,0,0,0.3); animation: float 6s ease-in-out infinite; }
            @keyframes float { 0%{transform:translateY(0)} 50%{transform:translateY(-10px)} 100%{transform:translateY(0)} }
            .hero-text { flex:1 1 50%; padding:2rem; max-width:700px; }
            .hero-text h1 { font-size:4.5rem; line-height:1.2; margin-bottom:1.5rem; letter-spacing:-1px; }
            .hero-text h1 span { color:var(--main-color); text-shadow:2px 2px 6px rgba(0,0,0,0.3); }
            .hero-text p { font-size:1.7rem; margin-bottom:2.5rem; line-height:1.6; }
            .hero-cta { display:flex; gap:.6rem; flex-wrap:wrap; margin-top:1rem; }
            .hero-text button { padding:.9rem 2rem; border:2px solid var(--white); border-radius:6px; background:transparent; color:var(--white); font-weight:bold; font-size:1.1rem; cursor:pointer; transition: all .3s ease, transform .2s ease; }
            .hero-text button:hover { background:rgba(255,255,255,.2); transform:scale(1.05); }

            /* Slideshow */
            .hero-viewport { position:relative; min-height:min(72vh, 720px); width:100%; border-radius:18px; overflow:hidden; }
            .slides { position:absolute; inset:0; }
            .slides img { position:absolute; inset:0; width:100%; height:100%; object-fit:cover; opacity:0; transition: opacity 900ms ease; }
            .slides img.active { opacity:1; }
            .hero-gradient { position:absolute; inset:0; background:linear-gradient(90deg, rgba(0,0,0,.7) 0%, rgba(0,0,0,.25) 55%, rgba(0,0,0,0) 100%); z-index:0; }
            .hero-overlay { position:relative; z-index:1; display:flex; align-items:center; height:100%; padding: clamp(1rem, 4.2vw, 3rem); }

            /* Section text (match coffee.php) */
            .section-title { font-size: clamp(2rem, 4vw, 3.2rem); margin:0 0 .6rem; font-weight:800; line-height:1.08; }
            /* Ensure hero h1 matches section-title styling, but use coffee.php font sizes */
            .hero-text h1.section-title { letter-spacing: normal; font-weight:800; line-height:1.08; font-size: 4.5rem; }
            .hero-text h1.section-title span{ text-shadow: none; }
            .section-sub { color: rgba(255,255,255,.88); margin:0 0 1.25rem; line-height:1.6; font-size:1.7rem; }

            /* Scroll gallery */
            .scroll-gallery { display:flex; gap:12px; overflow-x:auto; scroll-snap-type:x proximity; padding:2px 2px 10px; scrollbar-width:none; }
            .scroll-gallery::-webkit-scrollbar { display:none; }
            .scroll-gallery img { flex:0 0 auto; width:clamp(240px, 38vw, 480px); height:clamp(160px, 28vw, 320px); object-fit:cover; border-radius:12px; scroll-snap-align:start; box-shadow:0 6px 18px rgba(0,0,0,.25); }
            .scroll-gallery.dragging { scroll-snap-type:none; }

            /* Cards */
            .coffee-cards { display:grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap:2.5rem; }
            .card { background-color: var(--card-bg); border-radius:16px; overflow:hidden; transition: transform .3s ease, box-shadow .3s ease; box-shadow:0 4px 12px rgba(0,0,0,0.2); }
            .card:hover { transform: translateY(-8px); box-shadow:0 8px 20px rgba(0,0,0,0.4); }
            .card img { width:100%; height:180px; object-fit:cover; transition: transform .5s ease; }
            .card:hover img { transform: scale(1.1); }
            .card-body { padding:1.2rem; }
            .card h3 { font-size:1.3rem; margin-bottom:.6rem; color: var(--main-color); }
            .card p { font-size:1rem; line-height:1.5; }

            /* Responsive tweaks (mirror coffee.php) */
            @media (min-width:1025px){
              .hero-viewport{ display:grid; grid-template-columns: minmax(560px,58%) 1fr; align-items:stretch; }
              .slides{ position:relative; height:100%; }
              .hero-gradient{ display:none; }
              .hero-overlay{ position:relative; grid-column:2; display:flex; align-items:center; justify-content:center; padding: clamp(1rem, 4vw, 3rem); }
              .hero-text{ margin:0; max-width:680px; text-align:left; padding:0; }
              .hero-cta{ justify-content:flex-start; }
            }
            @media (max-width:1024px){
              .hero-text h1:not(.section-title){ font-size:3.2rem; }
              .hero-text h1.section-title{ font-size:3.2rem; }
              .hero-text p{ font-size:1.4rem; }
            }
            @media (max-width:768px){
              .hero{ flex-direction:column; gap:3rem; padding:1rem 0; }
              .hero-text{ padding:1rem; text-align:center; }
              .hero-text h1:not(.section-title){ font-size:2.8rem; }
              .hero-text h1.section-title{ font-size:2.8rem; }
              .hero-text p{ font-size:1.2rem; }
              .main-content{ padding:1rem; }
            }
            @media (max-width:480px){
              .main-content{ padding:1.25rem; }
              .coffee-cards{ grid-template-columns:1fr; }
              .hero-text h1:not(.section-title){ font-size:2rem; }
              .hero-text h1.section-title{ font-size:2rem; }
              .hero-text p{ font-size:1.05rem; }
              .section-sub{ font-size:1.05rem; }
              .hero-text button{ width:100%; }
            }
          </style>
        </head>
        <body class="bg-cover bg-center bg-no-repeat" style="background-image:url('../images/LAbg.png')">
          <!-- Mobile Top Bar (from backup) -->
          <div class="md:hidden flex items-center justify-between px-4 py-2 bg-white/90 backdrop-blur-sm shadow sticky top-0 z-30">
            <div class="flex items-center gap-2">
              <img src="../images/logo.png" alt="Logo" class="w-10 h-10 rounded-full" />
              <span class="font-semibold text-[#4B2E0E] text-lg">Home</span>
            </div>
            <div class="flex items-center gap-2">
              <button id="mobile-nav-toggle" class="p-2 rounded-full border border-[#4B2E0E] text-[#4B2E0E]" aria-label="Open navigation">
                <i class="fa-solid fa-bars"></i>
              </button>
            </div>
          </div>

          <!-- Mobile Slide-over Nav (from customerpage.php) -->
          <div id="mobile-nav-panel" class="md:hidden fixed inset-0 z-40 hidden" aria-hidden="true">
            <div class="absolute inset-0 bg-black/40" id="mobile-nav-backdrop"></div>
            <div class="absolute left-0 top-0 h-full w-60 bg-white shadow-lg p-4 flex flex-col gap-4 overflow-y-auto" role="dialog" aria-modal="true" aria-label="Navigation menu">
              <div class="flex justify-between items-center mb-2">
                <h2 class="text-[#4B2E0E] font-semibold">Navigation</h2>
                <button id="mobile-nav-close" class="text-gray-500 text-xl" aria-label="Close navigation"><i class="fa-solid fa-xmark"></i></button>
              </div>
              <?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
              <nav class="flex flex-col gap-2 text-sm" role="menu">
                <a href="../Customer/advertisement" class="flex items-center gap-2 px-3 py-2 rounded-md border <?php echo $currentPage=='advertisement.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]';?>" role="menuitem"><i class="fas fa-home"></i> Home</a>
                <a href="../Customer/customerpage" class="flex items-center gap-2 px-3 py-2 rounded-md border <?php echo $currentPage=='customerpage.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]';?>" role="menuitem"><i class="fas fa-shopping-cart"></i> Cart</a>
                <a href="../Customer/transactionrecords" class="flex items-center gap-2 px-3 py-2 rounded-md border <?php echo $currentPage=='transactionrecords.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]';?>" role="menuitem"><i class="fas fa-list"></i> Transactions</a>
                <a href="../all/setting" class="flex items-center gap-2 px-3 py-2 rounded-md border <?php echo $currentPage=='setting.php' ? 'bg-[#4B2E0E] text-white border-[#4B2E0E]' : 'border-gray-300 text-[#4B2E0E]';?>" role="menuitem"><i class="fas fa-cog"></i> Settings</a>
                <button id="logout-btn-mobile" class="flex items-center gap-2 px-3 py-2 rounded-md border border-gray-300 text-[#4B2E0E] text-left" role="menuitem"><i class="fas fa-sign-out-alt"></i> Logout</button>
              </nav>
            </div>
          </div>

          <div class="page-shell">
            <!-- Sidebar (from customerpage.php) -->
            <aside class="hidden md:flex bg-white bg-opacity-90 backdrop-blur-sm flex-col items-center py-6 space-y-8 shadow-lg la-sidebar" aria-label="Sidebar navigation">
              <img src="../images/logo.png" alt="Logo" class="w-12 h-12 rounded-full mb-5" />
              <?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
              <button title="Home" onclick="window.location.href='../Customer/advertisement'"><i class="fas fa-home text-xl <?= $currentPage == 'advertisement.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i></button>
              <button title="Cart" onclick="window.location.href='../Customer/customerpage'"><i class="fas fa-shopping-cart text-xl <?= $currentPage == 'customerpage.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i></button>
              <button title="Orders" onclick="window.location.href='../Customer/transactionrecords'"><i class="fas fa-list text-xl <?= $currentPage == 'transactionrecords.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i></button>
              <button title="Settings" onclick="window.location.href='../all/setting'"><i class="fas fa-cog text-xl <?= $currentPage == 'setting.php' ? 'text-[#C4A07A]' : 'text-[#4B2E0E]' ?>"></i></button>
              <button id="logout-btn" title="Logout"><i class="fas fa-sign-out-alt text-xl text-[#4B2E0E]"></i></button>
            </aside>

            <!-- Main content (coffee.php hero -> visit) -->
            <main class="main-content">
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
          <h1 class="section-title">Crafted Coffee,<br><span>Cozy Moments</span></h1>
          <p>Handcrafted espresso, creamy lattes, and seasonal flavors — brewed fresh for your best moments of the day.</p>
          <div class="hero-cta">
            <button onclick="location.href='#menu'" aria-label="View menu">View Menu</button>
            <button onclick="location.href='login.php'" aria-label="Order now">Order Now</button>
          </div>
        </div>
      </div>
    </section>

              <section id="story" class="fade-in">
                <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin-bottom:1rem;">
                  <div style="flex:1 1 60%;min-width:240px;">
                    <h2 class="section-title" style="font-size:clamp(2rem,4vw,3.2rem);margin:0;">What Makes <span style="color:var(--light-brown);">Love</span> <span style="color:var(--main-color);">Amaiah</span> Special</h2>
                    <p class="section-sub" style="margin-top:.6rem;max-width:68%;">From ethically sourced beans to warm service, we pour care into every cup. Stop by, slow down, and savor.</p>
                  </div>
                  <div style="flex:0 0 auto;opacity:.95;"></div>
                </div>
                <div class="scroll-gallery" aria-label="Highlights" style="padding:0;">
                  <div style="flex:0 0 auto;width:clamp(260px,30vw,420px);border-radius:18px;overflow:hidden;position:relative;background:#000;box-shadow:0 6px 18px rgba(0,0,0,.35);">
                    <img src="../images/ad2.jpg" alt="Signature Beverages" style="width:100%;height:220px;object-fit:cover;display:block;">
                    <div style="position:absolute;left:14px;bottom:14px;color:#fff;font-weight:800;padding:.6rem 1rem;background:linear-gradient(180deg,transparent,rgba(0,0,0,.48));border-radius:10px;">Signature Beverages</div>
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

              <section class="coffee-cards fade-in" id="menu">
                <h2 class="section-title" style="grid-column:1/-1">Featured drinks</h2>
                <div class="card">
                  <img src="../images/affogato.png" alt="Affogato coffee dessert" loading="lazy" width="600" height="400">
                  <div class="card-body"><h3>Affogato</h3><p>Espresso poured over vanilla ice cream — bold, creamy, and decadent.</p></div>
                </div>
                <div class="card">
                  <img src="../images/caramel_cloud_latte.png" alt="Caramel Cloud Latte drink" loading="lazy" width="600" height="400">
                  <div class="card-body"><h3>Caramel Cloud Latte</h3><p>Fluffy foam, bold espresso, and silky caramel — heavenly in every sip.</p></div>
                </div>
                <div class="card">
                  <img src="../images/cinnamon_macchiato.png" alt="Cinnamon Macchiato coffee" loading="lazy" width="600" height="400">
                  <div class="card-body"><h3>Cinnamon Macchiato</h3><p>Warm cinnamon meets espresso and milk — sweet, spicy, and smooth.</p></div>
                </div>
                <div class="card">
                  <img src="../images/iced_shaken_brownie.png" alt="Iced Brownie Espresso drink" loading="lazy" width="600" height="400">
                  <div class="card-body"><h3>Iced Brownie Espresso</h3><p>Shaken espresso with rich brownie flavor — bold, cold, and energizing.</p></div>
                </div>
              </section>

              <section id="visit" class="fade-in" style="display:grid; gap: .6rem;">
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:.5rem;align-items:stretch;">
                  <div style="width:100%;">
                    <div style="border-radius:20px;overflow:hidden;box-shadow:0 8px 30px rgba(0,0,0,.4);height:100%;">
                      <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d1936.02507423145!2d121.11573586846639!3d13.955619745329125!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x33bd6d0e92625b7f%3A0x5245bd256c2df445!2sLove%2C%20Amaiah%20Cafe!5e0!3m2!1sen!2sph!4v1760377982264!5m2!1sen!2sph" style="border:0;width:100%;height:100%;min-height:420px;max-height:560px;display:block" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                  </div>
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
          </div>

          <script>
            // Hero slideshow (from coffee.php)
            (function(){
              const slides = document.querySelectorAll('.slides img');
              if(!slides.length) return;
              let i = 0;
              setInterval(()=>{ slides[i].classList.remove('active'); i = (i + 1) % slides.length; slides[i].classList.add('active'); }, 4000);
            })();

            // Horizontal scroll auto-loop (from coffee.php)
            (function(){
              const scroller = document.querySelector('.scroll-gallery');
              if (!scroller) return;
              let isPaused = false;
              const prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
              const speed = prefersReduced ? 40 : 160;
              const original = Array.from(scroller.children);
              const cloneOnce = () => original.forEach(node => scroller.appendChild(node.cloneNode(true)));
              cloneOnce();
              if (scroller.scrollWidth <= scroller.clientWidth + 32) cloneOnce();
              let singleWidth = scroller.scrollWidth / 2;
              let startX=0, startScroll=0, dragging=false;
              const pageX = (e)=> (e.touches ? e.touches[0].pageX : e.pageX);
              const pause = ()=> { isPaused = true; };
              const resume = ()=> { isPaused = false; };
              const onDown = (e)=>{ dragging=true; scroller.classList.add('dragging'); startX=pageX(e); startScroll=scroller.scrollLeft; pause(); };
              const onMove = (e)=>{ if(!dragging) return; scroller.scrollLeft = startScroll - (pageX(e) - startX); };
              const onUp = ()=>{ if(!dragging) return; dragging=false; scroller.classList.remove('dragging'); setTimeout(resume, 600); };
              scroller.addEventListener('mousedown', onDown);
              scroller.addEventListener('touchstart', onDown, { passive:true });
              window.addEventListener('mousemove', onMove);
              window.addEventListener('touchmove', onMove, { passive:true });
              window.addEventListener('mouseup', onUp);
              window.addEventListener('touchend', onUp);
              scroller.addEventListener('wheel', ()=>{ pause(); clearTimeout(scroller._wheelT); scroller._wheelT = setTimeout(resume, 700); }, { passive:true });
              let last = performance.now();
              const loop = (now)=>{ const dt = (now - last)/1000; last = now; if(!isPaused){ scroller.scrollLeft += speed*dt; if(scroller.scrollLeft >= singleWidth){ scroller.scrollLeft -= singleWidth; } } requestAnimationFrame(loop); };
              requestAnimationFrame((t)=>{ last=t; loop(t); });
            })();

            // Mobile nav open/close (from backup)
            (function(){
              const mobileNavToggle = document.getElementById('mobile-nav-toggle');
              const mobileNavPanel = document.getElementById('mobile-nav-panel');
              const mobileNavClose = document.getElementById('mobile-nav-close');
              const mobileNavBackdrop = document.getElementById('mobile-nav-backdrop');
              function open(){ mobileNavPanel.classList.remove('hidden'); mobileNavPanel.setAttribute('aria-hidden','false'); document.body.classList.add('overflow-hidden'); }
              function close(){ mobileNavPanel.classList.add('hidden'); mobileNavPanel.setAttribute('aria-hidden','true'); document.body.classList.remove('overflow-hidden'); }
              mobileNavToggle?.addEventListener('click', open);
              mobileNavClose?.addEventListener('click', close);
              mobileNavBackdrop?.addEventListener('click', close);
            })();

            // Logout (match customerpage.php)
            (function(){
              const logoutBtn = document.getElementById('logout-btn');
              const logoutBtnMobile = document.getElementById('logout-btn-mobile');
              function handleLogout(){
                if (window.Swal){
                  Swal.fire({ title:'Are you sure you want to log out?', icon:'warning', showCancelButton:true, confirmButtonColor:'#4B2E0E', cancelButtonColor:'#d33', confirmButtonText:'Yes, log out', cancelButtonText:'Cancel' }).then(r=>{ if(r.isConfirmed){ window.location.href = '../all/logoutcos'; }});
                } else { if(confirm('Log out?')) window.location.href = '../all/logoutcos'; }
              }
              logoutBtn?.addEventListener('click', (e)=>{ e.preventDefault(); handleLogout(); });
              if (logoutBtnMobile){ logoutBtnMobile.addEventListener('click', (e)=>{ e.preventDefault(); logoutBtn?.click(); }); }
            })();
          </script>
        </body>
        </html>