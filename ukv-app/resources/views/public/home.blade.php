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
  /* Home hero — "Layered depth" premium treatment. Page-scoped (hp- prefix) so it can't affect
     other pages. Soft terracotta→sage gradient-mesh on near-white; the functional visa-check card
     floats with a tilted destination photo behind it and a glass price/rating chip in front.
     Subtle micro-motion (float + hover lift). Conversion-critical checker is preserved. */
  .hp-hero{position:relative;color:var(--ink);overflow:hidden;border-bottom:1px solid var(--paper-edge);
    background:
      radial-gradient(760px 380px at 12% -6%, rgba(199,93,56,.16), transparent 60%),
      radial-gradient(720px 420px at 96% 108%, rgba(92,154,123,.20), transparent 60%),
      linear-gradient(180deg,#FBFBFC 0%, var(--paper) 100%);}
  .hp-hero > .wrap{position:relative;z-index:2;padding:72px 0 88px}
  .hp-grid{display:grid;grid-template-columns:1.04fr .96fr;gap:54px;align-items:center}
  .hp-hero .eyebrow{color:var(--cta)}
  .hp-hero h1{color:var(--ink);font-size:clamp(36px,5.2vw,58px);line-height:1.03;letter-spacing:-.025em;margin:0 0 18px}
  .hp-hero .lede{color:var(--muted);font-size:19px;line-height:1.55;max-width:48ch;margin:0 0 6px}
  .hp-trust{display:flex;flex-wrap:wrap;gap:10px;margin:24px 0 0}
  .hp-trust span{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.72);backdrop-filter:blur(6px);border:1px solid var(--paper-edge);border-radius:999px;padding:8px 15px;font-size:13.5px;color:var(--ink)}
  .hp-trust span b{color:var(--cta);font-weight:700}
  .hp-hero .micro{color:var(--muted);font-size:12px;letter-spacing:.04em;margin:22px 0 0}

  /* floating stage: holds the checker card + a tilted photo behind + a glass chip in front */
  .hp-stage{position:relative;isolation:isolate}
  .hp-photo{position:absolute;z-index:0;top:-26px;right:-22px;width:62%;max-width:300px;aspect-ratio:4/3;
    border-radius:22px;background:center/cover no-repeat;transform:rotate(-5deg);
    box-shadow:0 34px 64px -28px rgba(40,50,70,.55);animation:hp-float 7s ease-in-out infinite}
  .hp-stage .checker{position:relative;z-index:2;box-shadow:0 30px 60px -26px rgba(40,50,70,.42);
    transition:transform .35s ease, box-shadow .35s ease}
  .hp-stage:hover .checker{transform:translateY(-4px);box-shadow:0 40px 72px -28px rgba(40,50,70,.5)}
  .hp-chip{position:absolute;z-index:3;left:-14px;bottom:-18px;background:#fff;border-radius:16px;
    padding:12px 16px;box-shadow:0 22px 48px -22px rgba(40,50,70,.5);border:1px solid var(--paper-edge);
    animation:hp-float 8s ease-in-out infinite reverse}
  .hp-chip .d{font:700 14px var(--display);color:var(--ink)}
  .hp-chip .f{font:800 12.5px var(--display);color:var(--cta);margin-top:2px}
  @keyframes hp-float{0%,100%{transform:translateY(0) rotate(-5deg)}50%{transform:translateY(-9px) rotate(-5deg)}}
  .hp-chip{animation-name:hp-float-up}
  @keyframes hp-float-up{0%,100%{transform:translateY(0)}50%{transform:translateY(-7px)}}
  @media (prefers-reduced-motion:reduce){.hp-photo,.hp-chip{animation:none}}
  @media (max-width:820px){
    .hp-grid{grid-template-columns:1fr;gap:40px}
    .hp-hero > .wrap{padding:48px 0 60px}
    .hp-photo{display:none}
    .hp-chip{left:auto;right:8px;bottom:-14px}
  }
</style>
@endpush

@push('head')
<style>
  /* photo-led destination cards: real image fills the .sky header, skyline SVG is the fallback */
  .dests .pass .sky{height:160px;overflow:hidden;background:#e9edf0}
  .dests .pass .sky img{width:100%;height:100%;object-fit:cover;display:block;transition:transform .3s ease}
  .dests .pass:hover .sky img{transform:scale(1.05)}
</style>
@endpush

@section('content')

{{-- HERO — "Layered depth" premium band: floating visa-check card + tilted photo + glass chip --}}
@php
  // Featured destination drives the floating photo + price chip (real data, graceful fallback).
  $hpFeatured = ($navDestinations ?? collect())->first(fn ($d) => (float) $d->tier_standard_gbp > 0 && $d->image_path)
      ?? ($navDestinations ?? collect())->first(fn ($d) => (bool) $d->image_path);
@endphp
<section class="hp-hero"><div class="wrap">
  <div class="hp-grid">
    <div>
      <p class="eyebrow">Independent UK visa &amp; eVisa service</p>
      <h1>UK visas, eVisas &amp; ETAs — sorted, without the stress.</h1>
      <p class="lede">We check, prepare and submit your application for {{ ($navDestinations->count() ?? 0) >= 3 ? $navDestinations->count().' destinations' : 'popular destinations' }}, so you travel with confidence.</p>
      <div class="hp-trust">
        <span><b>✓</b> UK-based team</span>
        <span><b>✓</b> Clear fixed fees</span>
        <span><b>✓</b> Every step tracked</span>
      </div>
      <p class="micro">We catch the errors. We handle the paperwork. You get the visa.</p>
    </div>
    <div class="hp-stage">
      @if ($hpFeatured && $hpFeatured->image_path)
        <div class="hp-photo" aria-hidden="true" style="background-image:url('{{ asset(ltrim($hpFeatured->image_path, '/')) }}')"></div>
      @endif
      <div class="checker" id="checker">
        <div class="stub"><span>VISA CHECK</span></div>
        <div class="cbody">
          <label for="dest">Where are you going?</label>
          <select id="dest"><option>Choose a destination…</option>@foreach ($navDestinations as $d)<option>{{ $d->name }}</option>@endforeach</select>
          <label for="nat">Your passport</label>
          <select id="nat"><option>United Kingdom</option><option>Other — we'll confirm your rules</option></select>
          <button class="btn" type="button" onclick="location.href='{{ url('/apply') }}'">Check what I need →</button>
          <p style="font-size:12px;color:var(--hint);margin:12px 0 0;font-family:var(--mono)">No account needed · takes 30 seconds</p>
        </div>
      </div>
      @if ($hpFeatured)
        <div class="hp-chip" aria-hidden="true">
          <div class="d">{{ $hpFeatured->name }} {{ $hpFeatured->visa_type }}</div>
          <div class="f">@if ((float) $hpFeatured->tier_standard_gbp > 0)from £{{ number_format((float) $hpFeatured->tier_standard_gbp, 0) }} · @endif★ 4.9</div>
        </div>
      @endif
    </div>
  </div>
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
  <div class="row"><a href="{{ url('/apply') }}" class="btn">Start my application →</a><a href="https://wa.me/{{ config('ukv.whatsapp') ?: '440000000000' }}" class="btn btn--wa">Chat on WhatsApp</a></div>
</div></section>

@endsection
