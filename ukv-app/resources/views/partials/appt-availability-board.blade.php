{{-- Appointment availability board + self-serve slot-picker modal. Self-contained (own CSS with
     hardcoded colours so it works on any page/theme). Data: $byRegion + $availability (provided by
     DestinationController on /schengen-visa, and by a view composer on /schengen-visa-help).
     Real CentreSlot data via route('appointments.slots'). No-JS fallback = destination page link. --}}
@php $apbkWa = config('ukv.whatsapp') ?: '447882747584'; @endphp
<style>
  #sg-appts{padding:72px 0;background:#F4F6FA}
  #sg-appts .wrap{max-width:1080px;margin:0 auto;padding:0 20px}
  #sg-appts .sec-head{text-align:center;max-width:62ch;margin:0 auto}
  #sg-appts .sec-head .eyebrow{font-weight:800;font-size:.74rem;letter-spacing:.18em;text-transform:uppercase;color:#1F6E63;margin:0 0 12px}
  #sg-appts .sec-head h2{font:800 clamp(1.6rem,3.2vw,2.3rem)/1.12 "Outfit",system-ui,sans-serif;color:#16222E;letter-spacing:-.02em;margin:0}
  #sg-appts .sec-head .lede{margin:12px auto 0;max-width:58ch;color:#5d6b76;font-size:1.02rem;line-height:1.6}
  #sg-appts .ap-note{display:inline-flex;align-items:center;gap:9px;margin:16px auto 0;font-size:12.5px;color:#5d6b76;background:#fff;border:1px solid #E2E8EC;border-radius:999px;padding:8px 15px}
  #sg-appts .ap-note .d{width:8px;height:8px;border-radius:50%;background:#2E9A8C;flex:none;box-shadow:0 0 0 4px rgba(92,154,123,.16)}
  #sg-appts .sg-tabs{display:flex;flex-wrap:wrap;justify-content:center;gap:9px;margin-top:24px}
  #sg-appts .sg-tab{display:inline-flex;align-items:center;gap:7px;background:#fff;border:1px solid #E2E8EC;border-radius:999px;padding:9px 16px;font:700 13.5px "Outfit",system-ui,sans-serif;color:#33454f;cursor:pointer;transition:.15s}
  #sg-appts .sg-tab .c{font-size:11px;font-weight:800;background:#eef3f5;color:#5d6b76;border-radius:999px;padding:1px 7px}
  #sg-appts .sg-tab:hover{border-color:#2E9A8C}
  #sg-appts .sg-tab.active{background:#155E7A;border-color:#155E7A;color:#fff}
  #sg-appts .sg-tab.active .c{background:rgba(255,255,255,.2);color:#fff}
  #sg-appts .ap-panel{display:none;margin-top:22px}
  #sg-appts .ap-panel.active{display:block}
  #sg-appts .ap-tiles{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}
  @media (max-width:820px){#sg-appts .ap-tiles{grid-template-columns:1fr 1fr}}
  @media (max-width:480px){#sg-appts .ap-tiles{grid-template-columns:1fr}}
  #sg-appts .ap-tile{display:block;text-decoration:none;background:#fff;border:1px solid #E2E8EC;border-radius:15px;padding:16px 17px;box-shadow:0 12px 30px -26px rgba(40,50,70,.5);transition:transform .18s,box-shadow .18s}
  #sg-appts .ap-tile:hover{transform:translateY(-3px);box-shadow:0 34px 60px -30px rgba(22,34,46,.45)}
  #sg-appts .ap-tp{display:flex;justify-content:space-between;align-items:center;margin-bottom:13px;gap:10px}
  #sg-appts .ap-tile h4{font:800 16px "Outfit",system-ui,sans-serif;color:#16222E;margin:0}
  #sg-appts .ap-st{display:inline-flex;align-items:center;gap:6px;font:800 10px "Outfit",system-ui,sans-serif;letter-spacing:.05em;text-transform:uppercase;padding:4px 9px;border-radius:999px;white-space:nowrap}
  #sg-appts .ap-st .dot{width:6px;height:6px;border-radius:50%}
  #sg-appts .ap-st.ok{background:rgba(46,154,140,.14);color:#1F6E63}#sg-appts .ap-st.ok .dot{background:#2E9A8C}
  #sg-appts .ap-st.lim{background:rgba(200,146,58,.16);color:#946100}#sg-appts .ap-st.lim .dot{background:#c8923a}
  #sg-appts .ap-st.ask{background:rgba(21,94,122,.12);color:#155E7A}#sg-appts .ap-st.ask .dot{background:#155E7A}
  #sg-appts .ap-bar{height:6px;border-radius:999px;background:#e7edf0;overflow:hidden}
  #sg-appts .ap-bar>i{display:block;height:100%;border-radius:999px}
  #sg-appts .ap-bar>i.ok{background:linear-gradient(90deg,#2E9A8C,#5C9A7B)}
  #sg-appts .ap-bar>i.lim{background:linear-gradient(90deg,#c8923a,#e0b15f)}
  #sg-appts .ap-bar>i.ask{background:repeating-linear-gradient(90deg,#cdd7dc 0 6px,transparent 6px 12px)}
  #sg-appts .ap-dt{font:700 14px "Outfit",system-ui,sans-serif;color:#16222E;margin-top:11px}
  #sg-appts .ap-lb{font-size:11px;color:#5d6b76;margin-top:3px}
  #sg-appts .ap-legend{display:flex;flex-wrap:wrap;justify-content:center;gap:18px;margin-top:28px;font-size:12px;color:#5d6b76}
  #sg-appts .ap-legend span{display:inline-flex;align-items:center;gap:7px}
  #sg-appts .ap-legend i{width:9px;height:9px;border-radius:50%;display:inline-block}
  #sg-appts .btn{display:inline-flex;align-items:center;gap:9px;background:#155E7A;color:#fff;text-decoration:none;font:800 15px "Outfit",system-ui,sans-serif;padding:13px 22px;border-radius:12px}
  #sg-appts .btn svg{width:18px;height:18px;fill:#fff}
  /* modal */
  .slotm{position:fixed;inset:0;z-index:140;display:none;align-items:center;justify-content:center;padding:16px;background:rgba(10,16,24,.6);backdrop-filter:blur(2px)}
  .slotm.open{display:flex}
  .slotm-box{background:#fff;border-radius:20px;width:min(560px,100%);max-height:88vh;overflow:auto;box-shadow:0 50px 100px -30px rgba(0,0,0,.55)}
  .slotm-hd{background:linear-gradient(135deg,#16323b,#1F6E63);color:#fff;padding:22px 24px 20px}
  .slotm-top{display:flex;justify-content:space-between;align-items:flex-start;gap:12px}
  .slotm-top h3{font:800 21px "Outfit",system-ui,sans-serif;color:#fff;margin:0;letter-spacing:-.02em}
  .slotm-x{background:rgba(255,255,255,.16);border:0;width:34px;height:34px;border-radius:50%;font-size:20px;line-height:1;color:#fff;cursor:pointer;flex:none}
  .slotm-s{color:rgba(255,255,255,.82);font-size:13.5px;margin:8px 0 0;line-height:1.5}
  .slotm-trust{display:flex;gap:16px;margin:14px 0 0;flex-wrap:wrap}
  .slotm-trust span{display:inline-flex;align-items:center;gap:6px;font:600 12px "Outfit",system-ui,sans-serif;color:rgba(255,255,255,.9)}
  .slotm-trust b{color:#8fe3c9}
  .slotm-body{background:#eef4f3;padding:18px 20px}
  .slotm-foot{padding:16px 24px 20px}
  .slotm-book{display:flex;align-items:center;justify-content:center;gap:9px;width:100%;background:#25D366;color:#fff;border:0;border-radius:13px;font:800 16px "Outfit",system-ui,sans-serif;padding:15px 22px;cursor:pointer;text-decoration:none;box-shadow:0 12px 26px -12px rgba(37,211,102,.7)}
  .slotm-book[aria-disabled="true"]{background:#c7d0d6;box-shadow:none;cursor:not-allowed}
  .slotm-book svg{width:19px;height:19px;fill:#fff;flex:none}
  .slotm-note{font-size:12px;color:#5d6b76;margin:12px 0 0;text-align:center}
  .slotm-load{text-align:center;color:#5d6b76;font-size:14px;padding:22px 0}
  .sc-centre{background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 14px 34px -24px rgba(20,45,50,.6);margin:0 0 14px}
  .sc-centre:last-child{margin:0}
  .sc-head{display:flex;justify-content:space-between;align-items:center;gap:10px;padding:12px 16px;background:linear-gradient(90deg,#eafaf6,#f4fbf9);border-bottom:1px solid #d9ece7}
  .sc-name{font:800 14px "Outfit",system-ui,sans-serif;color:#16222E}
  .sc-num{font:700 11px "Outfit",system-ui,sans-serif;color:#1F6E63;background:#fff;border:1px solid #cfe8e3;border-radius:999px;padding:3px 10px;white-space:nowrap}
  .sc-slots{display:flex;flex-wrap:wrap;gap:8px;padding:16px}
  .sc-ask{font-size:13px;color:#5d6b76;margin:0;padding:14px 16px}
  .slot{position:relative;min-width:74px;text-align:center;border:1.5px solid #E2E8EC;border-radius:12px;padding:9px 12px 10px;cursor:pointer;background:#f7fafb;transition:.12s;flex:0 0 auto}
  .slot:hover{border-color:#2E9A8C;background:#eff8f6}
  .slot .wd{display:block;font:700 10px "Outfit",system-ui,sans-serif;letter-spacing:.08em;text-transform:uppercase;color:#5d6b76}
  .slot .dm{display:block;font:800 16px "Outfit",system-ui,sans-serif;color:#1B2A33;margin-top:1px}
  .slot.sel{border-color:#155E7A;background:#155E7A;box-shadow:0 8px 18px -10px rgba(21,94,122,.7)}
  .slot.sel .wd{color:rgba(255,255,255,.85)}
  .slot.sel .dm{color:#fff}
  .slot .soon{position:absolute;top:-9px;left:8px;font:800 9px "Outfit",system-ui,sans-serif;letter-spacing:.06em;text-transform:uppercase;color:#fff;background:#2E9A8C;border-radius:999px;padding:2px 7px}
</style>

<section id="sg-appts"><div class="wrap">
  <div class="sec-head">
    <p class="eyebrow">Appointments</p>
    <h2>Where slots are opening now</h2>
    <p class="lede">For most Schengen visas the biometric appointment is the real bottleneck, not the visa. Here is a recent snapshot by country, soonest first. Start early so you do not miss your window.</p>
    <div><span class="ap-note"><span class="d"></span>Indicative only. We confirm live availability with the centre before you pay.</span></div>
  </div>

  {{-- Slot-picker modal (populated by JS for the chosen country) --}}
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

  @php
    $availByRegion = ($byRegion ?? collect())
        ->map(fn ($g) => $g->filter(fn ($d) => (($availability[$d->id]['status'] ?? 'ask') !== 'ask'))->values())
        ->filter(fn ($g) => $g->isNotEmpty());
  @endphp

  @if ($availByRegion->isNotEmpty())
  <div class="sg-tabs" id="apptTabs" role="tablist">
    @foreach ($availByRegion as $region => $group)
      <button type="button" class="sg-tab @if($loop->first) active @endif" role="tab" data-region="{{ $region }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}">{{ str_replace(' Europe', '', $region) }} <span class="c">{{ $group->count() }}</span></button>
    @endforeach
  </div>

  @foreach ($availByRegion as $region => $group)
    <div class="ap-panel @if($loop->first) active @endif" data-region="{{ $region }}">
      <div class="ap-tiles">
        @foreach ($group as $d)
          @php
            $a = $availability[$d->id] ?? ['status' => 'ask', 'next_available_on' => null, 'confirmed_at' => null];
            $status = $a['status'];
            $label = ['ok' => 'Available', 'lim' => 'Limited', 'ask' => 'Ask us'][$status];
            $width = ['ok' => '82%', 'lim' => '34%', 'ask' => '100%'][$status];
          @endphp
          <a class="ap-tile" href="{{ url('/visa/'.$d->slug) }}"
             data-slotcountry="{{ $d->name }}"
             data-slotdate="{{ optional($a['next_available_on'])->toDateString() }}"
             data-slotband="{{ $status }}">
            <div class="ap-tp">
              <h4>{{ $d->name }}</h4>
              <span class="ap-st {{ $status }}"><span class="dot"></span>{{ $label }}</span>
            </div>
            <div class="ap-bar"><i class="{{ $status }}" style="width:{{ $width }}"></i></div>
            @if($a['next_available_on'])
              <div class="ap-dt">{{ $a['next_available_on']->format('j M Y') }}</div>
              <div class="ap-lb">Next available &middot; tap to book</div>
              @if($a['confirmed_at'])
                <div class="ap-lb">as of {{ $a['confirmed_at']->format('j M') }}</div>
              @endif
            @else
              <div class="ap-dt">Pick a slot</div>
              <div class="ap-lb">Tap to choose &amp; book</div>
            @endif
          </a>
        @endforeach
      </div>
    </div>
  @endforeach
  @else
    <div style="text-align:center;max-width:620px;margin:0 auto;padding:24px 26px;background:#fff;border:1px solid #E2E8EC;border-radius:16px;box-shadow:0 12px 30px -26px rgba(40,50,70,.5)">
      <p style="margin:0 0 14px;color:#33454f">We're checking live availability with the centres now. Tell us your country and dates and we'll confirm the soonest slot and book it for you.</p>
      <a href="https://wa.me/{{ $apbkWa }}?text={{ rawurlencode('Hi Beyond Passports, please check the soonest Schengen biometric appointment for my dates.') }}" class="btn">@include('partials.wa-glyph')Ask us on WhatsApp</a>
    </div>
  @endif

  <div class="ap-legend">
    <span><i style="background:#2E9A8C"></i>Available</span>
    <span><i style="background:#c8923a"></i>Limited</span>
    <span><i style="background:#155E7A"></i>Others on request, we check live</span>
  </div>
</div></section>

<script>
  (function () {
    var tabs = Array.prototype.slice.call(document.querySelectorAll('#apptTabs .sg-tab'));
    var panels = Array.prototype.slice.call(document.querySelectorAll('#sg-appts .ap-panel'));
    if (!tabs.length) return;
    tabs.forEach(function (t) {
      t.addEventListener('click', function () {
        var region = t.getAttribute('data-region');
        tabs.forEach(function (x) { var on = x === t; x.classList.toggle('active', on); x.setAttribute('aria-selected', on ? 'true' : 'false'); });
        panels.forEach(function (p) { p.classList.toggle('active', p.getAttribute('data-region') === region); });
      });
    });
  })();
</script>

<script>
  // Per-centre slot picker (identical logic to /schengen-visa). Booking is centre-specific:
  // pick country -> fetch bookable centres + real CentreSlot slots -> pick a slot -> book on
  // WhatsApp. Progressive enhancement: tiles keep their destination-page href if JS is off.
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

    Array.prototype.forEach.call(document.querySelectorAll('.ap-tile[data-slotcountry]'), function (t) {
      t.addEventListener('click', function (e) { e.preventDefault(); open(t.getAttribute('data-slotcountry'), t.getAttribute('data-slotdate'), t.getAttribute('data-slotband')); });
    });
    book.addEventListener('click', function (e) { if (book.getAttribute('aria-disabled') === 'true') e.preventDefault(); });
    document.getElementById('slotm-x').addEventListener('click', close);
    modal.addEventListener('click', function (e) { if (e.target === modal) close(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && modal.classList.contains('open')) close(); });
  })();
</script>
