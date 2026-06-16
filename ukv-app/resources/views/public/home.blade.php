@extends('layouts.public')

@section('title', 'UK Visas, eVisas & ETAs — Sorted Without the Stress | UKVisaCo')
@section('description', 'Independent UK visa & eVisa service. We check, prepare and submit your application for 14+ destinations — UK-based team, clear fixed fees, every step tracked. Not a government website.')

@push('head')
<style>
  /* Page-local layout only — design system lives in assets/ukv.css */
  .hero{padding:64px 0 0}
  .hero-grid{display:grid;grid-template-columns:1.1fr .9fr;gap:48px;align-items:center}
  .hero h1{font-size:clamp(38px,5.2vw,60px);color:var(--navy);letter-spacing:-.015em}
  .hero p.lede{font-size:19px;max-width:42ch;color:#33454f}
  .hero .micro{font-family:var(--mono);font-size:12px;color:var(--stamp);margin-top:18px}
  .hero-sky{margin:40px 0 0;background:var(--navy);border-radius:12px;overflow:hidden;height:120px}
  .hero-sky svg{width:100%;height:100%}
  @media (max-width:860px){.hero-grid{grid-template-columns:1fr!important}}
</style>
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "Organization",
  "name": "UKVisaCo",
  "url": "{{ url('/') }}",
  "description": "Independent UK visa & eVisa facilitation service. Not a government website.",
  "areaServed": "GB"
}
</script>
@endpush

@section('content')

{{-- HERO --}}
<section class="hero"><div class="wrap">
  <div class="hero-grid">
    <div>
      <p class="eyebrow">Independent UK visa &amp; eVisa service</p>
      <h1>UK visas, eVisas &amp; ETAs — sorted, without the stress.</h1>
      <p class="lede">We check, prepare and submit your application for 14+ destinations, so you travel with confidence. UK-based team, clear fixed fees, every step tracked.</p>
      <p class="micro">We catch the errors. We handle the paperwork. You get the visa.</p>
    </div>
    <div class="checker" id="checker">
      <div class="stub"><span>VISA CHECK</span><span>UKV&lt;START&lt;&lt;&lt;</span></div>
      <div class="cbody">
        <label for="dest">Where are you going?</label>
        <select id="dest"><option>Choose a destination…</option>@foreach ($navDestinations as $d)<option>{{ $d->name }}</option>@endforeach</select>
        <label for="nat">Your passport</label>
        <select id="nat"><option>United Kingdom</option><option>Other — we'll confirm your rules</option></select>
        <button class="btn" type="button" onclick="location.href='{{ url('/apply') }}'">Check what I need →</button>
        <p style="font-size:12px;color:var(--hint);margin:12px 0 0;font-family:var(--mono)">No account needed · takes 30 seconds</p>
      </div>
    </div>
  </div>
  {{-- skyline backdrop band below the hero --}}
  <div class="hero-sky" aria-hidden="true">
    <svg viewBox="0 0 240 96" preserveAspectRatio="xMidYMax meet"><use href="#ukv-skyline"></use></svg>
  </div>
</div></section>
<div class="mrz"><div class="wrap"><span>P&lt;GBR&lt;TRAVELLER&lt;&lt;READY&lt;TO&lt;GO&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;</span></div></div>

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
    <a class="pass reveal" href="{{ url('/visa/'.$d->slug) }}"><div class="sky"><svg viewBox="0 0 240 96" preserveAspectRatio="xMidYMax meet" role="img" aria-label="{{ $d->name }} skyline"><use href="#ukv-skyline"></use></svg></div><div class="lower"><div class="main"><div class="k">{{ $d->visa_type }}</div><h3>{{ $d->name }}</h3><div class="t">UK citizens{{ $d->max_stay_days ? ' · up to '.$d->max_stay_days.' days' : '' }}</div></div><div class="stub">@if ((float) $d->tier_standard_gbp > 0)<div class="fee">£{{ number_format((float) $d->tier_standard_gbp, 0) }}</div><div class="lab">FROM</div>@else<div class="fee">Free</div><div class="lab">GUIDE</div>@endif</div></div></a>
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
  <div class="by"><span class="avatar"><svg viewBox="0 0 240 96" preserveAspectRatio="xMidYMax meet" role="img" aria-label="UKVisaCo traveller"><use href="#ukv-skyline"></use></svg></span>— A UK traveller to Egypt · UKV&lt;2026&lt;004821&lt;&lt;&lt;</div>
</div></section>

{{-- APPOINTMENTS / NEAREST CENTRE --}}
<section id="appointments" class="alt"><div class="wrap reveal">
  <div class="sec-head">
    <p class="eyebrow">In-person centres</p>
    @if (($slotSummary['available_count'] ?? 0) > 0)
      <h2>Appointments ready when you are</h2>
      <p style="max-width:58ch;color:#33454f">
        <strong>{{ $slotSummary['available_count'] }} appointment{{ $slotSummary['available_count'] === 1 ? '' : 's' }} available</strong>@if (! empty($slotSummary['next_slot_at'])) — next on {{ $slotSummary['next_slot_at']->format('j M Y') }}@endif@if (($slotSummary['centre_count'] ?? 0) > 0), across {{ $slotSummary['centre_count'] }} centre{{ $slotSummary['centre_count'] === 1 ? '' : 's' }}@endif. Enter your postcode to find the one nearest you.
      </p>
    @else
      <h2>Find your nearest centre</h2>
      <p style="max-width:58ch;color:#33454f">For visas or IDPs that need an in-person visit, enter your postcode and we'll show the closest centre — so you don't have to go hunting.</p>
    @endif
  </div>
  <form method="GET" action="{{ route('centre.search') }}" style="display:flex;flex-wrap:wrap;gap:10px;margin-top:8px;max-width:520px">
    <input type="text" name="postcode" placeholder="e.g. SW1A 1AA" autocomplete="postal-code" required aria-label="Your postcode"
           style="flex:1;min-width:200px;padding:12px;border:1px solid var(--paper-edge,#d9cfbe);border-radius:8px;font:inherit;font-size:15px">
    <button type="submit" class="btn">Find nearest →</button>
  </form>
  <p class="hint" style="margin-top:10px"><a href="{{ url('/find-a-centre') }}">Browse the full centre finder →</a> · Most visas are online (eVisa/ETA) and need no appointment.</p>
</div></section>

{{-- CTA --}}
<section class="cta-band"><div class="wrap reveal">
  <div class="rule"></div>
  <h2>Let's get you travelling</h2>
  <p style="max-width:48ch;color:#cdd9e1">Start your application now, or message our UK team with any question.</p>
  <div class="row"><a href="{{ url('/apply') }}" class="btn">Start my application →</a><a href="https://wa.me/{{ config('ukv.whatsapp') ?: '440000000000' }}" class="btn btn--wa">Chat on WhatsApp</a></div>
</div></section>

@endsection
