(function () {
  const COOKIE_NAME = "la_cookie_consent";
  function readConsent() {
    const m = document.cookie.match(
      new RegExp("(?:^|; )" + COOKIE_NAME + "=([^;]*)")
    );
    if (!m) return null;
    try {
      return JSON.parse(decodeURIComponent(m[1]));
    } catch {
      return null;
    }
  }
  function writeConsent(obj) {
    const oneYear = 60 * 60 * 24 * 365;
    const val = encodeURIComponent(JSON.stringify(obj));
    let cookie =
      COOKIE_NAME +
      "=" +
      val +
      "; max-age=" +
      oneYear +
      "; path=/; SameSite=Lax";
    if (location.protocol === "https:") cookie += "; Secure";
    document.cookie = cookie;
    document.dispatchEvent(
      new CustomEvent("la:consent-updated", { detail: obj })
    );
  }
  function ensureDefaults(consent) {
    if (!consent) return null;
    return {
      necessary: true,
      functional: !!consent.functional,
      analytics: !!consent.analytics,
      marketing: !!consent.marketing,
      ts: consent.ts || new Date().toISOString(),
      v: consent.v || 1,
    };
  }
  function loadScript(src, attrs) {
    return new Promise((resolve, reject) => {
      const s = document.createElement("script");
      s.src = src;
      s.async = true;
      if (attrs) {
        Object.keys(attrs).forEach((k) => {
          if (attrs[k] === true) s.setAttribute(k, "");
          else if (attrs[k] !== false && attrs[k] != null)
            s.setAttribute(k, attrs[k]);
        });
      }
      s.onload = resolve;
      s.onerror = reject;
      document.head.appendChild(s);
    });
  }
  function injectBanner() {
    if (document.getElementById("la-cookie-banner")) return;
    const style = document.createElement("style");
    style.textContent = `#la-cookie-banner{position:fixed;left:16px;right:16px;bottom:16px;z-index:9999;background:#ffffff;border:1px solid rgba(0,0,0,0.08);box-shadow:0 10px 30px rgba(0,0,0,0.12);border-radius:14px;padding:14px 16px;display:flex;flex-wrap:wrap;align-items:center;gap:12px;font-family:system-ui,-apple-system,'Segoe UI',Roboto,Inter,Arial,sans-serif;color:#1f2937}
#la-cookie-banner .txt{flex:1 1 260px;font-size:14px;line-height:1.45}
#la-cookie-banner .act{display:flex;gap:8px;align-items:center}
#la-cookie-banner a{color:#4B2E0E;text-decoration:underline}
#la-cookie-banner .btn{border-radius:9999px;padding:8px 14px;border:1px solid rgba(0,0,0,0.1);background:#fff;color:#111827;font-weight:600;cursor:pointer}
#la-cookie-banner .btn.primary{background:#4B2E0E;color:#fff;border-color:rgba(255,255,255,0.6)}
#la-cookie-banner .btn:hover{filter:brightness(1.03)}
`;
    document.head.appendChild(style);
    const div = document.createElement("div");
    div.id = "la-cookie-banner";
    div.innerHTML = `
  <div class="txt">We use cookies to keep the site working and to improve your experience. You can manage preferences any time. See our <a href="../terms/privacy-notice.php#cookies">Privacy Notice</a>.</div>
      <div class="act">
        <button class="btn" id="la-reject">Reject non-essential</button>
  <a class="btn" href="../terms/cookie-preferences.php">Preferences</a>
        <button class="btn primary" id="la-accept">Accept all</button>
      </div>`;
    document.body.appendChild(div);
    div.querySelector("#la-accept").addEventListener("click", () => {
      writeConsent({
        necessary: true,
        functional: true,
        analytics: true,
        marketing: true,
        ts: new Date().toISOString(),
        v: 1,
      });
      div.remove();
    });
    div.querySelector("#la-reject").addEventListener("click", () => {
      writeConsent({
        necessary: true,
        functional: false,
        analytics: false,
        marketing: false,
        ts: new Date().toISOString(),
        v: 1,
      });
      div.remove();
    });
  }

  const api = {
    get() {
      return readConsent();
    },
    set(consent) {
      writeConsent(ensureDefaults(consent));
    },
    allow(type) {
      const c = readConsent();
      return !!(c && c[type]);
    },
    load(type, src, attrs) {
      if (this.allow(type)) {
        return loadScript(src, attrs);
      }
      return Promise.resolve(false);
    },
    ensureBanner() {
      if (!readConsent()) injectBanner();
    },
  };
  window.LAConsent = api;

  // Auto-show banner only when explicitly enabled on a page
  // Set `window.LAConsentShowBanner = true` BEFORE including this script to show the banner.
  const showBanner =
    typeof window.LAConsentShowBanner !== "undefined" &&
    !!window.LAConsentShowBanner;
  if (showBanner) {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", () => api.ensureBanner());
    } else {
      api.ensureBanner();
    }
  }
})();
