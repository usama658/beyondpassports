@extends('layouts.public')

@section('title', 'Legal & policies — Privacy, Terms, Complaints, Disclaimer | UKVisaCo')
@section('description', 'UKVisaCo legal centre: privacy policy, terms of service, complaints procedure and disclaimer. Independent UK visa & eVisa facilitation — not a government website; our service fee is separate from government fees and we cannot guarantee visa approval.')
@section('canonical', url('/legal'))

@push('head')
<style>
  /* legal page — page-scoped layout only. Palette/type/components inherited from ukv.css. */

  /* hero */
  .legal-hero{padding:52px 0 0}
  .legal-hero h1{font-size:clamp(34px,5vw,52px);color:var(--navy);letter-spacing:-.015em;margin:0}
  .legal-hero p.lede{font-size:18px;color:#33454f;max-width:60ch;margin:14px 0 0}

  /* two-column shell: sticky switcher + document body */
  .legal-shell{display:grid;grid-template-columns:240px minmax(0,1fr);gap:48px;align-items:start;padding-top:8px}

  /* document switcher (real nav) */
  .doc-switch{position:sticky;top:84px}
  .doc-switch .switch-lab{font-family:var(--mono);font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:var(--stamp);margin:0 0 12px}
  .doc-switch ul{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:2px}
  .doc-switch a{display:block;padding:10px 14px;border-radius:8px;color:var(--ink);font-size:15px;font-weight:500;border-left:3px solid transparent;text-decoration:none}
  .doc-switch a:hover{background:var(--white);color:var(--cta)}
  .doc-switch a:focus-visible{outline:2px solid var(--cta);outline-offset:2px}
  .doc-switch a[aria-current="true"]{background:var(--white);border-left-color:var(--gold);color:var(--navy);font-weight:600}

  /* document body */
  .doc-body{max-width:70ch}
  .doc-body .updated{font-family:var(--mono);font-size:12px;letter-spacing:.04em;color:var(--hint);margin:0 0 4px}
  .legal-sec{padding:8px 0 40px;border-bottom:1px dashed var(--paper-edge);scroll-margin-top:84px}
  .legal-sec:last-of-type{border-bottom:0}
  .legal-sec h2{font-size:clamp(24px,3vw,32px);color:var(--navy);letter-spacing:-.01em;margin:0 0 4px}
  .legal-sec h3{font-size:17px;color:var(--navy);margin:26px 0 6px}
  .doc-body p{font-size:15.5px;line-height:1.72;color:#2b3b44;margin:0 0 14px}
  .doc-body ul.plain{margin:0 0 14px;padding-left:20px}
  .doc-body ul.plain li{font-size:15.5px;line-height:1.7;color:#2b3b44;margin:0 0 7px}
  .doc-body a{color:var(--cta)}
  .doc-body strong{color:var(--ink)}

  /* placeholder banner per section */
  .ph-note{display:flex;gap:10px;align-items:flex-start;background:#fdf7e9;border:1px solid #e9d9a8;border-radius:8px;padding:11px 14px;margin:0 0 20px;font-family:var(--mono);font-size:12px;line-height:1.5;letter-spacing:.02em;color:#7a5d12}
  .ph-note svg{flex:0 0 18px;height:18px;color:var(--gold);margin-top:1px}

  /* page-level draft-review banner */
  .draft-banner{display:flex;gap:12px;align-items:flex-start;background:#fdf7e9;border:1px solid #e9d9a8;border-radius:10px;padding:14px 18px;margin:22px 0 0;max-width:80ch}
  .draft-banner svg{flex:0 0 20px;height:20px;color:var(--gold);margin-top:2px}
  .draft-banner p{margin:0;font-size:14px;line-height:1.6;color:#7a5d12}
  .draft-banner strong{color:#5e4708}

  .back-top{display:inline-block;margin-top:6px;font-family:var(--mono);font-size:12px;letter-spacing:.06em;color:var(--stamp);text-decoration:none}
  .back-top:hover{text-decoration:underline}
  .back-top:focus-visible{outline:2px solid var(--cta);outline-offset:2px}

  @media (max-width:860px){
    .legal-shell{grid-template-columns:1fr;gap:24px}
    .doc-switch{position:static;top:auto;border:1px solid var(--paper-edge);border-radius:12px;background:var(--white);padding:16px 16px 18px}
    .doc-switch ul{flex-direction:row;flex-wrap:wrap;gap:8px}
    .doc-switch a{border-left:0;border:1px solid var(--paper-edge);padding:8px 12px;font-size:14px}
    .doc-switch a[aria-current="true"]{border-color:var(--gold)}
    .doc-body{max-width:none}
  }
</style>
@endpush

@section('content')

{{-- PAGE TITLE HERO --}}
<section class="legal-hero" id="top"><div class="wrap">
  <div class="reveal">
    <p class="eyebrow">Legal centre</p>
    <h1>Legal &amp; policies</h1>
    <p class="lede">Everything that governs how we work with you, in plain English: how we handle your data, the terms of our service, how to raise a complaint, and an important disclaimer about who we are. UKVisaCo is an independent facilitation service — not a government website and not affiliated with gov.uk or any embassy.</p>
    <div class="draft-banner" role="note">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 9v4m0 4h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/></svg>
      <p><strong>Draft for review — have a solicitor review before relying on these.</strong> These policies are working drafts written for UKVisaCo's business model. They have not yet been checked by a qualified legal adviser and should not be treated as final legal advice.</p>
    </div>
  </div>
</div></section>

{{-- MRZ STRIP --}}
<div class="mrz"><div class="wrap"><span>UKV&lt;CO&lt;LEGAL&lt;PRIVACY&lt;TERMS&lt;COMPLAINTS&lt;DISCLAIMER&lt;INDEPENDENT&lt;SERVICE&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;</span></div></div>

{{-- TWO-COLUMN: SWITCHER + DOCUMENT --}}
<section style="padding-top:40px"><div class="wrap">
  <div class="legal-shell">

    {{-- DOCUMENT SWITCHER --}}
    <nav class="doc-switch reveal" aria-label="Legal documents">
      <p class="switch-lab" id="switch-lab">Documents</p>
      <ul aria-labelledby="switch-lab">
        <li><a href="#privacy">Privacy</a></li>
        <li><a href="#terms">Terms</a></li>
        <li><a href="#complaints">Complaints</a></li>
        <li><a href="#disclaimer">Disclaimer</a></li>
      </ul>
    </nav>

    {{-- DOCUMENT BODY --}}
    <div class="doc-body">

      {{-- PRIVACY --}}
      <article class="legal-sec" id="privacy" aria-labelledby="privacy-h">
        <p class="updated">Last updated: 2026-06-15</p>
        <h2 id="privacy-h">Privacy Policy</h2>
        <div class="ph-note"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 9v4m0 4h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/></svg><span>[Placeholder — review with a solicitor before launch]</span></div>

        <p>This policy explains what personal data UKVisaCo collects when you use our visa, eVisa, ETA and IDP-guidance facilitation service, why we collect it, how long we keep it, and the rights you have over it. The <strong>data controller</strong> is UKVisaCo, company registration number <strong>[to complete]</strong>, registered at <strong>[to complete]</strong>. For any privacy question, email <a href="mailto:privacy@ukvisaco.example">privacy@ukvisaco.example</a> <strong>[to complete]</strong>.</p>

        <h3>What data we collect</h3>
        <ul class="plain">
          <li><strong>Identity &amp; contact details</strong> — your name, email address, phone number and billing address.</li>
          <li><strong>Passport &amp; travel details</strong> — passport number, nationality, date and place of birth, travel dates, destination and trip purpose.</li>
          <li><strong>Documents you upload</strong> — passport scans, photographs and any supporting documents needed for your application.</li>
          <li><strong>Order &amp; payment information</strong> — your order reference, the service options you chose, and payment confirmation (card details are handled by our payment processor, Stripe, and are not stored by us).</li>
          <li><strong>Technical data</strong> — limited device, browser and usage information needed to operate and secure the site.</li>
        </ul>

        <h3>Why we collect it and our lawful basis</h3>
        <p>We process your data primarily to <strong>perform our contract</strong> with you (UK GDPR Article 6(1)(b)) — checking, preparing and submitting your application and keeping you updated. Where we process special-category or sensitive details contained in travel documents, or pass them to an authority, we rely on your <strong>explicit consent</strong> (Article 9(2)(a)), which you may withdraw at any time. We also process limited data to meet legal obligations (such as tax and accounting) and for our legitimate interests in securing, supporting and improving the service.</p>

        <h3>How long we keep it (retention &amp; auto-purge)</h3>
        <p>We keep data only as long as needed to deliver your order and meet our legal duties. Sensitive passport scans, photographs and supporting documents are <strong>automatically purged approximately 90 days after your order is closed</strong> (completed or cancelled). Basic order and accounting records — order reference, fee paid and dates — are retained for the period required by UK tax and consumer law (generally up to six years), then deleted.</p>

        <h3>Sub-processors</h3>
        <p>We share data only with carefully selected providers acting on our instructions under a data-processing agreement:</p>
        <ul class="plain">
          <li><strong>Stripe</strong> — payment processing.</li>
          <li><strong>HubSpot</strong> — CRM, to manage your case and our communications with you.</li>
          <li><strong>Email provider</strong> — to send order confirmations, updates and replies.</li>
          <li><strong>Hosting provider</strong> — to run and secure this website and store your data.</li>
          <li><strong>Anthropic</strong> — AI assistance used to help check and prepare application content. We do not use your data to train third-party AI models.</li>
        </ul>

        <h3>Cross-border transfers</h3>
        <p>Some of our sub-processors (for example Stripe, HubSpot and Anthropic) may process data in the <strong>United States</strong>. Where data is transferred outside the UK, we rely on appropriate safeguards — such as the UK International Data Transfer Agreement (IDTA), the UK Extension to the EU-US Data Privacy Framework, or equivalent contractual protections — so your data continues to receive an essentially equivalent level of protection.</p>

        <h3>Your rights</h3>
        <p>You have the right to access, correct, erase, restrict or object to our processing of your data, to data portability, and to withdraw consent where processing is consent-based. To exercise any of these, contact us at <a href="mailto:privacy@ukvisaco.example">privacy@ukvisaco.example</a> and we will respond within one month, as required by law.</p>

        <h3>Complaining to the ICO</h3>
        <p>If you are unhappy with how we have handled your data, you can lodge a complaint with the UK <strong>Information Commissioner's Office (ICO)</strong>. Contact the ICO at <a href="https://ico.org.uk" rel="noopener">ico.org.uk</a>, by phone on <strong>0303 123 1113</strong>, or by post at Information Commissioner's Office, Wycliffe House, Water Lane, Wilmslow, Cheshire SK9 5AF. We would, however, appreciate the chance to resolve your concern first.</p>

        <a class="back-top" href="#top">↑ Back to top</a>
      </article>

      {{-- TERMS --}}
      <article class="legal-sec" id="terms" aria-labelledby="terms-h">
        <p class="updated">Last updated: 2026-06-15</p>
        <h2 id="terms-h">Terms of Service</h2>
        <div class="ph-note"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 9v4m0 4h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/></svg><span>[Placeholder — review with a solicitor before launch]</span></div>

        <p>These terms govern your use of UKVisaCo's service. UKVisaCo is operated by <strong>[to complete]</strong> (company registration number <strong>[to complete]</strong>), and you can reach us at <strong>[to complete]</strong>. By placing an order you agree to these terms. Please read them alongside our Privacy Policy and Disclaimer below.</p>

        <h3>Nature of our service</h3>
        <p>UKVisaCo provides <strong>independent facilitation and guided self-service</strong>: we help you complete, check and submit applications for visas, eVisas and travel authorisations. <strong>We are not a government body, are not affiliated with gov.uk or any embassy, and we do not provide legal or immigration advice.</strong> If you need regulated immigration advice, please consult a qualified adviser.</p>

        <h3>Our service fee vs government fees</h3>
        <p>The price you pay us is a <strong>service fee for our assistance</strong>. It is <strong>separate from, and additional to, any government or official application fee</strong>, which is set and collected by the relevant authority. Where a government fee applies, we will make the amount clear before you pay.</p>

        <h3>Your responsibilities</h3>
        <ul class="plain">
          <li>Provide <strong>accurate, complete and truthful</strong> information and documents.</li>
          <li>Check the details we prepare on your behalf before submission.</li>
          <li>Apply in good time for your travel dates and respond promptly to requests.</li>
        </ul>
        <p>We are not responsible for delays, refusals or losses caused by inaccurate or late information supplied by you.</p>

        <h3>No guarantee of approval</h3>
        <p><strong>All decisions on visas, eVisas and travel authorisations are made solely by the relevant government or official authority.</strong> We cannot and do not guarantee that any application will be approved, nor the time a decision will take. Any "express" or "priority" option speeds <strong>our handling only</strong> — it does not speed or influence the authority's decision.</p>

        <h3>Cancellation &amp; refunds</h3>
        <p>Under the <strong>Consumer Contracts (Information, Cancellation and Additional Charges) Regulations 2013</strong> you have a <strong>14-day right to cancel</strong> from the day you place your order, for a full refund of our service fee. <strong>Important exception:</strong> if you ask us to <strong>begin work within the 14-day period and acknowledge that you do so</strong>, you will pay for the work reasonably carried out up to the point you cancel; and once the service has been <strong>fully performed</strong> with your consent (for example, once your application has been submitted to the authority), you lose the right to cancel under these Regulations. Government and official fees that have already been paid to a third party on your behalf are generally non-refundable to us. To cancel, email <a href="mailto:complaints@ukvisaco.example">complaints@ukvisaco.example</a> with your order reference; our refund policy and any deductions are summarised at checkout and in your confirmation email.</p>

        <h3>Liability</h3>
        <p>To the extent permitted by law, our liability for any claim arising from the service is limited to the amount of the <strong>service fee you paid us</strong> for the relevant order. We are not liable for the decisions, processing times, fees or conduct of any government or official authority. Nothing in these terms excludes liability that cannot lawfully be excluded, including for death or personal injury caused by negligence, or for fraud. These terms do not affect your statutory rights.</p>

        <h3>Governing law</h3>
        <p>These terms, and any dispute arising from them or from our service, are governed by the <strong>laws of England and Wales</strong>, and are subject to the <strong>exclusive jurisdiction of the courts of England and Wales</strong>. If you live elsewhere in the UK, you may instead bring proceedings in your local courts as permitted by law.</p>

        <a class="back-top" href="#top">↑ Back to top</a>
      </article>

      {{-- COMPLAINTS --}}
      <article class="legal-sec" id="complaints" aria-labelledby="complaints-h">
        <p class="updated">Last updated: 2026-06-15</p>
        <h2 id="complaints-h">Complaints Procedure</h2>
        <div class="ph-note"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 9v4m0 4h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/></svg><span>[Placeholder — review with a solicitor before launch]</span></div>

        <p>We want every customer to be happy with our service. If something has gone wrong, we'd like the chance to put it right.</p>

        <h3>How to raise a complaint</h3>
        <p>Email <a href="mailto:complaints@ukvisaco.example">complaints@ukvisaco.example</a> with your <strong>order reference</strong>, a description of the issue and what outcome you'd like. You can also call us on the number at the top of this page.</p>

        <h3>Our response timescale</h3>
        <ul class="plain">
          <li>We will <strong>acknowledge</strong> your complaint within <strong>5 working days</strong> of receiving it.</li>
          <li>We aim to provide a <strong>full written response, and to resolve the matter, within 28 days</strong>. If we need longer (for example, where we're waiting on a third party), we'll explain why and give you a new date.</li>
        </ul>

        <h3>Escalation</h3>
        <p>If you're not satisfied with our final response, you may ask for it to be reviewed by a senior member of our team, who will reply with our final position. Where relevant, you may also raise the matter with an alternative dispute resolution body, or contact <strong>Trading Standards</strong> via the <strong>Citizens Advice consumer service</strong> on 0808 223 1133. Complaints specifically about how we handle your personal data can be taken to the <a href="https://ico.org.uk" rel="noopener">ICO</a>.</p>

        <a class="back-top" href="#top">↑ Back to top</a>
      </article>

      {{-- DISCLAIMER --}}
      <article class="legal-sec" id="disclaimer" aria-labelledby="disclaimer-h">
        <p class="updated">Last updated: 2026-06-15</p>
        <h2 id="disclaimer-h">Disclaimer</h2>
        <div class="ph-note"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 9v4m0 4h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/></svg><span>[Placeholder — review with a solicitor before launch]</span></div>

        <p><strong>UKVisaCo is an independent service. We are not affiliated with, endorsed by, or connected to gov.uk, the UK government, any embassy, consulate or official authority.</strong> Official applications can be made directly with the relevant authority, usually for the government fee alone.</p>

        <p>The information on this website is provided as <strong>general guidance only</strong> and does not constitute legal or immigration advice. Visa and travel-authorisation requirements <strong>depend on your nationality, country of residence and individual circumstances</strong>, and they change frequently.</p>

        <p>Before you travel or apply, always <strong>verify requirements with official sources</strong> — the destination government's official website, your nearest embassy or consulate, and gov.uk for UK-specific guidance. We are not liable for decisions you make in reliance on general information published here.</p>

        <p><strong>No guarantee of outcome.</strong> Visa, eVisa and travel-authorisation decisions are made solely by the relevant authority; we cannot guarantee any approval. Some authorisations, such as an ETA, do not produce a physical document. Any <strong>"express" or "priority" option speeds our own handling only</strong> — preparing and submitting your application faster. It does <strong>not</strong> speed up, influence or change the government's processing time or decision.</p>

        <a class="back-top" href="#top">↑ Back to top</a>
      </article>

    </div>
  </div>
</div></section>

{{-- CTA BAND --}}
<section class="cta-band"><div class="wrap reveal">
  <div class="rule"></div>
  <h2>Questions before you start?</h2>
  <p style="max-width:50ch;color:#cdd9e1">Talk to a real person about how our service works, what's included, and the fees — no obligation. Independent service, not a government website.</p>
  <div class="row"><a href="{{ url('/apply') }}" class="btn">Start my application →</a><a href="https://wa.me/{{ config('ukv.whatsapp') ?: '440000000000' }}" class="btn btn--wa">Chat on WhatsApp</a></div>
</div></section>

<script>
  // Highlights the active document in the switcher as you scroll.
  (function () {
    var links = Array.prototype.slice.call(document.querySelectorAll('.doc-switch a'));
    var secs  = links.map(function (a) { return document.querySelector(a.getAttribute('href')); }).filter(Boolean);
    if (!secs.length || !('IntersectionObserver' in window)) return;

    function setCurrent(id) {
      links.forEach(function (a) {
        if (a.getAttribute('href') === '#' + id) a.setAttribute('aria-current', 'true');
        else a.removeAttribute('aria-current');
      });
    }

    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) { if (e.isIntersecting) setCurrent(e.target.id); });
    }, { rootMargin: '-20% 0px -70% 0px', threshold: 0 });

    secs.forEach(function (s) { io.observe(s); });
    setCurrent(secs[0].id);
  })();
</script>
@endsection
