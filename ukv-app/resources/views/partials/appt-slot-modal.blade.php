{{-- Per-centre "pick a slot" modal, ported verbatim from /schengen-visa (destinations/index).
     Self-contained: resolved-hex CSS (no theme vars, so it renders identically on the dark lp-bold
     LP) + the exact slot-picker JS. Bind: any element with [data-slotcountry] opens it. Real slots
     come from route('appointments.slots') (CentreSlot), same inventory as the /schengen-visa page. --}}
@php
  $apbkWa = config('ukv.whatsapp') ?: '447882747584';
  // Preload every country's slots so the modal renders instantly (zero round-trip). The fetch
  // endpoint stays as a fallback for anything not in the blob. Cached in the service.
  $apptPreload = app(\App\Services\SlotService::class)->modalPayload();
  // Display mode (matches the board):
  //   count_focus -> CENTRE picker: pick a centre, we book its soonest slot. No date/week chips.
  //   week_labels -> WEEK chips (one per week, soonest held).
  //   default     -> exact-date picker with a time step.
  $apbkCentre = (bool) config('ukv.slots.count_focus');
  $apbkWeek = (bool) config('ukv.slots.week_labels');
  $apbkMode = $apbkCentre ? 'centre' : 'slot';
  $apbkNoun = $apbkCentre ? 'centre' : ($apbkWeek ? 'week' : 'date');
  $apbkSub = $apbkCentre
    ? 'Pick the centre. We find and lock the soonest slot there, and confirm the exact date with you live on WhatsApp.'
    : ($apbkWeek
      ? 'Pick the week before it vanishes. We lock it with the centre the moment you pick, and confirm the exact date with you live on WhatsApp.'
      : 'Pick the date before it vanishes. We lock it with the centre the moment you pick.');
