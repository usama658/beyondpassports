@extends('layouts.public')

{{-- "Plan a trip" — visa-led tour packages. Uses the site header/footer + ukv.css.
     Sections: hero (split + eligibility form) · how it works (.steps) · packages
     (overlay cards) · proof (stat band) · FAQ (accordion) · CTA (.cta-band + form).
     Numbers come from App\Support\SiteStats; packages from config('ukv.tours').
     Compliance: packages are enquiry-only (WhatsApp) until ATOL/PTR is in place —
     no prices, no checkout, no visa/approval guarantee. --}}

@section('title', 'Plan a trip — Europe tours with the Schengen visa built in | Beyond Passports')
@section('description', 'Visa-led European tour packages. We prepare the Schengen visa and book the appointment first, then wrap flights and hotels. Registered in the UK and Germany. No payment until after your free risk check.')

@php
  $tours   = config('ukv.tours.packages', []);
  $sla     = App\Support\SiteStats::responseSla();
  $apps    = App\Support\SiteStats::applications();
  $revs    = App\Support\SiteStats::reversals();
  $ins     = App\Support\SiteStats::insuranceMin();
  $waCheck = App\Support\SiteStats::chatUrl('Hi Beyond Passports, I would like to check my eligibility before booking a trip.');
  $waConsult = App\Support\SiteStats::chatUrl('Hi Beyond Passports, I would like to book my free consultation about a tour.');
  $bookMsg = fn ($p) => App\Support\SiteStats::chatUrl('Hi Beyond Passports, I am interested in the '.$p['name'].' ('.$p['where'].', '.$p['days'].') trip with the visa included. Please tell me more.');
@endphp

