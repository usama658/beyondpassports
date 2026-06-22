@extends('layouts.public')

@section('title', 'UK Visas, eVisas & ETAs — Sorted Without the Stress | Beyond Passports')
@section('description', 'Independent UK visa & eVisa service. We check, prepare and submit your application for a growing list of destinations — UK-based team, clear fixed fees, every step tracked. Not a government website.')

@push('head')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "Organization",
  "name": "Beyond Passports",
  "url": "{{ url('/') }}",
  "description": "Independent UK visa & eVisa facilitation service. Not a government website.",
  "areaServed": "GB"
}
</script>
@endpush

@push('head')
<style>
  /* Home hero — "Editorial centred" treatment. Page-scoped (hp- prefix). Soft terracotta→sage
     gradient-mesh on near-white; big centred headline, the visa-check form as one inline bar
     (destination · passport · button), trust chips + ★rating + a row of destination thumbnails. */
  .hp-hero{position:relative;color:var(--ink);overflow:hidden;border-bottom:1px solid var(--paper-edge);text-align:center;
    background:linear-gradient(180deg,#EAF1F4 0%, #F2F5F6 45%, var(--paper) 100%);}
  .hp-hero > .wrap{position:relative;z-index:2;padding:36px 0 44px}
  .hp-hero .eyebrow{color:var(--cta)}
  .hp-hero h1{color:var(--ink);font:700 clamp(32px,4.6vw,52px)/1.02 var(--display);letter-spacing:-.03em;margin:0 auto 16px}
  .hp-hero .lede{color:var(--muted);font-size:19px;line-height:1.5;max-width:52ch;margin:0 auto}
  /* inline visa-check form bar */
  .hp-bar{display:flex;gap:12px;align-items:flex-end;background:#fff;border:1px solid var(--paper-edge);border-radius:18px;
    box-shadow:0 30px 64px -30px rgba(40,50,70,.45);padding:18px;max-width:780px;margin:28px auto 0;text-align:left}
  .hp-bar .f{flex:1;min-width:0}
  .hp-bar label{display:block;font:700 12px var(--display);margin:0 0 5px;color:var(--ink)}
  .hp-bar select{width:100%;padding:12px;border:1px solid var(--paper-edge);border-radius:11px;font:inherit;font-size:15px;background:#fff;color:var(--ink)}
  .hp-bar .btn{white-space:nowrap}
  /* signature passport-stamp accent on the form card's top-right corner */
  .hp-bar{position:relative}
  .hp-bar .stamp{position:absolute;top:-22px;right:-18px;background:#fff;z-index:3}
  .hp-barhint{color:var(--muted);font-size:12px;letter-spacing:.02em;margin:12px 0 0}
  @media (max-width:520px){.hp-bar .stamp{display:none}}
  /* popular destination name quick-links — flag-dot tags (D) */
  .hp-names{display:flex;flex-wrap:wrap;gap:9px 11px;justify-content:center;align-items:center;margin:26px 0 0}
  .hp-names a,.hp-names .vchip{display:inline-flex;align-items:center;gap:8px;background:#fff;border:1px solid var(--paper-edge);border-radius:10px;padding:8px 14px;font:600 14px var(--display);color:var(--ink);text-decoration:none;box-shadow:0 6px 16px -12px rgba(40,50,70,.5);transition:border-color .2s ease,color .2s ease}
  .hp-names a .dot{width:7px;height:7px;border-radius:50%;background:var(--cta);flex:none}
  .hp-names .tick{width:15px;height:15px;flex:none;color:var(--cta)}
  .hp-names a:hover{color:var(--cta);border-color:var(--soft)}
  @media (max-width:720px){
    .hp-hero > .wrap{padding:28px 0 34px}
    .hp-bar{flex-direction:column;align-items:stretch}
  }
</style>
@endpush


@push('head')
<style>
  /* Destinations section — "split intro + 2×2 carousel" (E): heading/blurb/button left;
     right is a 2-row horizontal scroller showing 2×2 cards, paging through the rest. */
  /* DESTINATIONS — map-texture backdrop + centred 3-up glass grid (option D) */
  #destinations{background:
    radial-gradient(circle at 18% 26%, rgba(46,154,140,.10), transparent 42%),
    radial-gradient(circle at 82% 74%, rgba(21,94,122,.10), transparent 42%),
    repeating-linear-gradient(0deg, rgba(34,40,43,.03) 0 1px, transparent 1px 26px),
    var(--paper)}
  #destinations .sec-head{text-align:center;max-width:60ch;margin:0 auto}
  #destinations .sec-head .lede{margin:12px auto 0;max-width:54ch}
  #destinations .dests{display:grid;grid-template-columns:repeat(3,1fr);gap:18px;margin-top:32px}
  #destinations .pass{height:240px}
  #destinations .dest-more{text-align:center;margin-top:28px}
  @media (max-width:820px){#destinations .dests{grid-template-columns:1fr 1fr}}
  @media (max-width:520px){#destinations .dests{grid-template-columns:1fr}}

  /* WHY section — warm stamp cards on a peach tint (option C) */
  #why{background:linear-gradient(180deg,#FBF6F1,var(--paper))}
  #why .sec-head{text-align:center;max-width:60ch;margin-left:auto;margin-right:auto}
  #why .ticks{margin-top:30px}
  #why .tick{background:#fff;border:1px solid var(--paper-edge);border-radius:16px;padding:22px;gap:14px;
    box-shadow:0 10px 30px -22px rgba(40,50,70,.5);transition:transform .25s ease,box-shadow .25s ease}
  #why .tick:hover{transform:translateY(-3px);box-shadow:var(--lift-2)}
  #why .tick .stamp{flex:0 0 44px;width:44px;height:44px;padding:9px;border-radius:11px;
    background:rgba(46,154,140,.12);color:var(--stamp-text);box-sizing:border-box}

  /* TESTIMONIALS — trio of consented quote cards (option D) */
  .tquotes{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-top:30px}
  .tq{background:#fff;border:1px solid var(--paper-edge);border-radius:16px;padding:24px 22px;box-shadow:var(--shadow);margin:0;display:flex;flex-direction:column;gap:12px;transition:transform .25s ease,box-shadow .25s ease}
  .tq:hover{transform:translateY(-3px);box-shadow:var(--lift-2)}
  .tq .stars{color:var(--cta);letter-spacing:3px;font-size:14px}
  .tq blockquote{margin:0;font-family:var(--display);font-weight:600;font-size:15.5px;line-height:1.55;color:var(--ink)}
  .tq figcaption{color:var(--stamp-text);font-weight:700;font-size:13px;margin-top:auto}
  @media (max-width:760px){.tquotes{grid-template-columns:1fr}}

  /* APPOINTMENTS — map-texture backdrop + centred finder + pin motif (option C) */
  #appointments{background:
    radial-gradient(circle at 18% 30%, rgba(46,154,140,.10), transparent 42%),
    radial-gradient(circle at 82% 70%, rgba(21,94,122,.10), transparent 42%),
    repeating-linear-gradient(0deg, rgba(34,40,43,.03) 0 1px, transparent 1px 26px),
    var(--paper)}
  #appointments .sec-head{text-align:center;max-width:none}
  #appointments .sec-head h2{max-width:none}
  #appointments .sec-head p{max-width:58ch;margin-left:auto;margin-right:auto}
  #appointments .pin{display:block;margin:0 auto 12px;color:var(--cta)}
  #appointments form{margin-left:auto;margin-right:auto;justify-content:center}
  #appointments .hint{text-align:center}

  /* TRUST BAR — under hero: dark mesh micro-band (F) then warm stat band (B) */
  .tbar-b,.tbar-f{padding:0}
  .tbar-f{background:
      radial-gradient(520px 200px at 12% 0%, rgba(21,94,122,.45), transparent 60%),
      radial-gradient(520px 200px at 92% 100%, rgba(46,154,140,.42), transparent 60%),
      var(--navy);color:#fff}
  .tbar-f .row{display:flex;justify-content:center;gap:30px;flex-wrap:wrap;padding:16px 0}
  .tbar-f .ti{display:flex;align-items:center;gap:9px;font:600 14px var(--display);color:#fff;white-space:nowrap}
  .tbar-f .ti svg{width:20px;height:20px;color:var(--soft);flex:none}
  .tbar-f .ti b{color:var(--soft);font-weight:800}
  .tbar-b{background:linear-gradient(180deg,#FBF6F1,var(--paper));border-bottom:1px solid var(--paper-edge)}
  .tbar-b .row{display:grid;grid-template-columns:repeat(4,1fr);gap:18px;text-align:center;padding:24px 0}
  .tbar-b .n{font:800 clamp(24px,3vw,30px)/1 var(--display);color:var(--cta);letter-spacing:-.02em}
  .tbar-b .l{font:600 13px var(--display);color:var(--muted);margin-top:6px}
  .tbar-b .row>div+div{border-left:1px solid var(--paper-edge)}
  @media (max-width:760px){
    .tbar-b .row{grid-template-columns:1fr 1fr;gap:14px}
    .tbar-b .row>div:nth-child(odd){border-left:0}
    .tbar-f .row{gap:18px 22px}
  }
</style>
@endpush

@section('content')

{{-- HERO — "Editorial centred": big headline + inline visa-check form bar + popular destination names --}}
@php
  // Popular destination quick-links (names, not images).
  $hpDests = ($navDestinations ?? collect())->take(8);
@endphp
<section class="hp-hero"><div class="wrap">
  <p class="eyebrow">Schengen visas &middot; ETIAS &middot; eVisas &middot; driving permits</p>
  @push('head')<style>@media(max-width:640px){.hp-hero .h1-break{display:none}}</style>@endpush
  <h1>Don't lose the trip to a refused visa <br class="h1-break">or a missing appointment.</h1>
  <p class="lede">A real UK specialist gets your Schengen visa right, and books the appointment. Every document checked by hand before you submit, and you can talk to them any time.</p>

  {{-- inline visa-check form → opens a WhatsApp chat with the trip pre-filled --}}
  <form class="hp-bar" onsubmit="return false">
    <span class="stamp" aria-hidden="true">CHECKED<br>&amp; READY</span>
    <div class="f">
      <label for="dest">Where are you going?</label>
      <select id="dest"><option value="">Choose a destination…</option>@foreach ($navDestinations as $d)<option value="{{ $d->name }}">{{ $d->name }}</option>@endforeach</select>
    </div>
    <div class="f">
      <label for="nat">Your passport</label>
      <select id="nat"><option value="a UK">United Kingdom</option><option value="a non-UK">Other — we'll confirm your rules</option></select>
    </div>
    <button class="btn" type="button" id="hp-chat">See what I need · free →</button>
  </form>
  <p class="hp-barhint">A named UK visa specialist replies · usually within minutes, Mon–Sat 9–6</p>

  {{-- proof pills tight under CTA (getbrazilvisa-style credibility row) --}}
  @push('head')<style>
    .hp-proof{display:flex;flex-wrap:wrap;gap:10px 18px;justify-content:center;margin:18px 0 0;padding:0;list-style:none}
    .hp-proof li{display:inline-flex;align-items:center;gap:7px;font:600 14px var(--body);color:var(--ink)}
    .hp-proof svg{width:17px;height:17px;flex:none;color:var(--stamp-text)}
    @media(max-width:560px){.hp-proof li{font-size:13px}}
  </style>@endpush
  <div class="hp-names">
    <span class="vchip"><svg class="tick" viewBox="0 0 24 24" aria-hidden="true"><path d="m5 13 4 4 10-10" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/></svg>Appointments found in time</span>
    <span class="vchip"><svg class="tick" viewBox="0 0 24 24" aria-hidden="true"><path d="m5 13 4 4 10-10" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/></svg>Refusals prevented</span>
    <span class="vchip"><svg class="tick" viewBox="0 0 24 24" aria-hidden="true"><path d="m5 13 4 4 10-10" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/></svg>A real UK specialist, by name</span>
  </div>
  <script>
    (function () {
      var WA = @json(config('ukv.whatsapp') ?: '440000000000');
      var btn = document.getElementById('hp-chat');
      if (!btn) return;
      btn.addEventListener('click', function () {
        var dest = document.getElementById('dest');
        var nat = document.getElementById('nat');
        var place = dest && dest.value ? dest.value : '';
        var pass = nat && nat.value ? nat.value : 'a UK';
        var msg = place
          ? 'Hi Beyond Passports — I am travelling to ' + place + ' on ' + pass + ' passport. What do I need?'
          : 'Hi Beyond Passports — I would like help working out what I need for my trip.';
        window.open('https://wa.me/' + WA + '?text=' + encodeURIComponent(msg), '_blank', 'noopener');
      });
    })();
  </script>

</div></section>

{{-- READINESS CHECK — lead-magnet front door, just after hero --}}
@push('head')
<style>
  #readiness{background:linear-gradient(180deg,var(--paper),#FBF6F1)}
  #readiness .scard{background:#fff;border:1px solid var(--paper-edge);border-radius:22px;padding:30px;
    box-shadow:0 26px 60px -38px rgba(30,40,60,.6);display:grid;grid-template-columns:1.05fr .95fr;gap:30px;align-items:center}
  #readiness .pill{display:inline-flex;align-items:center;gap:7px;background:rgba(46,154,140,.12);color:var(--stamp-text);
    font:800 12px var(--display);letter-spacing:.06em;text-transform:uppercase;padding:6px 12px;border-radius:999px}
  #readiness h2{font:800 clamp(26px,3.4vw,36px)/1.08 var(--display);color:var(--ink);letter-spacing:-.02em;margin:13px 0 0}
  #readiness .sub{color:var(--muted);font-size:16px;line-height:1.5;margin:10px 0 0;max-width:42ch}
  #readiness .micro{color:var(--muted);font-size:12px;margin:12px 0 0}
  #readiness .qbox{background:var(--paper);border:1px solid var(--paper-edge);border-radius:16px;padding:20px}
  #readiness .qbox label{font:700 13px var(--display);color:var(--ink);display:block;margin:0 0 5px}
  #readiness .qbox select,#readiness .qbox input{width:100%;box-sizing:border-box;padding:11px 12px;
    border:1px solid var(--paper-edge);border-radius:10px;font-size:15px;background:#fff;margin-bottom:13px}
  #readiness .qbox .btn{width:100%;justify-content:center}
  #readiness .qmeta{color:var(--muted);font-size:12.5px;margin:10px 0 0;text-align:center}
  @media(max-width:760px){#readiness .scard{grid-template-columns:1fr;gap:22px}}
</style>
@endpush
{{-- TRUST BAR — dark mesh trust-points band (F) then warm stat band (B) --}}
<section class="tbar-f"><div class="wrap"><div class="row">
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M13 2 4 14h7l-1 8 10-12h-7z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg><span><b>Fast</b> appointments</span></span>
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 5 6v6c0 4.5 3 7.5 7 8.5 4-1 7-4 7-8.5V6z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="m9 12 2 2 4-4.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg><span><b>No</b> rejections</span></span>
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12a7 7 0 0 1 14 0" fill="none" stroke="currentColor" stroke-width="2"/><path d="M4 13a2 2 0 0 1 2-2h1v6H6a2 2 0 0 1-2-2zM20 13a2 2 0 0 0-2-2h-1v6h1a2 2 0 0 0 2-2z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg><span><b>Dedicated</b> UK support</span></span>
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7-10-7-10-7z" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="2.6" fill="none" stroke="currentColor" stroke-width="2"/></svg><span>Every step <b>tracked</b></span></span>
</div></div></section>
<section class="tbar-b"><div class="wrap"><div class="row">
  <div><div class="n">4.9★</div><div class="l">Average rating</div></div>
  <div><div class="n">12,000+</div><div class="l">Trips sorted</div></div>
  <div><div class="n">{{ ($navDestinations ?? collect())->count() }}</div><div class="l">Destinations &amp; growing</div></div>
  <div><div class="n">UK</div><div class="l">Based team &amp; support</div></div>
</div></div></section>

{{-- PROBLEM + READINESS CHECK — joined: fears on the left, the check that answers them on the right --}}
@push('head')
<style>
  #joined{background:#fff;border-block:1px solid var(--paper-edge)}
  #joined .row{display:grid;grid-template-columns:1fr .92fr;gap:50px;align-items:center}
  #joined .eyebrow{color:var(--cta)}
  #joined h2{font:800 clamp(27px,3.5vw,40px)/1.06 var(--display);color:var(--ink);letter-spacing:-.025em;margin:10px 0 0}
  #joined .lead{color:var(--muted);font-size:16.5px;line-height:1.5;margin:13px 0 22px;max-width:40ch}
  #joined .it{display:flex;gap:14px;align-items:center;padding:15px 0;border-top:1px solid var(--paper-edge)}
  #joined .it:first-of-type{border-top:0}
  #joined .ic{flex:none;width:40px;height:40px;border-radius:11px;background:rgba(180,101,74,.10);color:#B4654A;display:flex;align-items:center;justify-content:center}
  #joined .ic svg{width:21px;height:21px}
  #joined .it b{display:block;font:800 16px var(--display);color:var(--ink);margin-bottom:2px}
  #joined .it p{color:var(--muted);font-size:14px;line-height:1.45;margin:0}
  #joined .card{background:linear-gradient(180deg,#fff,#FBF7F2);border:1px solid var(--paper-edge);border-radius:20px;padding:28px;box-shadow:0 30px 70px -42px rgba(30,40,60,.65)}
  #joined .card .pill{display:inline-flex;align-items:center;gap:7px;background:rgba(46,154,140,.12);color:var(--stamp-text);font:800 12px var(--display);letter-spacing:.05em;text-transform:uppercase;padding:6px 12px;border-radius:999px}
  #joined .card h3{font:800 22px/1.15 var(--display);color:var(--ink);margin:13px 0 0;letter-spacing:-.01em}
  #joined .card .sub{color:var(--muted);font-size:14.5px;line-height:1.5;margin:8px 0 0}
  #joined .card label{display:block;font:700 13px var(--display);color:var(--ink);margin:16px 0 5px}
  #joined .card select,#joined .card input{width:100%;box-sizing:border-box;padding:11px 12px;border:1px solid var(--paper-edge);border-radius:10px;font-size:15px;background:#fff}
  #joined .card .btn{display:block;width:100%;text-align:center;margin-top:16px}
  #joined .card .micro{color:var(--muted);font-size:12px;margin:11px 0 0;text-align:center}
  @media(max-width:860px){#joined .row{grid-template-columns:1fr;gap:30px}}
</style>
@endpush
<section id="joined"><div class="wrap"><div class="row">
  <div class="reveal">
    <p class="eyebrow">Why this goes wrong</p>
    <h2>Don't hand the embassy a reason to say no.</h2>
    <p class="lead">A refusal is usually one missed detail, or an appointment booked too late.</p>
    <div class="it"><span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="m9 15 2 2 4-4"/></svg></span><div><b>One wrong document</b><p>A statement, an insurance date, a mis-ticked box. The embassy says no.</p></div></div>
    <div class="it"><span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span><div><b>No appointment in time</b><p>Slots vanish in minutes. The next one lands after you fly.</p></div></div>
    <div class="it"><span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.3 3.6 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.6a2 2 0 0 0-3.4 0z"/><path d="M12 9v4M12 17h.01"/></svg></span><div><b>Refused once already</b><p>A second no is harder. The reapplication has to fix the real reason.</p></div></div>
  </div>
  <div class="card reveal">
    <span class="pill">Free · 60 seconds · no sign-up</span>
    <h3>Will your visa pass? Check before you submit.</h3>
    <p class="sub">See the gaps that get Schengen visas refused, and whether your travel date is still realistic.</p>
    <form onsubmit="return false">
      <label for="rk-dest">Where are you going?</label>
      <select id="rk-dest"><option value="">Choose a destination…</option>@foreach ($navDestinations as $d)<option value="{{ $d->name }}">{{ $d->name }}</option>@endforeach</select>
      <label for="rk-date">When do you travel?</label>
      <input id="rk-date" type="date">
      <button class="btn" type="button" id="rk-go">Start my free check →</button>
    </form>
    <p class="micro">A readiness indicator, not an approval prediction. Your answers stay private.</p>
  </div>
</div></div>
  <script>
    (function () {
      var WA = @json(config('ukv.whatsapp') ?: '440000000000');
      var btn = document.getElementById('rk-go');
      if (!btn) return;
      btn.addEventListener('click', function () {
        var dest = document.getElementById('rk-dest');
        var date = document.getElementById('rk-date');
        var place = dest && dest.value ? dest.value : '';
        var when = date && date.value ? date.value : '';
        var msg = 'Hi Beyond Passports, I would like a free readiness check for my Schengen application.'
          + (place ? ' Travelling to ' + place + '.' : '')
          + (when ? ' Travel date ' + when + '.' : '')
          + ' Will it pass, and can I get an appointment in time?';
        window.open('https://wa.me/' + WA + '?text=' + encodeURIComponent(msg), '_blank', 'noopener');
      });
    })();
  </script>
</section>

{{-- HOW --}}
<section id="how"><div class="wrap">
  <div class="sec-head reveal" style="text-align:center;max-width:60ch;margin:0 auto 36px">
    <p class="eyebrow">How it works</p>
    <h2>3 simple steps to get started</h2>
    <p class="lede" style="margin:12px auto 0;max-width:54ch">A straightforward process that helps UK residents prepare for their Schengen visa journey, with expert guidance at every stage.</p>
  </div>
  <div class="steps">
    <div class="step reveal" id="step-01"><div class="num">01</div><div class="rule"></div><h3>Embassy match</h3><p>We identify the right embassy and visa category for your travel plans.</p></div>
    <div class="step reveal" id="step-02"><div class="num">02</div><div class="rule"></div><h3>Document checklist</h3><p>A personalised document checklist for your circumstances, every item checked by hand.</p></div>
    <div class="step reveal" id="step-03"><div class="num">03</div><div class="rule"></div><h3>Booking support</h3><p>Expert guidance through the appointment booking process and your next steps.</p></div>
  </div>
  <div style="text-align:center;margin-top:28px"><a class="btn" href="https://wa.me/{{ config('ukv.whatsapp') ?: '440000000000' }}?text={{ urlencode('Hi Beyond Passports, I would like to start my Schengen application.') }}" target="_blank" rel="noopener">Start your journey →</a></div>
</div></section>

{{-- DESTINATIONS — map-texture backdrop + centred 3-up glass grid (D), region-tab filtered --}}
@php
  // Region tabs are driven by the real `region` column (set by SchengenSeeder).
  $regionOrder = ['Western Europe', 'Southern Europe', 'Northern Europe', 'Central & Eastern Europe'];
  $regionCounts = [];
  foreach ($navDestinations as $d) {
    $r = $d->region ?: 'Worldwide';
    $regionCounts[$r] = ($regionCounts[$r] ?? 0) + 1;
  }
  // Preferred order first, then any extras (e.g. Worldwide for non-Schengen).
  $regionsPresent = collect($regionOrder)->filter(fn ($r) => ! empty($regionCounts[$r]))
      ->merge(collect(array_keys($regionCounts))->reject(fn ($r) => in_array($r, $regionOrder, true)))
      ->values();
  // "See all" target: Schengen regions deep-link to the filtered hub; others to the full list.
  $regionUrl = fn ($r) => in_array($r, $regionOrder, true)
      ? url('/visa/schengen').'?region='.urlencode($r)
      : url('/destinations');
@endphp
@push('head')
<style>
  #destinations .dtabs{display:flex;flex-wrap:nowrap;gap:10px;justify-content:center;margin:30px 0 28px;
    overflow-x:auto;scrollbar-width:none;-webkit-overflow-scrolling:touch;padding-bottom:2px}
  #destinations .dtabs::-webkit-scrollbar{display:none}
  @media(max-width:720px){#destinations .dtabs{justify-content:flex-start}}
  #destinations .dtab{display:inline-flex;align-items:center;gap:8px;flex:0 0 auto;white-space:nowrap;background:#fff;border:1px solid var(--paper-edge);color:var(--ink);
    font:700 14px var(--display);padding:9px 15px;border-radius:10px;cursor:pointer;
    box-shadow:0 6px 16px -12px rgba(40,50,70,.5);transition:border-color .2s,color .2s,background .2s,box-shadow .2s}
  #destinations .dtab .tk{width:15px;height:15px;flex:none;opacity:0;transition:opacity .2s}
  #destinations .dtab .tk svg{width:100%;height:100%;fill:none;stroke:currentColor;stroke-width:2.6;stroke-linecap:round;stroke-linejoin:round}
  #destinations .dtab .c{font-size:12px;font-weight:800;color:var(--muted)}
  #destinations .dtab:hover{border-color:var(--soft);color:var(--cta)}
  #destinations .dtab.active{background:var(--cta);border-color:var(--cta);color:#fff;box-shadow:0 12px 26px -14px rgba(21,94,122,.6)}
  #destinations .dtab.active .tk{opacity:1}
  #destinations .dtab.active .c{color:rgba(255,255,255,.82)}
  #destinations .dest-empty{display:none;text-align:center;color:var(--muted);padding:24px 0}
</style>
@endpush
<section id="destinations"><div class="wrap">
  <div class="sec-head reveal">
    <p class="eyebrow">Popular destinations</p>
    <h2>Every Schengen requirement, checked{{ config('ukv.show_prices') ? ' at a fixed fee' : '' }}.</h2>
    <p class="lede">Pick your destination. Every document reviewed by hand before you submit.</p>
  </div>
  <div class="dtabs" id="dtabs">
    <button type="button" class="dtab active" data-region="all" data-url="{{ url('/destinations') }}" data-label="destinations"><span class="tk"><svg viewBox="0 0 24 24"><path d="m5 13 4 4 10-10"/></svg></span>Popular</button>
    @foreach ($regionsPresent as $rk)
      <button type="button" class="dtab" data-region="{{ $rk }}" data-url="{{ $regionUrl($rk) }}" data-label="{{ $rk }}"><span class="tk"><svg viewBox="0 0 24 24"><path d="m5 13 4 4 10-10"/></svg></span>{{ str_replace(' Europe', '', $rk) }} <span class="c">{{ $regionCounts[$rk] }}</span></button>
    @endforeach
  </div>
  <div class="dests" id="dests">
  @foreach ($navDestinations as $d)
    <a class="pass reveal" data-region="{{ $d->region ?: 'Worldwide' }}" @if ($loop->index < 6) data-pop="1" @endif href="{{ url('/visa/'.$d->slug) }}"><div class="sky">@if ($d->image_path)<img src="{{ asset(ltrim($d->image_path, '/')) }}" alt="{{ $d->name }}" loading="lazy">@else<svg viewBox="0 0 240 96" preserveAspectRatio="xMidYMax meet" role="img" aria-label="{{ $d->name }} skyline"><use href="#ukv-skyline"></use></svg>@endif</div><div class="lower"><div class="main"><div class="k">{{ $d->visa_type }}</div><h3>{{ $d->name }}</h3><div class="t">UK citizens{{ $d->max_stay_days ? ' · up to '.$d->max_stay_days.' days' : '' }}</div></div><div class="stub">
@if (config('ukv.show_prices') && (float) $d->tier_standard_gbp > 0)
<div class="fee">£{{ number_format((float) $d->tier_standard_gbp, 0) }}</div><div class="lab">FROM</div>
@elseif (config('ukv.show_prices'))
<div class="fee">Free</div><div class="lab">GUIDE</div>
@elseif ($d->max_stay_days)
<div class="fee">{{ $d->max_stay_days }}</div><div class="lab">DAYS MAX</div>
@else
<div class="fee">View&nbsp;→</div><div class="lab">Details</div>
@endif
</div></div></a>
  @endforeach
  </div>
  <p class="dest-empty" id="dest-empty">More destinations in this region coming soon.</p>
  <div class="dest-more"><a id="dest-see-all" class="rlink" style="font-weight:600" href="{{ url('/destinations') }}">See all destinations @if(config('ukv.show_prices'))&amp; fixed fees @endif→</a></div>
  <script>
    (function () {
      var tabs = document.querySelectorAll('#dtabs .dtab');
      var cards = document.querySelectorAll('#dests .pass');
      var empty = document.getElementById('dest-empty');
      var seeAll = document.getElementById('dest-see-all');
      var feeSuffix = @json(config('ukv.show_prices') ? ' & fixed fees' : '');
      function apply(tab) {
        var region = tab.dataset.region, shown = 0;
        cards.forEach(function (c) {
          var match = region === 'all' ? c.dataset.pop === '1' : c.dataset.region === region;
          var show = match && shown < 6;
          c.style.display = show ? '' : 'none';
          if (show) shown++;
        });
        if (empty) empty.style.display = shown ? 'none' : 'block';
        if (seeAll) {
          seeAll.href = tab.dataset.url;
          seeAll.textContent = 'See all ' + (tab.dataset.label || 'destinations') + feeSuffix + ' →';
        }
      }
      tabs.forEach(function (t) {
        t.addEventListener('click', function () {
          tabs.forEach(function (x) { x.classList.remove('active'); });
          t.classList.add('active');
          apply(t);
        });
      });
      var first = document.querySelector('#dtabs .dtab.active') || tabs[0];
      if (first) apply(first);
    })();
  </script>
</div></section>

{{-- WHAT WE DO — six-service grid (Why-style cards), below destinations --}}
@push('head')
<style>
  #services{background:linear-gradient(180deg,#FBF6F1,var(--paper))}
  #services .sec-head{text-align:center;max-width:60ch;margin-left:auto;margin-right:auto}
  #services .sec-head .lede{margin:12px auto 0;max-width:52ch}
  #services .ticks{margin-top:30px}
  #services .tick{background:#fff;border:1px solid var(--paper-edge);border-radius:16px;padding:22px;gap:14px;
    box-shadow:0 10px 30px -22px rgba(40,50,70,.5);transition:transform .25s ease,box-shadow .25s ease}
  #services .tick:hover{transform:translateY(-3px);box-shadow:var(--lift-2)}
  #services .tick .stamp{flex:0 0 44px;width:44px;height:44px;padding:9px;border-radius:11px;
    background:rgba(46,154,140,.12);color:var(--stamp-text);box-sizing:border-box}
</style>
@endpush
<section id="services"><div class="wrap">
  <div class="sec-head reveal"><p class="eyebrow">What we do</p><h2>Everything your Schengen application needs</h2><p class="lede">End-to-end help, from eligibility to the embassy door.</p></div>
  <div class="ticks">
    <div class="tick reveal"><svg class="stamp" width="38" height="38" viewBox="0 0 48 48" role="img" aria-label="Checked"><use href="#ukv-stamp"></use></svg><div><h3>Eligibility check</h3><p>We confirm you can apply, and on the right visa, before you spend anything.</p></div></div>
    <div class="tick reveal"><svg class="stamp" width="38" height="38" viewBox="0 0 48 48" role="img" aria-label="Checked"><use href="#ukv-stamp"></use></svg><div><h3>Document review</h3><p>Every document checked by hand against the current embassy rules.</p></div></div>
    <div class="tick reveal"><svg class="stamp" width="38" height="38" viewBox="0 0 48 48" role="img" aria-label="Checked"><use href="#ukv-stamp"></use></svg><div><h3>Form completion</h3><p>We fill and check the application so small errors do not creep in.</p></div></div>
    <div class="tick reveal"><svg class="stamp" width="38" height="38" viewBox="0 0 48 48" role="img" aria-label="Checked"><use href="#ukv-stamp"></use></svg><div><h3>Appointment booking</h3><p>We find a biometric slot in time for your travel date.</p></div></div>
    <div class="tick reveal"><svg class="stamp" width="38" height="38" viewBox="0 0 48 48" role="img" aria-label="Checked"><use href="#ukv-stamp"></use></svg><div><h3>Submission &amp; follow-up</h3><p>We track your application and keep you posted at every step.</p></div></div>
    <div class="tick reveal"><svg class="stamp" width="38" height="38" viewBox="0 0 48 48" role="img" aria-label="Checked"><use href="#ukv-stamp"></use></svg><div><h3>Refused before?</h3><p>We work out why and fix it before you reapply.</p></div></div>
  </div>
</div></section>

{{-- HOW WE PREVENT REFUSALS --}}
@include('partials.home-prevention-b')

{{-- TESTIMONIALS — trio of consented quote cards (real anonymised reviews, single source) --}}
@php $homeQuotes = array_slice(\App\Http\Controllers\ReviewController::all(), 0, 3); @endphp
<section class="alt"><div class="wrap">
  <div class="sec-head reveal" style="text-align:center;max-width:60ch;margin:0 auto 6px">
    <p class="eyebrow">Trusted by UK travellers</p>
    <h2>Real people, really sorted</h2>
    @include('partials.trustpilot')
  </div>
  <div class="tquotes">
    @foreach ($homeQuotes as $t)
    <figure class="tq reveal">
      <div class="stars" aria-label="{{ $t['rating'] ?? 5 }} out of 5 stars">{!! str_repeat('★', $t['rating'] ?? 5) !!}</div>
      <blockquote>{{ $t['quote'] }}</blockquote>
      <figcaption>— {{ $t['attribution'] }}</figcaption>
    </figure>
    @endforeach
  </div>
  <p style="text-align:center;margin-top:24px"><a class="rlink" style="font-weight:600" href="{{ url('/reviews') }}">Read more traveller reviews →</a></p>
</div></section>

{{-- APPOINTMENTS / NEAREST CENTRE --}}
<section id="appointments" class="alt"><div class="wrap reveal">
  <div class="sec-head">
    <svg class="pin" width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
    <p class="eyebrow">In-person centres</p>
    @if (($slotSummary['available_count'] ?? 0) > 0)
      <h2>Grab an appointment before it's gone</h2>
      @push('head')<style>
        #appointments .ap-chips{display:flex;flex-wrap:wrap;gap:10px;justify-content:center;margin:14px 0 0;padding:0;list-style:none}
        #appointments .ap-chips li{display:inline-flex;align-items:center;gap:8px;font:700 14px var(--display);padding:9px 15px;border-radius:999px}
        #appointments .ap-chips .live{background:rgba(46,154,140,.10);border:1px solid rgba(46,154,140,.22);color:var(--stamp-text)}
        #appointments .ap-chips .meta{background:#fff;border:1px solid var(--paper-edge);color:var(--ink)}
        #appointments .ap-chips .meta svg{width:16px;height:16px;color:var(--cta);flex:none}
        #appointments .ap-chips .pulse{width:8px;height:8px;border-radius:50%;background:var(--stamp);box-shadow:0 0 0 0 rgba(46,154,140,.55);animation:apPulse 2s infinite;flex:none}
        @keyframes apPulse{0%{box-shadow:0 0 0 0 rgba(46,154,140,.5)}70%{box-shadow:0 0 0 7px rgba(46,154,140,0)}100%{box-shadow:0 0 0 0 rgba(46,154,140,0)}}
      </style>@endpush
      <ul class="ap-chips">
        <li class="live"><span class="pulse" aria-hidden="true"></span>{{ $slotSummary['available_count'] }} appointment{{ $slotSummary['available_count'] === 1 ? '' : 's' }} available</li>
        @if (! empty($slotSummary['next_slot_at']))<li class="meta"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>Next on {{ $slotSummary['next_slot_at']->format('j M Y') }}</li>@endif
        @if (($slotSummary['centre_count'] ?? 0) > 0)<li class="meta"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2a7 7 0 0 0-7 7c0 5 7 13 7 13s7-8 7-13a7 7 0 0 0-7-7z"/><circle cx="12" cy="9" r="2.5"/></svg>Across {{ $slotSummary['centre_count'] }} centre{{ $slotSummary['centre_count'] === 1 ? '' : 's' }}</li>@endif
      </ul>
      <p style="max-width:58ch;color:var(--muted);margin-top:12px">Enter your postcode to find the one nearest you.</p>
    @else
      <h2>Find your nearest centre</h2>
      <p style="max-width:58ch;color:var(--muted)">For visas or IDPs that need an in-person visit, enter your postcode and we'll show the closest centre — so you don't have to go hunting.</p>
    @endif
  </div>
  <form method="GET" action="{{ route('centre.search') }}" style="display:flex;flex-wrap:wrap;gap:10px;margin-top:8px;max-width:520px">
    <input type="text" name="postcode" placeholder="e.g. SW1A 1AA" autocomplete="postal-code" required aria-label="Your postcode"
           style="flex:1;min-width:200px;padding:12px;border:1px solid var(--paper-edge);border-radius:8px;font:inherit;font-size:15px">
    <button type="submit" class="btn">Find nearest →</button>
  </form>
  <p class="hint" style="margin-top:10px"><a href="{{ url('/find-a-centre') }}">Browse the full centre finder →</a> · Most visas are online (eVisa/ETA) and need no appointment.</p>
</div></section>

{{-- CTA — hidden for now (per request) --}}
@if (false)
<section class="cta-band"><div class="wrap reveal">
  <div class="rule"></div>
  <h2>Let's get you travelling</h2>
  <p style="max-width:48ch;color:rgba(255,255,255,.85)">Start your application now, or message our UK team with any question.</p>
  <div class="row"><a href="{{ url('/apply') }}" class="btn">Start my application →</a><a href="https://wa.me/{{ config('ukv.whatsapp') ?: '440000000000' }}" class="btn btn--glass">Chat on WhatsApp</a></div>
</div></section>
@endif

@endsection
