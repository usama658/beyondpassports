{{-- /visa/france — destination-page PILOT (competitor-SWOT-driven, docs/competitor-swot.md).
     STAGING: renders only when config('ukv.destinations.france_pilot') is true OR the request
     carries ?preview=bp2026; otherwise the route falls through to the drafted redirect.
     Section stack + conversion copy per the approved list appended to competitor-swot.md. --}}
@extends('layouts.public')

@section('title', 'France Visa from the UK | TLScontact Appointments Tracked Daily | Beyond Passports')
@section('description', 'France Schengen visa help from the UK. We watch the TLScontact calendar in London, Manchester and Edinburgh and take the earliest date. £130 total, £90 only after your appointment is booked.')

@php
  $wa = 'https://wa.me/'.config('ukv.whatsapp');
  $faqs = [
    ['q' => 'How do I get a France Schengen visa appointment from the UK?', 'a' => 'France uses TLScontact centres in London, Manchester and Edinburgh. You need a France-Visas application reference before the TLS calendar will show you dates. We complete the France-Visas form, generate your reference, then watch the calendar and take the earliest date that fits your travel.'],
    ['q' => 'How long does a France visa take from the UK?', 'a' => 'The consulate\'s published processing time is 10 to 15 working days after your biometric appointment, longer in peak season. Appointment lead time comes on top, so start 6 to 8 weeks before travel where you can. We cannot speed up the consulate\'s decision, only how fast your file is ready and submitted.'],
    ['q' => 'I\'m on a UK residence permit. Can I apply for a France visa from the UK?', 'a' => 'Yes. If you legally reside in the UK you apply from the UK, whatever passport you hold. Your UK immigration status documents form part of the file, and we check them before anything is submitted.'],
    ['q' => 'What does the France visa cost in total?', 'a' => 'Three parts: the consulate fee of €90 for adults (paid to the consulate), the TLScontact service charge (paid to TLS, confirmed at booking), and our £130 fee, of which £90 is only due once your appointment is booked.'],
    ['q' => 'What travel insurance do I need for France?', 'a' => 'Schengen rules require medical cover of at least €30,000, valid across the whole Schengen Area for your full stay. A compliant certificate is part of the file pack we prepare.'],
    ['q' => 'I was refused a Schengen visa before. Does that affect a France application?', 'a' => 'A refusal sits on the Schengen system for five years and every consulate can see it. It does not block a new application, but the new file has to answer the refusal reason. We review the refusal letter first and tell you honestly whether and how to reapply.'],
    ['q' => 'I\'m self-employed. What extra documents does France ask for?', 'a' => 'Typically your last tax return or SA302, business bank statements, and proof of your business (Companies House entry or invoices). We give you the exact list for your situation and check each document before submission.'],
    ['q' => 'When are France appointments hardest to get?', 'a' => 'Spring and summer, and the weeks before school holidays. The TLScontact calendar releases dates in batches, so empty today does not mean empty tomorrow. That is why we watch it rather than asking you to keep refreshing.'],
  ];
@endphp

@push('head')
<style>
.vfr{--edge:#dde3ec;--stamp:#2E9A8C;--stampt:#1F6E63;--cta:#155E7A;--paper:#F4F6FA;--muted:#5d6b76;--ink:#16222E;--wa:#25D366;--mint:#8fe3c9;
  font-family:'Outfit',system-ui,-apple-system,sans-serif;color:var(--ink);background:var(--paper)}
.vfr .wrap{max-width:1080px;margin:0 auto;padding:0 20px}
.vfr section{padding:44px 0}
.vfr h2{font-size:clamp(24px,3.4vw,32px);font-weight:800;letter-spacing:-.02em;margin:0 0 8px}
.vfr .sub{color:var(--muted);font-size:15px;max-width:62ch;margin:0 0 22px}
.vfr .eyebrow{display:inline-flex;align-items:center;gap:8px;font-size:12px;font-weight:800;letter-spacing:.09em;text-transform:uppercase;color:var(--stampt);margin-bottom:10px}
.vfr .eyebrow::before{content:'';width:26px;height:3px;border-radius:2px;background:var(--stamp)}

