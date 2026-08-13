{{--
    UTM capture + lead attribution (Google Ads keyword tracking).

    Reads the ValueTrack/UTM params off the landing URL (utm_source/medium/campaign/term/content,
    matchtype, device, network, loc, gclid — see docs/canonical-values.md for the account-level
    tracking template), stores them in localStorage for 30 days (last-touch wins), then:

      1. exposes window.bpUtm() so lead forms can attach attribution to their payload, and
      2. appends a short human-readable "[ref: campaign · keyword · matchtype · device]" line to
         every wa.me CTA's prefill text, so the keyword that produced each WhatsApp lead arrives
         inside the chat itself and can be logged on the Lead Chats tab.

    No personal data is ever written — only the ad-click params Google already put in the URL.
    Include once per page, near the end of the body (must run before any lead-form submit).
--}}
<script>
(function () {
  var KEYS = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content',
              'matchtype', 'device', 'network', 'loc', 'gclid'];
  var TTL = 30 * 24 * 60 * 60 * 1000;

  function stored() {
    try {
      var r = JSON.parse(localStorage.getItem('bp_utm') || 'null');
      if (r && r.ts && (Date.now() - r.ts) < TTL) return r;
    } catch (e) {}
    return null;
  }

  var qs = new URLSearchParams(location.search), fresh = {}, hit = false;
  KEYS.forEach(function (k) {
    var v = qs.get(k);
    if (v) { fresh[k] = v.slice(0, 120); hit = true; }
  });

  var data;
  if (hit) {
    fresh.ts = Date.now();
    fresh.lp = location.pathname;
    try { localStorage.setItem('bp_utm', JSON.stringify(fresh)); } catch (e) {}
    data = fresh;
  } else {
    data = stored();
  }

  window.bpUtm = function () { return data; };
  if (!data) return;

  // Short agent-readable ref for the WhatsApp prefill. Campaign + keyword carry the signal;
  // matchtype/device disambiguate. gclid stays out of the chat (stored for form payloads only).
  var bits = [data.utm_campaign, data.utm_term, data.matchtype, data.device].filter(Boolean);
  if (!bits.length) return;
  var ref = '[ref: ' + bits.join(' · ') + ']';

  function decorate() {
    document.querySelectorAll('a[href*="wa.me/"],a[href*="api.whatsapp.com"]').forEach(function (a) {
      if (a.dataset.utmDone) return;
      try {
        var u = new URL(a.href);
        var t = u.searchParams.get('text') || '';
        if (t.indexOf('[ref: ') > -1) return;
        u.searchParams.set('text', (t ? t + '\n\n' : '') + ref);
        a.href = u.toString();
        a.dataset.utmDone = '1';
      } catch (e) {}
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', decorate);
  } else {
    decorate();
  }
})();
</script>
