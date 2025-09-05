<?php
// Accessibility page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Love Amaiah — Accessibility</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
        :root { --accent:#4B2E0E; --ink:#111827; --text:#374151; --muted:#6b7280; --border:rgba(0,0,0,0.08); }
        *{ box-sizing:border-box; }
        body{ margin:0; font-family: system-ui, -apple-system, Segoe UI, Roboto, 'Inter', Arial, sans-serif; background:#ffffff; color:var(--ink); }

        .topbar{ position: sticky; top:0; z-index:50; background:#fff; border-bottom:1px solid var(--border);} 
        .topbar-inner{ max-width:1100px; margin:0 auto; padding:.8rem 1rem; display:flex; align-items:center; justify-content:space-between; }
        .brand{ display:flex; align-items:center; gap:.6rem; text-decoration:none; color:var(--ink);} 
        .brand img{ width:36px; height:36px; border-radius:50%; }
        .brand span{ font-weight:800; color:var(--accent); letter-spacing:.2px; }
        .actions{ display:flex; align-items:center; gap:.5rem; }
        .link{ color:var(--ink); text-decoration:none; padding:.5rem .8rem; border-radius:9999px; }
        .link:hover{ background:rgba(0,0,0,0.04); }
        .btn{ background:var(--accent); color:#fff; border-radius:9999px; padding:.5rem .9rem; text-decoration:none; font-weight:700; }
        .btn:hover{ filter:brightness(1.05); }

        .wrap{ max-width:900px; margin: 2rem auto 3rem; padding: 0 1rem; }
        .header{ margin-bottom:1.5rem; }
        .header h1{ font-size: clamp(1.8rem,3.5vw,2.4rem); margin:.25rem 0; }
        .updated{ color:var(--muted); font-size:.95rem; }

        section{ margin: 2rem 0; }
        h2{ font-size: clamp(1.25rem,2.5vw,1.5rem); margin:0 0 .5rem; }
        p{ color:var(--text); line-height:1.75; margin:.35rem 0; }
        a{ color:var(--accent); }
        ul{ margin:.5rem 0 .5rem 1.1rem; color:var(--text); }

        .callout{ background:#f8fafc; border:1px solid var(--border); border-radius:14px; padding:1rem; color:var(--text); }
        .cta-row{ margin-top:1rem; display:flex; gap:.6rem; flex-wrap:wrap; }
        .btn-outline{ background:#fff; color:#1f2937; border:1px solid var(--border); border-radius:9999px; padding:.5rem .95rem; text-decoration:none; font-weight:600; }
        .btn-outline:hover{ background:#f8f8f8; }

        .disclaimer{ border-top:1px solid var(--border); padding-top:1rem; margin-top:2rem; color:var(--text); }
    </style>
    <meta name="description" content="Our commitment to accessibility at Love Amaiah across physical and digital experiences." />
    <meta name="theme-color" content="#4B2E0E" />
    <link rel="icon" href="../images/logo.png" />
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

    <main class="wrap">
        <div class="header">
            <h1>Commitment to inclusion and accessibility</h1>
            <div class="updated">Last updated: August 22, 2025</div>
        </div>

        <section id="commitment">
            <p>At Love Amaiah, our mission is to create welcoming experiences for every guest. We are committed to upholding a culture where inclusion, diversity, equity, and accessibility are valued and respected. Working with our community and partners, we continue to expand accessibility resources and practices across our physical stores and digital experiences.</p>
            <p>If you have questions or need assistance, please contact us at <a href="mailto:support@loveamaiah.example">support@loveamaiah.example</a>.</p>
        </section>

        <section id="inclusive-spaces">
            <h2>Inclusive spaces</h2>
            <p>We are working to enhance accessibility across our store environments through an inclusive design approach. Our framework focuses on independence, choice, and ease-of-use for all people in both physical and digital spaces. We incorporate feedback from a diverse community of customers, partners, and accessibility experts to develop solutions that scale.</p>
            <p>As we renovate or open new locations, we aim to incorporate inclusive design elements that support mobility, hearing, and vision accessibility, and to provide clear signage and staff support where feasible.</p>
        </section>

        <section id="digital-accessibility">
            <h2>Digital accessibility</h2>
            <p>We strive to make Love Amaiah digital properties accessible for all users. Our efforts are guided by the Web Content Accessibility Guidelines (WCAG) 2.1 Levels A and AA and are informed by applicable laws and standards, such as the Americans with Disabilities Act (ADA) and Section 508 of the U.S. Rehabilitation Act.</p>
            <ul>
                <li>Designing and building with semantic HTML and accessible components.</li>
                <li>Providing sufficient color contrast, keyboard navigation, and clear focus states.</li>
                <li>Supporting screen readers with appropriate labels and alt text.</li>
                <li>Testing iteratively and addressing accessibility bugs as they are identified.</li>
            </ul>
            <div class="callout">
                Our accessibility program is ongoing. As we ship improvements, some areas may not yet fully meet our goals. We appreciate your feedback.
            </div>
        </section>

        <section id="feedback">
            <h2>Digital accessibility feedback</h2>
            <p>If you are a customer with a disability or assisting someone with a disability and are experiencing issues accessing any Love Amaiah digital content, please reach out. You can also request information about our digital accessibility efforts or share feedback:</p>
            <div class="cta-row">
                <a class="btn-outline" href="mailto:accessibility@loveamaiah.example?subject=Digital%20Accessibility%20Request">Digital accessibility contact form</a>
                <a class="btn-outline" href="mailto:support@loveamaiah.example?subject=Customer%20Service">Customer service</a>
            </div>
        </section>

        <section id="disclaimer" class="disclaimer">
            <h2>Disclaimer</h2>
            <p>Please be aware that our efforts to maintain accessibility and usability are ongoing. While we strive to make the Love Amaiah website and applications as accessible as possible, some issues may be encountered by different assistive technologies as the range of those technologies is wide and varied. We appreciate your understanding.</p>
        </section>
    </main>
</body>
</html>
