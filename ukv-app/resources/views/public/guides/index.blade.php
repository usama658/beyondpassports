@extends('layouts.public')

@section('title', 'Visa guides for UK travellers — plain-English help | Beyond Passports')
@section('description', 'Plain-English visa guides for UK travellers — eVisas, ETAs, passport-validity rules, documents and processing times. Independent service, not a government website. General info only; requirements depend on your nationality and residence.')

@section('canonical', url('/guides'))

@push('head')
<style>
  /* Page-local layout only — design system + palette live in assets/ukv.css */
  .page-hero{padding:64px 0 8px}
  .page-hero h1{font-family:var(--display);font-size:clamp(34px,5vw,54px);color:var(--navy);letter-spacing:-.015em;margin:.1em 0 .35em}
  .page-hero p.sub{font-size:18px;max-width:52ch;color:#33454f;margin:0}

  /* Country-hub chips — link ACROSS to the money-page hubs that carry a cluster */
  .hubs{display:flex;flex-wrap:wrap;gap:10px;margin:6px 0 4px}
  .hubs a{font-family:var(--mono);font-size:13px;letter-spacing:.04em;padding:9px 18px;border-radius:999px;
    border:1.5px solid var(--navy);background:transparent;color:var(--navy);text-decoration:none;transition:background .12s ease,color .12s ease}
  .hubs a:hover{background:var(--navy);color:#fff}
  .hubs a:focus-visible{outline:2px solid var(--cta);outline-offset:2px}

  .compliance{font-family:var(--mono);font-size:12px;color:var(--hint);margin:26px 0 0;max-width:66ch}
</style>
@endpush

@section('content')

{{-- HERO --}}
<section class="page-hero"><div class="wrap">
  <p class="eyebrow">Visa guides</p>
  <h1>Travel-ready: plain-English visa guides</h1>
  <p class="sub">Plain-English guides to eVisas, ETAs, passport rules, documents and processing times for UK travellers. Practical, honest and jargon-free.</p>
</div></section>

{{-- COUNTRY HUBS — link across to the money pages that carry a published cluster --}}
@if ($countryHubs->isNotEmpty())
<section><div class="wrap">
  <div class="sec-head reveal" style="margin-bottom:14px"><p class="eyebrow">By destination</p><h2 style="font-size:clamp(24px,3vw,30px);color:var(--navy)">Guides for a specific country</h2></div>
  <nav class="hubs" aria-label="Country guide hubs">
    @foreach ($countryHubs as $hub)
      <a href="{{ url('/visa/'.$hub->slug) }}">{{ $hub->name }} guides →</a>
    @endforeach
  </nav>
</div></section>
@endif

{{-- EVERGREEN GUIDE GRID --}}
<section><div class="wrap">
  @if ($evergreen->isNotEmpty())
    <div class="sec-head reveal" style="margin-bottom:18px"><p class="eyebrow">Read up</p><h2 style="font-size:clamp(24px,3vw,30px);color:var(--navy)">General travel guides</h2></div>
    @include('partials.guide-cluster', ['cluster' => $evergreen])
  @else
    <p class="sub" style="margin:20px 0">New guides are on the way — in the meantime, our free checker can tell you exactly what your trip needs.</p>
  @endif

  <p class="compliance">Beyond Passports is an independent service and is not a government website. Guides are general information only — exact requirements depend on your nationality, residence and trip, so always confirm at the official source before you travel.</p>
</div></section>

{{-- CTA --}}
<section class="cta-band"><div class="wrap reveal">
  <div class="rule"></div>
  <h2>Ready when you are</h2>
  <p style="max-width:48ch;color:#eef0f1">Read up, then let our UK team confirm exactly what your trip needs.</p>
  <div class="row">
    <a href="{{ url('/apply') }}" class="btn">Start my application →</a>
    <a href="{{ url('/tools') }}" class="btn btn--ghost" style="color:#fff;border-color:#fff">Check what I need</a>
  </div>
</div></section>

@endsection
