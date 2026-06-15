@extends('layouts.public')

@section('title', 'Traveller Reviews — What UK Travellers Say | UKVisaCo')
@section('description', 'Anonymised, consented reviews from UK travellers we have helped with eVisas, ETAs and IDPs. Independent UK service — not a government website. No approval guarantees.')
@section('canonical', url('/reviews'))

@push('head')
<style>
  /* reviews — page-local layout only. Design system lives in assets/ukv.css */
  .hero{padding:64px 0 40px}
  .hero h1{font-size:clamp(34px,5vw,56px);color:var(--navy);letter-spacing:-.015em;max-width:20ch}
  .hero p.lede{font-size:19px;max-width:48ch;color:#33454f;margin-top:14px}
  .hero .micro{font-family:var(--mono);font-size:12px;color:var(--stamp);margin-top:18px}
  .callout{max-width:70ch}
  .callout p{font-size:16px;line-height:1.6;color:#33454f;margin:0}
</style>
@endpush

{{-- No Review / AggregateRating JSON-LD: these are curated, anonymised marketing
     quotes, not a verified ratings dataset. Emitting an aggregate would invent
     data we cannot substantiate. Schema intentionally omitted. --}}

@section('content')

{{-- HERO --}}
<section class="hero"><div class="wrap">
  <p class="eyebrow">Traveller reviews</p>
  <h1>What UK travellers say about us.</h1>
  <p class="lede">Real words from travellers we have helped — shared with their consent and anonymised for privacy. We never promise an approval; what we promise is careful checking and a UK team you can actually talk to.</p>
  <p class="micro">Consented &amp; anonymised · independent service · not a government website</p>
</div></section>
<div class="mrz"><div class="wrap"><span>P&lt;GBR&lt;TRAVELLERS&lt;&lt;IN&lt;THEIR&lt;OWN&lt;WORDS&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;</span></div></div>

{{-- ALL TESTIMONIALS (shared partial, full list) --}}
@include('partials.testimonials', [
    'testimonials' => $testimonials,
    'limit'        => 0,
    'eyebrow'      => 'In our travellers’ words',
    'heading'      => 'Reviews from UK travellers',
    'showAll'      => false,
])

{{-- COMPLIANCE / TRANSPARENCY CALLOUT --}}
<section id="transparency"><div class="wrap">
  <div class="sec-head reveal"><p class="eyebrow">About these reviews</p><h2>Honest, anonymised, consented</h2></div>
  <div class="callout reveal">
    <p>Every review on this page is shared with the traveller’s permission and anonymised — we never publish full names or identifying details. UKVisaCo is an independent commercial service, not a government website. Visa and travel-authorisation decisions are made solely by the relevant authorities, and a good experience with us is not a promise of any particular outcome for you.</p>
  </div>
</div></section>

{{-- CTA --}}
<section class="cta-band"><div class="wrap reveal">
  <div class="rule"></div>
  <h2>Let’s get you travelling</h2>
  <p style="max-width:48ch;color:#cdd9e1">Start your application now, or message our UK team with any question — even just to check whether you need us.</p>
  <div class="row"><a href="{{ url('/apply') }}" class="btn">Start my application →</a><a href="https://wa.me/440000000000" class="btn btn--wa">Chat on WhatsApp</a></div>
</div></section>

@endsection
