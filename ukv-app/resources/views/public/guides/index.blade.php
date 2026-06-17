@extends('layouts.public')

@section('title', 'Visa guides for UK travellers — plain-English help | Beyond Passports')
@section('description', 'Plain-English visa guides for UK travellers — eVisas, ETAs, passport-validity rules, documents and processing times. Independent service, not a government website. General info only; requirements depend on your nationality and residence.')

@section('canonical', url('/guides'))

@push('head')
<style>
  /* guides/index — page-local layout only. Design system lives in assets/ukv.css */

  /* ---- HERO -------------------------------------------------------- */
  .gi-hero > .wrap { padding-top: 76px; padding-bottom: 80px }
  .gi-hero h1 {
    font-size: clamp(38px, 5vw, 58px);
    font-weight: 800;
    letter-spacing: -.03em;
    color: var(--navy);
    max-width: 18ch;
    margin: 0 0 18px;
  }
  .gi-hero .lede {
    font-size: 19px;
    line-height: 1.6;
    color: var(--muted);
    max-width: 50ch;
    margin: 0 0 28px;
  }
  .gi-trust {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 4px;
  }
  .gi-trust span {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: rgba(255,255,255,.78);
    backdrop-filter: blur(6px);
    border: 1px solid var(--paper-edge);
    border-radius: 999px;
    padding: 7px 14px;
    font-size: 13px;
    font-weight: 600;
    color: var(--ink);
  }
  .gi-trust span b { color: var(--cta) }

  /* ---- COUNTRY HUBS ------------------------------------------------ */
  .gi-hubs-wrap { padding: 52px 0 0 }
  .gi-hubs-head { margin-bottom: 22px }
  .gi-hubs {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
  }
  .gi-hubs a {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 14px;
    font-weight: 600;
    letter-spacing: .02em;
    padding: 10px 18px;
    border-radius: 999px;
    border: 1.5px solid var(--paper-edge);
    background: var(--white);
    color: var(--ink);
    text-decoration: none;
    box-shadow: 0 2px 8px -4px rgba(40,50,70,.14);
    transition: border-color .14s ease, background .14s ease, color .14s ease, transform .14s ease, box-shadow .14s ease;
  }
  .gi-hubs a:hover {
    border-color: var(--cta);
    background: var(--cta);
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px -8px rgba(199,93,56,.4);
  }
  .gi-hubs a:focus-visible { outline: 3px solid var(--cta); outline-offset: 3px }
  .gi-hubs a .arrow { opacity: .55; font-size: 12px; transition: opacity .14s ease }
  .gi-hubs a:hover .arrow { opacity: 1 }

  /* ---- GUIDES SECTION ---------------------------------------------- */
  .gi-guides-wrap { padding: 56px 0 64px }
  .gi-guides-head { margin-bottom: 36px }

  /* ---- EMPTY STATE ------------------------------------------------- */
  .gi-empty {
    background: var(--white);
    border: 1px solid var(--paper-edge);
    border-radius: 18px;
    padding: 48px 40px;
    text-align: center;
    max-width: 52ch;
    margin: 0 auto;
    box-shadow: var(--shadow);
  }
  .gi-empty p { font-size: 17px; color: var(--muted); margin: 0 0 24px }

  /* ---- COMPLIANCE -------------------------------------------------- */
  .gi-compliance {
    margin-top: 48px;
    padding-top: 24px;
    border-top: 1px solid var(--paper-edge);
    font-size: 12.5px;
    line-height: 1.65;
    color: var(--muted);
    max-width: 72ch;
  }

  /* ---- DIVIDER between hubs + guides ------------------------------ */
  .gi-divider {
    height: 1px;
    background: var(--paper-edge);
    margin: 0;
  }

  @media (max-width: 860px) {
    .gi-hero > .wrap { padding-top: 52px; padding-bottom: 56px }
    .gi-guides-wrap { padding: 40px 0 48px }
  }
</style>
@endpush

@section('content')

{{-- HERO — soft-sky gradient mesh, centred single-column --}}
<section class="mesh-hero mesh-hero--sm gi-hero">
  <div class="wrap">
    <div class="mh-grid">
      <div class="mh-copy">
        <p class="eyebrow">Visa guides</p>
        <h1>Plain-English guides for UK travellers</h1>
        <p class="lede">eVisas, ETAs, passport rules, documents and processing times — written clearly so you can travel with confidence.</p>
        <div class="gi-trust">
          <span>✔ <b>Independent</b> — not a government site</span>
          <span>✔ Jargon-free</span>
          <span>✔ Updated regularly</span>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- COUNTRY HUBS --}}
@if ($countryHubs->isNotEmpty())
<section class="gi-hubs-wrap" style="background:var(--white);border-top:1px solid var(--paper-edge);border-bottom:1px solid var(--paper-edge)">
  <div class="wrap">
    <div class="sec-head gi-hubs-head reveal">
      <p class="eyebrow">By destination</p>
      <h2>Guides for a specific country</h2>
    </div>
    <nav class="gi-hubs" aria-label="Country guide hubs">
      @foreach ($countryHubs as $hub)
        <a href="{{ url('/visa/'.$hub->slug) }}">
          {{ $hub->name }}
          <span class="arrow" aria-hidden="true">→</span>
        </a>
      @endforeach
    </nav>
    <div style="height:52px"></div>
  </div>
</section>
@endif

<div class="gi-divider" aria-hidden="true"></div>

{{-- EVERGREEN GUIDE GRID --}}
<section class="gi-guides-wrap">
  <div class="wrap">
    @if ($evergreen->isNotEmpty())
      <div class="sec-head gi-guides-head reveal">
        <p class="eyebrow">Read up</p>
        <h2>General travel guides</h2>
      </div>
      @include('partials.guide-cluster', ['cluster' => $evergreen])
    @else
      <div class="gi-empty">
        <p>New guides are on the way — in the meantime, our free checker can tell you exactly what your trip needs.</p>
        <a href="{{ url('/tools') }}" class="btn">Check what I need →</a>
      </div>
    @endif

    <p class="gi-compliance">
      Beyond Passports is an independent service and is not a government website.
      Guides are general information only — exact requirements depend on your nationality, residence and trip, so always confirm at the official source before you travel.
    </p>
  </div>
</section>

{{-- CTA BAND --}}
<section class="cta-band"><div class="wrap reveal">
  <div class="rule"></div>
  <h2>Ready when you are</h2>
  <p style="max-width:48ch;color:#eef0f1">Read up, then let our UK team confirm exactly what your trip needs.</p>
  <div class="row">
    <a href="{{ url('/apply') }}" class="btn">Start my application →</a>
    <a href="{{ url('/tools') }}" class="btn btn--glass">Check what I need</a>
  </div>
</div></section>

@endsection
