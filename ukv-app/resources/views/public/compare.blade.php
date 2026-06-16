@extends('layouts.public')

@section('title', 'Apply Yourself vs Use Beyond Passports — Honest Comparison | Beyond Passports')
@section('description', 'An honest, balanced comparison of applying for your visa, eVisa, ETA or IDP yourself versus using Beyond Passports. Real trade-offs on cost, time, error-checking and support. Not a government website.')
@section('canonical', url('/compare'))

@push('head')
<style>
  /* compare page — page-local layout only. Design system lives in assets/ukv.css */
  .hero{padding:64px 0 40px}
  .hero h1{font-size:clamp(34px,5vw,56px);color:var(--navy);letter-spacing:-.015em;max-width:20ch}
  .hero p.lede{font-size:19px;max-width:48ch;color:#33454f;margin-top:14px}

  /* Comparison table — page-scoped, real table semantics */
  .cmp{
    --cmp-edge:var(--paper-edge);
    --cmp-head:var(--navy);
    --cmp-accent:var(--stamp);
    --cmp-zebra:#f6f9fa;
    --cmp-diy:#33454f;
    --cmp-us:var(--navy);
  }
  .cmp-scroll{overflow-x:auto;-webkit-overflow-scrolling:touch;border:1px solid var(--cmp-edge);border-radius:14px;background:var(--white);box-shadow:var(--shadow)}
  table.cmp-table{width:100%;border-collapse:collapse;min-width:640px;font-size:16px}
  table.cmp-table caption{text-align:left;font-family:var(--mono);font-size:13px;color:var(--muted);padding:14px 18px;border-bottom:1px solid var(--cmp-edge)}
  table.cmp-table th,table.cmp-table td{padding:18px 20px;text-align:left;vertical-align:top;line-height:1.55;border-bottom:1px solid var(--cmp-edge)}
  table.cmp-table thead th{background:var(--cmp-head);color:#fff;font-family:var(--display);font-weight:600;font-size:18px;letter-spacing:-.01em}
  table.cmp-table thead th:first-child{background:#08203a}
  table.cmp-table tbody th[scope=row]{font-family:var(--display);font-weight:600;color:var(--navy);font-size:16px;width:22%;background:var(--cmp-zebra)}
  table.cmp-table td:nth-child(2){color:var(--cmp-diy)}
  table.cmp-table td:nth-child(3){color:var(--cmp-us);background:#f4f9f9}
  table.cmp-table tbody tr:last-child th,table.cmp-table tbody tr:last-child td{border-bottom:0}
  .cmp-tag{display:inline-block;font-family:var(--mono);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;padding:2px 8px;border-radius:99px;margin-bottom:6px}
  .cmp-tag--free{background:#eaf3ea;color:#2f6b3a}
  .cmp-tag--fee{background:#f3eddb;color:#8a6a14}
  .cmp-note{font-family:var(--mono);font-size:13px;color:var(--stamp);display:block;margin-top:6px}

  /* Balanced two-up blocks */
  .balance{display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:8px}
  .balance .card{background:var(--white);border:1px solid var(--paper-edge);border-radius:14px;padding:26px 26px 28px}
  .balance .card.diy{border-top:3px solid var(--muted)}
  .balance .card.us{border-top:3px solid var(--stamp)}
  .balance .card h3{font-family:var(--display);font-size:21px;color:var(--navy);margin:0 0 6px;letter-spacing:-.01em}
  .balance .card .kicker{font-family:var(--mono);font-size:12px;text-transform:uppercase;letter-spacing:.08em;color:var(--muted);margin:0 0 14px}
  .balance .card ul{list-style:none;margin:0;padding:0}
  .balance .card li{position:relative;padding-left:24px;font-size:16px;line-height:1.55;color:#33454f;margin:0 0 12px}
  .balance .card li:last-child{margin-bottom:0}
  .balance .card li::before{content:"";position:absolute;left:0;top:8px;width:9px;height:9px;border-radius:2px;background:var(--gold);transform:rotate(45deg)}
  .honest-note{max-width:70ch;margin-top:26px;font-family:var(--mono);font-size:14px;color:var(--stamp);background:var(--white);border:1px solid var(--paper-edge);border-left:3px solid var(--gold);padding:14px 16px;line-height:1.55}

  @media (max-width:760px){
    .balance{grid-template-columns:1fr}
  }
</style>
@endpush

@section('content')

{{-- HERO --}}
<section class="hero"><div class="wrap">
  <p class="eyebrow">Honest comparison</p>
  <h1>Apply yourself vs use Beyond Passports</h1>
  <p class="lede">Both are valid. Here's the real trade-off — no spin.</p>
</div></section>
<div class="mrz"><div class="wrap"><span>P&lt;GBR&lt;DIY&lt;&lt;OR&lt;&lt;UKVISACO&lt;&lt;HONEST&lt;COMPARISON&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;</span></div></div>

{{-- COMPARISON TABLE --}}
<section id="compare"><div class="wrap cmp">
  <div class="sec-head reveal"><p class="eyebrow">Side by side</p><h2>The honest comparison</h2></div>
  <div class="cmp-scroll reveal" tabindex="0" role="region" aria-label="Comparison of applying yourself versus using Beyond Passports">
    <table class="cmp-table">
      <caption>Doing it yourself vs using Beyond Passports — scroll sideways on small screens.</caption>
      <thead>
        <tr>
          <th scope="col">What matters</th>
          <th scope="col">Do it yourself</th>
          <th scope="col">Beyond Passports</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <th scope="row">Cost</th>
          <td><span class="cmp-tag cmp-tag--free">Cheapest</span><br>You pay only the official government or embassy fee — nothing extra. This is always the lower-cost route.</td>
          <td><span class="cmp-tag cmp-tag--fee">Fee on top</span><br>You pay the same government fee <strong>plus</strong> our service fee on top, for the checking and support. We show both separately before you pay.</td>
        </tr>
        <tr>
          <th scope="row">Time &amp; effort</th>
          <td>You read the rules, fill the forms and gather documents yourself. Straightforward for confident travellers; fiddly if the rules are unclear.</td>
          <td>We do the legwork and tell you exactly what to provide. Less of your time, but you hand over some control of the process.</td>
        </tr>
        <tr>
          <th scope="row">Error-checking</th>
          <td>You check your own application. Most people get it right — but a wrong date or name format can cause delays or a rejected form.</td>
          <td>A real person reviews your details before submission, so common mistakes are caught early.</td>
        </tr>
        <tr>
          <th scope="row">Support if something goes wrong</th>
          <td>You rely on the authority's own help channels, which can be slow or hard to reach.</td>
          <td>A UK team on the phone and WhatsApp helps you sort issues and resubmit if needed.</td>
        </tr>
        <tr>
          <th scope="row">Speed of <em>your</em> paperwork</th>
          <td>As fast as you can complete it. No middle step.</td>
          <td>Express options speed up <strong>our</strong> handling and submission.<span class="cmp-note">Neither route changes the government's own decision time.</span></td>
        </tr>
        <tr>
          <th scope="row">Who it suits</th>
          <td>Confident travellers, simple applications, anyone who'd rather keep the cost down and has time to do it.</td>
          <td>Busy people, complex or first-time applications, or anyone who'd rather have a person check it and answer questions.</td>
        </tr>
      </tbody>
    </table>
  </div>
  <p class="honest-note">Straight talk: applying yourself is always the cheapest option, and for many trips it's perfectly easy. We charge a service fee on top of the official fee — so only use us if the time saved and the extra checking are worth it to you. Neither route, and no express option, makes a government or embassy approve faster or guarantees any outcome.</p>
</div></section>

{{-- BALANCED BLOCKS --}}
<section id="when" class="alt"><div class="wrap">
  <div class="sec-head reveal"><p class="eyebrow">Be honest with yourself</p><h2>When each route is the better choice</h2></div>
  <div class="balance">
    <div class="card diy reveal">
      <p class="kicker">When DIY is the better choice</p>
      <h3>Do it yourself if…</h3>
      <ul>
        <li>Your application is simple and you've done one before.</li>
        <li>You want the lowest possible cost — DIY only ever costs the official fee.</li>
        <li>You have time to read the rules and double-check your own details.</li>
        <li>The requirements for your destination are clear and you're confident.</li>
        <li>You'd rather keep full control of every step yourself.</li>
      </ul>
    </div>
    <div class="card us reveal">
      <p class="kicker">When we're worth it</p>
      <h3>Use Beyond Passports if…</h3>
      <ul>
        <li>You're short on time and would rather not navigate the forms.</li>
        <li>It's your first application, or the rules feel confusing.</li>
        <li>The trip matters and you want a person to check before it's submitted.</li>
        <li>You want someone on the phone or WhatsApp if a problem comes up.</li>
        <li>The cost of our fee is worth the time and worry it saves you.</li>
      </ul>
    </div>
  </div>
  <p class="honest-note">If you read both columns and DIY clearly fits you better — do that. We'd rather you saved the money than paid us for something you don't need. Ask us and we'll tell you honestly.</p>
</div></section>

{{-- HOW USING US WORKS --}}
<section id="how"><div class="wrap">
  <div class="sec-head reveal"><p class="eyebrow">If you choose us</p><h2>How using Beyond Passports works</h2></div>
  <div class="steps">
    <div class="step reveal"><div class="num">01</div><div class="rule"></div><h3>We check</h3><p>Tell us your trip and passport. We confirm what you need — and say plainly if you'd be better off applying yourself.</p></div>
    <div class="step reveal"><div class="num">02</div><div class="rule"></div><h3>We prepare</h3><p>A real person reviews and prepares your documents, catching errors before anything goes near a government portal.</p></div>
    <div class="step reveal"><div class="num">03</div><div class="rule"></div><h3>We submit &amp; track</h3><p>We handle the submission and keep you updated until your authorisation comes through. The decision still rests with the authority.</p></div>
  </div>
</div></section>

{{-- FAQ --}}
<section id="faq" class="alt"><div class="wrap">
  <div class="sec-head reveal" style="margin:0 auto 36px;text-align:center"><p class="eyebrow">Good to know</p><h2>Frequently asked questions</h2></div>
  <div class="faqs">
    <details class="faq reveal">
      <summary>Is this a government service?</summary>
      <div class="a"><p>No. Beyond Passports is an independent, private service — not a government website and not affiliated with gov.uk, any embassy or any official authority. You can always apply directly with the relevant authority yourself; using us is entirely optional.</p></div>
    </details>
    <details class="faq reveal">
      <summary>Do I pay more by using you?</summary>
      <div class="a"><p>Yes — and we won't pretend otherwise. The official government or embassy fee is the same whichever route you take. Our service fee is charged <strong>on top</strong> of that official fee, and pays for the checking, preparation and support we provide. We show both fees separately before you pay, so there are no surprises. Applying yourself is always the lower-cost option.</p></div>
    </details>
    <details class="faq reveal">
      <summary>Does express get me approved faster?</summary>
      <div class="a"><p>No. An express option speeds up <strong>our</strong> handling and submission of your application — it does not make a government or embassy decide any faster, and it cannot guarantee approval. The decision, and how long it takes, is entirely down to the relevant authority.</p></div>
    </details>
  </div>
</div></section>

{{-- CTA --}}
<section class="cta-band"><div class="wrap reveal">
  <div class="rule"></div>
  <h2>Decide what suits you — or ask us, we'll be honest</h2>
  <p style="max-width:50ch;color:#cdd9e1">Happy to do it yourself? Brilliant — go for it. Want a person to check it and save you the hassle? Start your application, or message us first and we'll tell you straight whether you need us.</p>
  <div class="row"><a href="{{ url('/apply') }}" class="btn">Start my application →</a><a href="https://wa.me/{{ config('ukv.whatsapp') ?: '440000000000' }}" class="btn btn--wa">Ask us first on WhatsApp</a></div>
</div></section>

{{-- FAQPage structured data --}}
@verbatim
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "Is this a government service?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "No. Beyond Passports is an independent, private service — not a government website and not affiliated with gov.uk, any embassy or any official authority. You can always apply directly with the relevant authority yourself; using us is entirely optional."
      }
    },
    {
      "@type": "Question",
      "name": "Do I pay more by using you?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Yes. The official government or embassy fee is the same whichever route you take. Our service fee is charged on top of that official fee, and pays for the checking, preparation and support we provide. We show both fees separately before you pay. Applying yourself is always the lower-cost option."
      }
    },
    {
      "@type": "Question",
      "name": "Does express get me approved faster?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "No. An express option speeds up our handling and submission of your application — it does not make a government or embassy decide any faster, and it cannot guarantee approval. The decision, and how long it takes, is entirely down to the relevant authority."
      }
    }
  ]
}
</script>
@endverbatim
@endsection
