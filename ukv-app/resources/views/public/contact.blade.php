@extends('layouts.public')

@section('title', 'Contact us — talk to a real UK-based person | Beyond Passports')
@section('description', 'Questions about your UK outbound visa? Call us and a real, UK-based person answers — Mon–Sat 9–6. WhatsApp for quick questions, email for documents, or request a callback. Independent service — not a government website.')

@push('head')
<style>
  /* ── contact page — page-scoped styles only. Design system in ukv.css ──── */

  /* ── Hero — copy + live status card ─────────────────────────────────────── */
  .ct-hero {
    background: linear-gradient(180deg, #EAF1F4, #F2F5F6 60%, var(--paper));
    border-bottom: 1px solid var(--paper-edge);
  }
  .ct-hero-grid {
    display: grid;
    grid-template-columns: 1.1fr .9fr;
    gap: 48px;
    align-items: center;
  }
  .ct-hero-copy h1 { max-width: 16ch; }
  .ct-hero-copy .lede { max-width: 52ch; }
  .ct-statuscard {
    background:
      radial-gradient(360px 180px at 110% -10%, rgba(21,94,122,.30), transparent 60%),
      radial-gradient(340px 180px at -10% 120%, rgba(46,154,140,.28), transparent 60%),
      var(--navy);
    color: #fff;
    border-radius: 20px;
    padding: 26px 28px;
    box-shadow: var(--lift-2);
  }
  .ct-status-pill {
    display: inline-flex; align-items: center; gap: 8px;
    border-radius: 999px; padding: 6px 13px;
    font: 700 12px var(--display); margin-bottom: 16px;
  }
  .ct-status-pill .sdot { width: 9px; height: 9px; border-radius: 50%; flex: 0 0 9px; }
  .ct-status-pill.is-open { background: rgba(37,211,102,.16); border: 1px solid rgba(37,211,102,.4); color: #7be3a6; }
  .ct-status-pill.is-open .sdot { background: #25D366; box-shadow: 0 0 0 3px rgba(37,211,102,.25); }
  .ct-status-pill.is-closed { background: rgba(169,204,218,.14); border: 1px solid rgba(169,204,218,.36); color: var(--soft); }
  .ct-status-pill.is-closed .sdot { background: var(--soft); }
  .ct-statuscard .sc-lab { font: 700 10px var(--display); letter-spacing: .14em; text-transform: uppercase; color: var(--soft); margin: 0 0 4px; }
  .ct-statuscard .sc-big { font: 800 23px var(--display); margin: 0 0 16px; letter-spacing: -.01em; }
  .ct-statuscard .ct-actions { margin-top: 0; }
  .ct-statuscard .ct-actions .btn { flex: 1; justify-content: center; padding: 13px 16px; font-size: 14.5px; }

  /* ── Hero accent strip ─────────────────────────────────────────────────── */
  .ct-hero-note {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-top: 28px;
    background: rgba(255,255,255,.72);
    backdrop-filter: blur(6px);
    border: 1px solid var(--paper-edge);
    border-radius: 999px;
    padding: 9px 18px;
    font-size: 13px;
    font-weight: 600;
    color: var(--ink);
  }
  .ct-hero-note::before {
    content: "";
    display: inline-block;
    width: 8px; height: 8px;
    border-radius: 50%;
    background: #25D366;
    flex: 0 0 8px;
  }

  /* ── Hero action row ───────────────────────────────────────────────────── */
  .ct-actions {
    display: flex;
    gap: 14px;
    flex-wrap: wrap;
    margin-top: 28px;
  }
  .ct-actions .btn { padding: 16px 28px; font-size: 16px; }

  /* ── Contact method cards — call-first asymmetric ───────────────────────── */
  .ct-methods {
    display: grid;
    grid-template-columns: 1.25fr 1fr;
    gap: 18px;
  }
  .ct-method--primary { grid-column: 1; grid-row: 1 / span 2; justify-content: center; }
  .ct-method--wa { grid-column: 2; grid-row: 1; }
  .ct-method--email { grid-column: 2; grid-row: 2; }
  .ct-method {
    background: var(--white);
    border: 1px solid var(--paper-edge);
    border-radius: 18px;
    box-shadow: var(--lift-1);
    padding: 30px 26px 28px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    transition: transform .25s ease, box-shadow .25s ease;
    position: relative;
    overflow: hidden;
  }
  .ct-method:hover { transform: translateY(-3px); box-shadow: var(--lift-2); }
  /* Subtle accent bar at top */
  .ct-method::before {
    content: "";
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: var(--cta);
    opacity: .18;
    border-radius: 18px 18px 0 0;
  }
  .ct-method--primary::before { opacity: 1; }

  .ct-method .ct-ico {
    width: 48px; height: 48px;
    border-radius: 14px;
    background: linear-gradient(135deg, #eef5f2, #dff0eb);
    color: var(--stamp-text);
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 10px;
    flex: 0 0 48px;
  }
  .ct-method .ct-ico svg { width: 24px; height: 24px; }
  .ct-method--wa .ct-ico { background: linear-gradient(135deg, #e4f7ec, #d4f0e0); color: #1a9950; }
  .ct-method--email .ct-ico { background: linear-gradient(135deg, #f0f3ff, #e4e9ff); color: #3a52cc; }

  .ct-method h3 { font-size: 19px; color: var(--navy); margin: 0; }
  .ct-method .ct-detail {
    font-weight: 700;
    font-size: 17px;
    color: var(--navy);
    word-break: break-word;
    margin: 0;
  }
  .ct-method .ct-detail a { color: var(--cta); }
  .ct-method .ct-sub { font-size: 14px; color: var(--muted); margin: 0; line-height: 1.5; }
  .ct-method .ct-hours {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 600;
    color: var(--stamp-text);
    background: #eef5f1;
    border-radius: 999px;
    padding: 4px 10px;
    margin-top: 6px;
    align-self: flex-start;
    letter-spacing: .02em;
  }

  /* big navy call card (after base rules so it wins) */
  .ct-methods .ct-method--primary {
    background:
      radial-gradient(360px 200px at 100% 0, rgba(21,94,122,.30), transparent 60%),
      radial-gradient(340px 200px at 0 100%, rgba(46,154,140,.26), transparent 60%),
      var(--navy);
    color: #fff;
  }
  .ct-methods .ct-method--primary::before { display: none; }
  .ct-methods .ct-method--primary .ct-ico { background: rgba(255,255,255,.12); color: var(--soft); width: 56px; height: 56px; flex: 0 0 56px; }
  .ct-methods .ct-method--primary .ct-ico svg { width: 28px; height: 28px; }
  .ct-methods .ct-method--primary h3 { color: #fff; font-size: 24px; }
  .ct-methods .ct-method--primary .ct-detail { font-size: 22px; }
  .ct-methods .ct-method--primary .ct-detail a { color: #fff; }
  .ct-methods .ct-method--primary .ct-sub { color: rgba(255,255,255,.8); font-size: 15px; }
  .ct-methods .ct-method--primary .ct-hours { background: rgba(255,255,255,.12); color: var(--soft); }
  .ct-methods .ct-method--primary .ct-call-btn { align-self: flex-start; margin-top: 18px; padding: 13px 26px; }
  /* right-column cards: icon-left rows (matches preview), all text kept */
  .ct-methods .ct-method--wa,
  .ct-methods .ct-method--email { flex-direction: row; align-items: flex-start; gap: 16px; }
  .ct-methods .ct-method--wa .ct-ico,
  .ct-methods .ct-method--email .ct-ico { margin-bottom: 0; }
  .ct-mbody { display: flex; flex-direction: column; gap: 6px; }

  /* ── Callback section — form on soft-sky ground ─────────────────────────── */
  .ct-form-sec { background: linear-gradient(180deg, #EAF1F4, var(--paper)); }
  .ct-callback-wrap { max-width: 560px; margin: 0 auto; }
  .ct-callback .checker { background: var(--white); border: 1px solid var(--paper-edge); box-shadow: var(--lift-2); }
  .ct-callback .checker .cbody { padding: 32px 34px; }
  .ct-callback-intro { text-align: center; max-width: 52ch; margin: 0 auto 24px; }
  .ct-callback-intro .eyebrow { color: var(--cta); }
  .ct-callback-intro h2 { font-size: clamp(26px, 3.2vw, 36px); color: var(--navy); margin-bottom: 10px; }
  .ct-callback-intro p { color: var(--muted); margin: 0; }

  /* Override bare checker so inputs in callback section get premium styling */
  .ct-callback .checker { border-radius: 20px; overflow: hidden; }
  .ct-callback .checker .stub {
    background: linear-gradient(100deg, var(--navy) 0%, #2e3740 100%);
    padding: 16px 22px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .ct-callback .checker .stub span:first-child {
    font-size: 13px;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: #fff;
  }
  .ct-callback .checker .stub span:last-child {
    font-size: 12px;
    color: var(--soft);
    font-weight: 600;
  }
  .ct-callback .checker .cbody { padding: 28px 28px 24px; }
  .ct-callback .checker label {
    display: block;
    font-size: 13px;
    font-weight: 700;
    color: var(--ink);
    margin: 16px 0 5px;
    letter-spacing: .01em;
  }
  .ct-callback .checker label:first-of-type { margin-top: 0; }
  .ct-callback .checker select,
  .ct-callback .checker input[type="text"],
  .ct-callback .checker input[type="tel"] {
    width: 100%;
    padding: 13px 14px;
    border: 1.5px solid var(--paper-edge);
    border-radius: 12px;
    font: inherit;
    font-size: 15px;
    background: var(--paper);
    color: var(--ink);
    transition: border-color .15s ease, box-shadow .15s ease;
  }
  .ct-callback .checker select:focus,
  .ct-callback .checker input:focus,
  .ct-callback .checker textarea:focus {
    outline: none;
    border-color: var(--cta);
    box-shadow: 0 0 0 3px rgba(21,94,122,.14);
  }
  .ct-callback textarea {
    width: 100%;
    padding: 13px 14px;
    border: 1.5px solid var(--paper-edge);
    border-radius: 12px;
    font: inherit;
    font-size: 15px;
    min-height: 100px;
    resize: vertical;
    background: var(--paper);
    color: var(--ink);
    transition: border-color .15s ease, box-shadow .15s ease;
  }
  .ct-callback textarea:focus {
    outline: none;
    border-color: var(--cta);
    box-shadow: 0 0 0 3px rgba(21,94,122,.14);
  }
  .ct-consent {
    display: flex;
    gap: 10px;
    align-items: flex-start;
    margin: 20px 0 0;
  }
  .ct-consent input { width: auto; margin-top: 3px; flex: 0 0 auto; accent-color: var(--cta); }
  .ct-consent label { margin: 0; font-weight: 500; color: var(--ink); font-size: 14px; line-height: 1.45; }
  .ct-privacy {
    font-size: 12px;
    color: var(--muted);
    margin: 10px 0 0;
    line-height: 1.5;
  }
  .ct-privacy a { color: var(--stamp-text); font-weight: 600; }

  .form-error { display: none; background: #fdeceb; border: 1px solid #f3c6c2; color: #8a2a22; border-radius: 10px; padding: 12px 14px; font-size: 14px; margin: 16px 0 0; }
  .form-error.show { display: block; }
  .form-ok   { display: none; background: #E2F1EE; border: 1px solid #b9ddd9; color: #0a5450; border-radius: 10px; padding: 16px 16px; font-size: 15px; margin: 16px 0 0; line-height: 1.5; }
  .form-ok.show   { display: block; }
  .form-ok strong { color: #073f3c; }

  .ct-callback .checker .btn { width: 100%; margin-top: 20px; padding: 15px; font-size: 16px; border-radius: 12px; }

  /* ── Reassurance band — navy mesh ───────────────────────────────────────── */
  .ct-reassure {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    align-items: center;
    justify-content: space-between;
    background:
      radial-gradient(420px 200px at 10% 0, rgba(21,94,122,.40), transparent 60%),
      radial-gradient(420px 200px at 92% 100%, rgba(46,154,140,.36), transparent 60%),
      var(--navy);
    border-radius: 20px;
    padding: 36px 40px;
    box-shadow: var(--lift-2);
  }
  .ct-reassure p {
    margin: 0;
    font-size: clamp(18px, 2.2vw, 22px);
    font-family: var(--display);
    font-weight: 700;
    color: #fff;
    max-width: 34ch;
    line-height: 1.25;
    letter-spacing: -.015em;
  }
  .ct-reassure .btn--ghost { background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.28); color: #fff; }
  .ct-reassure-links { display: flex; gap: 12px; flex-wrap: wrap; }

  @media (max-width: 860px) {
    .ct-hero-grid { grid-template-columns: 1fr; gap: 30px; }
    .ct-hero-copy h1, .ct-hero-copy .lede { max-width: none; }
  }
  @media (max-width: 760px) {
    .ct-methods { grid-template-columns: 1fr; }
    .ct-method--primary, .ct-method--wa, .ct-method--email { grid-column: 1; grid-row: auto; }
    .ct-reassure { padding: 24px 20px; }
    .ct-reassure p { font-size: 18px; }
  }
</style>
@endpush

@section('content')

{{-- 1. HERO — copy + live status card (phone is the primary channel) --}}
<section class="ct-hero"><div class="wrap"><div class="ct-hero-grid">
  <div class="ct-hero-copy reveal">
    <p class="eyebrow">Talk to a human</p>
    <h1>Questions? We're a phone call away.</h1>
    <p class="lede">Call us and a real, UK-based person picks up — no bots, no call centres overseas. We'll talk through your trip, your visa and what we'd do next, with no obligation.</p>
    <span class="ct-hero-note">Mon–Sat 9–6 UK time &nbsp;·&nbsp; independent service, not a government website</span>
  </div>
  <div class="ct-statuscard reveal" aria-label="Our team availability">
    <span class="ct-status-pill is-closed" id="ct-status" role="status">
      <span class="sdot" aria-hidden="true"></span><span id="ct-status-text">Mon–Sat 9–6 UK time</span>
    </span>
    <p class="sc-lab">Beyond Passports · UK team</p>
    <p class="sc-big">Mon–Sat · 9–6 UK time</p>
    <div class="ct-actions">
      <a href="tel:{{ config('ukv.phone_e164') ?: '+442079460000' }}" class="btn">Call now</a>
      <a href="https://wa.me/{{ config('ukv.whatsapp') ?: '442079460000' }}" class="btn btn--wa">WhatsApp</a>
    </div>
  </div>
</div></div></section>

{{-- 2. CONTACT METHODS --}}
<section><div class="wrap">
  <div class="sec-head reveal" style="max-width:60ch;margin:0 auto 44px;text-align:center">
    <p class="eyebrow">Ways to reach us</p>
    <h2>Pick whatever's easiest</h2>
  </div>
  <div class="ct-methods reveal">

    <div class="ct-method ct-method--primary">
      <span class="ct-ico" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.95.36 1.88.7 2.77a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.31-1.27a2 2 0 0 1 2.11-.45c.89.34 1.82.57 2.77.7A2 2 0 0 1 22 16.92Z"/></svg>
      </span>
      <h3>Call us</h3>
      <p class="ct-detail"><a href="tel:{{ config('ukv.phone_e164') ?: '+442079460000' }}">{{ config('ukv.phone') ?: '+44 20 7946 0000' }}</a></p>
      <p class="ct-sub">Our main line — best for anything you'd rather just talk through. A real, UK-based person picks up — no bots, no overseas call centres.</p>
      <span class="ct-hours">Mon–Sat &nbsp;9–6 UK time</span>
      <a href="tel:{{ config('ukv.phone_e164') ?: '+442079460000' }}" class="btn ct-call-btn">Call now</a>
    </div>

    <div class="ct-method ct-method--wa">
      <span class="ct-ico" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5Z"/></svg>
      </span>
      <div class="ct-mbody">
        <h3>WhatsApp</h3>
        <p class="ct-detail"><a href="https://wa.me/{{ config('ukv.whatsapp') ?: '442079460000' }}">{{ config('ukv.phone') ?: '+44 20 7946 0000' }}</a></p>
        <p class="ct-sub">Quick questions, photos of a document, a fast reply on the go.</p>
        <span class="ct-hours">Replies Mon–Sat &nbsp;9–6 UK time</span>
      </div>
    </div>

    <div class="ct-method ct-method--email">
      <span class="ct-ico" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/></svg>
      </span>
      <div class="ct-mbody">
        <h3>Email</h3>
        <p class="ct-detail"><a href="mailto:{{ config('ukv.email') ?: 'hello@beyondpassports.example' }}">{{ config('ukv.email') ?: 'hello@beyondpassports.example' }}</a></p>
        <p class="ct-sub">Best for sending documents or anything you want in writing.</p>
        <span class="ct-hours">Reply within one working day</span>
      </div>
    </div>

  </div>
</div></section>

{{-- 3. CALLBACK FORM (secondary; posts to POST /contact, progressively enhanced via fetch) --}}
<section class="ct-form-sec"><div class="wrap">
  <div class="ct-callback-wrap reveal">
    <div class="ct-callback">
      <div class="checker">
        <div class="cbody">
          <div class="ct-callback-intro">
            <p class="eyebrow">Rather we call you?</p>
            <h2>Request a callback</h2>
          </div>
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

            <label for="cb-message">Your enquiry <span style="font-weight:400;color:var(--muted)">(optional)</span></label>
            <textarea id="cb-message" name="message" placeholder="Where are you travelling, and roughly when?"></textarea>

            <div class="ct-consent">
              <input type="checkbox" id="cb-consent" name="consent" required aria-required="true">
              <label for="cb-consent">I agree to be contacted about my enquiry.</label>
            </div>
            <p class="ct-privacy">We only use your details to respond to this enquiry. We never share them. See our <a href="{{ url('/legal') }}#privacy">Privacy notice</a>.</p>

            {{-- Server-rendered states for the JS-off path; JS toggles the same nodes. --}}
            <div class="form-error{{ $errors->any() ? ' show' : '' }}" id="cb-error" role="alert" aria-live="assertive">@if ($errors->any()){{ $errors->first() }}@endif</div>
            <div class="form-ok{{ session('status') ? ' show' : '' }}" id="cb-ok" role="status" aria-live="polite">@if (session('status'))<strong>{{ session('status') }}</strong>@endif</div>

            <button type="submit" class="btn">Request a callback</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div></section>

{{-- 4. REASSURANCE BAND --}}
<section style="padding:56px 0"><div class="wrap">
  <div class="ct-reassure reveal">
    <p>Prefer to start now? Check your visa or begin an application.</p>
    <div class="ct-reassure-links">
      <a href="{{ url('/tools') }}" class="btn btn--ghost">Check your visa</a>
      <a href="{{ url('/apply') }}" class="btn">Start an application &rarr;</a>
    </div>
  </div>
</div></section>

@endsection

@push('head')
<script>
  // contact hero — live "Open now / Closed" status by real UK time (Mon–Sat 9–6).
  // Honest: shows green "Open now" only inside hours, amber "Closed — leave a message" otherwise.
  (function () {
    var pill = document.getElementById('ct-status');
    var text = document.getElementById('ct-status-text');
    if (!pill || !text) return;
    try {
      var parts = new Intl.DateTimeFormat('en-GB', {
        timeZone: 'Europe/London', weekday: 'short', hour: 'numeric', hour12: false
      }).formatToParts(new Date());
      var wd = '', hr = 0;
      parts.forEach(function (p) {
        if (p.type === 'weekday') wd = p.value;
        if (p.type === 'hour') hr = parseInt(p.value, 10);
      });
      var isSunday = wd === 'Sun';
      var open = !isSunday && hr >= 9 && hr < 18;
      pill.classList.remove('is-open', 'is-closed');
      pill.classList.add(open ? 'is-open' : 'is-closed');
      text.textContent = open ? 'Open now — Mon–Sat 9–6 UK' : 'Closed now — leave a message';
    } catch (e) { /* leave the server-rendered default */ }
  })();

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
