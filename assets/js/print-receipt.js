(function(){
  'use strict';

  function isAndroid(){
    try { return /Android/i.test(navigator.userAgent || ''); } catch(_) { return false; }
  }

  function peso(n){
    var num = Number(n);
    if (!isFinite(num)) return '₱' + n;
    try { return '₱' + num.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
    catch(_) { return '₱' + num.toFixed(2); }
  }

  function padRight(str, width){
    str = String(str);
    if (str.length >= width) return str.slice(0, width);
    return str + ' '.repeat(width - str.length);
  }
  function padLeft(str, width){
    str = String(str);
    if (str.length >= width) return str.slice(0, width);
    return ' '.repeat(width - str.length) + str;
  }

  // Build plain-text ESC/POS-friendly content (monospace, ~32 chars/line for 58mm)
  function buildTextReceipt(data){
    var width = 32; // typical
    var lines = [];
    var brand = (data.brand || 'Love Amaiah Cafe');
    var title = brand.length > width ? brand.slice(0, width) : brand;
    var divider = '-'.repeat(width);
    lines.push(center(title, width));
    if (data.subtitle) { lines.push(center(String(data.subtitle).slice(0, width), width)); }
    lines.push(divider);
    lines.push('Order: #' + data.OrderID);
    if (data.ReferenceNo) lines.push('Ref: ' + data.ReferenceNo);
    if (data.OrderDate) lines.push(("Date: "+ data.OrderDate));
    if (data.PaymentMethod) lines.push('Pay: ' + data.PaymentMethod);
    lines.push(divider);
    // Items
    (data.Items || []).forEach(function(it){
      var name = String(it.name || '').trim();
      var qty = Number(it.qty || 0);
      var unit = Number(it.unit || 0);
      var subtotal = Number(it.subtotal || qty * unit);
      // First line: item name
      splitName(name, width).forEach(function(nl, idx){
        if (idx === 0) {
          // append price/right aligned on first line
          var priceStr = peso(subtotal).replace(/^₱/, '₱');
          var left = padRight(nl, width - priceStr.length);
          lines.push(left + priceStr);
        } else {
          lines.push(padRight(nl, width));
        }
      });
      // Second line: qty x unit
      var qtyUnit = qty + ' x ' + peso(unit);
      lines.push(padRight('  ' + qtyUnit, width));
    });
    lines.push(divider);
    // Total
    var totalStr = peso(data.TotalAmount);
    var totalLine = padRight('TOTAL', width - totalStr.length) + totalStr;
    lines.push(totalLine);
    lines.push(divider);
    lines.push(center('Thank you for your order!', width));
    if (data.footer) {
      String(data.footer).split(/\r?\n/).forEach(function(f){ lines.push(center(f.slice(0, width), width)); });
    }
    lines.push('\n\n'); // feed a bit
    return lines.join('\n');

    function splitName(nm, w){
      var arr = [];
      var s = String(nm);
      while (s.length > 0) {
        arr.push(s.slice(0, w));
        s = s.slice(w);
      }
      return arr.length ? arr : [''];
    }
    function center(txt, w){
      txt = String(txt);
      if (txt.length >= w) return txt.slice(0, w);
      var pad = Math.floor((w - txt.length)/2);
      return ' '.repeat(pad) + txt;
    }
  }

  // Build a compact HTML receipt (target width ~58mm)
  function buildHtmlReceipt(data){
    var itemsHtml = (data.Items || []).map(function(it){
      var name = escapeHtml(it.name || '');
      var qty = Number(it.qty || 0);
      var unit = Number(it.unit || 0);
      var subtotal = Number(it.subtotal || qty * unit);
      return '<div class="row">'
        + '<div class="name">' + name + '</div>'
        + '<div class="amt">' + escapeHtml(peso(subtotal)) + '</div>'
        + '<div class="meta">' + qty + ' × ' + escapeHtml(peso(unit)) + '</div>'
      + '</div>';
    }).join('');
    var brand = escapeHtml(data.brand || 'Love Amaiah Cafe');
    var subtitle = data.subtitle ? '<div class="subtitle">' + escapeHtml(String(data.subtitle)) + '</div>' : '';
    var ref = data.ReferenceNo ? '<div class="line">Ref: ' + escapeHtml(data.ReferenceNo) + '</div>' : '';
    var pay = data.PaymentMethod ? '<div class="line">Pay: ' + escapeHtml(data.PaymentMethod) + '</div>' : '';
    var pickup = data.PickupAt ? '<div class="line">Pickup: ' + escapeHtml(data.PickupAt) + '</div>' : '';
    var css = [
      'body{margin:0;font-family:ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;}',
      '.rcpt{width:58mm; max-width:58mm; padding:6px 8px;}',
      '.title{font-weight:700; text-align:center; margin:4px 0;}',
      '.subtitle{font-size:12px; text-align:center; margin-top:2px; color:#444}',
      '.line{font-size:12px; margin:2px 0}',
      '.div{border-top:1px dashed #999; margin:6px 0}',
      '.row{font-size:12px; margin:4px 0}',
      '.row .name{display:inline-block; max-width:65%;}',
      '.row .amt{float:right;}',
      '.row .meta{color:#666; font-size:11px}',
      '.total{font-weight:700; font-size:13px; margin-top:6px}',
      '.center{ text-align:center;}',
      '@media print{ .no-print{ display:none !important; } }'
    ].join('\n');
    return (
      '<!doctype html>'+
      '<html><head><meta charset="utf-8">'+
      '<style>' + css + '</style></head><body>'+
      '<div class="rcpt">'+
        '<div class="title">' + brand + '</div>'+
        subtitle +
        '<div class="div"></div>'+
        '<div class="line">Order: #' + escapeHtml(String(data.OrderID)) + '</div>'+
        ref +
        '<div class="line">Date: ' + escapeHtml(String(data.OrderDate)) + '</div>'+
        pay + pickup +
        '<div class="div"></div>'+
        itemsHtml +
        '<div class="div"></div>'+
        '<div class="total">TOTAL <span style="float:right">' + escapeHtml(peso(data.TotalAmount)) + '</span></div>'+
        '<div class="div"></div>'+
        '<div class="center" style="margin-top:6px">Thank you for your order!</div>'+
      '</div>'+
      '</body></html>'
    );

    function escapeHtml(s){
      return String(s)
        .replace(/&/g,'&amp;')
        .replace(/</g,'&lt;')
        .replace(/>/g,'&gt;')
        .replace(/"/g,'&quot;')
        .replace(/'/g,'&#039;');
    }
  }

  function printViaBrowser(html){
    try {
      var w = window.open('', '_blank', 'width=420,height=600');
      if (!w) throw new Error('Popup blocked');
      w.document.open();
      w.document.write(html);
      w.document.close();
      // Try print after load
      var done = false;
      var attempt = function(){ if (done) return; done = true; try { w.focus(); w.print(); } catch(_) {} setTimeout(function(){ try{ w.close(); }catch(_){ } }, 300); };
      w.onload = attempt;
      setTimeout(attempt, 800);
      return true;
    } catch(_) { return false; }
  }

  function printViaRawBT(text){
    try {
      // RawBT custom scheme; base64 of text
      var uri = 'rawbt:base64,' + btoa(unescape(encodeURIComponent(text)));
      var opened = false;
      try { opened = !!window.open(uri, '_blank'); } catch(_) {}
      if (!opened) {
        // Fallback: try assigning location (may show chooser)
        window.location.href = uri;
      }
      return true;
    } catch(_) { return false; }
  }

  async function printViaQZ(html){
    try {
      if (!window.qz || !qz.websocket) return false;
      // Connect if not active
      if (!qz.websocket.isActive()) {
        await qz.websocket.connect().catch(function(){ /* ignore */ });
      }
      if (!qz.websocket.isActive()) return false;
      var printer = await qz.printers.getDefault().catch(function(){ return null; });
      if (!printer) return false;
      var cfg = qz.configs.create(printer, {
        units: 'mm',
        size: { width: 58, height: null },
        scaleContent: true,
        rasterize: true,
        margins: { top: 0, right: 0, bottom: 0, left: 0 }
      });
      await qz.print(cfg, [{ type: 'html', format: 'plain', data: html }]);
      return true;
    } catch(_) {
      return false;
    }
  }

  async function fetchReceiptData(orderId){
    var url = new URL('../ajax/get_receipt_data.php', window.location.href);
    url.searchParams.set('order_id', String(orderId));
    var res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' }, cache: 'no-store' });
    if (!res.ok) throw new Error('Network error');
    var json = await res.json();
    if (!json || !json.success) throw new Error(json && json.message ? json.message : 'Invalid response');
    return json.data;
  }

  async function onOrderCompleted(orderId){
    try {
      var data = await fetchReceiptData(orderId);
      // Normalize fields expected by builders
      data.brand = data.brand || 'Love Amaiah Cafe';
      data.subtitle = data.subtitle || '';
      data.footer = data.footer || '';
      var html = buildHtmlReceipt(data);
      // Try QZ Tray (desktop silent)
      var ok = await printViaQZ(html);
      if (ok) return;
      // Android RawBT path
      if (isAndroid()) {
        var text = buildTextReceipt(data);
        var okRaw = printViaRawBT(text);
        if (okRaw) return;
      }
      // Fallback to browser print dialog
      printViaBrowser(html);
    } catch(e) {
      // As a last resort, do nothing; we don't block UI
      // console.warn('Print failed', e);
    }
  }

  // Expose as global hook
  window.LA_onOrderCompleted = onOrderCompleted;
})();
