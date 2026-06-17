@extends('layouts.public')

@section('title', 'About Us — Independent UK Visa & eVisa Service | Beyond Passports')
@section('description', 'Beyond Passports is an independent, UK-based visa, eVisa, ETA and IDP facilitation service for British travellers. Not a government website. Clear fees, human checks, honest advice.')

@push('head')
<style>
  /* ── about page — page-scoped styles only. Design system in ukv.css ─────── */

  /* ── Hero — statement left + "who we are" credentials card right ─────────── */
  .ab-hero {
    background: linear-gradient(180deg, #EAF1F4, #F2F5F6 60%, var(--paper));
    border-bottom: 1px solid var(--paper-edge);
  }
  .ab-hero-grid {
    display: grid;
    grid-template-columns: 1.1fr .9fr;
    gap: 48px;
    align-items: center;
  }
  .ab-hero-copy h1 { max-width: 18ch; }
  .ab-hero-copy .lede { max-width: 50ch; }
  .ab-idcard {
    background:
      radial-gradient(380px 180px at 110% -10%, rgba(199,93,56,.30), transparent 60%),
      radial-gradient(360px 180px at -10% 120%, rgba(92,154,123,.28), transparent 60%),
      var(--navy);
    color: #fff;
    border-radius: 18px;
    padding: 26px 28px;
    box-shadow: var(--lift-2);
  }
  .ab-idcard .ic-k {
    font-size: 10.5px;
    font-weight: 800;
    letter-spacing: .14em;
    text-transform: uppercase;
    color: var(--soft);
    margin: 0 0 6px;
  }
  .ab-idcard .ic-row {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    gap: 16px;
    padding: 13px 0;
    border-top: 1px solid rgba(255,255,255,.14);
    font-size: 14px;
  }
  .ab-idcard .ic-row:first-of-type { border-top: 0; }
  .ab-idcard .ic-row span { color: rgba(255,255,255,.72); }
  .ab-idcard .ic-row b { font-weight: 700; color: #fff; }

  /* ── Who we are — prose ──────────────────────────────────────────────────── */
  .ab-prose { max-width: 66ch; }
  .ab-prose p {
    font-size: 17px;
    line-height: 1.7;
    color: #33454f;
    margin: 0 0 20px;
  }
  .ab-prose p:last-child { margin-bottom: 0; }
  .ab-note {
    background: var(--white);
    border: 1px solid var(--paper-edge);
    border-left: 4px solid var(--cta);
    border-radius: 0 12px 12px 0;
    padding: 16px 20px;
    margin: 28px 0 0;
    font-size: 15px;
    line-height: 1.6;
    color: var(--stamp-text);
  }

  /* ── Values — elevated tick cards ───────────────────────────────────────── */
  .ab-values {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
  }
  .ab-value {
    background: var(--white);
    border: 1px solid var(--paper-edge);
    border-radius: 18px;
    box-shadow: var(--lift-1);
    padding: 28px 26px;
    display: flex;
    gap: 18px;
    align-items: flex-start;
    transition: transform .25s ease, box-shadow .25s ease;
  }
  .ab-value:hover { transform: translateY(-3px); box-shadow: var(--lift-2); }
  .ab-value-icon {
    flex: 0 0 48px;
    width: 48px; height: 48px;
    border-radius: 14px;
    background: linear-gradient(135deg, #eef5f2, #dff0eb);
    display: flex; align-items: center; justify-content: center;
    font-size: 22px;
  }
  .ab-value h3 { font-size: 18px; color: var(--navy); margin: 0 0 6px; }
  .ab-value p  { margin: 0; font-size: 15px; color: var(--muted); line-height: 1.55; }

  /* ── How we help — steps use shared .steps class, override inner padding ─── */
  /* (no overrides needed — .steps / .step from ukv.css handles this) */

  /* ── Testimonial quote ───────────────────────────────────────────────────── */
  .ab-quote-section { position: relative; overflow: hidden; }
  .ab-quote-section::before {
    content: "\201C";
    position: absolute;
    top: -20px; left: 50%;
    transform: translateX(-50%);
    font-family: var(--display);
    font-size: 180px;
    font-weight: 800;
    color: var(--cta);
    opacity: .06;
    line-height: 1;
    pointer-events: none;
    z-index: 0;
  }
  .ab-quote-section > .wrap { position: relative; z-index: 1; }

  /* ── Transparency callout ────────────────────────────────────────────────── */
  .ab-callout {
    max-width: 72ch;
    background: var(--white);
    border: 1px solid var(--paper-edge);
    border-radius: 18px;
    box-shadow: var(--lift-1);
    padding: 32px 36px;
  }
  .ab-callout p { font-size: 16px; line-height: 1.65; color: #33454f; margin: 0; }
  .ab-callout p + p { margin-top: 16px; }

  /* ── Stat chips row (hero accent) ───────────────────────────────────────── */
  .ab-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 28px;
  }
  .ab-chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255,255,255,.72);
    backdrop-filter: blur(6px);
    border: 1px solid var(--paper-edge);
    border-radius: 999px;
    padding: 9px 16px;
    font-size: 13.5px;
    font-weight: 600;
    color: var(--ink);
  }
  .ab-chip b { color: var(--cta); }

  @media (max-width: 860px) {
    .ab-hero-grid { grid-template-columns: 1fr; gap: 30px; }
    .ab-hero-copy h1, .ab-hero-copy .lede { max-width: none; }
  }
  @media (max-width: 760px) {
    .ab-values { grid-template-columns: 1fr; }
    .ab-callout { padding: 22px 20px; }
  }
</style>
@endpush

@section('content')

{{-- HERO --}}
<section class="ab-hero"><div class="wrap"><div class="ab-hero-grid">
  <div class="ab-hero-copy reveal">
    <p class="eyebrow">About us</p>
    <h1>An independent UK team that makes visas simple.</h1>
    <p class="lede">We're a private, UK-based service that checks, prepares and submits travel-document applications for British travellers heading abroad — so you don't have to second-guess the rules.</p>
  </div>
  <div class="ab-idcard reveal" aria-label="Who we are at a glance">
    <p class="ic-k">Beyond Passports · who we are</p>
    <div class="ic-row"><span>Based</span><b>United Kingdom</b></div>
    <div class="ic-row"><span>Team hours</span><b>Mon–Sat 9–6</b></div>
    <div class="ic-row"><span>Status</span><b>Independent service</b></div>
    <div class="ic-row"><span>Not</span><b>a government website</b></div>
  </div>
</div></div></section>

{{-- WHO WE ARE --}}
<section id="who"><div class="wrap">
  <div class="sec-head reveal"><p class="eyebrow">Who we are</p><h2>A private service, not a public office</h2></div>
  <div class="ab-prose reveal">
    <p>Beyond Passports is an independent, UK-based visa &amp; eVisa facilitation service for British travellers going abroad. We help with eVisas, electronic travel authorisations (ETAs) and International Driving Permits (IDPs) — checking your details, preparing your paperwork and guiding you through submission so small mistakes don't turn into cancelled trips.</p>
    <p>We are <strong>not</strong> a government body, and we are <strong>not</strong> affiliated with gov.uk, any embassy, consulate or official authority. We're a private company you can <em>choose</em> to use to save time and avoid errors. You can always apply directly with the relevant authority yourself — using us is optional.</p>
    <p class="ab-note">Our service fee is separate from, and additional to, any government or embassy fee. The official fee is set and collected by the relevant authority; our fee pays for the checking, preparation and support we provide. We always show both clearly before you pay.</p>
  </div>
</div></section>

{{-- WHAT WE STAND FOR --}}
<section id="values" class="alt"><div class="wrap">
  <div class="sec-head reveal"><p class="eyebrow">What we stand for</p><h2>Four things we never compromise on</h2></div>
  <div class="ab-values">

    <div class="ab-value reveal">
      <span class="ab-value-icon" aria-hidden="true">🎯</span>
      <div>
        <h3>Honesty</h3>
        <p>If you don't actually need us, we'll tell you. We never promise approval — that decision isn't ours to make.</p>
      </div>
    </div>

    <div class="ab-value reveal">
      <span class="ab-value-icon" aria-hidden="true">✓</span>
      <div>
        <h3>Accuracy</h3>
        <p>A real person checks every document before anything is submitted, so errors are caught before they cost you.</p>
      </div>
    </div>

    <div class="ab-value reveal">
      <span class="ab-value-icon" aria-hidden="true">⬡</span>
      <div>
        <h3>Transparency</h3>
        <p>Clear fees up front — our service fee shown separately from any government or embassy fee. No surprises.</p>
      </div>
    </div>

    <div class="ab-value reveal">
      <span class="ab-value-icon" aria-hidden="true">◎</span>
      <div>
        <h3>Care</h3>
        <p>Real people on the phone and on WhatsApp — a UK team you can actually talk to when something matters.</p>
      </div>
    </div>

  </div>
</div></section>

{{-- HOW WE HELP --}}
<section id="how"><div class="wrap">
  <div class="sec-head reveal"><p class="eyebrow">How we help</p><h2>Three simple steps</h2></div>
  <div class="steps reveal">
    <div class="step"><div class="num">01</div><div class="rule"></div><h3>We check</h3><p>Tell us your trip and passport. We confirm exactly what you need — and whether you need us at all.</p></div>
    <div class="step"><div class="num">02</div><div class="rule"></div><h3>We prepare</h3><p>Our team reviews and prepares your documents, catching errors before anything goes near a government portal.</p></div>
    <div class="step"><div class="num">03</div><div class="rule"></div><h3>We submit &amp; track</h3><p>We handle the submission and keep you updated at every step until your authorisation comes through.</p></div>
  </div>
</div></section>

{{-- TESTIMONIAL --}}
<section class="alt ab-quote-section"><div class="wrap quote reveal">
  <p class="eyebrow">In our travellers' words</p>
  <blockquote>"I half-expected a faceless form. Instead a real person rang me back, spotted a date I'd entered wrong, and walked me through it. Felt like having a friend who actually knows the rules."</blockquote>
  <div class="by"><span class="avatar"><svg viewBox="0 0 48 48" role="img" aria-label="Beyond Passports traveller"><use href="#ukv-stamp"></use></svg></span>— A UK traveller to India</div>
</div></section>

{{-- COMPLIANCE / TRANSPARENCY CALLOUT --}}
<section id="transparency"><div class="wrap">
  <div class="sec-head reveal"><p class="eyebrow">Transparency</p><h2>The important bit, in plain English</h2></div>
  <div class="ab-callout reveal">
    <p>Beyond Passports is an independent commercial service. We are not a government website. Government and embassy fees are payable separately and set by the relevant authorities. Visa decisions are made solely by those authorities — we cannot guarantee any outcome.</p>
    <p>If you choose an express option, that speeds <strong>our</strong> handling of your application only — it does not make a government or embassy decide any faster, and an ETA does not produce a physical document.</p>
  </div>
</div></section>

{{-- CTA --}}
<section class="cta-band"><div class="wrap reveal">
  <div class="rule"></div>
  <h2>Let's get you travelling</h2>
  <p style="max-width:48ch;color:#eef0f1">Start your application now, or message our UK team with any question — even just to check whether you need us.</p>
  <div class="row"><a href="{{ url('/apply') }}" class="btn">Start my application &rarr;</a><a href="https://wa.me/{{ config('ukv.whatsapp') ?: '440000000000' }}" class="btn btn--glass">Chat on WhatsApp</a></div>
</div></section>

@endsection
