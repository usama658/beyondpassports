@extends('layouts.public')

@section('title', 'Free checkers — Do I need a visa or an IDP? | UKVisaCo')
@section('description', 'Free UK travel checkers from UKVisaCo. Find out whether you need a visa, eVisa or ETA, and whether you need an International Driving Permit (IDP) for your trip. Independent service — not a government website.')

@push('head')
<style>
  /* tools.blade — page-scoped layout only. Palette/type/components inherited from ukv.css. */
  .tools-hero{padding:48px 0 0;text-align:center}
  .tools-hero h1{font-size:clamp(32px,4.8vw,52px);color:var(--navy);letter-spacing:-.015em;max-width:18ch;margin:0 auto .3em}
  .tools-hero p.lede{font-size:18px;color:#33454f;max-width:54ch;margin:0 auto}
  .hero-sky{margin:22px auto 6px;max-width:620px;background:var(--navy);border-radius:12px;overflow:hidden;height:104px}
  .hero-sky svg{width:100%;height:100%}
  /* two-column checker grid */
  .checkers{display:grid;grid-template-columns:1fr 1fr;gap:26px;align-items:start}
  .checker:focus-within{box-shadow:0 0 0 3px rgba(20,86,184,.18),var(--shadow)}
  .checker .cbody form{margin:0}
  .checker .hint{font-family:var(--mono);font-size:11px;color:var(--hint);margin:12px 0 0;letter-spacing:.04em}
  /* checker validation message (announced via aria-live) + invalid-control state. (audit P3) */
  .checker .form-error{display:none;background:#fdeceb;border:1px solid #f3c6c2;color:#8a2a22;border-radius:6px;padding:10px 12px;font-size:13.5px;margin:12px 0 0}
  .checker .form-error.show{display:block}
  .checker select[aria-invalid="true"]{border-color:#c0392b;box-shadow:0 0 0 1px #c0392b}
  /* result panel inside a checker */
  .result{margin-top:16px;border:1px dashed var(--paper-edge);border-radius:8px;background:#f7fafb;padding:16px 16px 14px}
  .result[aria-hidden="true"]{display:none}
  .result .rtag{font-family:var(--mono);font-size:10px;letter-spacing:.12em;text-transform:uppercase;color:var(--stamp-text);margin:0 0 6px;display:flex;align-items:center;gap:8px}
  .result .rtag svg{flex:0 0 auto}
  .result h3{font-family:var(--display);font-size:18px;color:var(--navy);margin:0 0 6px;line-height:1.2}
  .result p{font-size:14px;color:#33454f;margin:0 0 10px;line-height:1.55}
  .result .rlink{font-family:var(--body);font-size:14px;font-weight:600}
  .result .rmicro{font-family:var(--mono);font-size:11px;color:var(--hint);margin:10px 0 0;letter-spacing:.03em}
  /* honest note */
  .honest{max-width:760px;margin:0 auto;background:var(--white);border:1px solid var(--paper-edge);border-left:4px solid var(--gold);border-radius:10px;padding:20px 22px}
  .honest p{margin:0;font-size:15px;color:#33454f;line-height:1.6}
  .honest strong{color:var(--ink)}
  @media (max-width:860px){
    .checkers{grid-template-columns:1fr}
  }
</style>
@endpush

@section('content')

{{-- HERO --}}
<section class="tools-hero"><div class="wrap">
  <p class="eyebrow">Free checkers</p>
  <h1>Do I need a visa? Do I need an IDP?</h1>
  <p class="lede">Two quick checkers for UK travellers — general guidance in seconds, with a real human to confirm your exact requirements before you pay.</p>
  <div class="hero-sky" aria-hidden="true">
    <svg viewBox="0 0 240 96" preserveAspectRatio="xMidYMax meet"><use href="#ukv-skyline"></use></svg>
  </div>
</div></section>
<div class="mrz"><div class="wrap"><span>UKV&lt;CHECK&lt;VISA&lt;AND&lt;IDP&lt;FREE&lt;GENERAL&lt;GUIDANCE&lt;ONLY&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;</span></div></div>

{{-- TWO CHECKERS --}}
<section><div class="wrap">
  <div class="checkers">

    {{-- A) VISA CHECKER --}}
    <div class="checker reveal" id="visa-card">
      <div class="stub"><span>VISA CHECK</span><span>UKV&lt;DEST&lt;&lt;&lt;</span></div>
      <div class="cbody">
        <form id="visa-form" novalidate>
          <label for="v-dest">Where are you going?</label>
          <select id="v-dest" name="dest">
            <option value="">Choose a destination…</option>
            @foreach ($navDestinations as $d)
            <option value="{{ $d->name }}">{{ $d->name }}</option>
            @endforeach
          </select>
          <label for="v-pass">Your passport</label>
          <select id="v-pass" name="pass">
            <option value="">Choose…</option>
            <option value="UK">United Kingdom</option>
            <option value="Other">Other nationality</option>
          </select>
          <button type="submit" class="btn">Check what I need →</button>
          <p class="hint">Free · general guidance · we confirm your exact rules</p>
          <p class="form-error" id="visa-error" role="alert" aria-live="assertive">Choose a destination and your passport to see your result.</p>
        </form>

        <div class="result" id="visa-result" role="region" aria-label="Visa checker result" aria-hidden="true" tabindex="-1">
          <p class="rtag"><svg width="16" height="16" viewBox="0 0 48 48" aria-hidden="true"><use href="#ukv-stamp"></use></svg> Visa check</p>
          <h3 id="visa-result-title">—</h3>
          <p id="visa-result-body">—</p>
          <a class="rlink" id="visa-result-link" href="{{ url('/apply') }}">See details &amp; fixed fees →</a>
          <p class="rmicro">General guidance for UK citizens. Your exact rules depend on your nationality, residence and trip.</p>
        </div>
      </div>
    </div>

    {{-- B) IDP CHECKER --}}
    <div class="checker reveal" id="idp-card">
      <div class="stub"><span>IDP CHECK</span><span>DVLA&lt;&lt;&lt;</span></div>
      <div class="cbody">
        <form id="idp-form" novalidate>
          <label for="i-dest">Where will you drive?</label>
          <select id="i-dest" name="dest">
            <option value="">Choose a destination…</option>
            @foreach ($navDestinations as $d)
            <option value="{{ $d->name }}">{{ $d->name }}</option>
            @endforeach
          </select>
          <label for="i-lic">Your UK licence</label>
          <select id="i-lic" name="lic">
            <option value="">Choose…</option>
            <option value="photocard">Photocard — full licence</option>
            <option value="paper">Paper — full licence</option>
            <option value="provisional">Provisional licence</option>
          </select>
          <button type="submit" class="btn">Check IDP →</button>
          <p class="hint">Free · IDPs are issued in person at PayPoint</p>
          <p class="form-error" id="idp-error" role="alert" aria-live="assertive">Choose where you'll drive and your licence type to see your result.</p>
        </form>

        <div class="result" id="idp-result" role="region" aria-label="IDP checker result" aria-hidden="true" tabindex="-1">
          <p class="rtag"><svg width="16" height="16" viewBox="0 0 48 48" aria-hidden="true"><use href="#ukv-stamp"></use></svg> IDP check</p>
          <h3 id="idp-result-title">—</h3>
          <p id="idp-result-body">—</p>
          <a class="rlink" id="idp-result-link" href="{{ url('/driving-abroad') }}">Get help with my IDP paperwork →</a>
          <p class="rmicro">General guidance for UK licence holders. An IDP is collected in person — we prepare and check, you collect.</p>
        </div>
      </div>
    </div>

  </div>
</div></section>

{{-- HOW THE CHECKER FLOW WORKS --}}
<section class="alt"><div class="wrap">
  <div class="sec-head reveal"><p class="eyebrow">From check to sorted</p><h2>How it works after the checker</h2></div>
  <div class="steps">
    <div class="step reveal"><div class="num">01</div><div class="rule"></div><h3>Check &amp; confirm</h3><p>Use the free checker for general guidance, then we confirm your exact requirements for your nationality, residence and trip.</p></div>
    <div class="step reveal"><div class="num">02</div><div class="rule"></div><h3>We prepare &amp; check</h3><p>Our UK team prepares and double-checks the paperwork — for a visa we submit it; for an IDP we ready it for your in-person collection.</p></div>
    <div class="step reveal"><div class="num">03</div><div class="rule"></div><h3>Submit or collect</h3><p>Your visa is submitted and tracked, or you collect your IDP in person at PayPoint with the paperwork already sorted.</p></div>
  </div>
</div></section>

{{-- HONEST NOTE --}}
<section><div class="wrap">
  <div class="honest reveal">
    <p><strong>These checkers give general guidance for UK citizens.</strong> Your exact requirements depend on your nationality, residence and trip — we confirm them before you pay. UKVisaCo is an independent service and is not a government website. No approval is guaranteed.</p>
  </div>
</div></section>

{{-- CTA --}}
<section class="cta-band"><div class="wrap reveal">
  <div class="rule"></div>
  <h2>Checked it — now let's sort it</h2>
  <p style="max-width:50ch;color:#cdd9e1">Start your visa application or get help preparing your IDP paperwork. A UK-based team checks every case before anything is submitted.</p>
  <div class="row"><a href="{{ url('/apply') }}" class="btn">Start my application →</a><a href="https://wa.me/{{ config('ukv.whatsapp') ?: '440000000000' }}" class="btn btn--wa">Chat on WhatsApp</a></div>
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

    // --- Validation announcement (WCAG 3.3.1 / 4.1.3 — audit P3) -----------------------
    // On an empty submit, announce a message in an aria-live region and flag the empty
    // control with aria-invalid="true" instead of silently moving focus.
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

    // --- VISA CHECKER --------------------------------------------------------
    var vForm   = document.getElementById('visa-form');
    var vResult = document.getElementById('visa-result');
    var vTitle  = document.getElementById('visa-result-title');
    var vBody   = document.getElementById('visa-result-body');
    var vLink   = document.getElementById('visa-result-link');
    var vError  = document.getElementById('visa-error');

    vForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var dest = vForm.dest.value;
      var pass = vForm.pass.value;
      if (!dest || !pass) {
        hide(vResult);
        clearInvalid(vForm.dest, vForm.pass);
        markInvalid(dest ? vForm.pass : vForm.dest);
        showFieldError(vError);
        (dest ? vForm.pass : vForm.dest).focus();
        return;
      }
      clearInvalid(vForm.dest, vForm.pass);
      hideFieldError(vError);
      if (pass === 'UK') {
        var info = VISA[dest];
        vTitle.textContent = 'Most UK travellers need: ' + info.note + ' for ' + dest + '.';
        vBody.textContent  = 'Based on a UK passport for tourism, the usual requirement is ' + info.note +
                             '. We can prepare and check it for you — and confirm your exact rules first.';
        vLink.textContent  = 'Start my application →';
        vLink.setAttribute('href', APPLY_URL);
        vLink.style.display = '';
      } else {
        vTitle.textContent = 'Your requirements depend on your nationality.';
        vBody.textContent  = 'Because you are not travelling on a UK passport, your requirements for ' + dest +
                             ' depend on your nationality and where you live — we will confirm them for you. Request a callback and a UK adviser will check your case.';
        vLink.textContent  = 'Request a callback →';
        vLink.setAttribute('href', APPLY_URL);
        vLink.style.display = '';
      }
      show(vResult);
      vResult.focus({ preventScroll: true });
    });

    // --- IDP CHECKER ---------------------------------------------------------
    var iForm   = document.getElementById('idp-form');
    var iResult = document.getElementById('idp-result');
    var iTitle  = document.getElementById('idp-result-title');
    var iBody   = document.getElementById('idp-result-body');
    var iLink   = document.getElementById('idp-result-link');
    var iError  = document.getElementById('idp-error');

    iForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var dest = iForm.dest.value;
      var lic  = iForm.lic.value;
      if (!dest || !lic) {
        hide(iResult);
        clearInvalid(iForm.dest, iForm.lic);
        markInvalid(dest ? iForm.lic : iForm.dest);
        showFieldError(iError);
        (dest ? iForm.lic : iForm.dest).focus();
        return;
      }
      clearInvalid(iForm.dest, iForm.lic);
      hideFieldError(iError);
      var destName = dest.replace(' (ESTA)', '').replace(' (eTA)', '');

      if (lic === 'provisional') {
        iTitle.textContent = 'You cannot get an IDP on a provisional licence.';
        iBody.textContent  = 'An International Driving Permit can only be issued to holders of a full UK driving licence. ' +
                             'With a provisional licence you are not eligible for an IDP, and most countries (including ' + destName +
                             ') will not let you drive on a provisional. You would need to pass your full UK test first.';
        iLink.style.display = 'none';
        show(iResult);
        iResult.focus({ preventScroll: true });
        return;
      }

      var rec = IDP[dest];
      var convNote = 'When one is needed, ' + destName + ' typically recognises the ' + rec.conv + ' International Driving Permit.';
      if (rec.need) {
        iTitle.textContent = 'You will usually need an IDP for ' + destName + '.';
        iBody.textContent  = 'On a full UK licence, an International Driving Permit (' + rec.conv + ') is typically required to drive in ' + destName +
                             '. An IDP is obtained in person at a PayPoint — we offer guided self-service: we prepare and check your paperwork, and you collect the IDP yourself in person.';
      } else {
        iTitle.textContent = 'You usually do not need an IDP for ' + destName + '.';
        iBody.textContent  = 'On a full UK licence, an International Driving Permit is generally not required for short visits to ' + destName +
                             ' (check car-hire and local rules). ' + convNote +
                             ' If you do need one, it is obtained in person at a PayPoint — we offer guided self-service: we prepare and check the paperwork, you collect it yourself.';
      }
      iLink.textContent = 'Get help with my IDP paperwork →';
      iLink.setAttribute('href', IDP_URL);
      iLink.style.display = '';
      show(iResult);
      iResult.focus({ preventScroll: true });
    });
  });
</script>
@endpush
