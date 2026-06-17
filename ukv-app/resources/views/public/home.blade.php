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
  <div class="sec-head reveal"><p class="eyebrow">Popular destinations</p><h2>Clear requirements, fixed fees</h2></div>
  <div class="dests">
    @foreach ($navDestinations as $d)
    <a class="pass reveal" href="{{ url('/visa/'.$d->slug) }}"><div class="sky">@if ($d->image_path)<img src="{{ asset(ltrim($d->image_path, '/')) }}" alt="{{ $d->name }}" loading="lazy">@else<svg viewBox="0 0 240 96" preserveAspectRatio="xMidYMax meet" role="img" aria-label="{{ $d->name }} skyline"><use href="#ukv-skyline"></use></svg>@endif</div><div class="lower"><div class="main"><div class="k">{{ $d->visa_type }}</div><h3>{{ $d->name }}</h3><div class="t">UK citizens{{ $d->max_stay_days ? ' · up to '.$d->max_stay_days.' days' : '' }}</div></div><div class="stub">@if ((float) $d->tier_standard_gbp > 0)<div class="fee">£{{ number_format((float) $d->tier_standard_gbp, 0) }}</div><div class="lab">FROM</div>@else<div class="fee">Free</div><div class="lab">GUIDE</div>@endif</div></div></a>
    @endforeach
  </div>
  <p style="margin-top:26px"><a class="rlink" style="font-weight:600" href="{{ url('/destinations') }}">See all destinations &amp; fixed fees →</a></p>
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

{{-- TESTIMONIAL --}}
<section class="alt"><div class="wrap quote reveal">
  <p class="eyebrow">Trusted by UK travellers</p>
  <blockquote>“They spotted my passport was a month short of the validity Egypt needed — before I'd booked anything. Sorted the renewal, then the visa. Stress gone.”</blockquote>
  <div class="by"><span class="avatar"><svg viewBox="0 0 240 96" preserveAspectRatio="xMidYMax meet" role="img" aria-label="Beyond Passports traveller"><use href="#ukv-skyline"></use></svg></span>— A UK traveller to Egypt</div>
</div></section>

{{-- APPOINTMENTS / NEAREST CENTRE --}}
<section id="appointments" class="alt"><div class="wrap reveal">
  <div class="sec-head">
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