@endphp
<script>window.__APPT_SLOTS = {!! json_encode($apptPreload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!};</script>
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
  /* Week-label chip: one per week, holds the soonest slot. Wider + smaller label than a day-cell. */
  #slotm .slot.wkslot{min-width:118px;padding:12px 14px}
  #slotm .slot.wkslot .dm{font-size:13px;line-height:1.3;margin-top:0}
  #slotm .slot.wkslot .ct{color:#2E9A8C}
  #slotm .slot .ct{display:block;font:700 9px "Outfit",system-ui,sans-serif;color:#1F6E63;margin-top:2px}
  /* Selected slot has a dark fill -> whiten the sub-label ("Tap to hold" / "N times") so it stays
     readable. Placed after .wkslot .ct so it wins for the week chip too (same specificity). */
  #slotm .slot.sel .ct{color:rgba(255,255,255,.92)}
  /* Day is picked -> its times ("clock") reveal below the day row for that centre. */
  #slotm .sc-times{padding:0 16px 16px;display:none}
  #slotm .sc-times.show{display:block}
  #slotm .sc-tlbl{display:flex;align-items:center;gap:7px;font:700 11px "Outfit",system-ui,sans-serif;text-transform:uppercase;letter-spacing:.08em;color:#5d6b76;margin:2px 0 10px}
  #slotm .sc-tlbl svg{width:14px;height:14px;stroke:#1F6E63}
  #slotm .tchips{display:flex;flex-wrap:wrap;gap:8px}
  #slotm .tchip{border:1.5px solid #dde3ec;border-radius:10px;padding:8px 14px;font:800 13px "Outfit",system-ui,sans-serif;color:#16222E;background:#f7fafb;cursor:pointer;transition:.12s}
  #slotm .tchip:hover{border-color:#2E9A8C;background:#eff8f6}
  #slotm .tchip.sel{background:#155E7A;color:#fff;border-color:#155E7A}
  #slotm.lim .tchip.sel{background:#b5791f;border-color:#b5791f}
  #slotm.low .tchip.sel{background:#c0392b;border-color:#c0392b}
  /* Availability band recolours the header, the selected slot and the "soonest" tag.
     Default (no class) = Available/green. lim = amber, low = red. */
  #slotm.lim .slotm-hd{background:linear-gradient(135deg,#4a3410,#b5791f)}
  #slotm.low .slotm-hd{background:linear-gradient(135deg,#4a1613,#c0392b)}
  #slotm.lim .slot.sel{border-color:#b5791f;background:#b5791f;box-shadow:0 8px 18px -10px rgba(181,121,31,.7)}
  #slotm.low .slot.sel{border-color:#c0392b;background:#c0392b;box-shadow:0 8px 18px -10px rgba(192,57,43,.7)}
  #slotm.lim .slot .soon{background:#b5791f}
  #slotm.low .slot .soon{background:#c0392b}
  /* ── Centre-selection mode (count_focus): pick a centre, not a date. 2x2 tile grid. ── */
  #slotm .sc-lead{font:800 11px "Outfit",system-ui,sans-serif;text-transform:uppercase;letter-spacing:.08em;color:#5d6b76;margin:0 0 12px}
  #slotm .cgrid{display:grid;grid-template-columns:1fr 1fr;gap:11px}
  #slotm .ct{position:relative;display:flex;flex-direction:column;gap:5px;width:100%;text-align:left;background:#fff;border:1.5px solid #dde3ec;border-radius:14px;padding:15px 14px 13px;cursor:pointer;transition:.12s;font-family:inherit;min-height:120px}
  #slotm .ct:hover{border-color:#2E9A8C;box-shadow:0 0 0 3px rgba(46,154,140,.14)}
  #slotm .ct .trow{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:2px}
  #slotm .ct .radio{width:20px;height:20px;border-radius:50%;border:2px solid #dde3ec;flex:none;position:relative;background:#fff}
  #slotm .ct .radio::after{content:'';position:absolute;inset:4px;border-radius:50%;background:#155E7A;opacity:0;transition:.12s}
  #slotm .ct:hover .radio{border-color:#2E9A8C}
  #slotm .ct .nm{font:800 16px "Outfit",system-ui,sans-serif;color:#16222E;line-height:1.2}
  #slotm .ct .sub{font-size:10px;font-weight:700;letter-spacing:.03em;text-transform:uppercase;color:#1F6E63;margin-top:-3px}
  #slotm .ct.sel .sub{color:rgba(255,255,255,.7)}
  #slotm .ct .meta{font-size:11px;color:#5d6b76;margin-top:3px;line-height:1.35}
  #slotm .ct .badge{font:800 10.5px "Outfit",system-ui,sans-serif;color:#1F6E63;background:rgba(46,154,140,.1);border-radius:999px;padding:4px 8px;white-space:nowrap}
  #slotm .ct .badge.find{background:#fff;border:1px solid #2E9A8C;color:#1F6E63}
  #slotm .ct .soontag{position:absolute;top:-9px;left:14px;font:800 8.5px "Outfit",system-ui,sans-serif;letter-spacing:.05em;text-transform:uppercase;color:#fff;background:#2f9e5f;border-radius:999px;padding:3px 8px;box-shadow:0 4px 10px -4px rgba(47,158,95,.6)}
  /* selected tile — band-aware fill (default navy / amber / red), same scheme as the slot chips */
  #slotm .ct.sel{border-color:#155E7A;background:#155E7A}
  #slotm .ct.sel .nm{color:#fff}
  #slotm .ct.sel .meta{color:rgba(255,255,255,.85)}
  #slotm .ct.sel .radio{border-color:#fff;background:#fff}
  #slotm .ct.sel .radio::after{opacity:1}
  #slotm .ct.sel .badge{background:rgba(255,255,255,.2);color:#fff}
  #slotm .ct.sel .badge.find{border-color:transparent}
  #slotm.lim .ct.sel{border-color:#b5791f;background:#b5791f}
  #slotm.low .ct.sel{border-color:#c0392b;background:#c0392b}
  /* Mobile: stack the trust ticks + tighten the centre rows so long centre names read cleanly. */
  @media(max-width:480px){
    #slotm .slotm-trust{flex-direction:column;gap:6px}
    #slotm .slotm-top h3{font-size:18px}
    #slotm .cgrid{grid-template-columns:1fr 1fr;gap:9px}
    #slotm .ct{min-height:0;padding:12px 11px}
    #slotm .ct .nm{font-size:16px;line-height:1.2}
    #slotm .ct .meta{font-size:11px}
    #slotm .ct .badge{font-size:10px;padding:4px 8px}
    #slotm .ct .soontag{left:13px;font-size:8.5px}
  }
</style>

<div class="slotm" id="slotm" role="dialog" aria-modal="true" aria-labelledby="slotm-title" data-wa="{{ $apbkWa }}" data-datenoun="{{ $apbkNoun }}" data-mode="{{ $apbkMode }}">
  <div class="slotm-box">
    <div class="slotm-hd">
      <div class="slotm-top">
        <h3 id="slotm-title">Select your {{ $apbkNoun }}</h3>
        <button type="button" class="slotm-x" id="slotm-x" aria-label="Close">&times;</button>
      </div>
      <p class="slotm-s">{{ $apbkSub }}</p>
      <div class="slotm-trust"><span><b>&checkmark;</b> Tap to hold</span><span><b>&checkmark;</b> Confirmed live on WhatsApp</span><span><b>&checkmark;</b> We do the booking</span></div>
    </div>
    <div class="slotm-body" id="slotm-centres" data-url="{{ route('appointments.slots', [], false) }}" data-timepicker="{{ config('ukv.appointments.time_picker') ? '1' : '0' }}"></div>
    <div class="slotm-foot">
      <a class="slotm-book" id="slotm-book" href="#" target="_blank" rel="noopener" aria-disabled="true">@include('partials.wa-glyph')Select a {{ $apbkNoun === 'centre' ? 'centre' : 'slot' }} to book</a>
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
    var noun  = modal.getAttribute('data-datenoun') || 'date'; // 'week' when week-labels on
    var mode  = modal.getAttribute('data-mode') || 'slot';      // 'centre' -> pick a centre, not a date
    var url   = box.getAttribute('data-url');
    var timePicker = box.getAttribute('data-timepicker') === '1'; // off -> day is the final pick
    var glyph = book.querySelector('svg') ? book.querySelector('svg').outerHTML : '';
    var country = '', centre = '', slot = '';

    // Day is the final selection (time-picker off): book with just the day, time confirmed on WhatsApp.
    function selectDay(dbtn, centreName, dayLabel) {
      Array.prototype.forEach.call(box.querySelectorAll('.slot'), function (x) { x.classList.remove('sel'); });
      dbtn.classList.add('sel');
      centre = centreName; slot = dayLabel;
      book.setAttribute('aria-disabled', 'false');
      book.href = bookHref();
      setLabel('Book ' + slot + ' now →');
      try { book.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); } catch (e) { book.scrollIntoView(); }
    }

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
    // A time is chosen -> that's the final selection (day + time).
    function selectTime(tbtn, centreName, dayLabel, timeLabel) {
      Array.prototype.forEach.call(box.querySelectorAll('.tchip'), function (x) { x.classList.remove('sel'); });
      tbtn.classList.add('sel');
      centre = centreName; slot = dayLabel + ' ' + timeLabel;
      book.setAttribute('aria-disabled', 'false');
      book.href = bookHref();
      setLabel('Book ' + slot + ' now →');
      try { book.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); } catch (e) { book.scrollIntoView(); }
    }
    // Short city from "{Country} visa application centre – {City}".
    function cityOf(n) {
      var seps = ['–', ' - ', '—'];
      for (var i = 0; i < seps.length; i++) { if (String(n).indexOf(seps[i]) > -1) { var p = String(n).split(seps[i]); return p[p.length - 1].trim(); } }
      return n;
    }
    // "Find me a slot" message for a centre with no published slots.
    function findHref(where) {
      var msg = 'Hi Beyond Passports, there are no published ' + (country || 'Schengen') + ' slots at ' + where +
        ' right now. Please find me the soonest slot there and book it for me.';
      return 'https://wa.me/' + wa + '?text=' + encodeURIComponent(msg);
    }
    // Centre mode: 2x2 tile grid. Every centre is selectable — ones with slots book the soonest;
    // empty ones carry a "Find me a slot" badge and send a find-a-slot request.
    function renderCentresPick(centres) {
      var lead = document.createElement('p'); lead.className = 'sc-lead'; lead.textContent = 'Choose an application centre';
      box.appendChild(lead);
      var grid = document.createElement('div'); grid.className = 'cgrid'; box.appendChild(grid);
      var firstOpen = true;
      centres.forEach(function (c) {
        var openN = (typeof c.open === 'number') ? c.open : ((c.days || []).length);
        var has = openN > 0;
        var city = cityOf(c.name);
        var tile = document.createElement('button'); tile.type = 'button'; tile.className = 'ct';
        tile.innerHTML = (has && firstOpen ? '<span class="soontag">Soonest</span>' : '') +
          '<div class="trow"><span class="radio"></span><span class="badge' + (has ? '' : ' find') + '">' +
          (has ? openN + ' open' : 'Secure now') + '</span></div>' +
          '<div class="nm">' + esc(city) + '</div>' +
          '<div class="sub">Visa application centre</div>' +
          '<div class="meta">' + (has ? 'We help you secure the earliest slot' : 'No slots open! We help you find one.') + '</div>';
        tile.addEventListener('click', function () {
          Array.prototype.forEach.call(box.querySelectorAll('.ct'), function (x) { x.classList.remove('sel'); });
          tile.classList.add('sel');
          centre = c.name;
          book.setAttribute('aria-disabled', 'false');
          if (has) { slot = 'Soonest available'; book.href = bookHref(); setLabel('Book ' + city + ' now'); }
          else { book.href = findHref(c.name); setLabel('Find me a ' + city + ' slot'); }
          try { book.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); } catch (e) {}
        });
        if (has) firstOpen = false;
        grid.appendChild(tile);
      });
    }
    function renderCentres(data) {
      box.innerHTML = '';
      var centres = (data && data.centres) || [];
      if (mode === 'centre' && centres.length) { renderCentresPick(centres); return; }
      if (!centres.length) {
        box.innerHTML = '<p class="sc-ask">We check live availability with the centres for ' + esc(country) + '. Tap below and we will confirm the soonest slot and book it for you.</p>';
        book.setAttribute('aria-disabled', 'false'); book.href = askHref(''); setLabel('Ask us on WhatsApp →');
        return;
      }
      var first = true;
      centres.forEach(function (c) {
        var card = document.createElement('div'); card.className = 'sc-centre';
        var days = (c.days) || [];
        // "open" = real available time-slots in the 30-day window (sums to the board total).
        var openN = (typeof c.open === 'number') ? c.open : days.length;
        card.innerHTML = '<div class="sc-head"><span class="sc-name">' + esc(c.name) + '</span>' +
          (openN ? '<span class="sc-num">' + openN + ' open</span>' : '') + '</div>';
        if (days.length) {
          var row = document.createElement('div'); row.className = 'sc-slots';
          var tbox = document.createElement('div'); tbox.className = 'sc-times';
          days.forEach(function (d, i) {
            // Week-label mode: the whole label is one line ("3rd Week Aug 2026"), no weekday split
            // and no times — the chip holds the soonest slot in that week. Otherwise the label is
            // "Thu 24 Jul": split weekday from the date for the day-cell.
            var wd, dm;
            if (d.week) {
              wd = ''; dm = d.label;
            } else {
              var parts = String(d.label).split(' ');
              wd = parts.length > 1 ? parts[0] : '';
              dm = parts.length > 1 ? parts.slice(1).join(' ') : d.label;
            }
            // Real times only: '00:00' is the "time TBC" marker for date-only slots, so a day
            // shows its time chips only when the centre actually published times (France/Poland).
            // Week chips carry no times (times array is empty) — picking the week is the selection.
            var times = d.week ? [] : (d.times || []).filter(function (t) { return t.label !== '00:00'; });
            var b = document.createElement('button'); b.type = 'button'; b.className = 'slot' + (d.week ? ' wkslot' : '');
            b.innerHTML = (first && i === 0 ? '<span class="soon">Soonest</span>' : '') +
              (wd ? '<span class="wd">' + esc(wd) + '</span>' : '') +
              '<span class="dm">' + esc(dm) + '</span>' +
              (d.week ? '<span class="ct">Tap to hold</span>' : '') +
              (times.length ? '<span class="ct">' + times.length + (times.length === 1 ? ' time' : ' times') + '</span>' : '');
            b.addEventListener('click', function () {
              // No published times for this day: picking the day is the whole selection.
              if (!times.length) { selectDay(b, c.name, d.label); return; }
              Array.prototype.forEach.call(box.querySelectorAll('.slot'), function (x) { x.classList.remove('sel'); });
              b.classList.add('sel');
              // Reveal this day's times (the "clock") in this centre's time box.
              tbox.innerHTML = '<div class="sc-tlbl"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>Times for ' + esc(d.label) + '</div>';
              var chips = document.createElement('div'); chips.className = 'tchips';
              times.forEach(function (t) {
                var tc = document.createElement('button'); tc.type = 'button'; tc.className = 'tchip';
                tc.textContent = t.label;
                tc.addEventListener('click', function () { selectTime(tc, c.name, d.label, t.label); });
                chips.appendChild(tc);
              });
              tbox.appendChild(chips); tbox.classList.add('show');
              // Picking a new day clears any prior time selection.
              book.setAttribute('aria-disabled', 'true'); book.removeAttribute('href'); setLabel('Select a time to book');
              try { tbox.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); } catch (e) {}
            });
            row.appendChild(b);
          });
          card.appendChild(row);
          card.appendChild(tbox);
          first = false;
        } else {
          var p = document.createElement('p'); p.className = 'sc-ask';
          p.textContent = 'No published slots right now — ask us to check live.';
          card.appendChild(p);
        }
        box.appendChild(card);
      });
    }
    function open(c, band) {
      country = c; centre = ''; slot = '';
      // Recolour the modal to the country's availability band (green default / amber / red).
      modal.classList.remove('lim', 'low');
      if (band === 'lim' || band === 'tight') modal.classList.add('lim');
      else if (band === 'low' || band === 'none') modal.classList.add('low');
      title.textContent = 'Select your ' + noun + ', ' + c;
      book.setAttribute('aria-disabled', 'true'); book.removeAttribute('href');
      setLabel('Select a ' + (mode === 'centre' ? 'centre' : 'slot') + ' to book');
      box.innerHTML = '<p class="slotm-load">Loading centres…</p>';
      modal.classList.add('open');
      // Instant path: render from the preloaded blob (no network). Falls through to fetch only
      // if this country wasn't inlined (e.g. matched by slug, or blob missing).
      var pre = (window.__APPT_SLOTS || {})[c];
      if (pre) { renderCentres({ country: c, centres: pre }); return; }
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
      t.addEventListener('click', function (e) { e.preventDefault(); open(t.getAttribute('data-slotcountry'), t.getAttribute('data-slotband')); });
    });
    book.addEventListener('click', function (e) { if (book.getAttribute('aria-disabled') === 'true') e.preventDefault(); });
    document.getElementById('slotm-x').addEventListener('click', close);
    modal.addEventListener('click', function (e) { if (e.target === modal) close(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && modal.classList.contains('open')) close(); });
  })();
</script>
