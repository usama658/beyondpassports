{{-- Compare body. Extracted verbatim from public/compare.blade.php so the coded route and the
     CMS locked-include render byte-identical markup + page-scoped CSS. Edit here, both update. --}}
<style>
  /* ── compare page — page-scoped styles only. Design system in ukv.css ───── */

  /* ── Hero — navy mesh split + side-by-side scale (pick B) ────────────────── */
  .cmp-hero{position:relative;overflow:hidden;background:var(--navy);color:#fff;padding:92px 0 84px}
  .cmp-hero::before{content:"";position:absolute;inset:0;background:
     radial-gradient(60% 80% at 12% 18%,rgba(21,94,122,.40),transparent 60%),
     radial-gradient(55% 75% at 88% 82%,rgba(63,114,89,.42),transparent 62%),
     radial-gradient(40% 60% at 70% 10%,rgba(169,204,218,.18),transparent 60%);}
  .cmp-hero::after{content:"";position:absolute;inset:0;background:linear-gradient(180deg,rgba(15,20,22,.10),rgba(15,20,22,.35));}
  .cmp-hero .wrap{position:relative;z-index:2}
  .cmp-hero .mh-grid{display:grid;grid-template-columns:1.1fr .9fr;gap:48px;align-items:center}
  .cmp-hero .eyebrow{color:var(--soft)}
  .cmp-hero h1{color:#fff;max-width:15ch}
  .cmp-hero .lede{color:rgba(255,255,255,.82);max-width:46ch}
  .cmp-scale{display:grid;grid-template-columns:1fr 1fr;gap:14px}
  .cmp-scol{border-radius:16px;padding:20px 18px;border:1px solid rgba(255,255,255,.16);background:rgba(255,255,255,.05)}
  .cmp-scol.us{background:linear-gradient(180deg,rgba(21,94,122,.22),rgba(255,255,255,.05));border-color:rgba(169,204,218,.45)}
  .cmp-scol .k{font-family:var(--display);font-size:11px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--soft);margin:0 0 12px}
  .cmp-scol.diy .k{color:rgba(255,255,255,.6)}
  .cmp-scol p{margin:0 0 10px;font-size:13.5px;line-height:1.4;color:rgba(255,255,255,.85);display:flex;gap:8px}
  .cmp-scol p:last-child{margin-bottom:0}
  .cmp-scol p svg{width:15px;height:15px;flex:0 0 15px;margin-top:1px}
  .cmp-scol .tick{color:#7fd6a8}
  .cmp-scol .dash{color:rgba(255,255,255,.45)}
  @media (max-width:820px){.cmp-hero .mh-grid{grid-template-columns:1fr;gap:30px}}

  /* ── Comparison — two route cards (pick B) ───────────────────────────────── */
  .cmp-cards{display:grid;grid-template-columns:1fr 1fr;gap:24px;align-items:start}
  .cmp-tcard{border:1px solid var(--paper-edge);border-radius:22px;overflow:hidden;background:var(--white);box-shadow:0 18px 46px -34px rgba(40,50,70,.5)}
  .cmp-tcard.us{border-color:#cfddea;box-shadow:0 28px 60px -34px rgba(21,94,122,.45)}
  @media (min-width:861px){.cmp-tcard.us{transform:translateY(-10px)}}
  .cmp-thead{padding:26px 28px 22px;border-bottom:1px solid var(--paper-edge)}
  .cmp-tcard.us .cmp-thead{background:linear-gradient(180deg,#eaf1f6,var(--white))}
  .cmp-thead .k{font-family:var(--body);font-size:11px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--muted);margin:0 0 8px}
  .cmp-tcard.us .cmp-thead .k{color:var(--cta)}
  .cmp-thead h3{font-family:var(--display);font-size:23px;margin:0 0 6px;letter-spacing:-.01em;color:var(--navy)}
  .cmp-thead p{margin:0;color:var(--muted);font-size:14px;line-height:1.5}
  .cmp-rib{display:inline-block;background:var(--cta);color:#fff;font-family:var(--body);font-size:10.5px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;border-radius:999px;padding:4px 11px;margin-bottom:11px}
  .cmp-tbody{padding:8px 28px 26px}
  .cmp-trow{padding:16px 0;border-bottom:1px solid #eef0f1}
  .cmp-trow:last-child{border-bottom:0}
  .cmp-trow .lab{font-family:var(--body);font-size:11px;font-weight:800;letter-spacing:.07em;text-transform:uppercase;color:var(--muted);margin:0 0 6px}
  .cmp-trow .val{display:flex;gap:9px;font-size:14.5px;line-height:1.5;color:#33454f}
  .cmp-trow .val svg{width:18px;height:18px;flex:0 0 18px;margin-top:1px}
  .cmp-trow .val span{color:#33454f}
  .cmp-trow .val .tick{color:var(--stamp)}          /* brand sage — advantage */
  .cmp-trow .val .cross{color:#9aa1a8}              /* neutral grey — limitation, not alarm */
  .cmp-trow .val .neu{color:#c2c7cc}                /* light grey — trade-off */
  @media (max-width:860px){.cmp-cards{grid-template-columns:1fr}}
  /* Footnote in a cell */
  .cmp-note {
    display: block;
    margin-top: 7px;
    font-size: 13px;
    color: var(--stamp-text);
    font-style: italic;
  }

  /* Honest note block — navy straight-talk panel (pick B) */
  .cmp-honest{position:relative;overflow:hidden;background:var(--navy);color:#fff;border-radius:18px;padding:28px 32px;margin-top:32px;box-shadow:0 26px 56px -36px rgba(40,50,70,.7)}
  .cmp-honest::before{content:"";position:absolute;inset:0;background:radial-gradient(50% 90% at 6% 12%,rgba(21,94,122,.30),transparent 60%),radial-gradient(45% 80% at 96% 90%,rgba(46,154,140,.26),transparent 62%)}
  .cmp-honest .ch-in{position:relative;display:flex;gap:18px;align-items:flex-start}
  .cmp-honest .ch-ic{flex:0 0 46px;width:46px;height:46px;border-radius:13px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;color:var(--soft)}
  .cmp-honest .ch-ic svg{width:24px;height:24px}
  .cmp-honest .ch-lab{font-family:var(--body);font-size:11px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--soft);margin:0 0 8px}
  .cmp-honest p{margin:0;font-size:15.5px;line-height:1.65;color:rgba(255,255,255,.9)}
  .cmp-honest p strong{color:#fff}

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

  /* ── FAQ — money-page tinted-panel accordion (.faq-e / .faqd) ───────────── */
  .faq-e .faq-panel{background:var(--white);border:1px solid var(--paper-edge);border-radius:18px;padding:6px 30px;max-width:80ch;margin:0 auto;box-shadow:0 16px 40px -30px rgba(40,50,70,.5)}
  .faqd details{border-bottom:1px solid var(--paper-edge);padding:18px 0}
  .faqd details:last-child{border-bottom:0}
  .faqd summary{font-family:var(--display);font-size:19px;color:var(--navy);font-weight:600;cursor:pointer;list-style:none;display:flex;justify-content:space-between;align-items:center;gap:16px}
  .faqd summary::-webkit-details-marker{display:none}
  .faqd summary::after{content:"+";font-size:22px;color:var(--cta);flex:0 0 auto;font-weight:700;transition:transform .15s ease}
  .faqd details[open] summary::after{content:"–"}
  .faqd .a p{margin:12px 0 0;color:#3a4b55;font-size:16px;line-height:1.65}

  @media (max-width: 760px) {
    .cmp-balance { grid-template-columns: 1fr; }
  }
</style>

{{-- HERO — navy mesh split + side-by-side scale (pick B) --}}
<section class="cmp-hero"><div class="wrap"><div class="mh-grid">
  <div class="reveal">
    <p class="eyebrow">Honest comparison</p>
    <h1>Apply yourself, or let us handle it</h1>
    <p class="lede">Both routes are valid. We lay out the real trade-off so you can choose, and tell you straight if doing it yourself suits you better.</p>
    @include('partials.trustpilot-cta', ['align' => 'left', 'theme' => 'dark', 'margin' => '18px 0 0'])
  </div>
  <div class="cmp-scale reveal">
    <div class="cmp-scol diy">
      <p class="k">Do it yourself</p>
      <p><svg class="tick" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg> Cheapest: official fee only</p>
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
  @php
    $tick  = '<svg class="tick" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>';
    $cross = '<svg class="cross" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>';
    $neu   = '<svg class="neu" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="M5 12h14"/></svg>';
  @endphp
  <div class="cmp-cards reveal">
    {{-- Route 1 — DIY --}}
    <div class="cmp-tcard diy" role="region" aria-label="Doing it yourself">
      <div class="cmp-thead">
        <p class="k">Route 1</p>
        <h3>Do it yourself</h3>
        <p>Lowest cost. Best when your application is simple and you have time to do it.</p>
      </div>
      <div class="cmp-tbody">
        <div class="cmp-trow"><p class="lab">Cost</p><div class="val">{!! $tick !!}<span>Only the official government or embassy fee. Always the lower-cost route.</span></div></div>
        <div class="cmp-trow"><p class="lab">Time &amp; effort</p><div class="val">{!! $neu !!}<span>You read the rules, fill the forms and gather documents yourself.</span></div></div>
        <div class="cmp-trow"><p class="lab">Error-checking</p><div class="val">{!! $cross !!}<span>You check your own application. A wrong date, name or unclear document is one of the avoidable things that get applications refused.</span></div></div>
        <div class="cmp-trow"><p class="lab">Support if something goes wrong</p><div class="val">{!! $cross !!}<span>The authority's own help channels, which can be slow or hard to reach.</span></div></div>
        <div class="cmp-trow"><p class="lab">Speed of <em>your</em> paperwork</p><div class="val">{!! $tick !!}<span>As fast as you can complete it. No middle step.</span></div></div>
        <div class="cmp-trow"><p class="lab">Who it suits</p><div class="val">{!! $neu !!}<span>Confident travellers and simple applications, if you'd rather keep the cost down.</span></div></div>
      </div>
    </div>
    {{-- Route 2 — Beyond Passports --}}
    <div class="cmp-tcard us" role="region" aria-label="Using Beyond Passports">
      <div class="cmp-thead">
        <span class="cmp-rib">Most support</span>
        <p class="k">Route 2</p>
        <h3>Beyond Passports</h3>
        <p>A real person checks and submits it, with UK support if something goes wrong.</p>
      </div>
      <div class="cmp-tbody">
        <div class="cmp-trow"><p class="lab">Cost</p><div class="val">{!! $cross !!}<span>The same government fee <strong>plus</strong> our service fee on top, shown separately before you pay.</span></div></div>
        <div class="cmp-trow"><p class="lab">Time &amp; effort</p><div class="val">{!! $tick !!}<span>We do the legwork and tell you exactly what to provide.</span></div></div>
        <div class="cmp-trow"><p class="lab">Error-checking</p><div class="val">{!! $tick !!}<span>A real person reviews your whole file before submission, removing the avoidable causes of refusal before it's submitted.</span></div></div>
        <div class="cmp-trow"><p class="lab">Support if something goes wrong</p><div class="val">{!! $tick !!}<span>A UK team on the phone and WhatsApp helps you sort issues and resubmit.</span></div></div>
        <div class="cmp-trow"><p class="lab">Speed of <em>your</em> paperwork</p><div class="val">{!! $tick !!}<span>Express speeds up <strong>our</strong> handling, not the government's own decision time.</span></div></div>
        <div class="cmp-trow"><p class="lab">Who it suits</p><div class="val">{!! $neu !!}<span>Busy people, first-time or complex applications, or anyone who wants it checked.</span></div></div>
      </div>
    </div>
  </div>
  <div class="cmp-honest reveal"><div class="ch-in">
    <span class="ch-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M12 2 4 5v6c0 5 3.5 8 8 11 4.5-3 8-6 8-11V5l-8-3z"/><path d="m9 12 2 2 4-4"/></svg></span>
    <div><p class="ch-lab">Straight talk</p>
    <p>Applying yourself is always the cheapest option, and for many trips it's perfectly easy. We charge a service fee <strong>on top</strong> of the official fee, so only use us if the time saved and the extra checking are worth it to you. Neither route, and no express option, makes a government or embassy approve faster or guarantees any outcome.</p></div>
  </div></div>
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
        <li>You want the lowest possible cost. DIY only ever costs the official fee.</li>
        <li>You have time to read the rules and double-check your own details.</li>
        <li>The requirements for your destination are clear and you're confident.</li>
        <li>You'd rather keep full control of every step yourself.</li>
      </ul>
    </div>
    <div class="cmp-card us reveal">
      <span class="cmp-kicker">When we're worth it</span>
      <h3>Use Beyond Passports if…</h3>
      <ul>
        <li>You're short on time and would rather not deal with the forms.</li>
        <li>It's your first application, or the rules feel confusing.</li>
        <li>The trip matters and you want a person to remove the avoidable causes of refusal before it's submitted.</li>
        <li>You want someone on the phone or WhatsApp if a problem comes up.</li>
        <li>The cost of our fee is worth the time and worry it saves you.</li>
      </ul>
    </div>
  </div>
  <div class="cmp-honest reveal"><div class="ch-in">
    <span class="ch-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M12 2 4 5v6c0 5 3.5 8 8 11 4.5-3 8-6 8-11V5l-8-3z"/><path d="m9 12 2 2 4-4"/></svg></span>
    <div><p class="ch-lab">No pressure</p>
    <p>If you read both columns and DIY clearly fits you better, do that. We'd rather you saved the money than paid us for something you don't need. Ask us and we'll tell you honestly.</p></div>
  </div></div>
</div></section>

{{-- HOW USING US WORKS --}}
<section id="how"><div class="wrap">
  <div class="sec-head reveal"><p class="eyebrow">If you choose us</p><h2>How using Beyond Passports works</h2></div>
  <div class="steps reveal">
    <div class="step"><div class="num">01</div><div class="rule"></div><h3>We check</h3><p>Tell us your trip and passport. We confirm what you need, and say plainly if you'd be better off applying yourself.</p></div>
    <div class="step"><div class="num">02</div><div class="rule"></div><h3>We prepare</h3><p>A real person reviews your documents for the things that actually get applications refused (history, source and consistency) before anything goes near a government portal.</p></div>
    <div class="step"><div class="num">03</div><div class="rule"></div><h3>We submit &amp; track</h3><p>We handle the submission and keep you updated until your authorisation comes through. The decision still rests with the authority.</p></div>
  </div>
</div></section>

{{-- FAQ --}}
<section id="faq" class="alt faq-e"><div class="wrap">
  <div class="sec-head reveal" style="margin:0 auto 36px;text-align:center"><p class="eyebrow">Good to know</p><h2>Frequently asked questions</h2></div>
  <div class="faq-panel faqd reveal">
    <details>
      <summary>Is this a government service?</summary>
      <div class="a"><p>No. Beyond Passports is an independent, private service, not a government website and not affiliated with gov.uk, any embassy or any official authority. You can always apply directly with the relevant authority yourself; using us is entirely optional.</p></div>
    </details>
    <details>
      <summary>Do I pay more by using you?</summary>
      <div class="a"><p>Yes, and we won't pretend otherwise. The official government or embassy fee is the same whichever route you take. Our service fee is charged <strong>on top</strong> of that official fee, and pays for the checking, preparation and support we provide. We show both fees separately before you pay, so there are no surprises. Applying yourself is always the lower-cost option.</p></div>
    </details>
    <details>
      <summary>Does express get me approved faster?</summary>
      <div class="a"><p>No. An express option speeds up <strong>our</strong> handling and submission of your application. It does not make a government or embassy decide any faster, and it cannot guarantee approval. The decision, and how long it takes, is entirely down to the relevant authority.</p></div>
    </details>
  </div>
</div></section>

{{-- CTA --}}
<section class="cta-band"><div class="wrap reveal">
  <div class="rule"></div>
  <h2>Decide what suits you, or ask us and we'll be honest</h2>
  <p style="max-width:50ch;color:#eef0f1">Happy to do it yourself? Brilliant, go for it. Want a person to check it and save you the hassle? Start your application, or message us first and we'll tell you straight whether you need us.</p>
  <div class="row"><a href="{{ App\Support\SiteStats::chatUrl('Hi Beyond Passports, help me decide whether to use you or apply myself.') }}" target="_blank" rel="noopener" class="btn">Check eligibility →</a> @include('partials.consult-cta')<a href="https://wa.me/{{ config('ukv.whatsapp') ?: '447882747584' }}?text={{ rawurlencode('Hi Beyond Passports, help me decide whether to use you or apply myself.') }}" class="btn btn--wa">@include('partials.wa-glyph')Ask us first on WhatsApp</a></div>
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
        "text": "No. Beyond Passports is an independent, private service, not a government website and not affiliated with gov.uk, any embassy or any official authority. You can always apply directly with the relevant authority yourself; using us is entirely optional."
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
        "text": "No. An express option speeds up our handling and submission of your application. It does not make a government or embassy decide any faster, and it cannot guarantee approval. The decision, and how long it takes, is entirely down to the relevant authority."
      }
    }
  ]
}
</script>
@endverbatim
