@extends('layouts.public')

@section('title', 'Free checkers: Do I need a visa or an IDP? | Beyond Passports')
@section('description', 'Free UK travel checkers from Beyond Passports. Find out whether you need a visa, eVisa or ETA, and whether you need an International Driving Permit (IDP) for your trip. Independent service, not a government website.')

@push('head')
<style>
  /* tools.blade — page-scoped layout only. Palette/type/components inherited from ukv.css. */

  /* ── Hero — navy mesh (pick B) ── */
  .tl-hero{
    background:
      radial-gradient(640px 280px at 12% 0, rgba(21,94,122,.5), transparent 60%),
      radial-gradient(600px 260px at 92% 100%, rgba(46,154,140,.5), transparent 60%),
      var(--navy);
  }
  .tl-hero .eyebrow{color:var(--soft)}
  .tl-hero-copy h1{font-size:clamp(30px,4vw,46px);letter-spacing:-.03em;color:#fff;max-width:18ch}
  .tl-hero-copy .lede{color:rgba(255,255,255,.85);max-width:50ch;margin:.6em 0 1.6em}
  /* mini trust row (glass on dark) */
  .tl-trust{display:flex;flex-wrap:wrap;gap:8px;margin-top:24px}
  .tl-trust span{display:inline-flex;align-items:center;gap:7px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);border-radius:999px;padding:7px 14px;font-size:12.5px;color:#fff;font-weight:600}
  .tl-trust span::before{content:"✓";display:inline-block;width:16px;height:16px;background:var(--soft);color:var(--navy);border-radius:50%;font-size:9px;font-weight:800;line-height:16px;text-align:center;flex:0 0 16px}

  /* ── Two-checker grid ── */
  .tl-grid{display:grid;grid-template-columns:1fr 1fr;gap:26px;align-items:start;max-width:920px;margin:0 auto}
  /* unified trip checker — horizontal bar (home .hp-bar style) */
  .tl-head{text-align:center;max-width:60ch;margin:0 auto 28px}
  .tl-barwrap{max-width:880px;margin:0 auto}
  .tl-bar{display:flex;gap:12px;align-items:flex-end;background:#fff;border:1px solid var(--paper-edge);border-radius:18px;
    box-shadow:0 30px 64px -30px rgba(40,50,70,.45);padding:18px;position:relative}
  .tl-bar .f{flex:1;min-width:0}
  .tl-bar .f--dest{flex:1.5}
  .tl-bar label{display:block;font:700 12px var(--display);margin:0 0 5px;color:var(--ink)}
  .tl-opt{font-weight:500;color:var(--muted);font-size:11px}
  .tl-bar select{width:100%;padding:12px;border:1px solid var(--paper-edge);border-radius:11px;font:inherit;font-size:15px;background:#fff;color:var(--ink)}
  .tl-bar .btn{white-space:nowrap}
  .tl-bar select[aria-invalid="true"]{border-color:#c0392b;box-shadow:0 0 0 1px #c0392b}
  .tl-stamp{position:absolute;top:-20px;right:-16px;z-index:3;width:56px;height:56px;border-radius:50%;background:#fff;
    border:2px solid var(--stamp);color:var(--stamp-text);display:grid;place-items:center;font:800 9px var(--display);text-align:center;line-height:1.15;
    box-shadow:0 10px 24px -12px rgba(40,50,70,.5)}
  .tl-barhint{text-align:center;margin-top:12px}
  .tl-barerr{display:none;max-width:560px;margin:12px auto 0}
  .tl-barerr.show{display:block}
  .tl-result{max-width:880px;margin:18px auto 0}
  .tl-seg{padding-top:16px;margin-top:16px;border-top:1px solid var(--paper-edge)}
  .tl-seg:first-child{padding-top:0;margin-top:0;border-top:0}
  .tl-seg[hidden]{display:none}
  @media (max-width:680px){.tl-bar{flex-direction:column;align-items:stretch}.tl-stamp{display:none}}

  /* ── Checker card overrides ── */
  /* navy stub header (pick B) */
  .tl-checker .stub{background:linear-gradient(100deg,var(--navy),#2e3740);color:#fff;border-bottom:0}
  .tl-checker .stub span:last-child{color:var(--soft)}
  .tl-checker .cbody{padding:26px 22px}
  .tl-checker label{display:block;font-size:13px;font-weight:700;color:var(--ink);margin:16px 0 5px;letter-spacing:.01em}
  .tl-checker label:first-of-type{margin-top:0}
  .tl-checker select{margin-bottom:0}
  .tl-checker .btn{width:100%;margin-top:18px;padding:14px 20px}
  .tl-checker .hint{font-family:var(--body);font-size:12px;color:var(--muted);margin:10px 0 0;letter-spacing:.01em}
  /* validation */
  .tl-checker .form-error{display:none;background:#fdeceb;border:1px solid #f3c6c2;color:#8a2a22;border-radius:8px;padding:10px 13px;font-size:13.5px;margin:12px 0 0}
  .tl-checker .form-error.show{display:block}
  .tl-checker select[aria-invalid="true"]{border-color:#c0392b;box-shadow:0 0 0 1px #c0392b}

  /* ── Result panel ── */
  .tl-result{margin-top:18px;background:var(--white);border:1px solid var(--paper-edge);border-radius:12px;padding:18px 18px 16px;box-shadow:var(--lift-1)}
  .tl-result[aria-hidden="true"]{display:none}
  .tl-result .rtag{font-family:var(--body);font-weight:800;font-size:10.5px;letter-spacing:.12em;text-transform:uppercase;color:var(--stamp-text);margin:0 0 8px;display:flex;align-items:center;gap:8px}
  .tl-result .rtag svg{flex:0 0 auto}
  .tl-result h3{font-family:var(--display);font-size:17px;color:var(--navy);margin:0 0 6px;line-height:1.25;letter-spacing:-.02em}
  .tl-result p{font-size:14px;color:#33454f;margin:0 0 10px;line-height:1.55}
  .tl-result .rlink{font-family:var(--body);font-size:14px;font-weight:700;color:var(--cta)}
  .tl-result .rmicro{font-family:var(--body);font-size:11.5px;color:var(--muted);margin:10px 0 0;letter-spacing:.01em}

  /* ── How-it-works section layout ── */
  .tl-steps-wrap{max-width:920px;margin:0 auto}

  /* ── Honest note ── */
  .tl-honest{max-width:920px;margin:0 auto;background:var(--white);border:1px solid var(--paper-edge);border-left:4px solid var(--stamp-text);border-radius:12px;padding:22px 26px}
  .tl-honest p{margin:0;font-size:15px;color:#33454f;line-height:1.65}
  .tl-honest strong{color:var(--ink)}

  @media (max-width:860px){
    .tl-grid{grid-template-columns:1fr}
    .tl-checker .cbody{padding:22px 18px}
  }
</style>
@endpush

@section('content')

{{-- HERO — navy mesh --}}
<section class="mesh-hero tl-hero">
  <div class="wrap">
    <div class="mh-grid">
      <div class="mh-copy tl-hero-copy reveal">
        <p class="eyebrow">Free checkers</p>
        <h1>Do I need a visa? Do I need an IDP?</h1>
        <p class="lede">Two quick checkers for UK travellers: general guidance in seconds, with a real visa specialist to confirm your exact requirements before you pay. The first step in not getting it wrong is to start the right application, with the right documents.</p>
        <div class="tl-trust">
          <span>Free to use</span>
          <span>General guidance, human-confirmed</span>
          <span>Not a government website</span>
        </div>
        @include('partials.trustpilot-cta', ['align' => 'left', 'theme' => 'dark', 'margin' => '20px 0 0'])
      </div>
      <div class="mh-card reveal" style="animation-delay:.08s">
        {{-- Preview of the visa checker card in the hero --}}
        <div class="checker tl-checker" style="box-shadow:var(--lift-3)">
          <div class="stub"><span>Visa check</span><span>Instant result</span></div>
          <div class="cbody">
            <label for="h-dest" style="font-size:13px;font-weight:700;color:var(--ink);display:block;margin:0 0 5px">Where are you going?</label>
            <select id="h-dest" style="width:100%;padding:12px;border:1px solid var(--paper-edge);border-radius:10px;font-size:15px;background:var(--white);color:var(--ink)">
              <option value="">Choose a destination…</option>
              @foreach ($navDestinations as $d)
              <option value="{{ $d->name }}">{{ $d->name }}</option>
              @endforeach
            </select>
            <label for="h-pass" style="font-size:13px;font-weight:700;color:var(--ink);display:block;margin:16px 0 5px">Your passport</label>
            <select id="h-pass" style="width:100%;padding:12px;border:1px solid var(--paper-edge);border-radius:10px;font-size:15px;background:var(--white);color:var(--ink)">
              <option value="">Choose…</option>
              <option value="UK">United Kingdom</option>
              <option value="Other">Other nationality</option>
            </select>
            <button type="button" id="h-go" class="btn" style="display:block;width:100%;text-align:center;margin-top:18px;padding:14px 20px;box-sizing:border-box">@include('partials.wa-glyph')Get my answer on WhatsApp →</button>
            <p style="font-size:12px;color:var(--muted);margin:10px 0 0;text-align:center">A real UK person replies · we confirm your exact rules</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- UNIFIED TRIP CHECKER --}}
<section><div class="wrap">
  <div class="sec-head reveal tl-head">
    <p class="eyebrow">Use the checkers</p>
    <h2>Find out what your trip requires</h2>
  </div>
  {{-- UNIFIED TRIP CHECKER — horizontal bar (visa + driving in one) --}}
  <div class="tl-barwrap">
    <form id="trip-form" class="tl-bar reveal" novalidate>
      <span class="tl-stamp" aria-hidden="true">CHECK<br>&amp; GO</span>
      <div class="f f--dest">
        <label for="t-dest">Where are you going?</label>
        <select id="t-dest" name="dest">
          <option value="">Choose a destination…</option>
          @foreach ($navDestinations as $d)
          <option value="{{ $d->name }}">{{ $d->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="f">
        <label for="t-pass">Your passport</label>
        <select id="t-pass" name="pass">
          <option value="">Choose…</option>
          <option value="UK">United Kingdom</option>
          <option value="Other">Other nationality</option>
        </select>
      </div>
      <div class="f">
        <label for="t-lic">Will you drive? <span class="tl-opt">(optional)</span></label>
        <select id="t-lic" name="lic">
          <option value="">Not driving</option>
          <option value="full">Full UK licence</option>
          <option value="provisional">Provisional licence</option>
        </select>
      </div>
      <button type="submit" class="btn">Check my trip →</button>
    </form>
    <p class="hint tl-barhint">Free · general guidance · we confirm your exact rules</p>
    <p class="form-error tl-barerr" id="trip-error" role="alert" aria-live="assertive">Choose a destination and your passport to see your result.</p>

    <div class="tl-result" id="trip-result" role="region" aria-label="Trip checker result" aria-hidden="true" tabindex="-1">
      <div class="tl-seg" id="tr-visa">
        <p class="rtag"><svg width="16" height="16" viewBox="0 0 48 48" aria-hidden="true"><use href="#ukv-stamp"></use></svg> Visa / entry</p>
        <h3 id="tr-visa-title">…</h3>
        <p id="tr-visa-body">…</p>
        <a class="rlink" id="tr-visa-link" href="{{ App\Support\SiteStats::chatUrl('Hi Beyond Passports, I used the checker, can you confirm my eligibility?') }}" target="_blank" rel="noopener">Check eligibility →</a> @include('partials.consult-cta')
      </div>
      <div class="tl-seg" id="tr-drive" hidden>
        <p class="rtag"><svg width="16" height="16" viewBox="0 0 48 48" aria-hidden="true"><use href="#ukv-stamp"></use></svg> Driving / IDP</p>
        <h3 id="tr-drive-title">…</h3>
        <p id="tr-drive-body">…</p>
        <a class="rlink" id="tr-drive-link" href="{{ url('/driving-abroad') }}">Get help with my IDP paperwork →</a>
      </div>
      <p class="rmicro">General guidance for UK citizens / licence holders. Your exact rules depend on your nationality, residence and trip. We confirm them before you pay.</p>
    </div>
  </div>
</div></section>

{{-- HOW THE CHECKER FLOW WORKS --}}
<section class="alt"><div class="wrap">
  <div class="sec-head reveal"><p class="eyebrow">From check to sorted</p><h2>How it works after the checker</h2></div>
  <div class="tl-steps-wrap">
    <div class="steps">
      <div class="step reveal"><div class="num">01</div><div class="rule"></div><h3>Check &amp; confirm</h3><p>Use the free checker for general guidance, then we confirm your exact requirements for your nationality, residence and trip.</p></div>
      <div class="step reveal" style="animation-delay:.06s"><div class="num">02</div><div class="rule"></div><h3>We prepare &amp; check</h3><p>Our UK team prepares the paperwork and checks it for the avoidable things that get applications refused. For a visa we submit it; for an IDP we ready it for your in-person collection.</p></div>
      <div class="step reveal" style="animation-delay:.12s"><div class="num">03</div><div class="rule"></div><h3>Submit or collect</h3><p>Your visa is submitted and tracked, or you collect your IDP in person at PayPoint with the paperwork already sorted.</p></div>
    </div>
  </div>
</div></section>

{{-- HONEST NOTE --}}
<section><div class="wrap">
  <div class="tl-honest reveal">
    <p><strong>These checkers give general guidance for UK citizens.</strong> Your exact requirements depend on your nationality, residence and trip. We confirm them before you pay. Beyond Passports is an independent service and is not a government website. No approval is guaranteed.</p>
  </div>
</div></section>

{{-- CTA --}}
<section class="cta-band"><div class="wrap reveal">
  <div class="rule"></div>
  <h2>Checked it? Now let's sort it</h2>
  <p style="max-width:50ch;color:#eef0f1">Start your visa application or get help preparing your IDP paperwork. A UK &amp; Europe team checks every case before anything is submitted.</p>
  <div class="row"><a href="{{ App\Support\SiteStats::chatUrl('Hi Beyond Passports, I used the checker, can you confirm my eligibility?') }}" target="_blank" rel="noopener" class="btn">Check eligibility →</a> @include('partials.consult-cta')<a href="https://wa.me/{{ config('ukv.whatsapp') ?: '447882747584' }}?text={{ rawurlencode('Hi Beyond Passports, I used the checker, can you confirm my eligibility?') }}" class="btn btn--glass">@include('partials.wa-glyph')Chat on WhatsApp</a></div>
</div></section>

@endsection

@push('head')
<script>
  // tools.blade — two free checkers. Vanilla JS, no network. General guidance only.
  document.addEventListener('DOMContentLoaded', function () {
    // URLs resolved server-side so client links honour the silo.
    var APPLY_URL = @json(url('/apply'));
    var IDP_URL   = @json(url('/driving-abroad'));

    // --- destination knowledge (UK citizen, tourism) -------------------------
    var VISA = {
      'Turkey':          { type: 'eVisa',     note: 'an eVisa' },
      'Egypt':           { type: 'eVisa',     note: 'an eVisa' },
      'India':           { type: 'eVisa',     note: 'an eVisa' },
      'USA (ESTA)':      { type: 'ETA',       note: 'an ESTA travel authorisation' },
      'Australia (eTA)': { type: 'ETA',       note: 'an Australian eTA' },
      'Thailand':        { type: 'visa-free', note: 'no visa (visa-free entry, with conditions)' }
    };
    var IDP = {
      'Turkey':          { need: true,  conv: '1949 type' },
      'Egypt':           { need: true,  conv: '1926/1949 type' },
      'India':           { need: true,  conv: '1949 type' },
      'USA (ESTA)':      { need: false, conv: '1949 type' },
      'Australia (eTA)': { need: false, conv: '1949 type' },
      'Thailand':        { need: true,  conv: '1949 type' }
    };

    function show(el) { el.setAttribute('aria-hidden', 'false'); }
    function hide(el) { el.setAttribute('aria-hidden', 'true'); }

    // --- Validation announcement (WCAG 3.3.1 / 4.1.3) -----------------------
    function showFieldError(errEl) { if (errEl) errEl.classList.add('show'); }
    function hideFieldError(errEl) { if (errEl) errEl.classList.remove('show'); }
    function markInvalid(ctrl) {
      ctrl.setAttribute('aria-invalid', 'true');
      var clear = function () {
        ctrl.removeAttribute('aria-invalid');
        ctrl.removeEventListener('change', clear);
      };
      ctrl.addEventListener('change', clear);
    }
    function clearInvalid() {
      for (var i = 0; i < arguments.length; i++) arguments[i].removeAttribute('aria-invalid');
    }

    // --- UNIFIED TRIP CHECKER (visa + optional driving in one) ---------------
    var tForm  = document.getElementById('trip-form');
    var tResult = document.getElementById('trip-result');
    var tError = document.getElementById('trip-error');
    // visa segment
    var vTitle = document.getElementById('tr-visa-title');
    var vBody  = document.getElementById('tr-visa-body');
    var vLink  = document.getElementById('tr-visa-link');
    // driving segment
    var driveSeg = document.getElementById('tr-drive');
    var dTitle = document.getElementById('tr-drive-title');
    var dBody  = document.getElementById('tr-drive-body');
    var dLink  = document.getElementById('tr-drive-link');

    function waHref(msg) { return 'https://wa.me/' + WA + '?text=' + encodeURIComponent(msg); }
    function asChat(link, msg, label) {
      link.textContent = label;
      link.setAttribute('href', waHref(msg));
      link.setAttribute('target', '_blank');
      link.setAttribute('rel', 'noopener');
      link.style.display = '';
    }

    function fillVisa(dest, pass) {
      var info = VISA[dest];
      if (pass === 'UK' && info) {
        vTitle.textContent = 'Most UK travellers need: ' + info.note + ' for ' + dest + '.';
        vBody.textContent  = 'Based on a UK passport for tourism, the usual requirement is ' + info.note + '. We can prepare and check it for you, and confirm your exact rules first.';
      } else if (pass === 'UK') {
        vTitle.textContent = "We'll confirm exactly what you need for " + dest + '.';
        vBody.textContent  = 'Requirements vary by destination and trip. Tell us your plans and a UK visa specialist confirms your exact rules, then we prepare and check everything before you submit.';
      } else {
        vTitle.textContent = 'Your requirements depend on your nationality.';
        vBody.textContent  = 'Because you are not travelling on a UK passport, your requirements for ' + dest + ' depend on your nationality and where you live. We will confirm them for you.';
      }
      asChat(vLink, 'Hi Beyond Passports, I am travelling to ' + dest + ' on ' + (pass === 'UK' ? 'a UK' : 'a non-UK') + ' passport. What do I need for my visa?', 'Sort my ' + dest + ' visa on WhatsApp →');
    }

    function fillDrive(dest, lic) {
      var destName = dest.replace(' (ESTA)', '').replace(' (eTA)', '');
      asChat(dLink, 'Hi Beyond Passports, I will be driving in ' + destName + ' on a UK licence. Do I need an IDP, and can you help with the paperwork?', 'Sort my IDP for ' + destName + ' on WhatsApp →');
      if (lic === 'provisional') {
        dTitle.textContent = 'You cannot get an IDP on a provisional licence.';
        dBody.textContent  = 'An International Driving Permit is only issued to holders of a full UK driving licence. With a provisional you are not eligible, and most countries (including ' + destName + ') will not let you drive on a provisional. You would need to pass your full UK test first.';
        dLink.style.display = 'none';
        return;
      }
      var rec = IDP[dest];
      if (!rec) {
        dTitle.textContent = "We'll confirm whether you need an IDP for " + destName + '.';
        dBody.textContent  = 'On a full UK licence, whether an International Driving Permit is required depends on the country and how long you are driving. Tell us your trip and we will confirm. If you need one, we offer guided self-service: we prepare and check the paperwork, you collect it in person at a PayPoint.';
        return;
      }
      if (rec.need) {
        dTitle.textContent = 'You will usually need an IDP for ' + destName + '.';
        dBody.textContent  = 'On a full UK licence, an International Driving Permit (' + rec.conv + ') is typically required to drive in ' + destName + '. An IDP is obtained in person at a PayPoint. We offer guided self-service: we prepare and check your paperwork, and you collect it yourself in person.';
      } else {
        dTitle.textContent = 'You usually do not need an IDP for ' + destName + '.';
        dBody.textContent  = 'On a full UK licence, an International Driving Permit is generally not required for short visits to ' + destName + ' (check car-hire and local rules). When one is needed, ' + destName + ' typically recognises the ' + rec.conv + ' permit. If you do need one, it is obtained in person at a PayPoint: we prepare and check the paperwork, you collect it yourself.';
      }
    }

    tForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var dest = tForm.dest.value;
      var pass = tForm.pass.value;
      var lic  = tForm.lic.value;
      if (!dest || !pass) {
        hide(tResult);
        clearInvalid(tForm.dest, tForm.pass);
        markInvalid(dest ? tForm.pass : tForm.dest);
        showFieldError(tError);
        (dest ? tForm.pass : tForm.dest).focus();
        return;
      }
      clearInvalid(tForm.dest, tForm.pass);
      hideFieldError(tError);

      fillVisa(dest, pass);
      if (lic) { fillDrive(dest, lic); driveSeg.hidden = false; }
      else { driveSeg.hidden = true; }

      show(tResult);
      tResult.focus({ preventScroll: true });
    });

    // --- HERO card → opens a WhatsApp chat with the trip pre-filled ----------
    var hDest = document.getElementById('h-dest');
    var hPass = document.getElementById('h-pass');
    var hGo   = document.getElementById('h-go');
    var WA    = @json(config('ukv.whatsapp') ?: '447882747584');
    if (hGo && hDest && hPass) {
      hGo.addEventListener('click', function () {
        var place = hDest.value ? hDest.value : '';
        var pass = hPass.value === 'Other' ? 'a non-UK' : 'a UK';
        var msg = place
          ? 'Hi Beyond Passports, I am travelling to ' + place + ' on ' + pass + ' passport. What do I need?'
          : 'Hi Beyond Passports, I would like help working out what I need for my trip.';
        window.open('https://wa.me/' + WA + '?text=' + encodeURIComponent(msg), '_blank', 'noopener');
      });
    }
  });
</script>
@endpush
