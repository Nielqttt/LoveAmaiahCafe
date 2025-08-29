<?php
// Do Not Share My Personal Information (Opt-out) page
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Love Amaiah — Do Not Share My Personal Information</title>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
	<style>
		:root { --accent:#4B2E0E; --ink:#1f2937; --muted:#6b7280; --soft:rgba(255,255,255,0.9); --border:rgba(0,0,0,0.08);} 
		*{ box-sizing:border-box; }
		body{ margin:0; font-family: system-ui, -apple-system, Segoe UI, Roboto, 'Inter', Arial, sans-serif; background:#ffffff; color:var(--ink); }

		/* Top bar (matches privacy/terms) */
		.topbar{ position: sticky; top:0; z-index:50; background:#fff; border-bottom:1px solid var(--border);} 
		.topbar-inner{ max-width:900px; margin:0 auto; padding:.8rem 1rem; display:flex; align-items:center; justify-content:space-between; }
		.brand{ display:flex; align-items:center; gap:.6rem; text-decoration:none; color:var(--ink);} 
		.brand img{ width:36px; height:36px; border-radius:50%; }
		.brand span{ font-weight:800; color:var(--accent); letter-spacing:.2px; }
		.actions{ display:flex; align-items:center; gap:.5rem; }
		.link{ color:var(--ink); text-decoration:none; padding:.5rem .8rem; border-radius:9999px; }
		.link:hover{ background:rgba(0,0,0,0.04); }
		.btn{ background:var(--accent); color:#fff; border-radius:9999px; padding:.5rem .9rem; text-decoration:none; font-weight:700; }
		.btn:hover{ filter:brightness(1.05); }

		/* Main content */
		.wrap{ max-width:900px; margin: 2rem auto 3rem; padding: 0 1rem; }
		.panel{ background:var(--soft); border:1px solid var(--border); border-radius:16px; padding:2rem 1.25rem; }
		h1{ text-align:center; font-size: clamp(1.6rem, 3vw, 2rem); margin:0 0 .75rem; }
		p.lead{ text-align:center; color:#374151; max-width:720px; margin:.25rem auto 1rem; line-height:1.7; }
		.meta{ text-align:center; color:var(--muted); font-size:.95rem; margin-bottom:1rem; }

		form{ max-width:520px; margin: 1rem auto 0; display:grid; gap:.75rem; }
		label{ font-weight:600; color:#111827; }
		.req{ color:#10b981; font-weight:600; margin-left:.25rem; }
		input[type=email]{ width:100%; padding:.75rem .9rem; border:1px solid var(--border); border-radius:8px; font-size:1rem; background:#fff; color:#111827; }
		input[type=email]:focus{ outline:3px solid rgba(75,46,14,.15); border-color:rgba(75,46,14,.35); }
		.actions-row{ display:flex; flex-wrap:wrap; gap:.6rem; align-items:center; justify-content:center; margin-top:.5rem; }
		.btn-primary{ background:var(--accent); color:#fff; border:none; border-radius:9999px; padding:.55rem 1.1rem; font-weight:700; cursor:pointer; }
		.btn-primary:hover{ filter:brightness(1.05); }
		.btn-outline{ background:#fff; color:#1f2937; border:1px solid var(--border); border-radius:9999px; padding:.5rem .95rem; text-decoration:none; font-weight:600; }
		.btn-outline:hover{ background:#f8f8f8; }

		.note{ text-align:center; color:#374151; margin-top:.75rem; font-size:.95rem; }
		.note a{ color:var(--accent); }

		.alert{ max-width:520px; margin: .75rem auto 0; background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; border-radius:10px; padding:.65rem .8rem; display:none; }

		@media (max-width: 520px){ .panel{ padding:1.25rem .9rem; } }
	</style>
	<meta name="robots" content="noindex,follow" />
	<meta name="description" content="Opt out of sharing your personal information for targeted advertising at Love Amaiah." />
	<meta name="theme-color" content="#4B2E0E" />
	<link rel="icon" href="../images/logo.png" />
</head>
<body>
	<header class="topbar">
		<div class="topbar-inner">
			<a class="brand" href="../all/coffee.php"><img src="../images/logo.png" alt="Love Amaiah"><span>Love Amaiah</span></a>
			<nav class="actions">
				<a class="link" href="https://maps.app.goo.gl/ruZNFNG7NkPm99sz8" target="_blank" rel="noopener"><i class="fa-solid fa-location-dot" style="margin-right:.4rem"></i>Find a store</a>
				<a class="link" href="../all/login.php">Sign in</a>
				<a class="btn" href="../all/registration.php">Join now</a>
			</nav>
		</div>
	</header>

	<main class="wrap">
		<div class="panel">
			<h1>Do not share my personal information</h1>
			<p class="lead">We will honor your choice to opt out of sharing personal information for targeted advertising. This choice does not impact your account experience and can be managed any time in your account settings. You can also control your data collection through cookie preferences. See our <a href="./privacy-notice.php">Privacy Notice</a> for more details.</p>
			<div class="meta">* indicates required field</div>

			<form id="optout-form" novalidate>
				<div>
					<label for="email">Email address<span class="req">*</span></label>
					<input id="email" name="email" type="email" placeholder="you@example.com" autocomplete="email" required />
				</div>
				<div class="actions-row">
					<button type="submit" class="btn-primary">Submit</button>
					<a href="./privacy-notice.php#cookies" class="btn-outline">Manage cookie preferences</a>
				</div>
			</form>

			<div id="success" class="alert" role="status">Your preference has been saved for this browser.</div>

			<div class="note">Tip: If you have a Love Amaiah account, you can also review marketing preferences after you <a href="../all/login.php">sign in</a>.</div>
		</div>
	</main>

	<script>
	(function(){
		const form = document.getElementById('optout-form');
		const email = document.getElementById('email');
		const success = document.getElementById('success');
		function setOptOutCookie(){
			const twoYears = 60*60*24*730; // seconds
			document.cookie = `la_optout_adsharing=1; max-age=${twoYears}; path=/; SameSite=Lax`;
		}
		form.addEventListener('submit', function(e){
			e.preventDefault();
			if(!email.checkValidity()){
				email.reportValidity();
				email.focus();
				return;
			}
			// In a real implementation, send to server using fetch(). Here we persist a browser cookie.
			setOptOutCookie();
			success.style.display = 'block';
			form.querySelector('button[type="submit"]').disabled = true;
			email.setAttribute('readonly', 'readonly');
		});
	})();
	</script>
</body>
</html>
