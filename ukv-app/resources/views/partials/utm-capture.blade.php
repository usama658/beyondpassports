{{--
    Lead attribution — Google Ads keyword + click-ID tracking (Beyond Passports).

    Merged from the original UTM-capture helper and the Tourloom/BP "wa-click" CRM handover
    (docs: ads-attribution-handover). On every page it:

      1. captures ad-click params off the landing URL (gclid / wbraid / gbraid + UTMs +
         matchtype/device/network/loc) into a 90-DAY COOKIE and a 30-day localStorage
         (last-touch), so a lead who clicks the ad one day and enquires days later is still
         attributed;
      2. exposes window.bpUtm() so JS lead forms attach attribution to their payload;
      3. appends TWO references to every WhatsApp prefill —
           • agent-readable  [ref: campaign · keyword · matchtype · device]  (Lead Chats log)
           • short CRM code  (Ref: BP-XXXXX)                                 (auto-matching)
         and fires a fire-and-forget navigator.sendBeacon to the CRM carrying the click ID;
      4. fills hidden gclid/wbraid/gbraid/utm_* fields on any classic <form>;
      5. exposes window.bpWaUrl(url) / window.bpWa(text) so JS-built wa.me CTAs
         (window.open / location.href / programmatic anchor.href) inherit the ref + beacon.

    The CRM endpoint (go.beyondpassports.co.uk -> visacrm-production on Railway) implements
    POST /api/track/wa-click with CORS; the ?brand=beyond-passports param tags the beacon to the
    right brand (the CRM DB is shared with Tourloom). The beacon is fire-and-forget and NEVER
    blocks the WhatsApp hand-off. No personal data is written; only the ad
    params Google already put in the URL. Runs before cookie consent by explicit decision.
    Include once per page, near the end of <body>. Idempotent: safe if two copies ever load.
--}}
<script>
(function () {
  if (window.__bpAttr) return;            // guard against double-inclusion
  window.__bpAttr = true;

  // ── Per-site settings (Beyond Passports) ──────────────────────────────────
  var TRACK  = 'https://go.beyondpassports.co.uk/api/track/wa-click?brand=beyond-passports';
  var PREFIX = 'BP';
  // ──────────────────────────────────────────────────────────────────────────

  // gclid/wbraid/gbraid + UTMs feed the CRM + hidden fields; matchtype/device/network/loc
  // enrich the agent-readable WhatsApp ref only.
  var CRM_KEYS = ['gclid','wbraid','gbraid','utm_source','utm_medium','utm_campaign','utm_term','utm_content'];
  var KEYS = CRM_KEYS.concat(['matchtype','device','network','loc']);
  var TTL_MS = 30 * 24 * 60 * 60 * 1000;  // localStorage last-touch window (bpUtm consumers)
  var COOKIE_MAX = 7776000;               // 90 days — do NOT shorten (covers the click→enquiry gap)

  function setCookie(k, v) {
    try {
      document.cookie = k + '=' + encodeURIComponent(v) + ';path=/;max-age=' + COOKIE_MAX
        + ';samesite=lax' + (location.protocol === 'https:' ? ';secure' : '');
    } catch (e) {}
  }
  function ck(n) {
    try {
      var esc = n.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
      var m = document.cookie.match(new RegExp('(^|; )' + esc + '=([^;]*)'));
      return m ? decodeURIComponent(m[2]) : '';
    } catch (e) { return ''; }
  }

  // 1) Capture fresh params off the landing URL → cookie (90d) + localStorage (30d last-touch).
  var qs = null, fresh = {}, hit = false;
  try { qs = new URLSearchParams(location.search); } catch (e) {}
  if (qs) KEYS.forEach(function (k) {
    var v = qs.get(k);
    if (v) { v = v.slice(0, 120); fresh[k] = v; setCookie(k, v); hit = true; }
  });

  function storedLS() {
    try {
      var r = JSON.parse(localStorage.getItem('bp_utm') || 'null');
      if (r && r.ts && (Date.now() - r.ts) < TTL_MS) return r;
    } catch (e) {}
    return null;
  }
  var data;
  if (hit) {
    fresh.ts = Date.now(); fresh.lp = location.pathname;
    try { localStorage.setItem('bp_utm', JSON.stringify(fresh)); } catch (e) {}
    data = fresh;
  } else {
    data = storedLS();
  }
  window.bpUtm = function () { return data; };

  // 2) Agent-readable ref: campaign · keyword · matchtype · device (blank for organic).
  var hbits = data ? [data.utm_campaign, data.utm_term, data.matchtype, data.device].filter(Boolean) : [];
  var humanRef = hbits.length ? '[ref: ' + hbits.join(' · ') + ']' : '';

  function newRef() {
    var s = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789', r = '';   // no confusable chars
    for (var i = 0; i < 5; i++) r += s[Math.floor(Math.random() * s.length)];
    return PREFIX + '-' + r;
  }
  function suffix(ref) { return (humanRef ? '\n\n' + humanRef : '') + ' (Ref: ' + ref + ')'; }

  // Fire-and-forget beacon to the CRM. Sends the click ID(s) — never blocks, never throws.
  function beacon(ref) {
    try {
      if (!navigator.sendBeacon) return;
      var b = { ref: ref, page: location.pathname };
      CRM_KEYS.forEach(function (k) { var v = ck(k) || (data && data[k]); if (v) b[k] = v; });
      navigator.sendBeacon(TRACK, new Blob([JSON.stringify(b)], { type: 'application/json' }));
    } catch (e) { /* attribution is optional; the link is not */ }
  }

  // 5) Decorate a full wa.me URL string: append refs, beacon once, idempotent. Never throws.
  //    Used by JS-built CTAs (window.open / location.href / anchor.href) so mobile primary
  //    CTAs are covered too, not just <a> anchors.
  window.bpWaUrl = function (url) {
    try {
      if (typeof url !== 'string' || !/wa\.me\/|api\.whatsapp\.com/.test(url)) return url;
      var u = new URL(url), t = u.searchParams.get('text') || '';
      if (t.indexOf('(Ref: ') > -1) return url;         // already carries a ref
      var ref = newRef(); beacon(ref);
      u.searchParams.set('text', t + suffix(ref));
      return u.toString();
    } catch (e) { return url; }                          // leave the link untouched if it won't parse
  };
  // Raw-text variant for CTAs that assemble the prefill text separately.
  window.bpWa = function (text) {
    text = text || '';
    if (text.indexOf('(Ref: ') > -1) return text;
    var ref = newRef(); beacon(ref);
    return text + suffix(ref);
  };

  // Central window.open patch — covers JS-built wa.me opens not authored as <a> anchors.
  var _open = window.open;
  window.open = function (u) {
    try { if (typeof u === 'string') arguments[0] = window.bpWaUrl(u); } catch (e) {}
    return _open.apply(this, arguments);
  };

  // 4) Decorate <a href="wa.me"> anchors + beacon on click. Links already carrying a ref
  //    (e.g. built through bpWaUrl) are left entirely alone, so no double-beacon.
  function wireAnchors() {
    document.querySelectorAll('a[href*="wa.me/"],a[href*="api.whatsapp.com"]').forEach(function (a) {
      if (a.dataset.waTracked) return;
      a.dataset.waTracked = '1';
      try {
        var u = new URL(a.href), t = u.searchParams.get('text') || '';
        if (t.indexOf('(Ref: ') > -1) return;           // already referenced — don't double
        var ref = newRef();
        u.searchParams.set('text', t + suffix(ref));
        a.href = u.toString();
        a.addEventListener('click', function () { beacon(ref); });
      } catch (e) {}
    });
  }

  // 3b) Fill hidden ad-param fields on classic forms (harmless if none present).
  function fillFields() {
    document.querySelectorAll('input[type=hidden]').forEach(function (el) {
      if (CRM_KEYS.indexOf(el.name) === -1) return;
      var v = ck(el.name);
      if (v && !el.value) el.value = v;                 // never blank an existing value
    });
  }

  function apply() { wireAnchors(); fillFields(); }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', apply);
  else apply();

  // Re-run for content added later: AJAX forms, popups, lazy sections. Debounced.
  if (window.MutationObserver) {
    var to = null;
    new MutationObserver(function () { clearTimeout(to); to = setTimeout(apply, 200); })
      .observe(document.documentElement, { childList: true, subtree: true });
  }
  document.addEventListener('wpcf7beforesubmit', fillFields);   // Contact Form 7 (if ever used)
})();
</script>
