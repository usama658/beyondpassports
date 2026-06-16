@extends('layouts.public')

@section('title', 'Contact us — talk to a real UK-based person | Beyond Passports')
@section('description', 'Questions about your UK outbound visa? Call us and a real, UK-based person answers — Mon–Sat 9–6. WhatsApp for quick questions, email for documents, or request a callback. Independent service — not a government website.')

@push('head')
<style>
  /* contact — page-scoped layout only. Palette/type/components inherited from ukv.css. */
  .contact-hero{padding:56px 0 0;text-align:center}
  .contact-hero .inner{max-width:720px;margin:0 auto}
  .contact-hero h1{font-size:clamp(34px,5vw,56px);color:var(--navy);letter-spacing:-.015em}
  .contact-hero p.lede{font-size:18px;color:#33454f;max-width:52ch;margin:0 auto 8px}
  .hero-actions{display:flex;gap:14px;flex-wrap:wrap;justify-content:center;margin-top:24px}
  .hero-actions .btn{padding:16px 30px;font-size:17px}
  .hero-note{font-family:var(--mono);font-size:11px;letter-spacing:.06em;color:var(--hint);margin:16px 0 0}

  /* contact methods row */
  .methods{display:grid;grid-template-columns:repeat(3,1fr);gap:22px}
  .method{background:var(--white);border:1px solid var(--paper-edge);border-radius:12px;box-shadow:var(--shadow);padding:26px 22px;display:flex;flex-direction:column;gap:8px}
  .method .ico{width:44px;height:44px;border-radius:10px;background:#eaf3f2;color:var(--stamp);display:flex;align-items:center;justify-content:center;margin-bottom:6px}
  .method .ico svg{width:26px;height:26px}
  .method h3{font-size:20px;color:var(--navy);margin:0}
  .method .detail{font-family:var(--mono);font-weight:700;font-size:18px;color:var(--navy);letter-spacing:.02em;word-break:break-word}
  .method .detail a{color:var(--navy)}
  .method .sub{font-size:14px;color:var(--muted);margin:0}
  .method .hours{font-family:var(--mono);font-size:11px;letter-spacing:.06em;color:var(--hint);margin:2px 0 0}

  /* callback form card */
  .callback-wrap{max-width:640px;margin:0 auto}
  .callback-intro{text-align:center;max-width:52ch;margin:0 auto 22px}
  .callback-intro h2{font-size:clamp(24px,3vw,32px);color:var(--navy)}
  .callback-intro p{color:#33454f;margin:0}
  .checker.callback{text-align:left}
  .callback textarea{width:100%;padding:12px;border:1px solid var(--paper-edge);border-radius:6px;font:inherit;font-size:15px;min-height:96px;resize:vertical}
  .callback textarea:focus,.callback input:focus,.callback select:focus{outline:2px solid var(--cta);outline-offset:1px;border-color:var(--cta)}
  .consent{display:flex;gap:10px;align-items:flex-start;margin:16px 0 0}
  .consent input{width:auto;margin-top:3px;flex:0 0 auto}
  .consent label{margin:0;font-weight:500;color:#4a5b65;font-size:14px;line-height:1.45}
  .privacy-note{font-family:var(--mono);font-size:11px;color:var(--hint);letter-spacing:.04em;margin:12px 0 0}
  .form-error{display:none;background:#fdeceb;border:1px solid #f3c6c2;color:#8a2a22;border-radius:6px;padding:11px 13px;font-size:14px;margin:14px 0 0}
  .form-error.show{display:block}
  .form-ok{display:none;background:#eaf3f2;border:1px solid #b9ddd9;color:#0a5450;border-radius:6px;padding:16px 16px;font-size:15px;margin:14px 0 0;line-height:1.5}
  .form-ok.show{display:block}
  .form-ok strong{color:#073f3c}

  /* reassurance band */
  .reassure-band{display:flex;flex-wrap:wrap;gap:18px;align-items:center;justify-content:space-between}
  .reassure-band p{margin:0;font-size:clamp(19px,2.4vw,24px);font-family:var(--display);color:var(--navy);max-width:34ch;line-height:1.25}
  .reassure-band .links{display:flex;gap:12px;flex-wrap:wrap}

  @media (max-width:760px){
    .methods{grid-template-columns:1fr}
  }
</style>
@endpush

@section('content')

{{-- 1. HERO — phone is the primary channel --}}
<section class="mesh-hero mesh-hero--sm"><div class="wrap"><div class="mh-grid"><div class="mh-copy reveal">
  <p class="eyebrow">Talk to a human</p>
  <h1>Questions? We're a phone call away.</h1>
  <p class="lede">Call us and a real, UK-based person picks up — no bots, no call centres overseas. We'll talk through your trip, your visa and what we'd do next, with no obligation.</p>
  <div class="hero-actions">
    <a href="tel:{{ config('ukv.phone_e164') ?: '+440000000000' }}" class="btn">Call us now</a>
    <a href="https://wa.me/{{ config('ukv.whatsapp') ?: '440000000000' }}" class="btn btn--wa">Message on WhatsApp</a>
  </div>
  <p class="hero-note">Mon–Sat 9–6 UK time · independent service, not a government website</p>
</div></div></div></section>

{{-- 2. CONTACT METHODS --}}
<section><div class="wrap">
  <div class="sec-head reveal" style="max-width:60ch;margin:0 auto 36px;text-align:center">
    <p class="eyebrow">Ways to reach us</p>
    <h2>Pick whatever's easiest</h2>
  </div>
  <div class="methods reveal">
    <div class="method">
      <span class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.95.36 1.88.7 2.77a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.31-1.27a2 2 0 0 1 2.11-.45c.89.34 1.82.57 2.77.7A2 2 0 0 1 22 16.92Z"/></svg></span>
      <h3>Call us</h3>
      <p class="detail"><a href="tel:{{ config('ukv.phone_e164') ?: '+440000000000' }}">{{ config('ukv.phone') ?: 'Call us' }}</a></p>
      <p class="sub">Our main line — best for anything you'd rather just talk through.</p>
      <p class="hours">Mon–Sat 9–6 UK time</p>
    </div>
    <div class="method">
      <span class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5Z"/></svg></span>
      <h3>WhatsApp</h3>
      <p class="detail"><a href="https://wa.me/{{ config('ukv.whatsapp') ?: '440000000000' }}">{{ config('ukv.phone') ?: 'Message us' }}</a></p>
      <p class="sub">Quick questions, photos of a document, a fast reply on the go.</p>
      <p class="hours">Replies Mon–Sat 9–6 UK time</p>
    </div>
    <div class="method">
      <span class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/></svg></span>
      <h3>Email</h3>
      <p class="detail"><a href="mailto:{{ config('ukv.email') ?: 'hello@ukvisaco.example' }}">{{ config('ukv.email') ?: 'hello@ukvisaco.example' }}</a></p>
      <p class="sub">Best for sending documents or anything you want in writing.</p>
      <p class="hours">We reply within one working day</p>
    </div>
  </div>
</div></section>

{{-- 3. CALLBACK FORM (secondary; posts to POST /contact, progressively enhanced via fetch) --}}
<section class="alt"><div class="wrap">
  <div class="callback-wrap reveal">
    <div class="callback-intro">
      <p class="eyebrow">Rather we call you?</p>
      <h2>Request a callback</h2>
      <p>Leave your number and the best time to reach you — we'll call when it suits you. No queue, no obligation.</p>
    </div>

    <div class="checker callback">
      <div class="stub"><span>Callback request</span><span>UK-based team</span></div>
      <div class="cbody">
        <form id="callback-form" method="POST" action="{{ url('/contact') }}" novalidate>
          @csrf
          <label for="cb-name">Your name</label>
          <input type="text" id="cb-name" name="name" value="{{ old('name') }}" autocomplete="name" placeholder="Jane Traveller" required aria-required="true">

          <label for="cb-phone">Phone number</label>
          <input type="tel" id="cb-phone" name="phone" value="{{ old('phone') }}" autocomplete="tel" placeholder="+44 0000 000000" required aria-required="true">

          <label for="cb-time">Best time to call</label>
          <select id="cb-time" name="time">
            <option value="anytime">Any time, Mon–Sat 9–6</option>
            <option value="morning">Morning (9–12)</option>
            <option value="afternoon">Afternoon (12–3)</option>
            <option value="late">Late afternoon (3–6)</option>
          </select>

          <label for="cb-message">Your enquiry <span style="font-weight:400;color:var(--hint)">(optional)</span></label>
          <textarea id="cb-message" name="message" placeholder="Where are you travelling, and roughly when?"></textarea>

          <div class="consent">
            <input type="checkbox" id="cb-consent" name="consent" required aria-required="true">
            <label for="cb-consent">I agree to be contacted about my enquiry.</label>
          </div>
          <p class="privacy-note">We only use your details to respond to this enquiry. We never share them. See our <a href="{{ url('/legal') }}#privacy">Privacy notice</a>.</p>

          {{-- Server-rendered states for the JS-off path; JS toggles the same nodes. --}}
          <div class="form-error{{ $errors->any() ? ' show' : '' }}" id="cb-error" role="alert" aria-live="assertive">@if ($errors->any()){{ $errors->first() }}@endif</div>
          <div class="form-ok{{ session('status') ? ' show' : '' }}" id="cb-ok" role="status" aria-live="polite">@if (session('status'))<strong>{{ session('status') }}</strong>@endif</div>

          <button type="submit" class="btn">Request a callback</button>
        </form>
      </div>
    </div>
  </div>
</div></section>

{{-- 4. REASSURANCE BAND --}}
<section class="alt" style="border-top:0;padding:48px 0"><div class="wrap">
  <div class="reassure-band reveal">
    <p>Prefer to start now? Check your visa or begin an application.</p>
    <div class="links">
      <a href="{{ url('/tools') }}" class="btn btn--ghost" style="padding:13px 22px">Check your visa</a>
      <a href="{{ url('/apply') }}" class="btn">Start an application →</a>
    </div>
  </div>
</div></section>

@endsection

@push('head')
<script>
  // contact — callback form. Progressive enhancement: POSTs to /contact via fetch when JS is on
  // (keeps the inline success state, no page reload); falls back to a normal form POST if JS is
  // off or fetch fails. Client-side checks mirror the server rules (name + phone + consent).
  document.addEventListener('DOMContentLoaded', function () {
    var form    = document.getElementById('callback-form');
    var name    = document.getElementById('cb-name');
    var phone   = document.getElementById('cb-phone');
    var consent = document.getElementById('cb-consent');
    var err     = document.getElementById('cb-error');
    var ok      = document.getElementById('cb-ok');
    var submit  = form ? form.querySelector('button[type="submit"]') : null;
    if (!form) return;

    function showError(msg, field) {
      ok.classList.remove('show');
      ok.innerHTML = '';
      err.textContent = msg;
      err.classList.add('show');
      if (field) field.focus();
    }

    function showSuccess(msg) {
      err.classList.remove('show');
      err.textContent = '';
      ok.innerHTML = '<strong>' + msg.replace(/</g, '&lt;') + '</strong> ' +
        'You can also call or WhatsApp us any time, Mon–Sat 9–6.';
      ok.classList.add('show');
      form.reset();
      ok.setAttribute('tabindex', '-1');
      ok.focus && ok.focus();
    }

    form.addEventListener('submit', function (e) {
      if (!name.value.trim()) {
        e.preventDefault();
        showError('Please tell us your name so we know who to ask for.', name);
        return;
      }
      if (!phone.value.trim()) {
        e.preventDefault();
        showError('Please add a phone number so we can call you back.', phone);
        return;
      }
      if (!consent.checked) {
        e.preventDefault();
        showError('Please tick the box to confirm we can contact you about your enquiry.', consent);
        return;
      }

      // fetch unavailable -> let the browser do the normal POST (no preventDefault).
      if (typeof window.fetch !== 'function') return;

      e.preventDefault();
      if (submit) { submit.disabled = true; }

      fetch(form.action, {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: new FormData(form),
        credentials: 'same-origin'
      })
        .then(function (res) {
          return res.json().catch(function () { return {}; }).then(function (data) {
            return { status: res.status, data: data };
          });
        })
        .then(function (r) {
          if (r.status >= 200 && r.status < 300 && r.data && r.data.ok) {
            showSuccess(r.data.message || 'Thanks — your callback is booked.');
          } else if (r.status === 422 && r.data && r.data.errors) {
            var firstKey = Object.keys(r.data.errors)[0];
            showError(r.data.errors[firstKey][0]);
          } else {
            showError('Sorry — something went wrong. Please call or WhatsApp us instead.');
          }
        })
        .catch(function () {
          showError('Sorry — we could not send that. Please call or WhatsApp us instead.');
        })
        .finally(function () { if (submit) { submit.disabled = false; } });
    });

    // clear the error as the traveller corrects things
    [name, phone].forEach(function (el) {
      el.addEventListener('input', function () { if (err.classList.contains('show')) err.classList.remove('show'); });
    });
    consent.addEventListener('change', function () { if (consent.checked && err.classList.contains('show')) err.classList.remove('show'); });
  });
</script>
@endpush
