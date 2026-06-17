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
  .hp-rating{display:inline-flex;gap:10px;align-items:center;margin:16px 0 0;font:700 14px var(--display);color:var(--stamp-text)}
  .hp-rating b{color:var(--cta)}
  .hp-rating .dot{color:var(--hint)}
  /* inline visa-check form bar */
  .hp-bar{display:flex;gap:12px;align-items:flex-end;background:#fff;border:1px solid var(--paper-edge);border-radius:18px;
    box-shadow:0 30px 64px -30px rgba(40,50,70,.45);padding:18px;max-width:780px;margin:28px auto 0;text-align:left}
  .hp-bar .f{flex:1;min-width:0}
  .hp-bar label{display:block;font:700 12px var(--display);margin:0 0 5px;color:var(--ink)}
  .hp-bar select{width:100%;padding:12px;border:1px solid var(--paper-edge);border-radius:11px;font:inherit;font-size:15px;background:#fff;color:var(--ink)}
  .hp-bar .btn{white-space:nowrap}
  .hp-barhint{color:var(--muted);font-size:12px;letter-spacing:.02em;margin:12px 0 0}
  .hp-trust{display:flex;flex-wrap:wrap;gap:10px;justify-content:center;margin:22px 0 0}
  .hp-trust span{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.72);backdrop-filter:blur(6px);border:1px solid var(--paper-edge);border-radius:999px;padding:8px 15px;font-size:13.5px;color:var(--ink)}
  .hp-trust span b{color:var(--cta);font-weight:700}
  .hp-thumbs{display:flex;gap:8px;justify-content:center;flex-wrap:wrap;margin:24px 0 0}
  .hp-thumbs a{width:68px;height:46px;border-radius:10px;overflow:hidden;display:block;box-shadow:0 8px 18px -10px rgba(40,50,70,.4)}
  .hp-thumbs img{width:100%;height:100%;object-fit:cover;display:block;transition:transform .3s ease}
  .hp-thumbs a:hover img{transform:scale(1.08)}
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
  .dest-split{display:grid;grid-template-columns:.82fr 1.18fr;gap:44px;align-items:center}
  .dest-intro{align-self:center}
  .dest-intro .lede{font-size:16px;margin:14px 0 0}
  .dest-intro .btn{margin-top:20px}
  .dest-nav{display:flex;justify-content:flex-end;gap:8px;margin:0 0 12px}
  .dest-nav button{width:40px;height:40px;border-radius:50%;border:1px solid var(--paper-edge);background:#fff;color:var(--cta);font:800 18px var(--display);cursor:pointer;line-height:1}
  .dest-nav button:hover{box-shadow:0 0 0 3px rgba(199,93,56,.14)}
  #destinations .dests{display:grid;grid-auto-flow:column;grid-template-rows:1fr;grid-template-columns:none;
    grid-auto-columns:calc(50% - 9px);gap:18px;overflow-x:auto;padding-bottom:10px;scrollbar-width:none}
  #destinations .dests::-webkit-scrollbar{display:none}
  #destinations .pass{height:250px}
  @media (max-width:860px){.dest-split{grid-template-columns:1fr;gap:28px}.dest-intro{position:static}}
  @media (max-width:520px){#destinations .dests{grid-auto-columns:calc(85%)}}

  /* WHY section — dark mesh band + frosted-glass reassurance cards (option B) */
  #why{position:relative;overflow:hidden;color:#fff;
    background:
      radial-gradient(640px 280px at 12% 0%, rgba(199,93,56,.5), transparent 60%),
      radial-gradient(600px 260px at 92% 100%, rgba(92,154,123,.5), transparent 60%),
      var(--navy)}
  #why .eyebrow{color:#F2C2AC}
  #why .sec-head h2{color:#fff}
  #why .tick{background:rgba(255,255,255,.10);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.18);
    border-radius:16px;padding:20px 22px;transition:transform .25s ease,background .25s ease}
  #why .tick:hover{transform:translateY(-3px);background:rgba(255,255,255,.14)}
  #why .tick h3{color:#fff}
  #why .tick p{color:rgba(255,255,255,.82)}
  #why .tick .stamp{filter:drop-shadow(0 2px 6px rgba(0,0,0,.3))}

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
    radial-gradient(circle at 18% 30%, rgba(92,154,123,.10), transparent 42%),
    radial-gradient(circle at 82% 70%, rgba(199,93,56,.10), transparent 42%),
    repeating-linear-gradient(0deg, rgba(34,40,43,.03) 0 1px, transparent 1px 26px),
    var(--paper)}
  #appointments .sec-head{text-align:center;max-width:62ch;margin-left:auto;margin-right:auto}
  #appointments .sec-head p{margin-left:auto;margin-right:auto}
  #appointments .pin{display:block;margin:0 auto 12px;color:var(--cta)}
  #appointments form{margin-left:auto;margin-right:auto;justify-content:center}
  #appointments .hint{text-align:center}
</style>
@endpush

@section('content')

{{-- HERO — "Editorial centred": big headline + inline visa-check form bar + trust/rating + thumbs --}}
@php
  // Destination thumbnails strip (photographed destinations only).
  $hpThumbs = ($navDestinations ?? collect())->filter(fn ($d) => (bool) $d->image_path)->take(6);
