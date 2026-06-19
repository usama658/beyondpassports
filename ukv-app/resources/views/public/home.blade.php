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
  .hp-hero > .wrap{position:relative;z-index:2;padding:60px 0 72px}
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
  .hp-names a{display:inline-flex;align-items:center;gap:8px;background:#fff;border:1px solid var(--paper-edge);border-radius:10px;padding:8px 14px;font:600 14px var(--display);color:var(--ink);text-decoration:none;box-shadow:0 6px 16px -12px rgba(40,50,70,.5);transition:border-color .2s ease,color .2s ease}
  .hp-names a .dot{width:7px;height:7px;border-radius:50%;background:var(--cta);flex:none}
  .hp-names a:hover{color:var(--cta);border-color:var(--soft)}
  @media (max-width:720px){
    .hp-hero > .wrap{padding:44px 0 52px}
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
  <p class="eyebrow">UK visas &middot; eVisas &middot; ETAs</p>
  <h1>Sorted, without the stress.</h1>
  <p class="lede">Tell us where you're going — we confirm exactly what you need and handle the paperwork.</p>

  {{-- inline visa-check form (real destination list + apply) --}}
  <form class="hp-bar" onsubmit="return false">
    <span class="stamp" aria-hidden="true">CHECKED<br>&amp; READY</span>
    <div class="f">
      <label for="dest">Where are you going?</label>
      <select id="dest"><option>Choose a destination…</option>@foreach ($navDestinations as $d)<option>{{ $d->name }}</option>@endforeach</select>
    </div>
    <div class="f">
      <label for="nat">Your passport</label>
      <select id="nat"><option>United Kingdom</option><option>Other — we'll confirm your rules</option></select>
    </div>
    <button class="btn" type="button" onclick="location.href='{{ url('/apply') }}'">Check what I need →</button>
  </form>
  <p class="hp-barhint">No account needed · takes 30 seconds</p>

  @if ($hpDests->count())
  <div class="hp-names">
    @foreach ($hpDests as $d)
      <a href="{{ url('/visa/'.$d->slug) }}"><span class="dot" aria-hidden="true"></span>{{ $d->name }}</a>
    @endforeach
  </div>
  @endif
</div></section>

{{-- TRUST BAR — dark mesh trust-points band (F) then warm stat band (B) --}}
<section class="tbar-f"><div class="wrap"><div class="row">
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 21V4m0 0 7 2 7-2v10l-7 2-7-2" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg><span><b>UK-based</b> team</span></span>
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="2" fill="none" stroke="currentColor" stroke-width="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3" fill="none" stroke="currentColor" stroke-width="2"/></svg><span>Secure <b>Stripe</b> payments</span></span>
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 12 12 3h7v7l-9 9z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><circle cx="15" cy="8" r="1.4" fill="currentColor"/></svg><span><b>Fixed</b> fees, shown up front</span></span>
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7-10-7-10-7z" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="2.6" fill="none" stroke="currentColor" stroke-width="2"/></svg><span>Every step <b>tracked</b></span></span>
</div></div></section>
<section class="tbar-b"><div class="wrap"><div class="row">
  <div><div class="n">4.9★</div><div class="l">Average rating</div></div>
  <div><div class="n">12,000+</div><div class="l">Trips sorted</div></div>
  <div><div class="n">{{ ($navDestinations ?? collect())->count() }}</div><div class="l">Destinations &amp; growing</div></div>
  <div><div class="n">UK</div><div class="l">Based team &amp; support</div></div>
</div></div></section>

{{-- HOW --}}
<section id="how"><div class="wrap">
  <div class="sec-head reveal"><p class="eyebrow">The process</p><h2>Three simple steps</h2></div>
  <div class="steps">
    <div class="step reveal" id="step-01"><div class="num">01</div><div class="rule"></div><h3>Tell us your trip</h3><p>Pick your destination and answer a few questions. We confirm exactly what you need.</p></div>
    <div class="step reveal" id="step-02"><div class="num">02</div><div class="rule"></div><h3>We prepare &amp; check</h3><p>Our team reviews your documents for errors before anything is submitted.</p></div>
    <div class="step reveal" id="step-03"><div class="num">03</div><div class="rule"></div><h3>Submit &amp; track</h3><p>We handle the submission and keep you updated until it's delivered.</p></div>
  </div>
</div></section>

{{-- DESTINATIONS — map-texture backdrop + centred 3-up glass grid (D) --}}
<section id="destinations"><div class="wrap">
  <div class="sec-head reveal">
    <p class="eyebrow">Popular destinations</p>
    <h2>Clear requirements, fixed fees</h2>
    <p class="lede">Browse the places we prepare and check applications for — clear fixed fees, every step tracked.</p>
  </div>
  <div class="dests">
  @foreach ($navDestinations->take(6) as $d)
    <a class="pass reveal" href="{{ url('/visa/'.$d->slug) }}"><div class="sky">@if ($d->image_path)<img src="{{ asset(ltrim($d->image_path, '/')) }}" alt="{{ $d->name }}" loading="lazy">@else<svg viewBox="0 0 240 96" preserveAspectRatio="xMidYMax meet" role="img" aria-label="{{ $d->name }} skyline"><use href="#ukv-skyline"></use></svg>@endif</div><div class="lower"><div class="main"><div class="k">{{ $d->visa_type }}</div><h3>{{ $d->name }}</h3><div class="t">UK citizens{{ $d->max_stay_days ? ' · up to '.$d->max_stay_days.' days' : '' }}</div></div><div class="stub">@if ((float) $d->tier_standard_gbp > 0)<div class="fee">£{{ number_format((float) $d->tier_standard_gbp, 0) }}</div><div class="lab">FROM</div>@else<div class="fee">Free</div><div class="lab">GUIDE</div>@endif</div></div></a>
  @endforeach
  </div>
  <div class="dest-more"><a class="rlink" style="font-weight:600" href="{{ url('/destinations') }}">See all destinations &amp; fixed fees →</a></div>
</div></section>

{{-- WHY --}}
<section id="why"><div class="wrap">
  <div class="sec-head reveal"><p class="eyebrow">Why travellers choose us</p><h2>We make sure it's right</h2></div>
  <div class="ticks">
    <div class="tick reveal"><svg class="stamp" width="38" height="38" viewBox="0 0 48 48" role="img" aria-label="Checked"><use href="#ukv-stamp"></use></svg><div><h3>We catch errors before they cost you</h3><p>A human checks every document before submission.</p></div></div>
    <div class="tick reveal"><svg class="stamp" width="38" height="38" viewBox="0 0 48 48" role="img" aria-label="Checked"><use href="#ukv-stamp"></use></svg><div><h3>One clear fee, no surprises</h3><p>Our service fee is separate from the government fee — shown up front.</p></div></div>
    <div class="tick reveal"><svg class="stamp" width="38" height="38" viewBox="0 0 48 48" role="img" aria-label="Checked"><use href="#ukv-stamp"></use></svg><div><h3>Express when you're short on time</h3><p>Express speeds our handling — not the government's decision.</p></div></div>
    <div class="tick reveal"><svg class="stamp" width="38" height="38" viewBox="0 0 48 48" role="img" aria-label="Checked"><use href="#ukv-stamp"></use></svg><div><h3>Honest advice</h3><p>We'll tell you if you don't need us. No false promises on approval.</p></div></div>
  </div>
</div></section>

{{-- TESTIMONIALS — trio of consented quote cards (real anonymised reviews, single source) --}}
@php $homeQuotes = array_slice(\App\Http\Controllers\ReviewController::all(), 0, 3); @endphp
<section class="alt"><div class="wrap">
  <div class="sec-head reveal" style="text-align:center;max-width:60ch;margin:0 auto 6px">
    <p class="eyebrow">Trusted by UK travellers</p>
    <h2>Real people, really sorted</h2>
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
      <h2>Appointments ready when you are</h2>
      <p style="max-width:58ch;color:var(--muted)">
        <strong>{{ $slotSummary['available_count'] }} appointment{{ $slotSummary['available_count'] === 1 ? '' : 's' }} available</strong>@if (! empty($slotSummary['next_slot_at'])) — next on {{ $slotSummary['next_slot_at']->format('j M Y') }}@endif@if (($slotSummary['centre_count'] ?? 0) > 0), across {{ $slotSummary['centre_count'] }} centre{{ $slotSummary['centre_count'] === 1 ? '' : 's' }}@endif. Enter your postcode to find the one nearest you.
      </p>
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
