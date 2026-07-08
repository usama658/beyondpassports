@extends('layouts.public')

@section('title', 'About Us: Independent Schengen Visa Service, UK & Europe | Beyond Passports')
@section('description', 'Beyond Passports is an independent Schengen visa consultancy registered in the UK and Europe. Not a government website. Clear fees, real human checks, honest advice.')

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
      radial-gradient(380px 180px at 110% -10%, rgba(21,94,122,.30), transparent 60%),
      radial-gradient(360px 180px at -10% 120%, rgba(46,154,140,.28), transparent 60%),
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

  /* ── Trust bands (mirrors home) — dark mesh points (F) + warm stat counters (B) ── */
  .tbar-b, .tbar-f { padding: 0; }
  .tbar-f {
    background:
      radial-gradient(520px 200px at 12% 0%, rgba(21,94,122,.45), transparent 60%),
      radial-gradient(520px 200px at 92% 100%, rgba(46,154,140,.42), transparent 60%),
      var(--navy);
    color: #fff;
  }
  .tbar-f .row { display: flex; justify-content: center; gap: 30px; flex-wrap: wrap; padding: 16px 0; }
  .tbar-f .ti { display: flex; align-items: center; gap: 9px; font: 600 14px var(--display); color: #fff; white-space: nowrap; }
  .tbar-f .ti svg { width: 20px; height: 20px; color: var(--soft); flex: none; }
  .tbar-f .ti b { color: var(--soft); font-weight: 800; }
  .tbar-b { background: linear-gradient(180deg, #FBF6F1, var(--paper)); border-bottom: 1px solid var(--paper-edge); }
  .tbar-b .row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px; text-align: center; padding: 24px 0; }
  .tbar-b .n { font: 800 clamp(24px, 3vw, 30px)/1 var(--display); color: var(--cta); letter-spacing: -.02em; }
  .tbar-b .l { font: 600 13px var(--display); color: var(--muted); margin-top: 6px; }
  .tbar-b .row > div + div { border-left: 1px solid var(--paper-edge); }
  @media (max-width: 760px) {
    .tbar-b .row { grid-template-columns: 1fr 1fr; gap: 14px; }
    .tbar-b .row > div:nth-child(odd) { border-left: 0; }
    .tbar-f .row { gap: 18px 22px; }
  }

  /* ── Who we are — prose + "we are / we are not" contrast cards ───────────── */
  .ab-who-grid {
    display: grid;
    grid-template-columns: 1.2fr .8fr;
    gap: 44px;
    align-items: start;
  }
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
  .ab-contrast { display: grid; gap: 14px; }
  .ab-cc {
    background: var(--white);
    border: 1px solid var(--paper-edge);
    border-radius: 16px;
    box-shadow: var(--lift-1);
    padding: 20px 22px;
  }
  .ab-cc .cc-t {
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .12em;
    text-transform: uppercase;
    margin: 0 0 12px;
  }
  .ab-cc.is-are .cc-t { color: var(--sage-deep, #1F6E63); }
  .ab-cc.is-not .cc-t { color: var(--cta); }
  .ab-cc ul { margin: 0; padding: 0; list-style: none; }
  .ab-cc li {
    display: flex;
    gap: 10px;
    align-items: flex-start;
    font-size: 14.5px;
    line-height: 1.5;
    color: #33454f;
    padding: 6px 0;
  }
  .ab-cc li svg { flex: 0 0 18px; width: 18px; height: 18px; margin-top: 2px; }

  /* ── Values — 4-up centred, icon-top cards ──────────────────────────────── */
  .ab-values {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
  }
  .ab-value {
    background: var(--white);
    border: 1px solid var(--paper-edge);
    border-radius: 18px;
    box-shadow: var(--lift-1);
    padding: 28px 22px;
    text-align: center;
    transition: transform .25s ease, box-shadow .25s ease;
  }
  .ab-value:hover { transform: translateY(-3px); box-shadow: var(--lift-2); }
  .ab-value-icon {
    width: 48px; height: 48px;
    margin: 0 auto 16px;
    border-radius: 14px;
    background: linear-gradient(135deg, #eef5f2, #dff0eb);
    display: flex; align-items: center; justify-content: center;
    color: #1F6E63;
  }
  .ab-value-icon svg { width: 24px; height: 24px; }
  .ab-value h3 { font-size: 18px; color: var(--navy); margin: 0 0 6px; }
  .ab-value p  { margin: 0; font-size: 14.5px; color: var(--muted); line-height: 1.55; }

  /* ── How we help — steps use shared .steps class, override inner padding ─── */
  /* (no overrides needed — .steps / .step from ukv.css handles this) */

  /* ── Testimonials — trio of consented quote cards (mirrors home) ─────────── */
  .tquotes { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 30px; }
  .tq {
    background: #fff; border: 1px solid var(--paper-edge); border-radius: 16px;
    padding: 24px 22px; box-shadow: var(--shadow); margin: 0;
    display: flex; flex-direction: column; gap: 12px;
    transition: transform .25s ease, box-shadow .25s ease;
  }
  .tq:hover { transform: translateY(-3px); box-shadow: var(--lift-2); }
  .tq .stars { color: var(--cta); letter-spacing: 3px; font-size: 14px; }
  .tq blockquote { margin: 0; font-family: var(--display); font-weight: 600; font-size: 15.5px; line-height: 1.55; color: var(--ink); }
  .tq figcaption { color: var(--stamp-text); font-weight: 700; font-size: 13px; margin-top: auto; }
  @media (max-width: 760px) { .tquotes { grid-template-columns: 1fr; } }

  /* ── Transparency callout — navy security-paper card + peach seal ────────── */
  .ab-callout {
    position: relative;
    overflow: hidden;
    max-width: 760px;
    border-radius: 20px;
    padding: 36px 40px;
    color: #e9ebee;
    background:
      radial-gradient(520px 240px at 10% -10%, rgba(21,94,122,.28), transparent 60%),
      radial-gradient(520px 240px at 95% 110%, rgba(46,154,140,.24), transparent 60%),
      repeating-linear-gradient(60deg, rgba(255,255,255,.02) 0 2px, transparent 2px 9px),
      var(--navy);
    box-shadow: var(--lift-2);
  }
  .ab-callout p { font-size: 16px; line-height: 1.65; color: rgba(255,255,255,.82); margin: 0; max-width: 60ch; }
  .ab-callout p + p { margin-top: 16px; }
  .ab-callout p strong { color: #fff; }
  .ab-callout .ab-seal {
    position: absolute; top: 24px; right: 26px;
    width: 64px; height: 64px; color: var(--soft); opacity: .9;
  }
  .ab-callout .ab-seal svg { width: 100%; height: 100%; display: block; }
  @media (max-width: 560px) { .ab-callout .ab-seal { position: static; margin: 0 0 14px; width: 52px; height: 52px; } }

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

  /* Transparency: full-width card + unrestricted heading */
  #transparency .sec-head, #transparency .sec-head h2 { max-width: none; }
  .ab-callout { max-width: none; width: 100%; }
  .ab-callout p { max-width: none; }
  @media (min-width: 561px) { .ab-callout { padding-right: 116px; } }

  @media (max-width: 860px) {
    .ab-hero-grid { grid-template-columns: 1fr; gap: 30px; }
    .ab-hero-copy h1, .ab-hero-copy .lede { max-width: none; }
    .ab-who-grid { grid-template-columns: 1fr; gap: 28px; }
  }
  @media (max-width: 900px) {
    .ab-values { grid-template-columns: 1fr 1fr; }
  }
  @media (max-width: 760px) {
    .ab-callout { padding: 22px 20px; }
  }
  @media (max-width: 520px) {
    .ab-values { grid-template-columns: 1fr; }
  }
</style>
@endpush

@section('content')

{{-- HERO --}}
<section class="ab-hero"><div class="wrap"><div class="ab-hero-grid">
  <div class="ab-hero-copy reveal">
    <p class="eyebrow">About us</p>
    <h1>An independent Schengen visa service, registered in the UK and Europe.</h1>
    <p class="lede">Beyond Passports is a private visa consultancy. We prepare, check and submit Schengen applications, catching the avoidable mistakes that get people refused.</p>
    @include('partials.trustpilot-cta', ['align' => 'left', 'margin' => '18px 0 0'])
  </div>
  <div class="ab-idcard reveal" aria-label="Beyond Passports on LinkedIn" style="display:flex;flex-direction:column;align-items:center;justify-content:center;gap:12px;text-align:center">
    <p class="ic-k" style="margin:0">Beyond Passports · who we are</p>
    {{-- LinkedIn profile badge. profile.js upgrades this to the rich card, and it sets
         LinkedIn cookies — so the script is consent-gated in partials.cookie-consent
         (loads only after the visitor accepts). Until then the fallback link shows:
         no third-party request, PECR-safe. --}}
    <div class="badge-base LI-profile-badge" data-locale="en_US" data-size="medium" data-theme="light" data-type="HORIZONTAL" data-vanity="beyond-passports-46389641b" data-version="v1"><a class="badge-base__link LI-simple-link" href="https://uk.linkedin.com/in/beyond-passports-46389641b?trk=profile-badge" target="_blank" rel="noopener">Beyond Passports on LinkedIn</a></div>
  </div>
</div></div></section>

{{-- TRUST BANDS — dark mesh trust-points (F) then warm stat counters (B); mirrors home --}}
<section class="tbar-f"><div class="wrap"><div class="row">
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 5 6v6c0 4.5 3 7.5 7 8.5 4-1 7-4 7-8.5V6z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="m9 12 2 2 4-4.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg><span><b>Schengen visa</b> experts</span></span>
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 7v10M9.5 9.2c0-1 1.1-1.7 2.5-1.7s2.5.7 2.5 1.7-1.1 1.6-2.5 1.6-2.5.7-2.5 1.7 1.1 1.7 2.5 1.7 2.5-.7 2.5-1.7" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg><span><b>No hidden</b> fees</span></span>
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 7v5l3 2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg><span><b>7-day</b> support</span></span>
  <span class="ti">@include('partials.uk-eu-flags',['size'=>15])<span>Registered in <b>UK &amp; Europe</b></span></span>
</div></div></section>
<section class="tbar-b"><div class="wrap"><div class="row" id="ab-counts">
  <div><div class="n" data-count="29">29</div><div class="l">Schengen countries covered</div></div>
  <div><div class="n">@include('partials.uk-eu-flags',['size'=>26])</div><div class="l">Registered, UK &amp; Europe</div></div>
  <div><div class="n">100%</div><div class="l">Files human-checked before submission</div></div>
  <div><div class="n">Mon&ndash;Sat</div><div class="l">Support, 9&ndash;6</div></div>
</div></div></section>

{{-- WHO WE ARE --}}
@php
  $ccTick = '<svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M4 10.5l4 4 8-9" stroke="#1F6E63" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
  $ccCross = '<svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M5 5l10 10M15 5L5 15" stroke="#155E7A" stroke-width="2.2" stroke-linecap="round"/></svg>';
@endphp
<section id="who"><div class="wrap">
  <div class="sec-head reveal"><p class="eyebrow">Who we are</p><h2>A private service, not a public office</h2></div>
  <div class="ab-who-grid">
    <div class="ab-prose reveal">
      <p>Founded in {{ App\Support\SiteStats::foundedYear() }}, Beyond Passports is an independent Schengen visa consultancy registered in the UK and Europe. We check your details, prepare your paperwork and guide you through submission, so small mistakes don't turn into a refused application or a cancelled trip.</p>
      <p>We are <strong>not</strong> a government body, and we are <strong>not</strong> affiliated with any embassy, consulate or official authority. We're a private company you can <em>choose</em> to use to save time and avoid errors. You can always apply directly yourself; using us is optional.</p>
      <p><strong>A real person checks every application before it's submitted. Nothing is outsourced.</strong> Registered in the UK and Europe, we confirm you qualify, review your documents for the things that actually get applications refused, and run a final check before anything reaches the consulate.</p>
      <p class="ab-note">Our service fee is separate from, and additional to, the consulate or embassy fee. The official fee is set and collected by the authority; our fee pays for the checking, preparation and support we provide. We always show both clearly before you pay.</p>
    </div>
    <div class="ab-contrast reveal">
      <div class="ab-cc is-are">
        <p class="cc-t">We are</p>
        <ul>
          <li>{!! $ccTick !!}Independent, registered in UK &amp; Europe</li>
          <li>{!! $ccTick !!}Real human document checks</li>
          <li>{!! $ccTick !!}Optional, your choice to use</li>
        </ul>
      </div>
      <div class="ab-cc is-not">
        <p class="cc-t">We are not</p>
        <ul>
          <li>{!! $ccCross !!}A government body or gov.uk</li>
          <li>{!! $ccCross !!}An embassy or consulate</li>
          <li>{!! $ccCross !!}Able to guarantee a decision</li>
        </ul>
      </div>
    </div>
  </div>
</div></section>

{{-- WHAT WE STAND FOR --}}
<section id="values" class="alt"><div class="wrap">
  <div class="sec-head reveal"><p class="eyebrow">What we stand for</p><h2>Four things we never compromise on</h2></div>
  <div class="ab-values">

    <div class="ab-value reveal">
      <span class="ab-value-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none"><path d="M12 3v18M5 7l7-3 7 3M5 7l-2 6a4 4 0 0 0 8 0L9 7m6 0l7-3M19 7l2 6a4 4 0 0 1-8 0l2-6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </span>
      <h3>Honesty</h3>
      <p>If you don't actually need us, we'll tell you. We never promise approval. That decision isn't ours to make.</p>
    </div>

    <div class="ab-value reveal">
      <span class="ab-value-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none"><path d="M12 3l7 3v5c0 4.5-3 7.6-7 9-4-1.4-7-4.5-7-9V6l7-3z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M8.5 12l2.4 2.4L15.7 9.6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </span>
      <h3>Accuracy</h3>
      <p>A real person checks every document before anything is submitted, so errors are caught before they cost you.</p>
    </div>

    <div class="ab-value reveal">
      <span class="ab-value-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.7"/></svg>
      </span>
      <h3>Transparency</h3>
      <p>Clear fees up front: our service fee shown separately from any government or embassy fee. No surprises.</p>
    </div>

    <div class="ab-value reveal">
      <span class="ab-value-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none"><path d="M12 20s-7-4.3-7-9.3A4 4 0 0 1 12 8a4 4 0 0 1 7-2.7c1 1 1 3.3 0 5.4-1.4 3-7 9.3-7 9.3z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>
      </span>
      <h3>Care</h3>
      <p>Real people on the phone and on WhatsApp. People you can actually talk to when something matters, registered in the UK and Europe.</p>
    </div>

  </div>
</div></section>

{{-- HOW WE HELP --}}
<section id="how"><div class="wrap">
  <div class="sec-head reveal"><p class="eyebrow">How we prevent refusals</p><h2>Three checks before you submit</h2></div>
  <div class="steps reveal">
    <div class="step"><div class="num">01</div><div class="rule"></div><h3>We check eligibility</h3><p>Tell us your trip and passport. We confirm you actually qualify, and whether you need us at all, before you pay.</p></div>
    <div class="step"><div class="num">02</div><div class="rule"></div><h3>We prepare &amp; check documents</h3><p>Our UK team reviews your documents for history, source and consistency: the things that actually get applications refused.</p></div>
    <div class="step"><div class="num">03</div><div class="rule"></div><h3>We submit &amp; track</h3><p>Nothing is submitted until a real UK person has checked the whole file. Then we handle submission and keep you updated to completion.</p></div>
  </div>
</div></section>

{{-- TEAM + LOCATION (config-driven; design abt-d) --}}
@include('partials.about-team')

{{-- TESTIMONIALS — trio of consented quote cards (real anonymised reviews, single source; mirrors home) --}}
@php $aboutQuotes = array_slice(\App\Http\Controllers\ReviewController::all(), 0, 3); @endphp
<section class="alt"><div class="wrap">
  <div class="sec-head reveal" style="text-align:center;max-width:60ch;margin:0 auto 6px">
    <p class="eyebrow">Trusted by UK travellers</p>
    <h2>Real people, really sorted</h2>
    <div style="display:flex;justify-content:center;margin-top:10px">@include('partials.trustpilot-cta', ['align' => 'center', 'margin' => '0'])</div>
  </div>
  <div class="tquotes">
    @foreach ($aboutQuotes as $t)
    <figure class="tq reveal">
      <div class="stars" aria-label="{{ $t['rating'] ?? 5 }} out of 5 stars">{!! str_repeat('★', $t['rating'] ?? 5) !!}</div>
      <blockquote>{{ $t['quote'] }}</blockquote>
      <figcaption>{{ $t['attribution'] }}</figcaption>
    </figure>
    @endforeach
  </div>
  <p style="text-align:center;margin-top:24px"><a class="rlink" style="font-weight:600" href="{{ url('/reviews') }}">Read more traveller reviews →</a></p>
</div></section>

{{-- COMPLIANCE / TRANSPARENCY CALLOUT --}}
<section id="transparency"><div class="wrap">
  <div class="sec-head reveal"><p class="eyebrow">Transparency</p><h2>The important bit, in plain English</h2></div>
  <div class="ab-callout reveal">
    <span class="ab-seal" aria-hidden="true">
      <svg viewBox="0 0 24 24" fill="none"><path d="M12 3l7 3v5c0 4.5-3 7.6-7 9-4-1.4-7-4.5-7-9V6l7-3z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="M8.5 12l2.4 2.4L15.7 9.6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </span>
    <p>Beyond Passports is an independent commercial service. We are not a government website. Government and embassy fees are payable separately and set by the relevant authorities. Visa decisions are made solely by those authorities, and we cannot guarantee any outcome.</p>
    <p>If you choose an express option, that speeds <strong>our</strong> handling of your application only. It does not make a consulate or visa centre decide any faster, and it does not change the appointment slots they have available.</p>
  </div>
</div></section>

{{-- CTA --}}
<section class="cta-band"><div class="wrap reveal">
  <div class="rule"></div>
  <h2>Let's get you travelling</h2>
  <p style="max-width:48ch;color:#eef0f1">Start your application now, or message our UK team with any question, even just to check whether you need us.</p>
  <div class="row"><a href="{{ App\Support\SiteStats::chatUrl('Hi Beyond Passports, I would like to know more about how you work.') }}" target="_blank" rel="noopener" class="btn">Check eligibility →</a> @include('partials.consult-cta')<a href="https://wa.me/{{ config('ukv.whatsapp') ?: '447882747584' }}?text={{ rawurlencode('Hi Beyond Passports, I would like to know more about how you work.') }}" class="btn btn--glass">@include('partials.wa-glyph')Chat on WhatsApp</a></div>
</div></section>

<script>
(function () {
  var grid = document.getElementById('ab-counts');
  if (!grid) return;
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var fmt = function (v, dec) {
    return dec ? v.toFixed(dec) : Math.round(v).toLocaleString('en-GB');
  };
  var run = function (el) {
    var target = parseFloat(el.getAttribute('data-count'));
    var dec = parseInt(el.getAttribute('data-dec') || '0', 10);
    var suffix = el.getAttribute('data-suffix') || '';
    if (isNaN(target)) return;
    if (reduce) { el.textContent = fmt(target, dec) + suffix; return; }
    var dur = 1100, start = null;
    var step = function (ts) {
      if (start === null) start = ts;
      var p = Math.min((ts - start) / dur, 1);
      var eased = 1 - Math.pow(1 - p, 3);
      el.textContent = fmt(target * eased, dec) + suffix;
      if (p < 1) requestAnimationFrame(step);
      else el.textContent = fmt(target, dec) + suffix;
    };
    requestAnimationFrame(step);
  };
  var io = new IntersectionObserver(function (entries) {
    entries.forEach(function (e) {
      if (e.isIntersecting) {
        grid.querySelectorAll('.n[data-count]').forEach(run);
        io.disconnect();
      }
    });
  }, { threshold: 0.4 });
  io.observe(grid);
})();
</script>

@endsection