@endphp
<section class="hp-hero"><div class="wrap">
  <p class="eyebrow">UK visas &middot; eVisas &middot; ETAs</p>
  <h1>Sorted, without the stress.</h1>
  <p class="lede">Tell us where you're going — we confirm exactly what you need and handle the paperwork.</p>
  <div class="hp-rating"><span><b>★ 4.9</b> rated</span><span class="dot">·</span><span>12,000+ trips</span></div>

  {{-- inline visa-check form (real destination list + apply) --}}
  <form class="hp-bar" onsubmit="return false">
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

  <div class="hp-trust">
    <span><b>✓</b> UK-based team</span>
    <span><b>✓</b> Clear fixed fees</span>
    <span><b>✓</b> Every step tracked</span>
  </div>

  @if ($hpThumbs->count())
  <div class="hp-thumbs" aria-hidden="true">
    @foreach ($hpThumbs as $d)
      <a href="{{ url('/visa/'.$d->slug) }}" title="{{ $d->name }}"><img src="{{ asset(ltrim($d->image_path, '/')) }}" alt="" loading="lazy"></a>
    @endforeach
  </div>
  @endif
</div></section>

{{-- HOW --}}
<section id="how"><div class="wrap">
  <div class="sec-head reveal"><p class="eyebrow">The process</p><h2>Three simple steps</h2></div>
  <div class="steps">
    <div class="step reveal" id="step-01"><div class="num">01</div><div class="rule"></div><h3>Tell us your trip</h3><p>Pick your destination and answer a few questions. We confirm exactly what you need.</p></div>
    <div class="step reveal" id="step-02"><div class="num">02</div><div class="rule"></div><h3>We prepare &amp; check</h3><p>Our team reviews your documents for errors before anything is submitted.</p></div>
    <div class="step reveal" id="step-03"><div class="num">03</div><div class="rule"></div><h3>Submit &amp; track</h3><p>We handle the submission and keep you updated until it's delivered.</p></div>
  </div>
</div></section>

{{-- DESTINATIONS --}}
<section id="destinations" class="alt"><div class="wrap">
  <div class="dest-split">
    <div class="dest-intro reveal">
      <p class="eyebrow">Popular destinations</p>
      <h2>Clear requirements, fixed fees</h2>
      <p class="lede">Browse the places we prepare and check applications for — clear fixed fees, every step tracked.</p>
      <a class="btn" href="{{ url('/destinations') }}">See all destinations →</a>
    </div>
    <div class="dest-carousel">
      <div class="dest-nav"><button type="button" data-dest-dir="-1" aria-label="Previous destinations">‹</button><button type="button" data-dest-dir="1" aria-label="Next destinations">›</button></div>
      <div class="dests" id="destScroller">
      @foreach ($navDestinations as $d)
      <a class="pass reveal" href="{{ url('/visa/'.$d->slug) }}"><div class="sky">@if ($d->image_path)<img src="{{ asset(ltrim($d->image_path, '/')) }}" alt="{{ $d->name }}" loading="lazy">@else<svg viewBox="0 0 240 96" preserveAspectRatio="xMidYMax meet" role="img" aria-label="{{ $d->name }} skyline"><use href="#ukv-skyline"></use></svg>@endif</div><div class="lower"><div class="main"><div class="k">{{ $d->visa_type }}</div><h3>{{ $d->name }}</h3><div class="t">UK citizens{{ $d->max_stay_days ? ' · up to '.$d->max_stay_days.' days' : '' }}</div></div><div class="stub">@if ((float) $d->tier_standard_gbp > 0)<div class="fee">£{{ number_format((float) $d->tier_standard_gbp, 0) }}</div><div class="lab">FROM</div>@else<div class="fee">Free</div><div class="lab">GUIDE</div>@endif</div></div></a>
      @endforeach
      </div>
    </div>
  </div>
</div></section>
<script>
  // Destinations carousel: auto-scrolls right; at the end, jumps back to the first and continues.
  // Pauses on hover/focus; arrows nudge it manually. Reduced-motion = static + arrows only.
  (function () {
    var sc = document.getElementById('destScroller');
    if (!sc) return;
    document.querySelectorAll('[data-dest-dir]').forEach(function (b) {
      b.addEventListener('click', function () { sc.scrollBy({ left: (sc.clientWidth + 18) * Number(b.dataset.destDir), behavior: 'smooth' }); });
    });
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    var paused = false, SPEED = 1.5;
    function frame() {
      if (!paused) {
        var max = sc.scrollWidth - sc.clientWidth;
        if (sc.scrollLeft >= max - 1) sc.scrollLeft = 0;   // reached end -> back to first
        else sc.scrollLeft += SPEED;
      }
      requestAnimationFrame(frame);
    }
    ['mouseenter', 'focusin', 'touchstart'].forEach(function (e) { sc.addEventListener(e, function () { paused = true; }); });
    ['mouseleave', 'focusout', 'touchend'].forEach(function (e) { sc.addEventListener(e, function () { paused = false; }); });
    requestAnimationFrame(frame);
  })();
</script>

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

{{-- CTA --}}
<section class="cta-band"><div class="wrap reveal">
  <div class="rule"></div>
  <h2>Let's get you travelling</h2>
  <p style="max-width:48ch;color:rgba(255,255,255,.85)">Start your application now, or message our UK team with any question.</p>
  <div class="row"><a href="{{ url('/apply') }}" class="btn">Start my application →</a><a href="https://wa.me/{{ config('ukv.whatsapp') ?: '440000000000' }}" class="btn btn--glass">Chat on WhatsApp</a></div>
</div></section>

@endsection
