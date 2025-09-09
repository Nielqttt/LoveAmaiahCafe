<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	<title>Love Amaiah — Our Company</title>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
	<link rel="stylesheet" href="../assets/css/responsive.css" />
	<style>
		:root{
			--accent:#4B2E0E;         /* deep coffee brown */
			--light-brown:#C4A07A;    /* warm highlight */
			--white:#fff;
			--text:#161616;
			--muted:#4b5563;
			--soft-bg:rgba(255,255,255,0.9);
		}
		*{box-sizing:border-box;margin:0;padding:0}
		body{
			font-family:"Segoe UI", system-ui, -apple-system, Roboto, Arial, sans-serif;
			color:var(--text);
			background:url('../images/LAbg.png') center/cover fixed no-repeat;
			min-height:100vh;
		}
		a{color:inherit;text-decoration:none}

		/* Top bar (kept consistent with coffee.php) */
		.top-bar{position:fixed;inset:0 0 auto 0;display:flex;justify-content:space-between;align-items:center;padding:1rem 5vw;background:rgba(0,0,0,.5);backdrop-filter:blur(8px);z-index:1000;box-shadow:0 2px 8px rgba(0,0,0,.2);}
		.top-bar.scrolled{background:rgba(0,0,0,.75);padding:.6rem 5vw}
		.logo-container{display:flex;align-items:center;gap:1rem;color:#fff}
		.logo-container img{height:48px;width:48px;border-radius:50%;object-fit:cover;border:2px solid rgba(255,255,255,.7)}
		.logo-container span{font-size:1.6rem;font-weight:800;background:linear-gradient(to right, var(--light-brown), #fff);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
		.auth-buttons{display:flex;gap:.75rem}
		.auth-buttons a{padding:.6rem 1rem;border:2px solid #fff;color:#fff;border-radius:8px;font-weight:600}

		main{padding:110px 0 0}
	.container{max-width:1280px;margin:0 auto;padding:0 5vw 4rem}

		/* Hero */
	.hero{display:grid;grid-template-columns:1.1fr .9fr;gap:3rem;align-items:center;margin-bottom:2.2rem}
	.hero h1{font-size:clamp(2.8rem, 3.5vw + 1rem, 4.8rem);line-height:1.05;margin-bottom:1rem;color:#fff}
	.hero p{color:#fff;font-size:clamp(1.15rem, 0.9vw + 0.8rem, 1.5rem);line-height:1.7}
	.hero .hero-media{border-radius:16px;overflow:hidden;box-shadow:0 12px 28px rgba(0,0,0,.28);border:1px solid rgba(0,0,0,.08)}
	.hero .hero-media img{display:block;width:100%;height:auto;min-height:420px;object-fit:cover}

		/* Sections */
		.section-card{background:var(--soft-bg);border:1px solid rgba(0,0,0,.06);border-radius:16px;padding:1.25rem 1.25rem 1.1rem;box-shadow:0 10px 24px rgba(0,0,0,.18);}
	.section{margin:2.6rem 0}
	.section h2{font-size:clamp(2rem, 1.2vw + 1.2rem, 2.6rem);color:var(--accent);margin:0 0 .6rem}
	.section p{color:#2d2d2d;line-height:1.85;font-size:1.125rem}
		/* On-dark variant for sections that sit over darker backgrounds */
		.section.light-on-dark h2{color:#fff}
		.section.light-on-dark p{color:#fff}

	.grid{display:grid;grid-template-columns:repeat(2,1fr);gap:3rem;align-items:center}
		.grid .img-wrap{border-radius:16px;overflow:hidden;box-shadow:0 10px 24px rgba(0,0,0,.22);border:1px solid rgba(0,0,0,.08);background:#fff}
	.grid .img-wrap img{display:block;width:100%;height:auto;min-height:360px;object-fit:cover}
		/* Optional gradient overlay utility for images */
		.img-wrap.gradient{position:relative}
		.img-wrap.gradient::after{content:"";position:absolute;inset:0;background:linear-gradient(160deg, rgba(0,0,0,.28), rgba(0,0,0,0));pointer-events:none}

	.pill{display:inline-flex;align-items:center;gap:.6rem;border-radius:9999px;padding:.7rem 1.15rem;border:1px solid rgba(0,0,0,.12);background:#fff;color:#111;font-weight:800;cursor:pointer;font-size:1.02rem}
		.pill.primary{background:var(--accent);color:#fff;border-color:transparent}
		.pill .fa{font-size:.95rem}

		/* Readability helper for dark backgrounds */
	.text-panel{background:rgba(0,0,0,.55);color:#fff;padding:1.35rem 1.6rem;border-radius:16px;border:1px solid rgba(255,255,255,.12);box-shadow:0 10px 24px rgba(0,0,0,.35);backdrop-filter:blur(4px)}
		.text-panel h2{color:#fff;text-shadow:0 2px 6px rgba(0,0,0,.35)}
		.text-panel p{color:#fff;text-shadow:0 1px 3px rgba(0,0,0,.35)}
		.badges{display:flex;gap:.5rem;flex-wrap:wrap;margin-top:.75rem}
		.badge{background:rgba(255,255,255,.18);color:#fff;border:1px solid rgba(255,255,255,.25);border-radius:9999px;padding:.25rem .6rem;font-weight:600;font-size:.85rem}

		/* Footer (same structure as coffee.php) */
		.site-footer{background:var(--soft-bg);color:#2b2b2b;border-top:1px solid rgba(0,0,0,.06)}
		.footer-content{padding:2rem 5vw}
		.footer-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:2rem 2.5rem}
		.footer-col h4{font-size:1.05rem;color:var(--accent);margin-bottom:.75rem}
		.footer-links{list-style:none}
		.footer-links li{margin:.4rem 0}
		.footer-links a{color:#444}
		.footer-links a:hover{color:var(--accent)}
		.footer-divider{border:none;border-top:1px solid rgba(0,0,0,.08);margin:1.25rem 0}
		.footer-social{display:flex;gap:.75rem;align-items:center}
		.footer-social .icon{width:36px;height:36px;border-radius:9999px;display:inline-flex;align-items:center;justify-content:center;background:#1f1f1f;color:#fff}
		.legal-links{display:flex;flex-wrap:wrap;gap:.8rem 1.25rem;color:#555;font-size:.9rem}
		.legal-links a{color:#555}
		.legal-links a:hover{color:var(--accent);text-decoration:underline}
		.copyright{margin-top:1rem;color:#666;font-size:.9rem}

		/* Responsive */
		@media (max-width: 960px){
			.hero{grid-template-columns:1fr;gap:2rem}
			.hero .hero-media img{min-height:320px}
		}
		@media (max-width: 768px){
			.grid{grid-template-columns:1fr;gap:1.6rem}
			.grid .img-wrap img{min-height:260px}
		}
	</style>
</head>
<body>
	<header class="top-bar">
		<a href="./coffee.php" class="logo-container">
			<img src="../images/logo.png" alt="Love Amaiah logo" />
			<span>Love Amaiah</span>
		</a>
		<button class="menu-toggle" aria-label="Open menu" aria-expanded="false" aria-controls="about-nav"><i class="fa-solid fa-bars"></i></button>
		<nav class="auth-buttons" id="about-nav" aria-label="Primary">
			<a href="https://maps.app.goo.gl/ruZNFNG7NkPm99sz8" target="_blank" rel="noopener"><i class="fa-solid fa-location-dot" style="margin-right:8px"></i>Find Our Store</a>
			<a href="./registration.php">Register</a>
			<a href="./login.php">Login</a>
		</nav>
	</header>
	<div id="nav-overlay" class="nav-overlay" aria-hidden="true"></div>

	<main>
		<div class="container">
			<!-- Hero -->
			<section class="hero">
				<div class="text-panel">
					<h1>Our Company</h1>
					<p>Rooted in warmth and community, Love Amaiah started with a simple idea: create a welcoming space where great coffee and good people meet. Here’s a peek into who we are and what we care about.</p>
				</div>
				<div class="hero-media"><img src="../images/mainpage_coffee.png" alt="Love Amaiah cafe and coffee" /></div>
			</section>

			<!-- Our Heritage -->
			<section class="section">
				<div class="section-card">
					<h2>Our Heritage</h2>
					<p>
						Love Amaiah began as a cozy neighborhood spot built on friendship, curiosity, and a love for carefully crafted drinks. From our earliest days, we believed every cup should tell a story—from the farmers who nurture the cherries to the roasters who coax out flavor and the baristas who serve with care. Over time, that philosophy became our compass as we grew from a single counter into a community hub.
					</p>
					<p style="margin-top:.9rem">
						Today we still obsess over the little things: the feel of a warm mug, the sound of milk steaming, the scent of freshly pulled espresso. We’re proud of our roots and excited for what’s ahead—inviting more people to gather, connect, and make everyday moments a little more memorable.
					</p>
				</div>
			</section>

			<!-- Coffee & Craft -->
			<section class="section light-on-dark">
				<div class="grid">
					<div class="img-wrap"><img src="../images/affogato.png" alt="Barista craft and pour" /></div>
					<div class="text-panel">
						<h2>Coffee &amp; Craft</h2>
						<p>
							It takes many hands to create the perfect cup. We partner with trusted growers, roast with intention, and train our baristas to bring out nuance in every blend. Whether you love bold and chocolatey or bright and fruit‑forward, we curate a menu that celebrates variety while keeping quality at the heart of it all.
						</p>
						<div style="margin-top:1rem">
							<a class="pill" href="./coffee.php"><i class="fa fa-mug-hot"></i> Learn more</a>
						</div>
					</div>
				</div>
			</section>

			<!-- Our Partners -->
			  <section class="section">
				<div class="grid" style="grid-auto-flow:dense">
				  <div class="text-panel">
						<h2>Our Partners</h2>
						<p>
							People make the difference. Our team—who we proudly call partners—bring hospitality to life with every conversation and every cup. We invest in their growth, well‑being, and success, and we champion a culture where everyone feels welcome.
						</p>
						<div style="margin-top:1rem">
							<a class="pill" href="./registration.php"><i class="fa fa-users"></i> Explore careers</a>
						</div>
					</div>
					<div class="img-wrap"><img src="../images/barista.png" alt="Our partners at work" /></div>
				</div>
			</section>

			<!-- Doing Good -->
					<section class="section">
						<div class="grid">
							<div class="img-wrap gradient"><img src="../images/iced_shaken_brownie.png" alt="Community and nature" /></div>
							<div class="text-panel">
						<h2>We Believe in the Pursuit of Doing Good</h2>
						<p>
							Our purpose goes beyond the walls of our cafe. From reducing waste and choosing responsible ingredients to supporting local initiatives, we aim to leave every place better than we found it. Small actions add up—and together with our guests, we can brew a positive impact.
						</p>
								<div class="badges" aria-hidden="true">
									<span class="badge">People</span>
									<span class="badge">Planet</span>
									<span class="badge">Community</span>
								</div>
						<div style="margin-top:1rem">
							<a class="pill primary" href="https://maps.app.goo.gl/ruZNFNG7NkPm99sz8" target="_blank" rel="noopener"><i class="fa fa-location-dot"></i> Visit us</a>
						</div>
					</div>
				</div>
			</section>
		</div>
	</main>



	<!-- Simple navbar scroll + burger toggle -->
	<script>
		document.documentElement.classList.add('has-js');
		window.addEventListener('scroll', () => {
			const tb = document.querySelector('.top-bar');
			if (window.scrollY > 50) tb.classList.add('scrolled'); else tb.classList.remove('scrolled');
		});
		(function(){
			const header = document.querySelector('.top-bar');
			const toggle = document.querySelector('.menu-toggle');
			const nav = document.getElementById('about-nav');
			const overlay = document.getElementById('nav-overlay');
			if(!header || !toggle || !nav || !overlay) return;
			function closeNav(){ header.classList.remove('nav-open'); toggle.setAttribute('aria-expanded','false'); overlay.style.display='none'; document.body.style.overflow=''; toggle.innerHTML = '<i class="fa-solid fa-bars"></i>'; }
			function openNav(){ header.classList.add('nav-open'); toggle.setAttribute('aria-expanded','true'); overlay.style.display='block'; document.body.style.overflow='hidden'; toggle.innerHTML = '<i class="fa-solid fa-xmark"></i>'; }
			toggle.addEventListener('click', ()=> header.classList.contains('nav-open') ? closeNav() : openNav());
			overlay.addEventListener('click', closeNav);
			nav.addEventListener('click', (e)=>{ if(e.target.closest('a')) closeNav(); });
			window.addEventListener('keydown', (e)=>{ if(e.key==='Escape') closeNav(); });
			window.addEventListener('resize', ()=>{ if(window.innerWidth > 1024) closeNav(); });
		})();
	</script>

		<!-- Pickup modal (parity with coffee.php) -->
		<div id="pickup-modal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="pickup-title" style="position:fixed;inset:0;background:rgba(0,0,0,.5);backdrop-filter:blur(2px);display:none;align-items:center;justify-content:center;z-index:1050">
			<div class="modal-card" role="document" style="background:var(--soft-bg);color:#2b2b2b;border-radius:16px;padding:1.25rem 1.25rem 1rem;width:min(95vw,520px);border:1px solid rgba(0,0,0,.08);box-shadow:0 12px 28px rgba(0,0,0,.25)">
				<h3 id="pickup-title" style="margin:0 0 .5rem 0;color:var(--accent);font-size:1.2rem">Order &amp; Pick Up Options</h3>
				<p style="margin:.25rem 0 .75rem;color:#3b3b3b">You can log in and order from our website, then pick it up later in our store.</p>
				<div class="modal-actions" style="display:flex;justify-content:flex-end;gap:.5rem;margin-top:.5rem">
					<button type="button" id="pickup-close" style="background:#fff;color:#333;border:1px solid rgba(0,0,0,.12);border-radius:9999px;padding:.5rem .9rem;font-weight:600;cursor:pointer">Close</button>
					<a href="./login.php" id="pickup-login" style="background:var(--accent);color:#fff;border:none;border-radius:9999px;padding:.5rem .9rem;font-weight:700;cursor:pointer;display:inline-flex;align-items:center">Login to order</a>
				</div>
			</div>
		</div>

		<script>
			(function(){
				const trigger = document.getElementById('pickup-options-link');
				const overlay = document.getElementById('pickup-modal');
				const btnClose = document.getElementById('pickup-close');
				const btnLogin = document.getElementById('pickup-login');
				if (!trigger || !overlay) return;
				function openModal(e){ if(e) e.preventDefault(); overlay.style.display='flex'; document.body.style.overflow='hidden'; btnLogin && btnLogin.focus(); }
				function closeModal(){ overlay.style.display='none'; document.body.style.overflow=''; }
				trigger.addEventListener('click', openModal);
				btnClose && btnClose.addEventListener('click', closeModal);
				overlay.addEventListener('click', (ev)=>{ if(ev.target===overlay) closeModal(); });
				document.addEventListener('keydown', (ev)=>{ if(ev.key==='Escape' && overlay.style.display==='flex') closeModal(); });
			})();
		</script>
</body>
</html>

    