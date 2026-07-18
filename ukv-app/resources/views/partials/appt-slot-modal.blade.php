{{-- Per-centre "pick a slot" modal, ported verbatim from /schengen-visa (destinations/index).
     Self-contained: resolved-hex CSS (no theme vars, so it renders identically on the dark lp-bold
     LP) + the exact slot-picker JS. Bind: any element with [data-slotcountry] opens it. Real slots
     come from route('appointments.slots') (CentreSlot), same inventory as the /schengen-visa page. --}}
@php $apbkWa = config('ukv.whatsapp') ?: '447882747584'; @endphp
<style>
  #slotm{position:fixed;inset:0;z-index:1400;display:none;align-items:center;justify-content:center;padding:16px;background:rgba(10,16,24,.6);backdrop-filter:blur(2px);font-family:"Outfit",system-ui,-apple-system,"Segoe UI",sans-serif}
  #slotm.open{display:flex}
  #slotm .slotm-box{background:#fff;border-radius:20px;width:min(560px,100%);max-height:88vh;overflow:auto;box-shadow:0 50px 100px -30px rgba(0,0,0,.55);animation:slotm-in .18s ease}
  @keyframes slotm-in{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:none}}
  #slotm .slotm-hd{background:linear-gradient(135deg,#16323b,#1F6E63);color:#fff;padding:22px 24px 20px}
  #slotm .slotm-top{display:flex;justify-content:space-between;align-items:flex-start;gap:12px}
  #slotm .slotm-top h3{font:800 21px "Outfit",system-ui,sans-serif;color:#fff;margin:0;letter-spacing:-.02em}
  #slotm .slotm-x{background:rgba(255,255,255,.16);border:0;width:34px;height:34px;border-radius:50%;font-size:20px;line-height:1;color:#fff;cursor:pointer;flex:none}
  #slotm .slotm-s{color:rgba(255,255,255,.82);font-size:13.5px;margin:8px 0 0;line-height:1.5}
  #slotm .slotm-trust{display:flex;gap:16px;margin:14px 0 0;flex-wrap:wrap}
  #slotm .slotm-trust span{display:inline-flex;align-items:center;gap:6px;font:600 12px "Outfit",system-ui,sans-serif;color:rgba(255,255,255,.9)}
  #slotm .slotm-trust b{color:#8fe3c9}
  #slotm .slotm-body{background:#eef4f3;padding:18px 20px}
  #slotm .slotm-foot{padding:16px 24px 20px}
  #slotm .slotm-book{display:flex;align-items:center;justify-content:center;gap:9px;width:100%;background:#25D366;color:#fff;border:0;border-radius:13px;font:800 16px "Outfit",system-ui,sans-serif;padding:15px 22px;cursor:pointer;text-decoration:none;box-shadow:0 12px 26px -12px rgba(37,211,102,.7)}
  #slotm .slotm-book[aria-disabled="true"]{background:#c7d0d6;box-shadow:none;cursor:not-allowed}
  #slotm .slotm-book svg{width:19px;height:19px;fill:#fff;flex:none}
  #slotm .slotm-note{font-size:12px;color:#5d6b76;margin:12px 0 0;text-align:center}
  #slotm .slotm-load{text-align:center;color:#5d6b76;font-size:14px;padding:22px 0}
  #slotm .sc-centre{background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 14px 34px -24px rgba(20,45,50,.6);margin:0 0 14px}
  #slotm .sc-centre:last-child{margin:0}
  #slotm .sc-head{display:flex;justify-content:space-between;align-items:center;gap:10px;padding:12px 16px;background:linear-gradient(90deg,#eafaf6,#f4fbf9);border-bottom:1px solid #d9ece7}
  #slotm .sc-name{font:800 14px "Outfit",system-ui,sans-serif;color:#16222E}
  #slotm .sc-num{font:700 11px "Outfit",system-ui,sans-serif;color:#1F6E63;background:#fff;border:1px solid #cfe8e3;border-radius:999px;padding:3px 10px;white-space:nowrap}
  #slotm .sc-slots{display:flex;flex-wrap:wrap;gap:8px;padding:16px}
  #slotm .sc-ask{font-size:13px;color:#5d6b76;margin:0;padding:14px 16px}
  #slotm .slot{position:relative;min-width:74px;text-align:center;border:1.5px solid #dde3ec;border-radius:12px;padding:9px 12px 10px;cursor:pointer;background:#f7fafb;transition:.12s;flex:0 0 auto}
  #slotm .slot:hover{border-color:#2E9A8C;background:#eff8f6}
  #slotm .slot .wd{display:block;font:700 10px "Outfit",system-ui,sans-serif;letter-spacing:.08em;text-transform:uppercase;color:#5d6b76}
  #slotm .slot .dm{display:block;font:800 16px "Outfit",system-ui,sans-serif;color:#16222E;margin-top:1px}
  #slotm .slot.sel{border-color:#155E7A;background:#155E7A;box-shadow:0 8px 18px -10px rgba(21,94,122,.7)}
  #slotm .slot.sel .wd{color:rgba(255,255,255,.85)}
  #slotm .slot.sel .dm{color:#fff}
  #slotm .slot .soon{position:absolute;top:-9px;left:8px;font:800 9px "Outfit",system-ui,sans-serif;letter-spacing:.06em;text-transform:uppercase;color:#fff;background:#2E9A8C;border-radius:999px;padding:2px 7px}
