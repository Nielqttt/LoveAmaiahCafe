<?php
// Cookie Preferences page
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Love Amaiah — Cookie Preferences</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <meta name="description" content="Manage your cookie preferences for Love Amaiah." />
  <meta name="theme-color" content="#4B2E0E" />
  <link rel="icon" href="../images/logo.png" />
  <style>
    :root { --accent:#4B2E0E; --ink:#111827; --text:#374151; --muted:#6b7280; --border:rgba(0,0,0,0.08); --soft:rgba(255,255,255,0.9);} 
    *{ box-sizing:border-box; }
    body{ margin:0; font-family: system-ui, -apple-system, Segoe UI, Roboto, 'Inter', Arial, sans-serif; background:#ffffff; color:var(--ink); }

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

    .wrap{ max-width:900px; margin: 1.5rem auto 3rem; padding: 0 1rem; }
    .header{ margin-bottom:1rem; }
    h1{ font-size: clamp(1.6rem,3.5vw,2.2rem); margin:.25rem 0; }
    p{ color:var(--text); line-height:1.7; }
    .muted{ color:var(--muted); font-size:.95rem; }

    .panel{ background:var(--soft); border:1px solid var(--border); border-radius:16px; padding:1.25rem; }
    .row{ display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; padding:.75rem 0; border-bottom:1px solid var(--border); }
    .row:last-child{ border-bottom:none; }
    .row h3{ margin:.1rem 0 .25rem; font-size:1.05rem; }
    .row p{ margin:.1rem 0; }

    .switch{ position:relative; display:inline-block; width:46px; height:26px; }
    .switch input{ opacity:0; width:0; height:0; }
    .slider{ position:absolute; cursor:pointer; inset:0; background:#d1d5db; transition:.2s; border-radius:9999px; }
    .slider:before{ position:absolute; content:""; height:20px; width:20px; left:3px; top:3px; background:white; transition:.2s; border-radius:50%; box-shadow:0 1px 2px rgba(0,0,0,0.2); }
    input:checked + .slider{ background:var(--accent); }
    input:checked + .slider:before{ transform: translateX(20px); }
    input:disabled + .slider{ background:#9ca3af; cursor:not-allowed; }

    .actions-row{ display:flex; flex-wrap:wrap; gap:.6rem; margin-top:1rem; }
    .btn-outline{ background:#fff; color:#1f2937; border:1px solid var(--border); border-radius:9999px; padding:.5rem .95rem; text-decoration:none; font-weight:600; }
    .btn-outline:hover{ background:#f8f8f8; }

    .alert{ display:none; margin-top:.75rem; background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; border-radius:10px; padding:.65rem .8rem; }
  </style>
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
    <div class="header">
      <h1>Cookie preferences</h1>
      <p class="muted">Choose which cookies we may use on your device. Necessary cookies are always on to keep the site working.</p>
    </div>

    <div class="panel" role="form" aria-labelledby="cookie-prefs">
      <div class="row">
        <div>
          <h3 id="cookie-prefs">Strictly necessary</h3>
          <p>Required for core site functionality such as security and session management.</p>
        </div>
        <label class="switch" title="Strictly necessary cookies are required">
          <input type="checkbox" checked disabled />
          <span class="slider" aria-hidden="true"></span>
        </label>
      </div>

      <div class="row">
        <div>
          <h3>Functional</h3>
          <p>Helps remember your preferences (like language) to provide a better experience.</p>
        </div>
        <label class="switch">
          <input id="c-functional" type="checkbox" />
          <span class="slider"></span>
        </label>
      </div>

      <div class="row">
        <div>
          <h3>Performance & analytics</h3>
          <p>Helps us understand site usage to improve performance and content.</p>
        </div>
        <label class="switch">
          <input id="c-analytics" type="checkbox" />
          <span class="slider"></span>
        </label>
      </div>

      <div class="row">
        <div>
          <h3>Marketing</h3>
          <p>Used to personalize offers and measure marketing effectiveness.</p>
        </div>
        <label class="switch">
          <input id="c-marketing" type="checkbox" />
          <span class="slider"></span>
        </label>
      </div>

      <div class="actions-row">
        <button id="save" class="btn">Save preferences</button>
        <button id="accept" class="btn-outline">Accept all</button>
        <button id="reject" class="btn-outline">Reject non-essential</button>
      </div>

      <div id="notice" class="alert" role="status">Your cookie preferences have been saved.</div>
    </div>

    <p style="margin-top:1rem; color:#374151;">Learn more in our <a href="./privacy-notice.php#cookies">Privacy Notice</a>.</p>
  </main>

  <script>
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
      function setStateFromConsent(){
        const c = readConsent();
        if(!c) return;
        document.getElementById('c-functional').checked = !!c.functional;
        document.getElementById('c-analytics').checked = !!c.analytics;
        document.getElementById('c-marketing').checked = !!c.marketing;
      }
      function saveFromUI(custom){
        const consent = custom || {
          necessary: true,
          functional: document.getElementById('c-functional').checked,
          analytics: document.getElementById('c-analytics').checked,
          marketing: document.getElementById('c-marketing').checked,
          ts: new Date().toISOString(),
          v: 1
        };
        writeConsent(consent);
        const n = document.getElementById('notice');
        n.style.display = 'block';
        setTimeout(()=>{ n.style.display='none'; }, 3000);
      }

      document.getElementById('save').addEventListener('click', ()=> saveFromUI());
      document.getElementById('accept').addEventListener('click', ()=> saveFromUI({necessary:true,functional:true,analytics:true,marketing:true,ts:new Date().toISOString(),v:1}));
      document.getElementById('reject').addEventListener('click', ()=> saveFromUI({necessary:true,functional:false,analytics:false,marketing:false,ts:new Date().toISOString(),v:1}));

      setStateFromConsent();
    })();
  </script>
</body>
</html>
