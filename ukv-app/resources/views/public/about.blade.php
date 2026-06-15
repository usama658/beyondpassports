@extends('layouts.public')

@section('title', 'About Us — Independent UK Visa & eVisa Service | UKVisaCo')
@section('description', 'UKVisaCo is an independent, UK-based visa, eVisa, ETA and IDP facilitation service for British travellers. Not a government website. Clear fees, human checks, honest advice.')

@push('head')
<style>
  /* about — page-local layout only. Design system lives in assets/ukv.css */
  .hero{padding:64px 0 40px}
  .hero h1{font-size:clamp(34px,5vw,56px);color:var(--navy);letter-spacing:-.015em;max-width:18ch}
  .hero p.lede{font-size:19px;max-width:46ch;color:#33454f;margin-top:14px}
  .prose{max-width:64ch}
  .prose p{font-size:17px;line-height:1.65;color:#33454f;margin:0 0 18px}
  .prose p:last-child{margin-bottom:0}
  .prose .note{font-family:var(--mono);font-size:14px;color:var(--stamp);background:var(--white);border:1px solid var(--paper-edge);border-left:3px solid var(--gold);padding:14px 16px;line-height:1.5}
  .callout{max-width:70ch}
  .callout p{font-size:16px;line-height:1.6;color:#33454f;margin:0}
  .callout p+p{margin-top:14px}
</style>
@endpush

@section('content')

{{-- HERO --}}
<section class="hero"><div class="wrap">
  <p class="eyebrow">About us</p>
  <h1>An independent UK team that makes visas simple.</h1>
  <p class="lede">We're a private, UK-based service that checks, prepares and submits travel-document applications for British travellers heading abroad — so you don't have to second-guess the rules.</p>
</div></section>
<div class="mrz"><div class="wrap"><span>P&lt;GBR&lt;UKVISACO&lt;&lt;INDEPENDENT&lt;UK&lt;TEAM&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;</span></div></div>

{{-- WHO WE ARE --}}
<section id="who"><div class="wrap">
  <div class="sec-head reveal"><p class="eyebrow">Who we are</p><h2>A private service, not a public office</h2></div>
  <div class="prose reveal">
    <p>UKVisaCo is an independent, UK-based visa &amp; eVisa facilitation service for British travellers going abroad. We help with eVisas, electronic travel authorisations (ETAs) and International Driving Permits (IDPs) — checking your details, preparing your paperwork and guiding you through submission so small mistakes don't turn into cancelled trips.</p>
    <p>We are <strong>not</strong> a government body, and we are <strong>not</strong> affiliated with gov.uk, any embassy, consulate or official authority. We're a private company you can <em>choose</em> to use to save time and avoid errors. You can always apply directly with the relevant authority yourself — using us is optional.</p>
    <p class="note">Our service fee is separate from, and additional to, any government or embassy fee. The official fee is set and collected by the relevant authority; our fee pays for the checking, preparation and support we provide. We always show both clearly before you pay.</p>
  </div>
</div></section>

{{-- WHAT WE STAND FOR --}}
<section id="values" class="alt"><div class="wrap">
  <div class="sec-head reveal"><p class="eyebrow">What we stand for</p><h2>Four things we never compromise on</h2></div>
  <div class="ticks">
    <div class="tick reveal"><svg class="stamp" width="38" height="38" viewBox="0 0 48 48" role="img" aria-label="Honesty"><use href="#ukv-stamp"></use></svg><div><h3>Honesty</h3><p>If you don't actually need us, we'll tell you. We never promise approval — that decision isn't ours to make.</p></div></div>
    <div class="tick reveal"><svg class="stamp" width="38" height="38" viewBox="0 0 48 48" role="img" aria-label="Accuracy"><use href="#ukv-stamp"></use></svg><div><h3>Accuracy</h3><p>A real person checks every document before anything is submitted, so errors are caught before they cost you.</p></div></div>
    <div class="tick reveal"><svg class="stamp" width="38" height="38" viewBox="0 0 48 48" role="img" aria-label="Transparency"><use href="#ukv-stamp"></use></svg><div><h3>Transparency</h3><p>Clear fees up front — our service fee shown separately from any government or embassy fee. No surprises.</p></div></div>
    <div class="tick reveal"><svg class="stamp" width="38" height="38" viewBox="0 0 48 48" role="img" aria-label="Care"><use href="#ukv-stamp"></use></svg><div><h3>Care</h3><p>Real people on the phone and on WhatsApp — a UK team you can actually talk to when something matters.</p></div></div>
  </div>
</div></section>

{{-- HOW WE HELP --}}
<section id="how"><div class="wrap">
  <div class="sec-head reveal"><p class="eyebrow">How we help</p><h2>Three simple steps</h2></div>
  <div class="steps">
    <div class="step reveal"><div class="num">01</div><div class="rule"></div><h3>We check</h3><p>Tell us your trip and passport. We confirm exactly what you need — and whether you need us at all.</p></div>
    <div class="step reveal"><div class="num">02</div><div class="rule"></div><h3>We prepare</h3><p>Our team reviews and prepares your documents, catching errors before anything goes near a government portal.</p></div>
    <div class="step reveal"><div class="num">03</div><div class="rule"></div><h3>We submit &amp; track</h3><p>We handle the submission and keep you updated at every step until your authorisation comes through.</p></div>
  </div>
</div></section>

{{-- TESTIMONIAL --}}
<section class="alt"><div class="wrap quote reveal">
  <p class="eyebrow">In our travellers' words</p>
  <blockquote>“I half-expected a faceless form. Instead a real person rang me back, spotted a date I'd entered wrong, and walked me through it. Felt like having a friend who actually knows the rules.”</blockquote>
  <div class="by"><span class="avatar"><svg viewBox="0 0 48 48" role="img" aria-label="UKVisaCo traveller"><use href="#ukv-stamp"></use></svg></span>— A UK traveller to India · UKV&lt;2026&lt;006140&lt;&lt;&lt;</div>
</div></section>

{{-- COMPLIANCE / TRANSPARENCY CALLOUT --}}
<section id="transparency"><div class="wrap">
  <div class="sec-head reveal"><p class="eyebrow">Transparency</p><h2>The important bit, in plain English</h2></div>
  <div class="callout reveal">
    <p>UKVisaCo is an independent commercial service. We are not a government website. Government and embassy fees are payable separately and set by the relevant authorities. Visa decisions are made solely by those authorities — we cannot guarantee any outcome.</p>
    <p>If you choose an express option, that speeds <strong>our</strong> handling of your application only — it does not make a government or embassy decide any faster, and an ETA does not produce a physical document.</p>
  </div>
</div></section>

{{-- CTA --}}
<section class="cta-band"><div class="wrap reveal">
  <div class="rule"></div>
  <h2>Let's get you travelling</h2>
  <p style="max-width:48ch;color:#cdd9e1">Start your application now, or message our UK team with any question — even just to check whether you need us.</p>
  <div class="row"><a href="{{ url('/apply') }}" class="btn">Start my application →</a><a href="https://wa.me/440000000000" class="btn btn--wa">Chat on WhatsApp</a></div>
</div></section>

@endsection
