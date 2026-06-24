@extends('layouts.public')

@section('title', 'Schengen visa from the UK, sorted | Beyond Passports')
@section('description', 'Applying for a Schengen visa from the UK? An independent UK team checks every document, completes the forms and books your appointment. Service fee separate from the embassy fee. Not a government website.')

@php
  // WhatsApp deep-link (same pattern as the other public pages).
  $waNumber = config('ukv.whatsapp') ?: '440000000000';
  $waLink = 'https://wa.me/'.$waNumber.'?text='.rawurlencode('Hi Beyond Passports, I need help with a Schengen visa');
  // Inline WhatsApp glyph (copied from services.blade.php — sized via .wa-g so it never blows up).
  $waGlyph = '<svg viewBox="0 0 24 24" aria-hidden="true" class="wa-g"><path d="M.057 24l1.687-6.163a11.867 11.867 0 0 1-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.817 11.817 0 0 1 8.413 3.488 11.824 11.824 0 0 1 3.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 0 1-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884a9.86 9.86 0 0 0 1.51 5.26l-.999 3.648 3.978-1.607zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>';
@endphp

@push('head')
<style>
  /* Schengen hub — page-scoped only (sg- prefix). Reuses the site design system in ukv.css. */

  /* Hero — soft-sky centred band (matches home/destinations hero treatment) */
  .sg-hero{background:linear-gradient(180deg,#EAF1F4 0%, #F2F5F6 55%, var(--paper) 100%);
    border-bottom:1px solid var(--paper-edge);text-align:center}
  .sg-hero > .wrap{padding:48px 0 44px}
  .sg-hero .eyebrow{color:var(--cta)}
  .sg-hero h1{font:700 clamp(30px,4.4vw,48px)/1.04 var(--display);letter-spacing:-.03em;color:var(--ink);margin:0 auto 16px;max-width:18ch}
  .sg-hero .lede{color:var(--muted);font-size:18px;line-height:1.5;max-width:54ch;margin:0 auto}
  .sg-hero .row{display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin-top:26px}
  .sg-hero .btn{display:inline-flex;align-items:center;gap:8px}
  .sg-hero .btn--wa{background:#25D366;border:0;color:#fff}
  .sg-hero .btn--wa:hover{background:#1da851}
  .btn .wa-g{width:17px;height:17px;fill:currentColor;flex:none;vertical-align:-3px}

  /* Trust band — dark mesh band (matches home / services .tbar-f) */
  .tbar-f{padding:0;background:
      radial-gradient(520px 200px at 12% 0%, rgba(21,94,122,.45), transparent 60%),
      radial-gradient(520px 200px at 92% 100%, rgba(46,154,140,.42), transparent 60%),
      var(--navy);color:#fff}
  .tbar-f .row{display:flex;justify-content:center;gap:30px;flex-wrap:wrap;padding:16px 0}
  .tbar-f .ti{display:flex;align-items:center;gap:9px;font:600 14px var(--display);color:#fff;white-space:nowrap}
  .tbar-f .ti svg{width:20px;height:20px;color:var(--soft);flex:none}
  .tbar-f .ti b{color:var(--soft);font-weight:800}
  @media (max-width:560px){.tbar-f .row{gap:14px 22px}}

  /* "What it covers" — explainer card + country example chips */
  #sg-covers .sec-head{text-align:center;max-width:60ch;margin:0 auto}
  #sg-covers .sec-head .lede{margin:12px auto 0;max-width:58ch}
  .sg-chips{display:flex;flex-wrap:wrap;gap:9px 11px;justify-content:center;margin:24px auto 0;max-width:60ch;padding:0;list-style:none}
  .sg-chips li{display:inline-flex;align-items:center;gap:8px;background:#fff;border:1px solid var(--paper-edge);
    border-radius:10px;padding:8px 14px;font:600 14px var(--display);color:var(--ink);
    box-shadow:0 6px 16px -12px rgba(40,50,70,.5)}
  .sg-chips .dot{width:7px;height:7px;border-radius:50%;background:var(--cta);flex:none}
  .sg-chips .more{color:var(--muted);box-shadow:none;background:transparent;border-style:dashed}

  /* WHAT WE DO — warm stamp-card grid (matches home "What we do") */
  #sg-do{background:linear-gradient(180deg,#FBF6F1,var(--paper))}
  #sg-do .sec-head{text-align:center;max-width:60ch;margin-left:auto;margin-right:auto}
  #sg-do .sec-head .lede{margin:12px auto 0;max-width:52ch}
  #sg-do .ticks{margin-top:30px}
  #sg-do .tick{background:#fff;border:1px solid var(--paper-edge);border-radius:16px;padding:22px;gap:14px;
    box-shadow:0 10px 30px -22px rgba(40,50,70,.5);transition:transform .25s ease,box-shadow .25s ease}
  #sg-do .tick:hover{transform:translateY(-3px);box-shadow:var(--lift-2)}
  #sg-do .tick .stamp{flex:0 0 44px;width:44px;height:44px;padding:9px;border-radius:11px;
    background:rgba(46,154,140,.12);color:var(--stamp-text);box-sizing:border-box}

  /* FAQ — tinted panel accordion (matches services / money pages) */
  .faq-e{background:var(--paper)}
  .faq-e .sec-head{text-align:center;max-width:60ch;margin-left:auto;margin-right:auto}
  .faq-panel{background:var(--white);border:1px solid var(--paper-edge);border-radius:18px;padding:6px 30px;
    max-width:80ch;margin:24px auto 0;box-shadow:0 16px 40px -30px rgba(40,50,70,.5)}
  .faqd{max-width:none}
  .faqd details{border-bottom:1px solid var(--paper-edge);padding:18px 0}
  .faqd details:last-child{border-bottom:0}
  .faqd summary{font-family:var(--display);font-size:19px;color:var(--navy);font-weight:600;cursor:pointer;
    list-style:none;display:flex;justify-content:space-between;align-items:center;gap:16px}
  .faqd summary::-webkit-details-marker{display:none}
  .faqd summary::after{content:"+";font-size:22px;color:var(--cta);flex:0 0 auto;font-weight:700;transition:transform .15s ease}
  .faqd details[open] summary::after{content:"\2013"}
  .faqd p{margin:12px 0 0;color:#3a4b55;font-size:16px;line-height:1.65}

  /* Final CTA WhatsApp button glyph */
  .cta-band .row .btn{display:inline-flex;align-items:center;gap:8px}
  .cta-band .row .wa-g{width:18px;height:18px;fill:currentColor;flex:none}
</style>
@endpush

@section('content')

{{-- 1) HERO — soft-sky centred, WhatsApp + free-checker CTAs --}}
<section class="sg-hero"><div class="wrap">
  <p class="eyebrow">Schengen visa</p>
  <h1>Your Schengen visa, sorted from the UK</h1>
  <p class="lede">One visa for most of Europe, prepared and checked by a real UK team. We review every document, complete the forms and book your appointment, so a small mistake does not cost you the trip.</p>
  <div class="row">
    <a class="btn btn--wa" href="{{ $waLink }}" target="_blank" rel="noopener">{!! $waGlyph !!} Chat on WhatsApp</a>
    <a class="btn btn--ghost" href="{{ url('/tools') }}">Run the free checker</a>
  </div>
  @include('partials.trustpilot-cta', ['align' => 'center', 'margin' => '20px 0 0'])
</div></section>

{{-- 2) TRUST BAND — dark mesh band (.tbar-f), 3 compliance points --}}
<section class="tbar-f"><div class="wrap"><div class="row">
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 5 6v6c0 4.5 3 7.5 7 8.5 4-1 7-4 7-8.5V6z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="m9 12 2 2 4-4.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg><span><b>Independent</b> UK team</span></span>
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 21h18M5 21V9l7-5 7 5v12M9 21v-6h6v6" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg><span><b>Not</b> a government website</span></span>
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 7v10M9.5 9.2c0-1 1.1-1.7 2.5-1.7s2.5.7 2.5 1.7-1.1 1.6-2.5 1.6-2.5.7-2.5 1.7 1.1 1.7 2.5 1.7 2.5-.7 2.5-1.7" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg><span>Service fee <b>separate</b> from the embassy fee</span></span>
</div></div></section>

{{-- 3) WHAT A SCHENGEN VISA COVERS --}}
<section id="sg-covers"><div class="wrap">
  <div class="sec-head reveal">
    <p class="eyebrow">The basics</p>
    <h2>What a Schengen visa covers</h2>
    <p class="lede">One short-stay visa lets you travel across the whole Schengen Area, 29 European countries, for up to 90 days in any 180-day period. It is for tourism, visiting family or friends, and most business trips. You apply to one embassy, then move freely between the countries once you are in.</p>
  </div>
  <ul class="sg-chips">
    <li><span class="dot" aria-hidden="true"></span>France</li>
    <li><span class="dot" aria-hidden="true"></span>Spain</li>
    <li><span class="dot" aria-hidden="true"></span>Italy</li>
    <li><span class="dot" aria-hidden="true"></span>Germany</li>
    <li><span class="dot" aria-hidden="true"></span>Greece</li>
    <li><span class="dot" aria-hidden="true"></span>Netherlands</li>
    <li class="more">and 23 more</li>
  </ul>
</div></section>

{{-- 4) WHAT WE DO — six-service stamp grid (.ticks / .tick / #ukv-stamp) --}}
<section id="sg-do"><div class="wrap">
  <div class="sec-head reveal">
    <p class="eyebrow">What we do</p>
    <h2>Everything your Schengen application needs</h2>
    <p class="lede">End-to-end help, from checking you can apply to the embassy door.</p>
  </div>
  <div class="ticks">
    <div class="tick reveal"><svg class="stamp" width="38" height="38" viewBox="0 0 48 48" role="img" aria-label="Checked"><use href="#ukv-stamp"></use></svg><div><h3>Eligibility check</h3><p>We confirm you can apply, and on the right visa, before you spend anything.</p></div></div>
    <div class="tick reveal"><svg class="stamp" width="38" height="38" viewBox="0 0 48 48" role="img" aria-label="Checked"><use href="#ukv-stamp"></use></svg><div><h3>Document review</h3><p>Every document checked by hand against the current embassy rules.</p></div></div>
    <div class="tick reveal"><svg class="stamp" width="38" height="38" viewBox="0 0 48 48" role="img" aria-label="Checked"><use href="#ukv-stamp"></use></svg><div><h3>Form completion</h3><p>We fill and check the application so small errors do not creep in.</p></div></div>
    <div class="tick reveal"><svg class="stamp" width="38" height="38" viewBox="0 0 48 48" role="img" aria-label="Checked"><use href="#ukv-stamp"></use></svg><div><h3>Appointment booking</h3><p>We find a biometric slot in time for your travel date.</p></div></div>
    <div class="tick reveal"><svg class="stamp" width="38" height="38" viewBox="0 0 48 48" role="img" aria-label="Checked"><use href="#ukv-stamp"></use></svg><div><h3>Submission &amp; follow-up</h3><p>We track your application and keep you posted at every step.</p></div></div>
    <div class="tick reveal"><svg class="stamp" width="38" height="38" viewBox="0 0 48 48" role="img" aria-label="Checked"><use href="#ukv-stamp"></use></svg><div><h3>Refused before?</h3><p>We work out why and fix it before you reapply.</p></div></div>
  </div>
</div></section>

{{-- 5) HOW IT WORKS — shared .steps --}}
<section id="how"><div class="wrap">
  <div class="sec-head reveal" style="text-align:center;max-width:60ch;margin:0 auto 36px">
    <p class="eyebrow">How it works</p>
    <h2>Four simple steps to your appointment</h2>
    <p class="lede" style="margin:12px auto 0;max-width:54ch">A straightforward process with a real UK specialist guiding you at every stage.</p>
  </div>
  <div class="steps">
    <div class="step reveal"><div class="num">01</div><div class="rule"></div><h3>Tell us about your trip</h3><p>Where you are going, when, and why. No card and no account to get started.</p></div>
    <div class="step reveal"><div class="num">02</div><div class="rule"></div><h3>We prepare and check everything</h3><p>Documents, forms and the details that get people refused, all reviewed by hand.</p></div>
    <div class="step reveal"><div class="num">03</div><div class="rule"></div><h3>You attend the appointment</h3><p>We book a biometric slot in time and tell you exactly what to bring.</p></div>
    <div class="step reveal"><div class="num">04</div><div class="rule"></div><h3>We track it to a decision</h3><p>We follow your application and keep you posted until the embassy decides.</p></div>
  </div>
  <div style="text-align:center;margin-top:28px"><a class="btn" href="{{ $waLink }}" target="_blank" rel="noopener">Tell us about your trip &rarr;</a></div>
</div></section>

{{-- 6) FAQ — tinted panel accordion (.faq-e / .faqd) --}}
<section id="faq" class="faq-e"><div class="wrap">
  <div class="sec-head reveal"><p class="eyebrow">Questions</p><h2>Schengen visa questions</h2></div>
  <div class="faq-panel reveal">
    <div class="faqd">
      <details><summary>Which country do I apply to?</summary><p>You apply to the country that is your main destination, where you will spend the most time. If you are spending equal time in several, you apply to the country you enter first. We help you work this out before you book anything.</p></details>
      <details><summary>How long does a Schengen visa take?</summary><p>Embassies usually decide within about 15 calendar days, though it can take longer in busy periods or if extra checks are needed. The biggest delay is often getting an appointment, so it is best to start early. We help you book in good time for your travel date.</p></details>
      <details><summary>What documents do I need?</summary><p>Typically a valid passport, recent photos, travel insurance with at least 25,000 euro of medical cover, proof of funds, your travel and accommodation plans, and proof of your ties to the UK such as a job letter or return ticket. We give you a checklist for your exact circumstances and check each item by hand.</p></details>
      <details><summary>Can you guarantee my visa will be approved?</summary><p>No, and you should be careful of anyone who says they can. The embassy makes the decision. What we do is remove the avoidable reasons they say no, by getting your documents and application right before you submit.</p></details>
      <details><summary>What does it cost?</summary><p>Our service fee is a clear amount shown before you pay anything. It is separate from the embassy visa fee, which is set by the authorities and paid to them. We tell you both so there are no surprises.</p></details>
      <details><summary>I was refused a Schengen visa before, can you help?</summary><p>Yes. A second refusal is harder, so the reapplication has to fix the real reason for the first no. We work out what went wrong and put it right before you reapply.</p></details>
    </div>
  </div>
</div></section>

{{-- 7) FINAL CTA — .cta-band, WhatsApp + free checker --}}
<section class="cta-band"><div class="wrap reveal">
  <div class="rule"></div>
  <h2>Tell us about your trip</h2>
  <p style="max-width:48ch;color:#eef0f1">Message our UK team on WhatsApp and we'll tell you exactly what your Schengen application needs, or run the free checker first.</p>
  <div class="row"><a href="{{ $waLink }}" target="_blank" rel="noopener" class="btn">{!! $waGlyph !!} Chat on WhatsApp</a><a href="{{ url('/tools') }}" class="btn btn--glass">Run the free checker</a></div>
</div></section>

@endsection
