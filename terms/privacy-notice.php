<?php
// Privacy Notice page
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Love Amaiah — Privacy Notice</title>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
	<style>
		:root {
			--accent: #4B2E0E;
			--light: #F7F2EC;
			--ink: #1f2937;
			--muted: #6b7280;
			--soft: rgba(255,255,255,0.85);
		}
		* { box-sizing: border-box; }
		html, body { height: 100%; }
		body { margin: 0; font-family: system-ui, -apple-system, Segoe UI, Roboto, 'Inter', Arial, sans-serif; background: #ffffff; color: var(--ink); }

		/* Top bar */
		.topbar { position: sticky; top: 0; z-index: 50; background: #fff; border-bottom: 1px solid rgba(0,0,0,0.06); }
		.topbar-inner { max-width: 1200px; margin: 0 auto; padding: .8rem 1rem; display: flex; align-items: center; justify-content: space-between; }
		.brand { display: flex; align-items: center; gap: .6rem; text-decoration: none; color: var(--ink); }
		.brand img { width: 36px; height: 36px; border-radius: 50%; }
		.brand span { font-weight: 800; color: var(--accent); letter-spacing: .2px; }
		.actions { display: flex; align-items: center; gap: .5rem; }
		.link { color: var(--ink); text-decoration: none; padding: .5rem .8rem; border-radius: 9999px; }
		.link:hover { background: rgba(0,0,0,0.04); }
		.btn { background: var(--accent); color: #fff; border-radius: 9999px; padding: .5rem .9rem; text-decoration: none; font-weight: 700; }
		.btn:hover { filter: brightness(1.05); }

		/* Layout */
		.container { max-width: 1200px; margin: 0 auto; padding: 1.5rem 1rem 3rem; display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; }
		.left { padding-top: 1rem; }
		.title { font-size: clamp(1.8rem, 3vw, 2.2rem); font-weight: 800; color: #111827; }
		.meta { margin-top: .75rem; color: var(--muted); font-size: .95rem; }

		.right { background: var(--soft); border: 1px solid rgba(0,0,0,0.06); border-radius: 16px; padding: 1.25rem 1.25rem; }
		.intro { background: var(--light); border: 1px solid rgba(0,0,0,0.06); border-radius: 12px; padding: 1rem; color: #374151; }
		h2 { margin: 1.25rem 0 .5rem; font-size: 1rem; color: #111827; letter-spacing: .2px; }
		h2.section { margin-top: 1.5rem; font-size: 1.05rem; color: #111827; }
		p { color: #374151; line-height: 1.7; margin: .5rem 0; }
		ul { margin: .4rem 0 .8rem 1.1rem; color: #374151; }
		a { color: var(--accent); }

		.toc { margin-top: 1rem; display: grid; gap: .4rem; }
		.toc a { text-decoration: none; color: #374151; padding: .35rem .5rem; border-radius: 8px; }
		.toc a:hover { background: rgba(0,0,0,0.04); }

		@media (max-width: 900px) { .container { grid-template-columns: 1fr; } .right { padding: 1rem; } }
	</style>
</head>
<body>
	<header class="topbar">
		<div class="topbar-inner">
			<a class="brand" href="../all/coffee"><img src="../images/logo.png" alt="Love Amaiah"><span>Love Amaiah</span></a>
			<nav class="actions">
				<a class="link" href="https://maps.app.goo.gl/ruZNFNG7NkPm99sz8" target="_blank" rel="noopener"><i class="fa-solid fa-location-dot" style="margin-right:.4rem"></i>Find a store</a>
				<a class="link" href="../all/login">Sign in</a>
				<a class="btn" href="../all/registration">Join now</a>
			</nav>
		</div>
	</header>

	<main class="container">
		<aside class="left">
			<div class="title">Love Amaiah Privacy Notice</div>
			<div class="meta">Last Revised: August 21, 2025</div>
			<div class="toc">
				<a href="#overview">Overview</a>
				<a href="#collect">Information We Collect</a>
				<a href="#use">How We Use Information</a>
				<a href="#cookies">Cookies & Similar Technologies</a>
			<a href="#health">Consumer Health Privacy Notice</a>
				<a href="#choices">Your Choices</a>
				<a href="#contact">Contact Us</a>
			</div>
		</aside>

		<section class="right">
			<div class="intro">
				<strong>At Love Amaiah, we approach data and privacy the same way we approach coffee: we put people first.</strong>
				<p>We strive to protect your information and comply with applicable privacy laws. We consider principles like data minimization, limited collection, and limited use. Taking care of your data is part of how we take care of you.</p>
			</div>

			<h2 id="overview" class="section">Overview</h2>
			<p>This Privacy Notice explains the types of personal information that Love Amaiah ("we," "us") collects, how we use it, how and when it may be shared, and the rights and choices you have. It also explains how you can contact us about our privacy practices.</p>

			<h2 id="collect" class="section">Information We Collect</h2>
			<ul>
				<li>Account details, such as name, email, phone number, and username.</li>
				<li>Order and transaction information (items purchased, prices, dates).</li>
				<li>Device and usage data (browser type, pages viewed) to improve our services.</li>
				<li>Optional marketing preferences and feedback you provide.</li>
			</ul>

			<h2 id="use" class="section">How We Use Information</h2>
			<ul>
				<li>To provide, personalize, and improve ordering and pickup services.</li>
				<li>To communicate with you about orders, updates, and service changes.</li>
				<li>To maintain security, prevent fraud, and comply with legal obligations.</li>
				<li>With your consent, to send promotions or updates you might enjoy.</li>
			</ul>

			<h2 id="cookies" class="section">Cookies & Similar Technologies</h2>
			<p>We use cookies and similar technologies to keep you signed in, remember preferences, and analyze site performance. You can control cookies through your browser settings; some features may not work without them.</p>

					<h2 id="health" class="section">Consumer Health Privacy Notice</h2>
					<p>Some jurisdictions have laws that govern the collection and use of <em>consumer health data</em>. This section explains our practices with respect to such data.</p>
					<h2>What is consumer health data?</h2>
					<p>Consumer health data may include information linked to you that identifies your physical or mental health status, health conditions, treatments, measurements, or inferences drawn from your activity that could reasonably indicate your health status.</p>
					<h2>Do we collect consumer health data?</h2>
					<p>We do not intentionally collect sensitive consumer health data in the ordinary course of operating our coffee website and services. If you choose to share information that could be considered health-related (for example, notes about allergens or dietary preferences), we use it solely to fulfill your requests and improve your experience.</p>
					<h2>How we use any health-related information you provide</h2>
					<ul>
						<li>To prepare your orders in accordance with your preferences (e.g., allergen avoidance).</li>
						<li>To provide customer support when you contact us about an order or experience.</li>
						<li>To maintain safety and comply with applicable laws.</li>
					</ul>
					<h2>Sharing</h2>
					<p>We do not sell consumer health data. We may share information with service providers that process data on our behalf (such as hosting, payment, or analytics) under contracts that require them to protect the data and use it only for our instructions.</p>
					<h2>Retention</h2>
					<p>We retain information only as long as necessary to provide services, comply with legal obligations, resolve disputes, and enforce agreements. We apply minimization practices to avoid keeping data longer than needed.</p>
					<h2>Your rights</h2>
					<p>Depending on your location, you may have the right to access, correct, delete, or limit the use of your information. To exercise these rights, contact us as described below. We will verify your request as required by law.</p>
					<h2>Region-specific disclosures</h2>
					<p>If your local law provides specific rights or definitions for consumer health data, we will honor those rights to the extent applicable. You can contact us for more details about how these laws may apply to you.</p>

			<h2 id="choices" class="section">Your Choices</h2>
			<ul>
				<li>You can update account information from your profile after signing in.</li>
				<li>You can opt in or out of marketing communications at any time.</li>
				<li>You may request access to or deletion of your personal data, subject to applicable law.</li>
			</ul>

			<h2 id="contact" class="section">Contact Us</h2>
			<p>If you have questions about this Notice or our practices, contact us at <a href="mailto:privacy@loveamaiah.example">privacy@loveamaiah.example</a>.</p>
		</section>
	</main>
</body>
</html>
