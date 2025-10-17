(function(){
  // Cross-page Orders badge notifier for Owner/Employee views
  const BADGE_KEY = 'la_orders_badge_count';
  const LATEST_KEY = 'la_orders_latest_id';
  const POLL_MS = 6000;

  function injectStyle(){
    if (document.getElementById('la-notif-style')) return;
    const css = `.notif-badge{position:absolute;top:-6px;right:-6px;min-width:18px;height:18px;padding:0 4px;display:none;align-items:center;justify-content:center;background:#ef4444;color:#fff;border-radius:9999px;font-size:11px;font-weight:700;line-height:1;box-shadow:0 0 0 2px #fff}`+
                `.notif-dot{position:absolute;top:-2px;right:-2px;width:8px;height:8px;background:#ef4444;border-radius:9999px;box-shadow:0 0 0 2px #fff;display:none}`+
                `.has-new .notif-dot{display:inline-block}`;
    const style = document.createElement('style');
    style.id = 'la-notif-style';
    style.textContent = css;
    document.head.appendChild(style);
  }

  function findOrderListButtons(){
    // Prefer explicit ids first (if present)
    const explicit = Array.from(document.querySelectorAll('#orderlist-icon-owner, #orderlist-icon-emp'));
    if (explicit.length) return explicit;
    // Fallback: any button or anchor navigating to tranlist
    const els = Array.from(document.querySelectorAll('button, a'));
    return els.filter(el => {
      const href = (el.getAttribute('href')||'') + (el.getAttribute('onclick')||'');
      return /\btranlist(\.php)?\b/i.test(href);
    });
  }

  function ensureBadge(el){
    if (!el) return null;
    let badge = el.querySelector('.notif-badge');
    if (!badge){
      badge = document.createElement('span');
      badge.className = 'notif-badge';
      badge.setAttribute('aria-hidden','true');
      el.appendChild(badge);
    }
    let dot = el.querySelector('.notif-dot');
    if (!dot){
      dot = document.createElement('span');
      dot.className = 'notif-dot';
      el.appendChild(dot);
    }
    return badge;
  }

  function showCount(targets, count){
    targets.forEach(el => {
      ensureBadge(el);
      const b = el.querySelector('.notif-badge');
      if (!b) return;
      if (count > 0){
        el.classList.add('has-new');
        b.textContent = String(count);
        b.style.display = 'inline-flex';
        b.setAttribute('aria-hidden','false');
      } else {
        el.classList.remove('has-new');
        b.textContent = '';
        b.style.display = 'none';
        b.setAttribute('aria-hidden','true');
      }
    });
  }

  async function poll(latest){
    try {
      const url = new URL('../ajax/get_transactions.php', window.location.href);
      if (latest > 0) url.searchParams.set('since_id', String(latest));
      url.searchParams.set('limit', '20');
      const res = await fetch(url.toString(), { cache: 'no-store', headers: { 'Accept': 'application/json' } });
      if (res.status === 401 || res.status === 403) return { stop: true };
      if (!res.ok) return {};
      const json = await res.json();
      const nextLatest = Math.max(latest||0, json.latest_id || 0);
      const rows = Array.isArray(json.data) ? json.data : [];
      // Count new non-archived orders
      const newCount = (latest>0) ? rows.filter(r => (r.Status !== 'Complete' && r.Status !== 'Rejected')).length : 0;
      return { newCount, nextLatest };
    } catch(_) { return {}; }
  }

  function isOrdersPage(){
    try { return /\ball\/tranlist(\.php)?$/i.test(location.pathname); } catch(_) { return false; }
  }

  function init(){
    injectStyle();
    const targets = findOrderListButtons();
    if (targets.length === 0) return; // nothing to show on this page

    // If we're on the Orders page, clear badge and skip polling (page handles its own)
    if (isOrdersPage()){
      try { localStorage.removeItem(BADGE_KEY); } catch(_){ }
      showCount(targets, 0);
      return;
    }

    // Initialize from storage
    let stored = parseInt(localStorage.getItem(BADGE_KEY) || '0', 10) || 0;
    showCount(targets, stored);

    // Load latest id
    let latest = parseInt(localStorage.getItem(LATEST_KEY) || '0', 10) || 0;

    // Kick off polling
    const tick = async () => {
      const res = await poll(latest);
      if (res && res.stop) return; // unauthorized; stop silently
      if (res && typeof res.nextLatest === 'number') { latest = res.nextLatest; try { localStorage.setItem(LATEST_KEY, String(latest)); } catch(_){} }
      if (res && res.newCount > 0){
        stored = (parseInt(localStorage.getItem(BADGE_KEY) || '0', 10) || 0) + res.newCount;
        try { localStorage.setItem(BADGE_KEY, String(stored)); } catch(_){}
        showCount(targets, stored);
        // Optional: beep via Notification API is managed on tranlist; we keep this silent here to avoid noise
      }
    };
    // Seed without counting (just to record latest id) on first run
    tick();
    const t = setInterval(tick, POLL_MS);
    // Sync across tabs / pages
    window.addEventListener('storage', (e) => {
      if (e.key === BADGE_KEY){
        const val = parseInt(e.newValue || '0', 10) || 0;
        showCount(targets, val);
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else { init(); }
})();
