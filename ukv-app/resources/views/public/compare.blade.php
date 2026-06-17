@extends('layouts.public')

@section('title', 'Apply Yourself vs Use Beyond Passports — Honest Comparison | Beyond Passports')
@section('description', 'An honest, balanced comparison of applying for your visa, eVisa, ETA or IDP yourself versus using Beyond Passports. Real trade-offs on cost, time, error-checking and support. Not a government website.')
@section('canonical', url('/compare'))

@push('head')
<style>
  /* ── compare page — page-scoped styles only. Design system in ukv.css ───── */

  /* ── Hero — navy mesh split + side-by-side scale (pick B) ────────────────── */
  .cmp-hero{position:relative;overflow:hidden;background:var(--navy);color:#fff;padding:92px 0 84px}
  .cmp-hero::before{content:"";position:absolute;inset:0;background:
     radial-gradient(60% 80% at 12% 18%,rgba(199,93,56,.40),transparent 60%),
     radial-gradient(55% 75% at 88% 82%,rgba(63,114,89,.42),transparent 62%),
     radial-gradient(40% 60% at 70% 10%,rgba(242,194,172,.18),transparent 60%);}
  .cmp-hero::after{content:"";position:absolute;inset:0;background:linear-gradient(180deg,rgba(15,20,22,.10),rgba(15,20,22,.35));}
  .cmp-hero .wrap{position:relative;z-index:2}
  .cmp-hero .mh-grid{display:grid;grid-template-columns:1.1fr .9fr;gap:48px;align-items:center}
  .cmp-hero .eyebrow{color:var(--soft)}
  .cmp-hero h1{color:#fff;max-width:15ch}
  .cmp-hero .lede{color:rgba(255,255,255,.82);max-width:46ch}
  .cmp-scale{display:grid;grid-template-columns:1fr 1fr;gap:14px}
  .cmp-scol{border-radius:16px;padding:20px 18px;border:1px solid rgba(255,255,255,.16);background:rgba(255,255,255,.05)}
  .cmp-scol.us{background:linear-gradient(180deg,rgba(199,93,56,.22),rgba(255,255,255,.05));border-color:rgba(242,194,172,.45)}
  .cmp-scol .k{font-family:var(--display);font-size:11px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--soft);margin:0 0 12px}
  .cmp-scol.diy .k{color:rgba(255,255,255,.6)}
  .cmp-scol p{margin:0 0 10px;font-size:13.5px;line-height:1.4;color:rgba(255,255,255,.85);display:flex;gap:8px}
  .cmp-scol p:last-child{margin-bottom:0}
  .cmp-scol p svg{width:15px;height:15px;flex:0 0 15px;margin-top:1px}
  .cmp-scol .tick{color:#7fd6a8}
  .cmp-scol .dash{color:rgba(255,255,255,.45)}
  @media (max-width:820px){.cmp-hero .mh-grid{grid-template-columns:1fr;gap:30px}}

  /* ── Comparison table ────────────────────────────────────────────────────── */
  .cmp-scroll {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    border: 1px solid var(--paper-edge);
    border-radius: 18px;
    background: var(--white);
    box-shadow: var(--lift-2);
  }
  table.cmp-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 640px;
    font-size: 15.5px;
  }
  table.cmp-table caption {
    text-align: left;
    font-size: 13px;
    color: var(--muted);
    padding: 14px 20px;
    border-bottom: 1px solid var(--paper-edge);
  }
  table.cmp-table th,
  table.cmp-table td {
    padding: 20px 22px;
    text-align: left;
    vertical-align: top;
    line-height: 1.6;
    border-bottom: 1px solid var(--paper-edge);
  }
  /* Header row */
  table.cmp-table thead tr {
    background: var(--navy);
  }
  table.cmp-table thead th {
    color: #fff;
    font-family: var(--display);
    font-weight: 700;
    font-size: 17px;
    letter-spacing: -.01em;
  }
  table.cmp-table thead th:first-child {
    background: #171b1d;
    color: rgba(255,255,255,.7);
    font-size: 13px;
    font-weight: 600;
    letter-spacing: .06em;
    text-transform: uppercase;
  }
  /* "Beyond Passports" column header — terracotta accent */
  table.cmp-table thead th:last-child {
    position: relative;
  }
  table.cmp-table thead th:last-child::after {
    content: "";
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 3px;
    background: var(--cta);
  }
  /* Row header cells */
  table.cmp-table tbody th[scope=row] {
    font-family: var(--display);
    font-weight: 700;
    color: var(--navy);
    font-size: 15px;
    width: 22%;
    background: #f8f9fa;
    vertical-align: middle;
  }
  /* DIY column */
  table.cmp-table td:nth-child(2) { color: #33454f; }
  /* Beyond Passports column — subtle sage tint */
  table.cmp-table td:nth-child(3) {
    color: var(--navy);
    background: #f4f9f7;
  }
  /* Last row — no bottom border */
  table.cmp-table tbody tr:last-child th,
  table.cmp-table tbody tr:last-child td { border-bottom: 0; }
  /* Hover state on rows */
  table.cmp-table tbody tr { transition: background .12s ease; }
  table.cmp-table tbody tr:hover td:nth-child(2) { background: #f6f8f9; }
  table.cmp-table tbody tr:hover td:nth-child(3) { background: #eef6f2; }

  /* Pill tags */
  .cmp-tag {
    display: inline-block;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    padding: 3px 10px;
    border-radius: 999px;
    margin-bottom: 8px;
  }
  .cmp-tag--free { background: #e8f5e9; color: #2e6b38; }
  .cmp-tag--fee  { background: #fff3e0; color: #8a5a14; }
  /* Footnote in a cell */
  .cmp-note {
    display: block;
    margin-top: 7px;
    font-size: 13px;
    color: var(--stamp-text);
    font-style: italic;
  }

  /* Honest note block (below table + below balance) */
  .cmp-honest {
    max-width: 72ch;
    margin-top: 28px;
    background: var(--white);
    border: 1px solid var(--paper-edge);
    border-left: 4px solid var(--cta);
    border-radius: 0 14px 14px 0;
    padding: 16px 20px;
    font-size: 14px;
    color: var(--stamp-text);
    line-height: 1.6;
  }

  /* ── Balanced two-up blocks ──────────────────────────────────────────────── */
  .cmp-balance {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    margin-top: 8px;
  }
  .cmp-card {
    background: var(--white);
    border: 1px solid var(--paper-edge);
    border-radius: 18px;
    padding: 28px 28px 30px;
    box-shadow: var(--lift-1);
    transition: transform .25s ease, box-shadow .25s ease;
  }
  .cmp-card:hover { transform: translateY(-3px); box-shadow: var(--lift-2); }
  .cmp-card.diy { border-top: 4px solid var(--muted); }
  .cmp-card.us  { border-top: 4px solid var(--stamp-text); }
  .cmp-card h3 {
    font-family: var(--display);
    font-size: 21px;
    color: var(--navy);
    margin: 0 0 6px;
    letter-spacing: -.01em;
  }
  .cmp-card .cmp-kicker {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: var(--muted);
    margin: 0 0 16px;
    display: block;
  }
  .cmp-card.us .cmp-kicker { color: var(--stamp-text); }
  .cmp-card ul { list-style: none; margin: 0; padding: 0; }
  .cmp-card li {
    position: relative;
    padding-left: 26px;
    font-size: 15.5px;
    line-height: 1.55;
    color: #33454f;
    margin: 0 0 13px;
  }
  .cmp-card li:last-child { margin-bottom: 0; }
  .cmp-card li::before {
    content: "";
    position: absolute;
    left: 0; top: 8px;
    width: 9px; height: 9px;
    border-radius: 2px;
    background: var(--muted);
    transform: rotate(45deg);
  }
  .cmp-card.us li::before { background: var(--cta); }

  /* ── FAQ accordion — inherit .faqs/.faq from ukv.css ────────────────────── */
  /* (all base styles inherited, no overrides needed) */

  @media (max-width: 760px) {
    .cmp-balance { grid-template-columns: 1fr; }
  }
</style>
@endpush

@section('content')

{{-- HERO — navy mesh split + side-by-side scale (pick B) --}}
<section class="cmp-hero"><div class="wrap"><div class="mh-grid">
  <div class="reveal">
    <p class="eyebrow">Honest comparison</p>
    <h1>Apply yourself, or let us handle it</h1>
    <p class="lede">Both routes are valid. We lay out the real trade-off so you can choose — and tell you straight if doing it yourself suits you better.</p>
  </div>
  <div class="cmp-scale reveal">
    <div class="cmp-scol diy">
      <p class="k">Do it yourself</p>
      <p><svg class="tick" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg> Cheapest — official fee only</p>
      <p><svg class="dash" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="M5 12h14"/></svg> You do the legwork</p>
      <p><svg class="dash" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="M5 12h14"/></svg> You check it yourself</p>
    </div>
    <div class="cmp-scol us">
      <p class="k">Beyond Passports</p>
      <p><svg class="tick" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg> A person checks it</p>
      <p><svg class="tick" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg> UK phone &amp; WhatsApp</p>
      <p><svg class="dash" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="M5 12h14"/></svg> Service fee on top</p>
    </div>
  </div>
</div></div></section>

{{-- COMPARISON TABLE --}}
<section id="compare"><div class="wrap">
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
  <p class="cmp-honest">Straight talk: applying yourself is always the cheapest option, and for many trips it's perfectly easy. We charge a service fee on top of the official fee — so only use us if the time saved and the extra checking are worth it to you. Neither route, and no express option, makes a government or embassy approve faster or guarantees any outcome.</p>
</div></section>

{{-- BALANCED BLOCKS --}}
<section id="when" class="alt"><div class="wrap">
  <div class="sec-head reveal"><p class="eyebrow">Be honest with yourself</p><h2>When each route is the better choice</h2></div>
  <div class="cmp-balance">
    <div class="cmp-card diy reveal">
      <span class="cmp-kicker">When DIY is the better choice</span>
      <h3>Do it yourself if…</h3>
      <ul>
        <li>Your application is simple and you've done one before.</li>
        <li>You want the lowest possible cost — DIY only ever costs the official fee.</li>
        <li>You have time to read the rules and double-check your own details.</li>
        <li>The requirements for your destination are clear and you're confident.</li>
        <li>You'd rather keep full control of every step yourself.</li>
      </ul>
    </div>
    <div class="cmp-card us reveal">
      <span class="cmp-kicker">When we're worth it</span>
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
  <p class="cmp-honest">If you read both columns and DIY clearly fits you better — do that. We'd rather you saved the money than paid us for something you don't need. Ask us and we'll tell you honestly.</p>
</div></section>

{{-- HOW USING US WORKS --}}
<section id="how"><div class="wrap">
  <div class="sec-head reveal"><p class="eyebrow">If you choose us</p><h2>How using Beyond Passports works</h2></div>
  <div class="steps reveal">
    <div class="step"><div class="num">01</div><div class="rule"></div><h3>We check</h3><p>Tell us your trip and passport. We confirm what you need — and say plainly if you'd be better off applying yourself.</p></div>
    <div class="step"><div class="num">02</div><div class="rule"></div><h3>We prepare</h3><p>A real person reviews and prepares your documents, catching errors before anything goes near a government portal.</p></div>
    <div class="step"><div class="num">03</div><div class="rule"></div><h3>We submit &amp; track</h3><p>We handle the submission and keep you updated until your authorisation comes through. The decision still rests with the authority.</p></div>
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
  <p style="max-width:50ch;color:#eef0f1">Happy to do it yourself? Brilliant — go for it. Want a person to check it and save you the hassle? Start your application, or message us first and we'll tell you straight whether you need us.</p>
  <div class="row"><a href="{{ url('/apply') }}" class="btn">Start my application &rarr;</a><a href="https://wa.me/{{ config('ukv.whatsapp') ?: '440000000000' }}" class="btn btn--wa">Ask us first on WhatsApp</a></div>
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