/* 1 · hero — split: copy + proof badges left, white availability form right */
.vfr .hero{padding:54px 0 50px;background:linear-gradient(180deg,#eef3f6,var(--paper) 72%)}
.vfr .hgrid{display:grid;grid-template-columns:1.35fr 1fr;gap:40px;align-items:center}
.vfr .hero h1{font-size:clamp(28px,4.2vw,44px);font-weight:800;letter-spacing:-.03em;line-height:1.08;margin:14px 0 0;max-width:20ch}
.vfr .hero h1 b{color:var(--stampt)}
.vfr .hsub{font-size:16.5px;color:var(--muted);line-height:1.55;max-width:52ch;margin:14px 0 0}
.vfr .hprice{font-size:14.5px;color:var(--ink);line-height:1.7;font-weight:600;background:rgba(46,154,140,.07);border:1px solid rgba(46,154,140,.2);border-left:3px solid var(--stamp);border-radius:12px;padding:14px 18px;margin:18px 0 0}
/* proof badges (refund-badge style) */
.vfr .pbadges{display:flex;flex-wrap:wrap;gap:12px;margin:18px 0 0}
.vfr .pbadge{display:flex;align-items:center;gap:12px;background:#fff;border:1px solid var(--edge);border-radius:12px;padding:12px 15px;box-shadow:0 12px 28px -22px rgba(20,34,46,.5)}
.vfr .pbadge .sl{flex:none;width:38px;height:38px;border-radius:50%;background:rgba(46,154,140,.12);border:1.5px solid rgba(46,154,140,.42);display:grid;place-items:center;position:relative}
.vfr .pbadge .sl svg{width:19px;height:19px;fill:var(--stampt)}
.vfr .pbadge .sl.glow{animation:vfrSeal 2s ease-in-out infinite}
.vfr .pbadge .sl .wave{position:absolute;inset:-4px;border-radius:50%;border:2px solid var(--stamp);animation:vfrWave 2s ease-out infinite;pointer-events:none}
@keyframes vfrSeal{0%,100%{box-shadow:0 0 4px -2px rgba(46,154,140,.3)}50%{box-shadow:0 0 18px 3px rgba(46,154,140,.85)}}
@keyframes vfrWave{0%{transform:scale(1);opacity:.8}100%{transform:scale(1.7);opacity:0}}
.vfr .pbadge .tx b{display:block;font:800 13.5px 'Outfit',sans-serif;color:var(--ink)}
.vfr .pbadge .tx span{display:block;font:600 11.5px 'Outfit',sans-serif;color:var(--muted);margin-top:1px;line-height:1.4}
/* white availability form card */
.vfr .fcard{background:#fff;border:1px solid var(--edge);border-radius:20px;padding:24px;box-shadow:0 30px 66px -30px rgba(20,34,46,.42)}
.vfr .fc-eye{display:flex;align-items:center;gap:9px;font:800 11px 'Outfit',sans-serif;letter-spacing:.1em;text-transform:uppercase;color:var(--stampt);margin-bottom:4px}
.vfr .fc-eye i{width:7px;height:7px;border-radius:50%;background:var(--stamp);display:inline-block;animation:vfrblink 1.6s infinite}
@keyframes vfrblink{50%{opacity:.3}}
.vfr .fc-h{font:800 19px 'Outfit',sans-serif;letter-spacing:-.01em;margin:0 0 14px;display:flex;align-items:center;gap:9px}
.vfr .fc-flag{width:24px;height:16px;border-radius:3px;box-shadow:0 0 0 1px rgba(0,0,0,.1)}
.vfr .cchips{display:flex;gap:6px;flex-wrap:wrap;margin:0 0 16px}
.vfr .cchips span{font:700 11px 'Outfit',sans-serif;background:var(--paper);border:1px solid var(--edge);border-radius:8px;padding:6px 9px;color:var(--muted)}
.vfr .cchips span.dl{color:var(--stampt)}
.vfr .fl{font:800 12px 'Outfit',sans-serif;color:var(--ink);margin:0 0 7px;display:block}
.vfr .fl .rq{color:var(--red)}
.vfr .tf{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:14px}
.vfr .tf button{border:1.5px solid var(--edge);background:#fff;border-radius:11px;padding:11px 6px;font:700 13px 'Outfit',sans-serif;color:var(--ink);cursor:pointer;transition:all .12s}
.vfr .tf button:hover{border-color:var(--stamp)}
.vfr .tf button.sel{background:var(--stampt);border-color:var(--stampt);color:#fff}
.vfr .tf.err{outline:2px solid rgba(192,73,47,.5);outline-offset:3px;border-radius:12px}
.vfr .pc{position:relative;display:flex;border:1px solid var(--edge);border-radius:11px;background:var(--paper);margin-bottom:14px}
.vfr .pc:focus-within{border-color:var(--cta);box-shadow:0 0 0 3px rgba(21,94,122,.12);background:#fff}
.vfr .pc .cc{display:flex;align-items:center;gap:7px;padding:0 12px;border-right:1px solid var(--edge);font:700 14px 'Outfit',sans-serif;white-space:nowrap}
.vfr .pc .cc img{width:20px;height:14px;border-radius:2px}
.vfr .pc input{flex:1;border:0;background:transparent;padding:13px 14px;font:600 15px 'Outfit',sans-serif;outline:none;color:var(--ink)}
.vfr .sbtn{display:flex;align-items:center;justify-content:center;gap:9px;width:100%;background:var(--wa);color:#fff;border:0;border-radius:13px;font:800 16px 'Outfit',sans-serif;padding:15px;cursor:pointer;box-shadow:0 14px 30px -12px rgba(37,211,102,.65);transition:filter .15s,transform .12s}
.vfr .sbtn:hover{filter:brightness(.95);transform:translateY(-1px)}
.vfr .sbtn[disabled]{opacity:.6;cursor:wait}
.vfr .sbtn svg{width:17px;height:17px;fill:#fff}
.vfr .fnote{font:600 11.5px 'Outfit',sans-serif;color:var(--muted);text-align:center;margin:10px 0 0}
.vfr .fthx{text-align:center;padding:6px 0}
.vfr .fthx[hidden]{display:none}
.vfr .fthx .tick{width:56px;height:56px;border-radius:50%;background:rgba(46,154,140,.14);border:2px solid var(--stamp);display:grid;place-items:center;margin:0 auto 12px}
.vfr .fthx .tick svg{width:26px;height:26px;fill:none;stroke:var(--stampt);stroke-width:3;stroke-linecap:round;stroke-linejoin:round}
.vfr .fthx h4{font:800 18px 'Outfit',sans-serif;margin:0 0 6px}
.vfr .fthx p{font-size:13.5px;color:var(--muted);line-height:1.6;margin:0}
@media(max-width:860px){.vfr .hgrid{grid-template-columns:1fr}}

/* 3 · fee table */
.vfr .fee{background:#fff;border:1px solid var(--edge);border-radius:18px;overflow:hidden;max-width:640px}
.vfr .fee .fr{display:flex;justify-content:space-between;gap:12px;padding:14px 20px;border-bottom:1px solid var(--edge);font-size:14.5px}
.vfr .fee .fr span:last-child{font-weight:800;white-space:nowrap}
.vfr .fee .fr small{display:block;color:var(--muted);font-size:12px;font-weight:600;margin-top:2px}
.vfr .fee .tot{background:rgba(46,154,140,.08)}
.vfr .fee .tot span{font-weight:800;color:var(--stampt)}
.vfr .fee-note{font-size:13px;color:var(--muted);margin-top:12px;max-width:60ch}
.vfr .paynow{display:flex;gap:10px;margin-top:14px;flex-wrap:wrap}
.vfr .paynow span{background:#fff;border:1.5px solid rgba(46,154,140,.4);color:var(--stampt);border-radius:12px;padding:10px 16px;font:800 13px 'Outfit',sans-serif}

/* 4 · process */
.vfr .steps{display:grid;grid-template-columns:repeat(5,1fr);gap:12px}
.vfr .st{background:#fff;border:1px solid var(--edge);border-radius:15px;padding:16px}
.vfr .st .n{width:28px;height:28px;border-radius:50%;background:rgba(46,154,140,.14);color:var(--stampt);display:grid;place-items:center;font:800 13px 'Outfit',sans-serif;margin-bottom:9px}
.vfr .st b{display:block;font-size:14px;margin-bottom:4px}
.vfr .st p{font-size:12.5px;color:var(--muted);line-height:1.5;margin:0}
.vfr .st .who{display:inline-block;font:800 10px 'Outfit',sans-serif;letter-spacing:.08em;text-transform:uppercase;color:var(--stampt);background:rgba(46,154,140,.1);border-radius:6px;padding:3px 7px;margin-bottom:7px}
.vfr .st .who.you{color:#b5791f;background:rgba(181,121,31,.1)}

/* 5 · docs */
.vfr .docs{display:grid;grid-template-columns:1fr 1fr;gap:12px;max-width:860px}
.vfr details{background:#fff;border:1px solid var(--edge);border-radius:14px;padding:0}
.vfr summary{cursor:pointer;font:800 14.5px 'Outfit',sans-serif;padding:15px 18px;list-style:none;display:flex;justify-content:space-between;align-items:center}
.vfr summary::after{content:'+';font-size:18px;color:var(--stampt)}
.vfr details[open] summary::after{content:'\2212'}
.vfr details ul{margin:0;padding:0 18px 15px 34px;font-size:13.5px;color:var(--muted);line-height:1.8}

/* 6 · pack */
.vfr .pack{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}
.vfr .pk{background:#fff;border:1px solid var(--edge);border-radius:15px;padding:16px}
.vfr .pk b{display:block;font-size:13.5px;margin-bottom:4px}
.vfr .pk p{font-size:12px;color:var(--muted);margin:0;line-height:1.5}

/* 7 · do/don't */
.vfr .dd{display:grid;grid-template-columns:1fr 1fr;gap:12px;max-width:860px}
.vfr .ddc{background:#fff;border:1px solid var(--edge);border-radius:16px;padding:20px}
.vfr .ddc h3{font-size:15px;font-weight:800;margin:0 0 10px}
.vfr .ddc ul{margin:0;padding-left:20px;font-size:13.5px;color:var(--muted);line-height:1.9}
.vfr .ddc.dont h3{color:#b5791f}

/* 8 · refusal */
.vfr .ref{position:relative;overflow:hidden;background:linear-gradient(160deg,#132c34,#1F6E63);border-radius:18px;padding:26px;color:#fff;display:flex;align-items:center;gap:20px;flex-wrap:wrap}
.vfr .ref h3{font-size:19px;font-weight:800;margin:0 0 6px}
.vfr .ref h3 b{color:var(--mint)}
.vfr .ref p{font-size:13.5px;color:rgba(255,255,255,.75);margin:0;max-width:52ch}
.vfr .ref .tx{flex:1;min-width:260px}
.vfr .refcta{display:inline-flex;align-items:center;gap:8px;background:var(--wa);color:#fff;border-radius:12px;font:800 14px 'Outfit',sans-serif;padding:13px 20px;text-decoration:none;white-space:nowrap}
.vfr .refcta svg{width:15px;height:15px;fill:#fff}

/* 9 · faq */
.vfr .faq details{margin-bottom:10px}
.vfr .faq summary{font-size:14.5px}
.vfr .faq details div{padding:0 18px 15px;font-size:13.5px;color:var(--muted);line-height:1.7}

/* 10 · reviews */
.vfr .rgrid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}
.vfr .rc{background:#fff;border:1px solid var(--edge);border-radius:15px;padding:18px}
.vfr .rc .st5{color:#e2a63d;font-size:13px;letter-spacing:2px;margin-bottom:8px}
.vfr .rc p{font-size:13px;line-height:1.65;color:var(--ink);margin:0 0 12px}
.vfr .rc .who{font:800 12.5px 'Outfit',sans-serif}
.vfr .rc .who span{display:block;font:600 11px 'Outfit',sans-serif;color:var(--muted);margin-top:2px}

/* 11 · more strip */
.vfr .more{position:relative;overflow:hidden;display:flex;align-items:center;gap:14px;flex-wrap:wrap;background:linear-gradient(160deg,#132c34,#1F6E63);border-radius:14px;padding:16px 18px;color:#fff}
.vfr .more .tx{font:800 14px 'Outfit',sans-serif}
.vfr .more .tx b{color:var(--mint)}
.vfr .more .tx span{display:block;font:600 12px 'Outfit',sans-serif;color:rgba(255,255,255,.65);margin-top:2px}
.vfr .more a{margin-left:auto;display:inline-flex;align-items:center;gap:7px;background:var(--wa);color:#fff;font:800 12.5px 'Outfit',sans-serif;padding:11px 16px;border-radius:999px;text-decoration:none;white-space:nowrap}
.vfr .more a svg{width:14px;height:14px;fill:#fff}

@media(max-width:860px){
  .vfr .steps{grid-template-columns:1fr 1fr}
  .vfr .pack{grid-template-columns:1fr 1fr}
  .vfr .rgrid{grid-template-columns:1fr}
  .vfr .docs,.vfr .dd{grid-template-columns:1fr}
  .vfr .crow{grid-template-columns:1fr}
  .vfr .more a{margin-left:0;width:100%;justify-content:center}
}
</style>
@endpush

@section('content')
<div class="vfr">

  {{-- 1 · HERO: mechanism headline + France availability --}}
  <section class="hero"><div class="wrap"><div class="hgrid">
    <div>
      <span class="eyebrow">France Schengen visa from the UK</span>
      <h1>We Watch the TLScontact Calendar. You Just <b>Show Up</b>.</h1>
      <p class="hsub">France appointments in London, Manchester and Edinburgh, rechecked daily. Tell us your dates, we take the earliest slot the calendar releases and confirm it with you.</p>
      <p class="hprice"><strong>&pound;130 total. &pound;90 only once your appointment is booked.</strong> Service fee refunded if the consulate refuses the file we prepared.</p>
      <div class="pbadges">
        <div class="pbadge">
          <span class="sl"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 1 3 5v6c0 5.5 3.8 10.7 9 12 5.2-1.3 9-6.5 9-12V5l-9-4zm-1.2 15L7 12.2l1.4-1.4 2.4 2.4 5-5L17.2 9l-6.4 6z"/></svg></span>
          <div class="tx"><b>Companies House 17331903</b><span>UK-registered Schengen visa consultancy</span></div>
        </div>
        <div class="pbadge">
          <span class="sl glow"><span class="wave" aria-hidden="true"></span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm0 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16zm.5-13H11v6l5.2 3.1.8-1.3-4.5-2.7V7z"/></svg></span>
          <div class="tx"><b>Reply within 30 minutes</b><span>A named consultant, 7 days a week</span></div>
        </div>
      </div>
    </div>

    <div class="fcard">
      <form id="vfr-form" autocomplete="off">
        <div class="fc-eye"><i></i>TLScontact France &middot; checked daily</div>
        <div class="fc-h"><img class="fc-flag" src="https://flagcdn.com/fr.svg" alt="">Find my France slot</div>
        <div class="cchips"><span>London</span><span>Manchester</span><span>Edinburgh</span><span class="dl"><b>checked daily</b></span></div>
        <label class="fl">When are you travelling? <span class="rq">*</span></label>
        <div class="tf" id="vfr-tf">
          <button type="button" data-tf="Within 3 weeks">Within 3 weeks</button><button type="button" data-tf="This month">This month</button><button type="button" data-tf="In 1&ndash;3 months">In 1&ndash;3 months</button><button type="button" data-tf="Just planning">Just planning</button>
        </div>
        <label class="fl" for="vfr-phone">Your phone number <span class="rq">*</span></label>
        <div class="pc"><span class="cc"><img src="https://flagcdn.com/gb.svg" alt="">+44</span><input id="vfr-phone" type="tel" inputmode="tel" placeholder="7911 123456"></div>
        <button type="submit" class="sbtn">@include('partials.wa-glyph')Check France availability</button>
        <p class="fnote">We reply within 30 minutes and take the earliest date at your nearest centre.</p>
      </form>
      <div class="fthx" hidden>
        <div class="tick"><svg viewBox="0 0 24 24"><path d="m5 13 4 4L19 7"/></svg></div>
        <h4>Request received</h4>
        <p><b>A named consultant replies within 30 minutes</b>, 7 days a week. We are already watching the TLScontact France calendar for your dates.</p>
      </div>
    </div>
  </div></div></section>

  {{-- 3 · FEE TABLE --}}
  <section><div class="wrap">
    <span class="eyebrow">Transparent pricing</span>
    <h2>What a France visa actually costs</h2>
    <p class="sub">The whole number, on the page. No "from" prices, no surprises after a form.</p>
    <div class="fee">
      <div class="fr"><span>Consulate fee (adult)<small>Paid to the French consulate, set by Schengen rules</small></span><span>&euro;90</span></div>
      <div class="fr"><span>TLScontact service charge<small>Paid to TLS, confirmed at booking</small></span><span>confirmed at booking</span></div>
      <div class="fr"><span>Beyond Passports fee<small>Form, FRA reference, document check, appointment, file pack, named consultant</small></span><span>&pound;130</span></div>
      <div class="fr tot"><span>Our fee covers everything we do</span><span>&pound;130 total</span></div>
    </div>
    <div class="paynow"><span>Payable now: &pound;40</span><span>&pound;90 only once your appointment is booked</span></div>
    <p class="fee-note">&pound;130 covers everything we do. The only things it does not include are the consulate's own fee and the TLS centre charge, paid to them directly. If the consulate refuses your application after we prepared the file, our service fee comes back to you.</p>
  </div></section>

  {{-- 4 · PROCESS --}}
  <section style="background:#fff"><div class="wrap">
    <span class="eyebrow">How it works for France</span>
    <h2>You attend one appointment. We do the rest.</h2>
    <div class="steps" style="margin-top:20px">
      <div class="st"><span class="n">1</span><span class="who">We</span><b>France-Visas form</b><p>We complete the official France-Visas application and generate your FRA reference. Without it, the TLS calendar shows nothing.</p></div>
      <div class="st"><span class="n">2</span><span class="who">We</span><b>Build the file</b><p>Itinerary, Schengen-compliant insurance, accommodation, cover letter, finances. Checked line by line before anything is booked.</p></div>
      <div class="st"><span class="n">3</span><span class="who">We</span><b>Take the earliest date</b><p>We watch the TLScontact calendar and secure the earliest date that fits your travel, at your nearest centre.</p></div>
      <div class="st"><span class="n">4</span><span class="who you">You</span><b>Biometrics at TLS</b><p>Your one job: attend with the pack we prepared. Fingerprints and photo, usually 15 to 20 minutes.</p></div>
      <div class="st"><span class="n">5</span><span class="who">Then</span><b>Travel with confidence</b><p>The consulate decides in 10 to 15 working days. Your passport comes back through TLS, and you fly.</p></div>
    </div>
  </div></section>

  {{-- 5 · DOCS BY TRAVELLER TYPE --}}
  <section><div class="wrap">
    <span class="eyebrow">Documents</span>
    <h2>Your France checklist, by situation</h2>
    <p class="sub">One missing document at TLScontact and your travel date is gone. This is what the file check exists for.</p>
    <div class="docs">
      <details open><summary>Employed</summary><ul>
        <li>Passport (3+ months validity beyond return, 2 blank pages)</li>
        <li>Last 3 months' payslips + employer letter with approved leave dates</li>
        <li>Last 3 months' bank statements</li>
        <li>UK immigration status (BRP / eVisa share code where applicable)</li>
        <li>Travel insurance &euro;30,000+, flight itinerary, accommodation</li>
      </ul></details>
      <details><summary>Self-employed</summary><ul>
        <li>Everything in Employed, minus the employer letter</li>
        <li>Latest tax return or SA302</li>
        <li>Business bank statements</li>
        <li>Proof of business: Companies House entry, contracts or invoices</li>
      </ul></details>
      <details><summary>Student</summary><ul>
        <li>Enrolment letter with term dates from your UK institution</li>
        <li>Student bank statements, or sponsor's statements + letter</li>
        <li>UK immigration status documents</li>
      </ul></details>
      <details><summary>Family / travelling with children</summary><ul>
        <li>Birth or marriage certificates linking the group</li>
        <li>Consent letter if one parent travels alone with a child</li>
        <li>One application per traveller, submitted together, appointments aligned</li>
      </ul></details>
    </div>
  </div></section>

  {{-- 6 · DELIVERABLES PACK --}}
  <section style="background:#fff"><div class="wrap">
    <span class="eyebrow">What you actually get</span>
    <h2>The appointment pack, in your hands before TLS</h2>
    <div class="pack" style="margin-top:20px">
      <div class="pk"><b>Completed France-Visas application</b><p>With your FRA reference, ready for the centre.</p></div>
      <div class="pk"><b>Tailored cover letter</b><p>Your trip, your ties, your finances, written for a visa officer.</p></div>
      <div class="pk"><b>Itinerary + insurance certificate</b><p>Bookings structured the way consulates expect, &euro;30k-compliant cover.</p></div>
      <div class="pk"><b>Appointment confirmation + briefing</b><p>Date, centre, what happens in the room, what to carry.</p></div>
    </div>
  </div></section>

  {{-- 7 · DO / DON'T --}}
  <section><div class="wrap">
    <span class="eyebrow">Straight answers</span>
    <h2>What we do, and what we don't</h2>
    <div class="dd" style="margin-top:18px">
      <div class="ddc"><h3>We do</h3><ul>
        <li>Complete the France-Visas form and generate your FRA reference</li>
        <li>Watch the TLScontact calendar and take the earliest workable date</li>
        <li>Check every document before it goes anywhere near the centre</li>
        <li>Reply within 30 minutes, from a named consultant</li>
        <li>Refund our service fee if the consulate refuses the file we prepared</li>
      </ul></div>
      <div class="ddc dont"><h3>We don't</h3><ul>
        <li>Speed up the consulate's decision. Nobody can, and we won't pretend</li>
        <li>Book you through a different country to game the main-destination rule</li>
        <li>Sell queue-jumping or "premium slots" that don't exist</li>
        <li>Guarantee a visa. The decision is always the consulate's</li>
        <li>We are not the government and not affiliated with TLScontact</li>
      </ul></div>
    </div>
  </div></section>

  {{-- 8 · REFUSAL RECOVERY --}}
  <section><div class="wrap">
    <div class="ref">
      <div class="tx">
        <h3>Refused before? <b>Review my Refusal.</b></h3>
        <p>A refusal sits on the Schengen system for five years, and a wrong re-application makes it worse. Send us the refusal letter, we find the real trigger and tell you honestly whether and how to reapply for France.</p>
      </div>
      <a class="refcta" href="{{ $wa }}?text={{ rawurlencode('Hi Beyond Passports, I was refused a Schengen visa before and want to apply for France. Can you review my refusal letter?') }}">@include('partials.wa-glyph')Review my refusal</a>
    </div>
  </div></section>

  {{-- 9 · FAQ --}}
  <section style="background:#fff"><div class="wrap">
    <span class="eyebrow">France visa FAQ</span>
    <h2>Asked every week, answered straight</h2>
    <div class="faq" style="margin-top:18px;max-width:760px">
      @foreach($faqs as $f)
      <details><summary>{{ $f['q'] }}</summary><div>{{ $f['a'] }}</div></details>
      @endforeach
    </div>
  </div></section>

  {{-- 10 · REVIEWS (France-relevant) --}}
  <section><div class="wrap">
    <span class="eyebrow">France clients</span>
    <h2>What France applicants say</h2>
    <div class="rgrid" style="margin-top:18px">
      <div class="rc"><div class="st5">★★★★★</div><p>First time applying for a Schengen visa and I had no clue where to start. They sorted my France application, checked every document, and it came back approved. Kept me posted the whole time.</p><div class="who">Adaeze Okafor<span>May 2026 · BP-2026-103487</span></div></div>
      <div class="rc"><div class="st5">★★★★★</div><p>Could not find a single appointment for France anywhere. These lot found one within 3 days. Didn't believe it until I saw the confirmation email. Proper service.</p><div class="who">Fatima Hussain<span>Manchester · May 2026 · BP-2026-100184</span></div></div>
      <div class="rc"><div class="st5">★★★★★</div><p>Needed it before a wedding on a tight timeline. Honest that they can't rush the consulate, just the paperwork. Did exactly what they said.</p><div class="who">Kwame Mensah<span>Apr 2026 · BP-2026-100842</span></div></div>
    </div>
  </div></section>

  {{-- 11 · MORE STRIP --}}
  <section style="padding-top:0"><div class="wrap">
    <div class="more">
      <div class="tx">Dates not working for <b>your travel</b>?<span>The calendar moves daily. Tell us your dates and we watch it for you.</span></div>
      <a href="{{ $wa }}?text={{ rawurlencode('Hi Beyond Passports, the France dates shown do not fit my travel. My dates are: ') }}">@include('partials.wa-glyph')Ask us to watch the calendar</a>
    </div>
  </div></section>

  {{-- 12 · DISCLAIMER --}}
  <section style="padding:0 0 44px"><div class="wrap">
    @include('partials.disclaimer-strip', ['text' => 'Beyond Passports is an independent consultancy registered in England and Wales. We are not the government, not the French consulate, and not TLScontact. Appointment dates shown are indicative, updated daily and confirmed live with the centre before you pay. We help prepare applications and assist with appointment booking; every visa decision rests with the relevant consulate.'])
  </div></section>

</div>

<script>
(function(){
  var form = document.getElementById('vfr-form'); if (!form) return;
  var seg = document.getElementById('vfr-tf'), timeframe = '';
  seg.querySelectorAll('button').forEach(function(b){
    b.addEventListener('click', function(){
      seg.querySelectorAll('button').forEach(function(x){ x.classList.remove('sel'); });
      b.classList.add('sel'); timeframe = b.getAttribute('data-tf') || ''; seg.classList.remove('err');
    });
  });
  form.addEventListener('submit', function(e){
    e.preventDefault();
    if (!timeframe) { seg.classList.add('err'); seg.scrollIntoView({block:'center'}); return; }
    var input = document.getElementById('vfr-phone');
    var phone = (input.value || '').trim();
    if (!phone) { input.focus(); return; }
    if (phone.charAt(0) !== '+') { phone = '+44 ' + phone.replace(/[^\d]/g,'').replace(/^0+/,''); }
    var btn = form.querySelector('.sbtn'); btn.disabled = true;
    var payload = { phone: phone, dest: 'France', intent: 'France slot - ' + timeframe, utm: (window.bpUtm ? window.bpUtm() : null) };
    fetch(@json(route('lp-bold.lead')), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': @json(csrf_token()), 'Accept': 'application/json' },
      body: JSON.stringify(payload)
    }).catch(function(){}).finally(function(){
      form.hidden = true;
      var thx = form.parentNode.querySelector('.fthx'); if (thx) thx.hidden = false;
    });
  });
})();
</script>
<script type="application/ld+json">{!! json_encode(['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => array_map(fn ($f) => ['@type' => 'Question', 'name' => $f['q'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']]], $faqs)], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endsection