</style>

<div class="slotm" id="slotm" role="dialog" aria-modal="true" aria-labelledby="slotm-title" data-wa="{{ $apbkWa }}">
  <div class="slotm-box">
    <div class="slotm-hd">
      <div class="slotm-top">
        <h3 id="slotm-title">Available slots</h3>
        <button type="button" class="slotm-x" id="slotm-x" aria-label="Close">&times;</button>
      </div>
      <p class="slotm-s">Booking is centre by centre. Pick a slot at the centre that suits you.</p>
      <div class="slotm-trust"><span><b>&checkmark;</b> No payment now</span><span><b>&checkmark;</b> Confirmed live on WhatsApp</span><span><b>&checkmark;</b> We book it for you</span></div>
    </div>
    <div class="slotm-body" id="slotm-centres" data-url="{{ route('appointments.slots', [], false) }}"></div>
    <div class="slotm-foot">
      <a class="slotm-book" id="slotm-book" href="#" target="_blank" rel="noopener" aria-disabled="true">@include('partials.wa-glyph')Select a slot to book</a>
      <p class="slotm-note">Booking is confirmed live with the centre before anything is paid.</p>
    </div>
  </div>
</div>

<script>
  // Per-centre slot picker. Any [data-slotcountry] element -> fetch that country's bookable centres
  // + real CentreSlot slots -> pick a slot -> book on WhatsApp. Progressive enhancement: the tile
  // keeps its WhatsApp href if JS is off.
  (function () {
    var modal = document.getElementById('slotm');
    if (!modal) return;
    var box   = document.getElementById('slotm-centres');
    var title = document.getElementById('slotm-title');
    var book  = document.getElementById('slotm-book');
    var wa    = modal.getAttribute('data-wa');
    var url   = box.getAttribute('data-url');
    var glyph = book.querySelector('svg') ? book.querySelector('svg').outerHTML : '';
    var country = '', centre = '', slot = '';

    function setLabel(t) { book.innerHTML = glyph + t; }
    function esc(s) { return String(s == null ? '' : s).replace(/[<>&"]/g, function (c) { return { '<': '&lt;', '>': '&gt;', '&': '&amp;', '"': '&quot;' }[c]; }); }
    function bookHref() {
      var msg = 'Hi Beyond Passports, I would like to book my ' + (country || 'Schengen') +
        ' Schengen biometric appointment.\nCentre: ' + centre + '\nSlot: ' + slot +
        '\nPlease confirm this live with the centre and book it for me.';
      return 'https://wa.me/' + wa + '?text=' + encodeURIComponent(msg);
    }
    function askHref(where) {
      var msg = 'Hi Beyond Passports, I would like a ' + (country || 'Schengen') + ' appointment' +
        (where ? ' at ' + where : '') + '. Please check the soonest live slot and book it for me.';
      return 'https://wa.me/' + wa + '?text=' + encodeURIComponent(msg);
    }
    function select(btn, centreName, dateLabel) {
      Array.prototype.forEach.call(box.querySelectorAll('.slot'), function (x) { x.classList.remove('sel'); });
      btn.classList.add('sel');
      centre = centreName; slot = dateLabel;
      book.setAttribute('aria-disabled', 'false');
      book.href = bookHref();
      setLabel('Ask us to book ' + slot + ' →');
      try { book.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); } catch (e) { book.scrollIntoView(); }
    }
    function renderCentres(data) {
      box.innerHTML = '';
      var centres = (data && data.centres) || [];
      if (!centres.length) {
        box.innerHTML = '<p class="sc-ask">We check live availability with the centres for ' + esc(country) + '. Tap below and we will confirm the soonest slot and book it for you.</p>';
        book.setAttribute('aria-disabled', 'false'); book.href = askHref(''); setLabel('Ask us on WhatsApp →');
        return;
      }
      var first = true;
      centres.forEach(function (c) {
        var card = document.createElement('div'); card.className = 'sc-centre';
        var n = (c.slots && c.slots.length) || 0;
        card.innerHTML = '<div class="sc-head"><span class="sc-name">' + esc(c.name) + '</span>' +
          (n ? '<span class="sc-num">' + n + ' open</span>' : '') + '</div>';
        if (n) {
          var row = document.createElement('div'); row.className = 'sc-slots';
          c.slots.forEach(function (s, i) {
            var parts = String(s.label).split(' ');
            var wd = parts.length > 1 ? parts[0] : '';
            var dm = parts.length > 1 ? parts.slice(1).join(' ') : s.label;
            var b = document.createElement('button'); b.type = 'button'; b.className = 'slot';
            b.innerHTML = (first && i === 0 ? '<span class="soon">Soonest</span>' : '') +
              (wd ? '<span class="wd">' + esc(wd) + '</span>' : '') +
              '<span class="dm">' + esc(dm) + '</span>';
            b.addEventListener('click', function () { select(b, c.name, s.label); });
            row.appendChild(b);
          });
          card.appendChild(row);
          first = false;
        } else {
          var p = document.createElement('p'); p.className = 'sc-ask';
          p.textContent = 'No published slots right now — ask us to check live.';
          card.appendChild(p);
        }
        box.appendChild(card);
      });
    }
    function open(c) {
      country = c; centre = ''; slot = '';
      title.textContent = 'Available slots — ' + c;
      book.setAttribute('aria-disabled', 'true'); book.removeAttribute('href');
      setLabel('Select a slot to book');
      box.innerHTML = '<p class="slotm-load">Loading centres…</p>';
      modal.classList.add('open');
      fetch(url + '?country=' + encodeURIComponent(c), { headers: { 'Accept': 'application/json' } })
        .then(function (r) { return r.json(); })
        .then(renderCentres)
        .catch(function () {
          box.innerHTML = '';
          book.setAttribute('aria-disabled', 'false'); book.href = askHref(''); setLabel('Ask us on WhatsApp →');
        });
    }
    function close() { modal.classList.remove('open'); }

    Array.prototype.forEach.call(document.querySelectorAll('[data-slotcountry]'), function (t) {
      t.addEventListener('click', function (e) { e.preventDefault(); open(t.getAttribute('data-slotcountry')); });
    });
    book.addEventListener('click', function (e) { if (book.getAttribute('aria-disabled') === 'true') e.preventDefault(); });
    document.getElementById('slotm-x').addEventListener('click', close);
    modal.addEventListener('click', function (e) { if (e.target === modal) close(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && modal.classList.contains('open')) close(); });
  })();
</script>
