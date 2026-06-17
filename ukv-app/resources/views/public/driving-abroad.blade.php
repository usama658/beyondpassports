@extends('layouts.public')

@section('title', 'International Driving Permit (IDP) — sorted the right way | Beyond Passports')
@section('description', 'Need an International Driving Permit to drive abroad? We confirm which IDP type you need (1949/1968/1926), prepare and check your paperwork, and tell you exactly what to bring to your in-person PayPoint visit. Independent service — not a government website; the IDP is issued in person at PayPoint, not by us.')

@push('head')
<style>
  /* driving-abroad — page-scoped layout only. Palette/type/components from ukv.css. */

  /* ── hero — navy mesh, centred, three convention type cards ───── */
  .idp-hero{
    background:
      radial-gradient(620px 280px at 88% 0, rgba(199,93,56,.42), transparent 60%),
      radial-gradient(560px 260px at 8% 100%, rgba(92,154,123,.34), transparent 60%),
      var(--navy);
    color:#fff;text-align:center;padding:64px 0;
  }
  .idp-hero .eyebrow{color:var(--soft)}
  .idp-hero h1{color:#fff;max-width:16ch;margin:0 auto}
  @media (min-width:760px){ .idp-hero h1{max-width:760px} }
  .idp-hero .lede{color:rgba(255,255,255,.85);max-width:54ch;margin:14px auto 0}
  .idp-types{display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin:28px 0 0}
  .idp-types .t{background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.18);
    border-radius:14px;padding:14px 22px;min-width:150px}
  .idp-types .t b{display:block;font:800 18px var(--display);color:var(--soft)}
  .idp-types .t span{font-size:12.5px;color:rgba(255,255,255,.72)}
  .idp-hero .btn{margin-top:28px}

  /* ── two-column prose split ──────────────────────────────────── */
  .da-split{display:grid;grid-template-columns:1.05fr .95fr;gap:48px;align-items:start}
  .da-prose p{color:#3a4248;margin:0 0 1em;font-size:16.5px;line-height:1.72}
  .da-prose p:last-child{margin-bottom:0}

  /* ── what-is — full-width prose + definition callout ─────────── */
  .da-what{max-width:none}
  .da-what .da-prose{max-width:74ch}
  .da-defcard{text-align:left;background:linear-gradient(180deg,#FBF6F1,var(--white));
    border:1px solid var(--paper-edge);border-left:4px solid var(--cta);
    border-radius:0 14px 14px 0;padding:20px 24px;margin-top:24px;
    font-size:15.5px;line-height:1.6;color:#3a4248;box-shadow:var(--lift-1)}
  .da-defcard strong{color:var(--navy)}

  /* ── guided self-service — availability rail + do tags ───────── */
  .idp-guide{background:var(--paper)}
  .ig-rail{display:flex;border:1px solid var(--paper-edge);border-radius:16px;overflow:hidden;box-shadow:var(--lift-1);margin-bottom:30px}
  .ig-rung{flex:1;padding:22px 20px;background:var(--white);border-right:1px solid var(--paper-edge);display:flex;flex-direction:column;gap:8px}
  .ig-rung:last-child{border-right:0}
  .ig-rung.is-yes{background:linear-gradient(180deg,#eef5f1,var(--white))}
  .ig-rung .lab{display:flex;align-items:center;gap:8px;font:800 15px var(--display);color:var(--navy)}
  .ig-rung.is-yes .lab{color:var(--sage-t)}
  .ig-rung .s{font-size:13px;color:var(--muted)}
  .ig-rung svg{flex:0 0 20px;width:20px;height:20px}
  .ig-rung .ic-no{color:#b5453a}.ig-rung .ic-yes{color:var(--sage-t)}
  .ig-two{display:grid;grid-template-columns:1.1fr .9fr;gap:30px;align-items:center}
  .ig-note{font-size:15px;color:#3a4248;line-height:1.65;margin:0}.ig-note strong{color:var(--navy)}
  .ig-tags{display:grid;gap:10px}
  .ig-tag{display:flex;gap:11px;align-items:flex-start;background:var(--white);border:1px solid var(--paper-edge);border-radius:12px;padding:13px 15px;font-size:14px;color:#3a4248;line-height:1.5;box-shadow:0 2px 8px -4px rgba(40,50,70,.08)}
  .ig-tag svg{flex:0 0 20px;width:20px;height:20px;color:var(--sage-t);margin-top:1px}
  .ig-tag strong{color:var(--ink)}
  @media (max-width:820px){ .ig-rail{flex-direction:column}.ig-rung{border-right:0;border-bottom:1px solid var(--paper-edge)}.ig-rung:last-child{border-bottom:0}.ig-two{grid-template-columns:1fr} }

  /* ── honest framing panel ────────────────────────────────────── */
  .da-frame{display:grid;grid-template-columns:1fr 1fr;gap:28px;align-items:start}
  .da-frame-col h3{font-size:19px;font-weight:700;color:var(--navy);margin:0 0 14px}

  .da-honest{border:1px solid var(--paper-edge);border-radius:16px;
    background:var(--paper);padding:24px 24px;
    border-top:3px solid var(--cta)}
  .da-honest p{margin:0 0 .85em;color:#3a4248;font-size:15px;line-height:1.65}
  .da-honest p:last-child{margin:0}
  .da-honest strong{color:var(--ink)}

  /* ── bring checklist — distinct icon tiles, 2-up ─────────────── */
  .da-bring{list-style:none;margin:0;padding:0;display:grid;grid-template-columns:1fr 1fr;gap:14px}
  .da-bring li{display:flex;gap:14px;align-items:flex-start;
    background:var(--white);border:1px solid var(--paper-edge);
    border-radius:14px;padding:18px 20px;box-shadow:var(--lift-1)}
  .da-bring li .bi{flex:0 0 44px;width:44px;height:44px;border-radius:12px;
    background:linear-gradient(135deg,#eef5f2,#dff0eb);color:var(--sage-t);
    display:flex;align-items:center;justify-content:center}
  .da-bring li .bi svg{width:22px;height:22px}
  .da-bring li span{font-size:15px;color:#3a4248;line-height:1.55}
  .da-bring li strong{color:var(--ink)}
  @media (max-width:680px){ .da-bring{grid-template-columns:1fr} }

  /* ── eligibility — can / can't contrast cards ────────────────── */
  #who .da-split{align-items:center}
  #who .da-prose .eyebrow{margin-bottom:8px}
  #who .who-h2{font-size:clamp(26px,3vw,34px);color:var(--navy);margin:0 0 16px;letter-spacing:-.02em}
  .da-cc{display:grid;gap:14px}
  .da-cc-card{border:1px solid var(--paper-edge);border-radius:16px;background:var(--white);padding:22px 24px;box-shadow:var(--lift-1)}
  .da-cc-card.is-can{border-top:3px solid var(--sage-t)}
  .da-cc-card.is-cant{border-top:3px solid var(--cta)}
  .da-cc-card .cc-t{font:800 11px var(--display);letter-spacing:.12em;text-transform:uppercase;margin:0 0 12px}
  .da-cc-card.is-can .cc-t{color:var(--sage-t)}
  .da-cc-card.is-cant .cc-t{color:var(--cta)}
  .da-cc-card ul{list-style:none;margin:0;padding:0;display:grid;gap:10px}
  .da-cc-card li{display:flex;gap:10px;align-items:flex-start;font-size:14.5px;color:#3a4248;line-height:1.5}
  .da-cc-card li svg{flex:0 0 22px;width:22px;height:22px;margin-top:1px}
  .da-cc-card li strong{color:var(--ink)}
  .da-cc-card.is-can li svg{color:var(--sage-t)}
  .da-cc-card.is-cant li svg{color:#b5453a}

  /* ── callout (provisional licence warning) ───────────────────── */
  .da-callout{display:flex;gap:16px;align-items:flex-start;
    border:1px solid var(--paper-edge);border-left:4px solid var(--cta);
    border-radius:14px;background:var(--white);padding:20px 22px;
    box-shadow:var(--lift-1)}
  .da-callout svg{flex:0 0 28px;height:28px;color:var(--cta);margin-top:2px}
  .da-callout p{margin:0;font-size:15.5px;color:#3a4248;line-height:1.6}
  .da-callout strong{color:var(--ink)}

  /* ── find PayPoint — split copy + form card ──────────────────── */
  .da-find-grid{display:grid;grid-template-columns:1fr 1fr;gap:44px;align-items:center}
  .da-find-pin{display:flex;align-items:center;gap:12px;margin-bottom:8px}
  .da-find-pin svg{width:30px;height:30px;color:var(--cta);flex:none}
  .da-find-pin .eyebrow{margin:0}
  .da-find-grid h2{font-size:clamp(24px,3vw,32px);color:var(--navy);margin:0 0 10px;letter-spacing:-.02em}
  .da-find-grid .da-find-copy p{color:#3a4248;font-size:16px;line-height:1.6;margin:0;max-width:46ch}
  .da-find-card{background:var(--paper);border:1px solid var(--paper-edge);border-radius:18px;padding:26px 28px;box-shadow:var(--lift-1)}
  .da-find-card .da-pp-form{flex-direction:column;flex-wrap:nowrap;max-width:none;margin-top:0}
  .da-find-card .da-pp-form input[type=text]{min-width:0;width:100%;background:var(--white)}
  .da-find-card .da-pp-form .btn{width:100%;justify-content:center}
  .da-find-card .da-pp-hint{margin-top:14px}
  @media (max-width:820px){ .da-find-grid{grid-template-columns:1fr;gap:26px} }

  /* ── PayPoint finder form ────────────────────────────────────── */
  .da-pp-form{display:flex;flex-wrap:wrap;gap:10px;margin-top:20px;max-width:520px}
  .da-pp-form input[type=text]{
    flex:1;min-width:180px;padding:13px 15px;
    border:1.5px solid var(--paper-edge);border-radius:10px;
    font:inherit;font-size:15px;color:var(--ink);
    transition:border-color .15s ease,box-shadow .15s ease}
  .da-pp-form input[type=text]:focus{
    border-color:var(--cta);box-shadow:0 0 0 3px rgba(199,93,56,.14);outline:none}
  .da-pp-hint{margin-top:12px;font-size:13px;color:var(--muted);line-height:1.55}
  .da-pp-hint a{color:var(--cta);font-weight:600}

  /* ── FAQ — override global .faq for fuller styling on this page ─ */
  .da-faqs{max-width:760px;margin:0 auto;display:grid;gap:14px}
  .da-faq{border:1px solid var(--paper-edge);border-radius:14px;
    background:var(--white);overflow:hidden;box-shadow:var(--lift-1)}
  .da-faq summary{list-style:none;cursor:pointer;padding:18px 22px;
    font-weight:700;font-size:17.5px;color:var(--navy);
    display:flex;justify-content:space-between;align-items:center;gap:16px;
    transition:background .15s ease}
  .da-faq summary:hover{background:var(--paper)}
  .da-faq summary::-webkit-details-marker{display:none}
  .da-faq summary::after{content:"+";font-size:22px;color:var(--cta);line-height:1;
    font-weight:700;transition:transform .2s ease}
  .da-faq[open] summary{background:var(--paper)}
  .da-faq[open] summary::after{content:"–"}
  .da-faq .da-a{padding:0 22px 20px;color:#3a4248;font-size:15.5px;line-height:1.65}
  .da-faq .da-a p{margin:0}

  /* ── section-head centering override for FAQ ─────────────────── */
  .da-sec-center{text-align:center;max-width:none;margin-bottom:32px}

  @media (max-width:860px){
    .da-split,.da-frame{grid-template-columns:1fr}
    .da-types{gap:10px}
  }
</style>
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "FAQPage",
  "mainEntity": [
    {"@@type":"Question","name":"Is this a government service?","acceptedAnswer":{"@@type":"Answer","text":"No. Beyond Passports is an independent service — not a government website and not a government issuer. We provide guidance and document checking. The IDP itself is an official document issued at PayPoint stores; our service fee is separate from the official IDP fee you pay there."}},
    {"@@type":"Question","name":"Can you get the IDP for me?","acceptedAnswer":{"@@type":"Answer","text":"No — an IDP must be issued in person to you, the licence holder, at a PayPoint store. We can't collect it on your behalf. What we do is confirm which IDP type you need, check and prepare your paperwork, and tell you exactly what to bring and where to go so your visit succeeds first time."}},
    {"@@type":"Question","name":"Which countries need which type?","acceptedAnswer":{"@@type":"Answer","text":"It depends on the country. There are three IDP types, tied to the 1949, 1968 and 1926 conventions. Most of Europe and many other countries use the 1968 type; the USA and several others use the 1949 type; a small number still require the older 1926 type — and some destinations accept more than one. Tell us where you're driving and we'll confirm the exact type (or types) you need before you set off."}},
    {"@@type":"Question","name":"What if I only have a provisional licence?","acceptedAnswer":{"@@type":"Answer","text":"You can't get an IDP on a provisional licence. An IDP can only be issued against a full UK driving licence, because it translates a licence you already hold. If you only have a provisional, you're not eligible yet — we'll tell you straight rather than send you on a wasted trip."}}
  ]
}
</script>
@endpush

@section('content')

{{-- HERO --}}
<section class="idp-hero">
  <div class="wrap reveal">
    <p class="eyebrow">Driving abroad</p>
    <h1>International Driving Permit&nbsp;— sorted the right way</h1>
    <p class="lede">We confirm which IDP you need and prepare your paperwork, so your in-person collection goes smoothly first time.</p>
    <div class="idp-types">
      <div class="t"><b>1949</b><span>USA &amp; parts of Asia/Africa</span></div>
      <div class="t"><b>1968</b><span>Most of Europe</span></div>
      <div class="t"><b>1926</b><span>A few countries</span></div>
    </div>
    <div><a href="{{ url('/tools') }}" class="btn">Check if I need an IDP →</a></div>
  </div>
</section>

{{-- WHAT IS AN IDP --}}
<section id="what">
  <div class="wrap">
    <div class="da-what reveal">
      <p class="eyebrow">The basics</p>
      <h2 style="color:var(--navy);font-size:clamp(28px,3.4vw,38px);margin-bottom:18px">What is an International Driving Permit?</h2>
      <div class="da-prose">
        <p>An International Driving Permit (IDP) is an official document that translates your UK driving licence into other languages, so police and hire-car companies abroad can read it alongside your photocard licence. It doesn't replace your UK licence — you carry both together.</p>
        <p>Different countries recognise different IDP types, set by three international conventions. Some countries accept one type, some another, and a few accept more than one. Carrying the wrong type can mean being turned away at the car-hire desk or fined at the roadside — so getting the right one matters.</p>
      </div>
      <div class="da-defcard">An IDP <strong>translates</strong> the licence you already hold — it never replaces it. You always carry your UK photocard <strong>and</strong> the IDP together.</div>
    </div>
  </div>
</section>

{{-- GUIDED SELF-SERVICE FRAMING — navy mesh band --}}
@php
  $igTick = '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
  $igCross = '<svg class="ic-no" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>';
  $igCheck = '<svg class="ic-yes" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
@endphp
<section class="idp-guide" id="how-it-really-works">
  <div class="wrap">
    <div class="sec-head reveal">
      <p class="eyebrow">How it really works</p>
      <h2>An IDP is collected in person — we make sure you're ready</h2>
    </div>

    <div class="ig-rail reveal" aria-label="Where you can get an IDP">
      <div class="ig-rung"><span class="lab">{!! $igCross !!}Online</span><span class="s">Not available</span></div>
      <div class="ig-rung"><span class="lab">{!! $igCross !!}Post Office</span><span class="s">Stopped in 2019</span></div>
      <div class="ig-rung is-yes"><span class="lab">{!! $igCheck !!}PayPoint</span><span class="s">In person, same day</span></div>
    </div>

    <div class="ig-two reveal">
      <p class="ig-note">We are <strong>not a government issuer</strong> and we cannot get the permit for you — it must be issued in person to the licence holder. What we sell is <strong>guidance and document checking</strong>, with our service fee shown separately from the small official IDP fee you pay at PayPoint.</p>
      <div class="ig-tags">
        <div class="ig-tag">{!! $igTick !!}<span>We <strong>confirm which IDP type</strong> (1949 / 1968 / 1926) your destination needs.</span></div>
        <div class="ig-tag">{!! $igTick !!}<span>We <strong>check your paperwork</strong> so nothing's missing or out of date.</span></div>
        <div class="ig-tag">{!! $igTick !!}<span>We tell you <strong>exactly what to bring and where to go</strong> — your nearest PayPoint.</span></div>
      </div>
    </div>
  </div>
</section>

{{-- WHAT TO BRING — light --}}
<section class="alt" id="bring">
  <div class="wrap">
    <div class="sec-head reveal" style="margin-bottom:20px">
      <h2 style="font-size:clamp(22px,2.6vw,28px)">What to bring to your PayPoint visit</h2>
    </div>
    <ul class="da-bring reveal">
      <li>
        <span class="bi" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.7"/><circle cx="8" cy="11" r="2" stroke="currentColor" stroke-width="1.7"/><path d="M13 9h5M13 13h5M6 15h8" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></span>
        <span>Your <strong>full UK photocard driving licence</strong> (the holder must attend in person).</span>
      </li>
      <li>
        <span class="bi" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><rect x="3" y="6" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.7"/><circle cx="12" cy="13" r="3.2" stroke="currentColor" stroke-width="1.7"/><path d="M8 6l1.5-2h5L16 6" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg></span>
        <span>A recent <strong>passport-style photo</strong> for the permit.</span>
      </li>
      <li>
        <span class="bi" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><rect x="2.5" y="6" width="19" height="12" rx="2" stroke="currentColor" stroke-width="1.7"/><circle cx="12" cy="12" r="2.6" stroke="currentColor" stroke-width="1.7"/></svg></span>
        <span>The <strong>official IDP fee</strong> to pay at the counter (separate from our service fee).</span>
      </li>
      <li>
        <span class="bi" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M12 3l7 3v5c0 4.5-3 7.6-7 9-4-1.4-7-4.5-7-9V6l7-3z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
        <span>If your photocard shows an old address, a <strong>valid passport or other ID</strong> may be requested — we'll confirm.</span>
      </li>
    </ul>
  </div>
</section>

{{-- WHO CAN GET ONE --}}
<section id="who">
  <div class="wrap">
    @php
      $whoTick = '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
      $whoCross = '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>';
    @endphp
    <div class="da-split">
      <div class="da-prose reveal">
        <p class="eyebrow">Eligibility</p>
        <h2 class="who-h2">Who can get an IDP?</h2>
        <p>To get an International Driving Permit you must be <strong>18 or over</strong> and hold a <strong>full UK driving licence</strong> (photocard). The permit translates a licence you already hold — so a full, valid UK licence is the starting point.</p>
        <p>If your full licence covers the vehicle you'll drive abroad, you're good to go once you have the right IDP type for the country.</p>
      </div>
      <div class="da-cc reveal">
        <div class="da-cc-card is-can">
          <p class="cc-t">You can get one if</p>
          <ul>
            <li>{!! $whoTick !!}<span>You're <strong>18 or over</strong></span></li>
            <li>{!! $whoTick !!}<span>You hold a <strong>full UK photocard licence</strong></span></li>
            <li>{!! $whoTick !!}<span>It covers the vehicle you'll drive abroad</span></li>
          </ul>
        </div>
        <div class="da-cc-card is-cant">
          <p class="cc-t">You can't (yet) if</p>
          <ul>
            <li>{!! $whoCross !!}<span>You only hold a <strong>provisional licence</strong> — we'll tell you honestly rather than send you on a wasted trip.</span></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- 3 STEPS --}}
<section class="alt" id="steps">
  <div class="wrap">
    <div class="sec-head reveal">
      <p class="eyebrow">The process</p>
      <h2>Three simple steps</h2>
    </div>
    <div class="steps">
      <div class="step reveal">
        <div class="num">01</div>
        <div class="rule"></div>
        <h3>We confirm your IDP type &amp; documents</h3>
        <p>Tell us where you're driving. We confirm which IDP (1949/1968/1926) you need and check your paperwork.</p>
      </div>
      <div class="step reveal">
        <div class="num">02</div>
        <div class="rule"></div>
        <h3>You collect it at PayPoint</h3>
        <p>Take your prepared documents to your nearest PayPoint store and the permit is issued to you in person.</p>
      </div>
      <div class="step reveal">
        <div class="num">03</div>
        <div class="rule"></div>
        <h3>Drive abroad with confidence</h3>
        <p>Carry your IDP alongside your UK licence and you're road-ready at the car-hire desk and the roadside.</p>
      </div>
    </div>
  </div>
</section>

{{-- FIND NEAREST PAYPOINT — reuses /find-a-centre pre-scoped to the paypoint type --}}
<section id="find-paypoint">
  <div class="wrap">
    <div class="da-find-grid">
      <div class="da-find-copy reveal">
        <div class="da-find-pin">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
          <p class="eyebrow">Step 2, made easy</p>
        </div>
        <h2>Find your nearest PayPoint</h2>
        <p>Enter your postcode and we'll show the closest PayPoint stores that issue the IDP in person — so you don't have to go hunting.</p>
      </div>
      <div class="da-find-card reveal">
        <form class="da-pp-form" method="GET" action="{{ route('centre.search') }}">
          <input type="hidden" name="type" value="paypoint">
          <input type="text" name="postcode" placeholder="e.g. SW1A 1AA"
                 autocomplete="postal-code" required aria-label="Your postcode">
          <button type="submit" class="btn">Find nearest →</button>
        </form>
        <div class="da-pp-hint">
          <a href="{{ url('/find-a-centre?type=paypoint') }}">Or use my location on the full finder →</a><br>
          <span>The IDP is issued in person at PayPoint — we prepare and check your paperwork; we don't issue the permit ourselves.</span>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- FAQ --}}
<section id="faq" class="alt">
  <div class="wrap">
    <div class="sec-head da-sec-center reveal">
      <p class="eyebrow">Good to know</p>
      <h2>Frequently asked questions</h2>
    </div>
    <div class="da-faqs">
      <details class="da-faq reveal">
        <summary>Is this a government service?</summary>
        <div class="da-a"><p>No. Beyond Passports is an independent service — not a government website and not a government issuer. We provide guidance and document checking. The IDP itself is an official document issued at PayPoint stores; our service fee is separate from the official IDP fee you pay there.</p></div>
      </details>
      <details class="da-faq reveal">
        <summary>Can you get the IDP for me?</summary>
        <div class="da-a"><p>No — an IDP must be issued in person to you, the licence holder, at a PayPoint store. We can't collect it on your behalf. What we do is confirm which IDP type you need, check and prepare your paperwork, and tell you exactly what to bring and where to go so your visit succeeds first time.</p></div>
      </details>
      <details class="da-faq reveal">
        <summary>Which countries need which type?</summary>
        <div class="da-a"><p>It depends on the country. There are three IDP types, tied to the 1949, 1968 and 1926 conventions. Most of Europe and many other countries use the 1968 type; the USA and several others use the 1949 type; a small number still require the older 1926 type — and some destinations accept more than one. Tell us where you're driving and we'll confirm the exact type (or types) you need before you set off.</p></div>
      </details>
      <details class="da-faq reveal">
        <summary>What if I only have a provisional licence?</summary>
        <div class="da-a"><p>You can't get an IDP on a provisional licence. An IDP can only be issued against a <strong>full</strong> UK driving licence, because it translates a licence you already hold. If you only have a provisional, you're not eligible yet — we'll tell you straight rather than send you on a wasted trip.</p></div>
      </details>
    </div>
  </div>
</section>

{{-- CTA BAND --}}
<section class="cta-band">
  <div class="wrap reveal">
    <div class="rule"></div>
    <h2>Driving abroad soon? Let's get you road-ready</h2>
    <p style="max-width:50ch;color:#eef0f1">Check whether you need an IDP and which type, then we'll prepare your paperwork so your in-person PayPoint visit goes right first time. Independent service, not a government website.</p>
    <div class="row">
      <a href="{{ url('/tools') }}" class="btn">Check if I need an IDP →</a>
      <a href="https://wa.me/{{ config('ukv.whatsapp') ?: '440000000000' }}" class="btn btn--glass">Chat on WhatsApp</a>
    </div>
  </div>
</section>

@endsection