@push('head')
<style>
  /* Page-scoped (tr- prefix). Sits on top of ukv.css tokens; reuses global
     .steps/.step, .tbar-b tokens, .btn, .eyebrow, .wrap, .cta-band. */
  .tr-sec{padding:64px 0}
  .tr-sec .sec-head{max-width:60ch;margin:0 auto 36px;text-align:center}
  .tr-sub{color:var(--muted);font-size:17px;line-height:1.55}
  .tr-white{background:#fff;border-block:1px solid var(--paper-edge)}
  .tr-flag{width:22px;height:15px;border-radius:3px;display:inline-block;vertical-align:-2px;box-shadow:0 0 0 1px rgba(0,0,0,.06)}
  /* hero */
  .tr-hero{background:linear-gradient(180deg,#EAF1F4 0%,#F2F5F6 45%,var(--paper) 100%);border-bottom:1px solid var(--paper-edge)}
  .tr-hgrid{display:grid;grid-template-columns:1.12fr .88fr;gap:44px;align-items:center;padding:54px 0 62px}
  .tr-hero h1{color:var(--ink);font:800 clamp(32px,4.6vw,52px)/1.02 var(--display);letter-spacing:-.035em;margin:0 0 16px}
  .tr-hero .lede{color:var(--muted);font-size:19px;line-height:1.5;margin:0 0 18px;max-width:46ch}
  .tr-htrust{color:var(--stamp-text);font-size:14px;font-weight:600}
  .tr-chips{display:flex;gap:8px;flex-wrap:wrap;margin:16px 0 0}
  .tr-chip{display:inline-flex;align-items:center;gap:7px;background:#fff;border:1px solid var(--paper-edge);border-radius:999px;padding:7px 13px;font:600 13px var(--display);color:var(--ink);box-shadow:0 6px 16px -12px rgba(40,50,70,.5)}
  .tr-chip b{color:var(--stamp);font-weight:800}
  .tr-form{background:#fff;border:1px solid var(--paper-edge);border-radius:22px;padding:28px;box-shadow:0 30px 70px -42px rgba(30,40,60,.65)}
  .tr-form .fl{font:800 20px var(--display);letter-spacing:-.01em;margin:0 0 6px}
  .tr-form .fs{color:var(--muted);font-size:14px;line-height:1.5;margin:0 0 18px}
  .tr-form label{display:block;font:700 11px var(--display);letter-spacing:.06em;text-transform:uppercase;color:var(--muted);margin:0 0 5px}
  .tr-form input{width:100%;padding:13px 14px;border:1px solid var(--paper-edge);border-radius:11px;font:500 15px var(--display);margin:0 0 12px;background:#fbfdfd;color:var(--ink)}
  .tr-form .btn{width:100%;margin-top:4px}
  .tr-form .fnote{text-align:center;color:var(--muted);font-size:12.5px;margin:12px 0 0}
  /* how — reuse global .steps; keep on paper */
  .tr-how .reassure{text-align:center;margin:30px 0 0;font:700 15px var(--display);color:var(--cta)}
  /* packages — overlay cards */
  .tr-cin{display:grid;grid-template-columns:repeat(3,1fr);gap:22px}
  .tr-card{position:relative;height:400px;border-radius:18px;overflow:hidden;display:flex;flex-direction:column;justify-content:flex-end;color:#fff;box-shadow:0 30px 60px -34px rgba(20,34,46,.6)}
  .tr-card .bg{position:absolute;inset:0;background-size:cover;background-position:center}
  .tr-card .scrim{position:absolute;inset:0;background:linear-gradient(180deg,rgba(0,0,0,0) 24%,rgba(12,20,30,.55) 60%,rgba(12,20,30,.93))}
  .tr-card .day{position:absolute;top:15px;right:15px;font:800 12px var(--display);color:var(--ink);background:rgba(255,255,255,.95);padding:6px 12px;border-radius:999px}
  .tr-card .vis{position:absolute;top:15px;left:15px;font:800 11px var(--display);color:#fff;background:rgba(21,94,122,.95);padding:6px 12px;border-radius:999px}
  .tr-card .in{position:relative;padding:22px;text-align:left}
  .tr-card .co{font:600 12px var(--display);opacity:.92;display:flex;gap:8px;align-items:center;margin:0 0 6px;color:#eaf2f6}
  .tr-card h3{margin:0 0 14px;font:800 22px/1.1 var(--display);letter-spacing:-.02em;color:#fff}
  .tr-card .btn{width:100%;background:#25D366}
  .tr-card .btn:hover{background:#1da851}
  .tr-pkfoot{text-align:center;margin:34px 0 0;color:var(--muted);font-size:14.5px}
  /* proof — 5-col white stat band */
  .tr-stats{background:#fff;border:1px solid var(--paper-edge);border-radius:18px;box-shadow:var(--shadow);overflow:hidden}
  .tr-stats .row{display:grid;grid-template-columns:repeat(5,1fr);gap:18px;text-align:center;padding:30px 12px}
  .tr-stats .n{font:800 clamp(24px,3vw,32px)/1 var(--display);color:var(--cta);letter-spacing:-.02em}
  .tr-stats .l{font:600 13px var(--display);color:var(--muted);margin-top:8px;line-height:1.4;padding:0 6px}
  .tr-stats .row>div+div{border-left:1px solid var(--paper-edge)}
  .tr-honest{text-align:center;color:var(--muted);font-size:13.5px;margin:22px auto 0;max-width:64ch}
  /* faq — services accordion panel */
  .tr-faqpanel{background:#fff;border:1px solid var(--paper-edge);border-radius:18px;padding:6px 30px;max-width:80ch;margin:0 auto;box-shadow:0 16px 40px -30px rgba(40,50,70,.5)}
  .tr-faqd details{border-bottom:1px solid var(--paper-edge);padding:18px 0}
  .tr-faqd details:last-child{border-bottom:0}
  .tr-faqd summary{font-family:var(--display);font-size:19px;color:var(--navy);font-weight:600;cursor:pointer;list-style:none;display:flex;justify-content:space-between;align-items:center;gap:16px}
  .tr-faqd summary::-webkit-details-marker{display:none}
  .tr-faqd summary::after{content:"+";font-size:22px;color:var(--cta);flex:0 0 auto;font-weight:700}
  .tr-faqd details[open] summary::after{content:"\2013"}
  .tr-faqd p{margin:12px 0 0;color:#3a4b55;font-size:16px;line-height:1.65}
  /* cta form on the .cta-band */
  .tr-ctaform{display:flex;gap:12px;max-width:640px;margin:24px auto 0;flex-wrap:wrap}
  .tr-ctaform input{flex:1;min-width:180px;padding:15px 16px;border-radius:12px;border:1px solid rgba(255,255,255,.28);background:rgba(255,255,255,.12);color:#fff;font:500 15px var(--display)}
  .tr-ctaform input::placeholder{color:#c4d3dc}
  .tr-ctafnote{margin:12px 0 0;color:#93a4ae;font-size:12.5px}
  .tr-ctaconsult{margin:16px 0 0;color:#c4d3dc;font-size:14.5px}
  .tr-ctaconsult a{color:var(--on-dark);font-weight:700;text-decoration:none}
  @media(max-width:900px){.tr-hgrid{grid-template-columns:1fr}.tr-cin{grid-template-columns:1fr 1fr}}
  @media(max-width:760px){.tr-cin{grid-template-columns:1fr}.tr-stats .row{grid-template-columns:1fr 1fr}.tr-stats .row>div+div{border-left:0}}
</style>
@endpush

@section('content')
@php
  $waIcon = '<svg viewBox="0 0 24 24" aria-hidden="true" style="width:17px;height:17px;fill:#fff;vertical-align:-3px"><path d="M.057 24l1.687-6.163a11.9 11.9 0 1 1 4.45 4.45L.057 24z"/></svg>';
@endphp

{{-- 1 · HERO --}}
<section class="tr-hero"><div class="wrap"><div class="tr-hgrid">
  <div>
    <p class="eyebrow">Flights, hotels and the Schengen visa. One booking.</p>
    <h1>Book the trip.<br>We clear the visa.</h1>
    <p class="lede">Every other tour leaves the visa to you. We prepare it in-house, book your appointment and cut the refusal risk, then wrap it with flights and hotels. You just pack.</p>
    <div class="tr-htrust">✦ Registered in the UK and Germany · advisor usually replies within {{ $sla }}</div>
    <div class="tr-chips">
      <span class="tr-chip"><b>✓</b> Flights included</span>
      <span class="tr-chip"><b>✓</b> Hotels + transfers</span>
      <span class="tr-chip"><b>✓</b> Visa + appointment</span>
    </div>
  </div>
  <div class="tr-form">
    <div class="fl">Check your eligibility, free</div>
    <div class="fs">Just your name and number. A UK and Germany advisor spots what could get you refused, then holds the soonest slot before it goes.</div>
    <label for="tr-name">Your name</label><input id="tr-name" type="text" autocomplete="name" placeholder="Full name">
    <label for="tr-phone">Phone number</label><input id="tr-phone" type="tel" autocomplete="tel" placeholder="Mobile number">
    <a class="btn" href="{{ $waCheck }}" target="_blank" rel="noopener" data-tr-appt>{!! $waIcon !!} Check my eligibility</a>
    <p class="fnote">No payment. No obligation.</p>
  </div>
</div></div></section>

{{-- 2 · HOW IT WORKS (global .steps) --}}
<section class="tr-sec tr-how"><div class="wrap">
  <div class="sec-head">
    <p class="eyebrow">How it works</p>
    <h2>We clear the visa first. Then you travel.</h2>
    <p class="tr-sub" style="margin:12px auto 0;max-width:52ch">Every other tour leaves the visa to you. We flip it: the visa is prepared and the appointment booked before you pay for a single flight.</p>
  </div>
  <div class="steps">
    <div class="step"><div class="num">01</div><h3>Free risk check</h3><p>Name and number, that is it. An advisor tells you honestly what could get you refused. No payment.</p></div>
    <div class="step"><div class="num">02</div><h3>We prepare the visa</h3><p>We build the application the way a consulate reads it, and lock in the appointment before slots go.</p></div>
    <div class="step"><div class="num">03</div><h3>We wrap the trip</h3><p>Visa handled, we book the flights and hotels around your dates. You just pack.</p></div>
  </div>
  <p class="reassure">No payment until after your free risk check.</p>
</div></section>

{{-- 3 · PACKAGES (overlay cards, enquiry-only) --}}
<section class="tr-sec tr-white"><div class="wrap" style="text-align:center">
  <p class="eyebrow">Six ways to see Europe</p>
  <h2>Pick the trip. The visa comes built in.</h2>
  <p class="tr-sub" style="max-width:60ch;margin:0 auto 40px">Every package includes the Schengen visa preparation, the appointment booking, return flights and hotels. One booking, one team, one price.</p>
  <div class="tr-cin">
    @foreach ($tours as $p)
      <div class="tr-card">
        <div class="bg" style="background:{{ $p['img'] }}"></div><div class="scrim"></div>
        <span class="vis">{{ $p['flagship'] ? '★ Flagship' : 'Visa + appointment' }}</span>
        <span class="day">{{ $p['days'] }}</span>
        <div class="in">
          <div class="co"><span class="tr-flag" style="background:{{ $p['flag'] }}"></span>{{ $p['where'] }}</div>
          <h3>{{ $p['name'] }}</h3>
          <a class="btn" href="{{ $bookMsg($p) }}" target="_blank" rel="noopener">{!! $waIcon !!} Book this trip →</a>
        </div>
      </div>
    @endforeach
  </div>
  <p class="tr-pkfoot">Get a quote. No payment until after your free risk check.</p>
</div></section>

{{-- 4 · PROOF (5-col stat band) --}}
<section class="tr-sec"><div class="wrap" style="text-align:center">
  <div class="sec-head">
    <p class="eyebrow">Trusted</p>
    <h2>Why people trust us with the visa</h2>
    <p class="tr-sub">Our own records. We do not promise outcomes. We make the case as strong as it can be.</p>
  </div>
  <div class="tr-stats"><div class="row">
    <div><div class="n">UK &amp; DE</div><div class="l">Registered in the United Kingdom and Germany</div></div>
    <div><div class="n">{{ $apps }}</div><div class="l">Applications prepared and counting</div></div>
    <div><div class="n">{{ $revs }}</div><div class="l">Previously refused cases turned around</div></div>
    <div><div class="n">{{ $ins }}</div><div class="l">Minimum travel insurance per traveller</div></div>
    <div><div class="n">{{ $sla }}</div><div class="l">Typical advisor reply time</div></div>
  </div></div>
</div></section>

{{-- 5 · FAQ (accordion) --}}
<section class="tr-sec"><div class="wrap">
  <div class="sec-head"><p class="eyebrow">Questions</p><h2>Questions people ask</h2></div>
  <div class="tr-faqpanel"><div class="tr-faqd">
    <details open><summary>Does a refusal affect future travel?</summary><p>Yes. It is logged in the Schengen system for around five years and must be declared on future visa applications. That is exactly why the second attempt has to be built properly.</p></details>
    <details><summary>Which consulate do I apply to?</summary><p>The country you spend the most time in, or your first entry point if nights are equal. We work this out for you, because the wrong choice alone can cause a refusal.</p></details>
    <details><summary>What if appointment slots are gone?</summary><p>We book the appointment as part of preparing your visa, before you commit to travel dates. If the soonest slot is tight, we tell you upfront.</p></details>
    <details><summary>When do I pay?</summary><p>Nothing until after your free risk check. You only commit once you know where you stand.</p></details>
    <details><summary>Can you guarantee the visa?</summary><p>No, and be careful of anyone who does. The decision is always the consulate&rsquo;s. We make your application as strong as it can be.</p></details>
  </div></div>
</div></section>

{{-- 6 · CTA (.cta-band + form) --}}
<section class="cta-band"><div class="wrap reveal">
  <div class="rule"></div>
  <h2>Start with the visa. The holiday follows.</h2>
  <p style="max-width:52ch;color:#eef0f1">Send your name and number. We will tell you honestly what your chances look like and what the trip would involve. Free, no obligation.</p>
  <div class="tr-ctaform">
    <input id="tr-cta-name" type="text" autocomplete="name" placeholder="Your name" aria-label="Your name">
    <input id="tr-cta-phone" type="tel" autocomplete="tel" placeholder="Phone number" aria-label="Phone number">
    <a class="btn btn--glass" href="{{ $waCheck }}" target="_blank" rel="noopener" data-tr-appt="cta">{!! $waIcon !!} Check my eligibility</a>
  </div>
  <p class="tr-ctafnote">No payment. No obligation.</p>
  <p class="tr-ctaconsult">Prefer to talk it through first? <a href="{{ $waConsult }}" target="_blank" rel="noopener">Book a free consultation.</a></p>
</div></section>

<script>
/* Prefill the WhatsApp message with the visitor's name + phone (no server round-trip). */
(function () {
  var base = @json($waCheck);
  function wire(btnSel, nameId, phoneId) {
    var btn = document.querySelector(btnSel);
    if (!btn) return;
    function build() {
      var n = (document.getElementById(nameId) || {}).value || '';
      var p = (document.getElementById(phoneId) || {}).value || '';
      if (!n && !p) { btn.href = base; return; }
      var msg = 'Hi Beyond Passports, I would like to check my eligibility before booking a trip.'
        + (n ? ' Name: ' + n + '.' : '') + (p ? ' Phone: ' + p + '.' : '');
      btn.href = 'https://wa.me/{{ config('ukv.whatsapp') ?: '440000000000' }}?text=' + encodeURIComponent(msg);
    }
    ['input', 'change'].forEach(function (e) {
      var nn = document.getElementById(nameId), pp = document.getElementById(phoneId);
      if (nn) nn.addEventListener(e, build); if (pp) pp.addEventListener(e, build);
    });
  }
  wire('[data-tr-appt]:not([data-tr-appt="cta"])', 'tr-name', 'tr-phone');
  wire('[data-tr-appt="cta"]', 'tr-cta-name', 'tr-cta-phone');
})();
</script>
@endsection
