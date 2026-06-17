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

  /* ── what-is — centred prose + definition callout ────────────── */
  .da-what{max-width:720px;margin:0 auto;text-align:center}
  .da-what .da-prose{text-align:left}
  .da-what h2{margin-inline:auto}
  .da-defcard{text-align:left;background:linear-gradient(180deg,#FBF6F1,var(--white));
    border:1px solid var(--paper-edge);border-left:4px solid var(--cta);
    border-radius:0 14px 14px 0;padding:20px 24px;margin-top:24px;
    font-size:15.5px;line-height:1.6;color:#3a4248;box-shadow:var(--lift-1)}
  .da-defcard strong{color:var(--navy)}

  /* ── honest framing panel ────────────────────────────────────── */
  .da-frame{display:grid;grid-template-columns:1fr 1fr;gap:28px;align-items:start}
  .da-frame-col h3{font-size:19px;font-weight:700;color:var(--navy);margin:0 0 14px}

  .da-honest{border:1px solid var(--paper-edge);border-radius:16px;
    background:var(--paper);padding:24px 24px;
    border-top:3px solid var(--cta)}
  .da-honest p{margin:0 0 .85em;color:#3a4248;font-size:15px;line-height:1.65}
  .da-honest p:last-child{margin:0}
  .da-honest strong{color:var(--ink)}

  /* ── stamp checklist ─────────────────────────────────────────── */
  .da-bring{list-style:none;margin:0;padding:0;display:grid;gap:14px}
  .da-bring li{display:flex;gap:14px;align-items:flex-start;
    background:var(--white);border:1px solid var(--paper-edge);
    border-radius:12px;padding:14px 16px;
    box-shadow:0 2px 8px -4px rgba(40,50,70,.08)}
  .da-bring li svg{flex:0 0 28px;height:28px;color:var(--stamp-text);margin-top:1px}
  .da-bring li span{font-size:15px;color:#3a4248;line-height:1.55}
  .da-bring li strong{color:var(--ink)}

  /* ── callout (provisional licence warning) ───────────────────── */
  .da-callout{display:flex;gap:16px;align-items:flex-start;
    border:1px solid var(--paper-edge);border-left:4px solid var(--cta);
    border-radius:14px;background:var(--white);padding:20px 22px;
    box-shadow:var(--lift-1)}
  .da-callout svg{flex:0 0 28px;height:28px;color:var(--cta);margin-top:2px}
  .da-callout p{margin:0;font-size:15.5px;color:#3a4248;line-height:1.6}
  .da-callout strong{color:var(--ink)}

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

{{-- GUIDED SELF-SERVICE FRAMING --}}
<section class="alt" id="how-it-really-works">
  <div class="wrap">
    <div class="sec-head reveal">
      <p class="eyebrow">How it really works</p>
      <h2>An IDP is collected in person — we make sure you're ready</h2>
    </div>

    <div class="da-frame">
      <div class="da-frame-col reveal">
        <h3>The honest picture</h3>
        <div class="da-honest">
          <p>In the UK, an IDP is issued <strong>in person, over the counter, at a PayPoint store</strong> — and you collect it yourself the same day. It is <strong>not available online</strong>, and (since 2019) it is <strong>no longer issued by the Post Office</strong>.</p>
          <p>We are <strong>not a government issuer</strong> and we cannot get the permit for you — it must be issued in person to the licence holder. What we sell is <strong>guidance and document checking</strong>, with our service fee shown separately from the small official IDP fee you pay at PayPoint.</p>
        </div>
      </div>
      <div class="da-frame-col reveal">
        <h3>What we do for you</h3>
        <p style="color:#3a4248;font-size:15px;margin:0 0 16px">We take the guesswork out of the trip to PayPoint so it succeeds first time:</p>
        <ul class="da-bring">
          <li>
            <svg width="28" height="28" viewBox="0 0 48 48" aria-hidden="true"><use href="#ukv-stamp"></use></svg>
            <span>We <strong>confirm which IDP type</strong> (1949 / 1968 / 1926) your destination needs.</span>
          </li>
          <li>
            <svg width="28" height="28" viewBox="0 0 48 48" aria-hidden="true"><use href="#ukv-stamp"></use></svg>
            <span>We <strong>check your paperwork</strong> so nothing's missing or out of date.</span>
          </li>
          <li>
            <svg width="28" height="28" viewBox="0 0 48 48" aria-hidden="true"><use href="#ukv-stamp"></use></svg>
            <span>We tell you <strong>exactly what to bring and where to go</strong> — your nearest PayPoint store.</span>
          </li>
        </ul>
      </div>
    </div>

    <div class="sec-head reveal" style="margin:52px 0 20px">
      <h2 style="font-size:clamp(22px,2.6vw,28px)">What to bring to your PayPoint visit</h2>
    </div>
    <ul class="da-bring reveal">
      <li>
        <svg width="28" height="28" viewBox="0 0 48 48" aria-hidden="true"><use href="#ukv-stamp"></use></svg>
        <span>Your <strong>full UK photocard driving licence</strong> (the holder must attend in person).</span>
      </li>
      <li>
        <svg width="28" height="28" viewBox="0 0 48 48" aria-hidden="true"><use href="#ukv-stamp"></use></svg>
        <span>A recent <strong>passport-style photo</strong> for the permit.</span>
      </li>
      <li>
        <svg width="28" height="28" viewBox="0 0 48 48" aria-hidden="true"><use href="#ukv-stamp"></use></svg>
        <span>The <strong>official IDP fee</strong> to pay at the counter (separate from our service fee).</span>
      </li>
      <li>
        <svg width="28" height="28" viewBox="0 0 48 48" aria-hidden="true"><use href="#ukv-stamp"></use></svg>
        <span>If your photocard shows an old address, a <strong>valid passport or other ID</strong> may be requested — we'll confirm.</span>
      </li>
    </ul>
  </div>
</section>

{{-- WHO CAN GET ONE --}}
<section id="who">
  <div class="wrap">
    <div class="sec-head reveal">
      <p class="eyebrow">Eligibility</p>
      <h2>Who can get an IDP?</h2>
    </div>
    <div class="da-split">
      <div class="da-prose reveal">
        <p>To get an International Driving Permit you must be <strong>18 or over</strong> and hold a <strong>full UK driving licence</strong> (photocard). The permit translates a licence you already hold — so a full, valid UK licence is the starting point.</p>
        <p>If your full licence covers the vehicle you'll drive abroad, you're good to go once you have the right IDP type for the country.</p>
      </div>
      <div class="reveal">
        <div class="da-callout">
          <svg viewBox="0 0 48 48" aria-hidden="true" style="width:28px;height:28px"><use href="#ukv-stamp"></use></svg>
          <p><strong>Provisional licence holders cannot get an IDP.</strong> An IDP can only be issued against a full UK driving licence. If you only hold a provisional licence, you are not eligible — and we'll tell you that honestly rather than take you to a counter that will turn you away.</p>
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
    <div class="sec-head reveal">
      <p class="eyebrow">Step 2, made easy</p>
      <h2>Find your nearest PayPoint</h2>
      <p style="max-width:54ch;color:#3a4248;font-size:16px;margin-top:6px">Enter your postcode and we'll show the closest PayPoint stores that issue the IDP in person — so you don't have to go hunting.</p>
    </div>
    <form class="da-pp-form reveal" method="GET" action="{{ route('centre.search') }}">
      <input type="hidden" name="type" value="paypoint">
      <input type="text" name="postcode" placeholder="e.g. SW1A 1AA"
             autocomplete="postal-code" required aria-label="Your postcode">
      <button type="submit" class="btn">Find nearest →</button>
    </form>
    <div class="da-pp-hint reveal">
      <a href="{{ url('/find-a-centre?type=paypoint') }}">Or use my location on the full finder →</a><br>
      <span>The IDP is issued in person at PayPoint — we prepare and check your paperwork; we don't issue the permit ourselves.</span>
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
