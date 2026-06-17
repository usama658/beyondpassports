@extends('layouts.public')

@section('title', 'Visa guides for UK travellers — plain-English help | Beyond Passports')
@section('description', 'Plain-English visa guides for UK travellers — eVisas, ETAs, passport-validity rules, documents and processing times. Independent service, not a government website. General info only; requirements depend on your nationality and residence.')

@section('canonical', url('/guides'))

@push('head')
<style>
  /* guides/index — page-local layout only. Design system lives in assets/ukv.css */

  /* ---- HERO — navy mesh split + popular topics (pick B) ------------- */
  .gi-hero{position:relative;overflow:hidden;background:var(--navy);color:#fff}
  .gi-hero::before{content:"";position:absolute;inset:0;background:
     radial-gradient(60% 80% at 12% 16%,rgba(199,93,56,.40),transparent 60%),
     radial-gradient(55% 75% at 88% 84%,rgba(92,154,123,.42),transparent 62%),
     radial-gradient(40% 60% at 70% 8%,rgba(242,194,172,.16),transparent 60%)}
  .gi-hero::after{content:"";position:absolute;inset:0;background:linear-gradient(180deg,rgba(15,20,22,.08),rgba(15,20,22,.32))}
  .gi-hero > .wrap{position:relative;z-index:2;padding-top:84px;padding-bottom:80px}
  .gi-hero .gi-grid{display:grid;grid-template-columns:1.05fr .95fr;gap:48px;align-items:center}
  .gi-hero .eyebrow{color:var(--soft)}
  .gi-hero h1{font-size:clamp(34px,5vw,54px);font-weight:800;letter-spacing:-.03em;color:#fff;max-width:16ch;margin:0 0 16px;line-height:1.05}
  .gi-hero .lede{font-size:clamp(16px,2vw,19px);line-height:1.55;color:rgba(255,255,255,.82);max-width:50ch;margin:0}
  /* popular-topics list */
  .gi-topics{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.16);border-radius:18px;padding:10px 12px}
  .gi-topics a{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 14px;border-radius:12px;color:#fff;text-decoration:none;border-bottom:1px solid rgba(255,255,255,.08)}
  .gi-topics a:last-child{border-bottom:0}
  .gi-topics a:hover{background:rgba(255,255,255,.08)}
  .gi-topics a:focus-visible{outline:2px solid var(--soft);outline-offset:2px}
  .gi-topics .tl{display:flex;align-items:center;gap:13px;min-width:0}
  .gi-topics .ti{width:36px;height:36px;border-radius:9px;background:rgba(242,194,172,.16);display:flex;align-items:center;justify-content:center;color:var(--soft);flex:0 0 36px}
  .gi-topics .ti svg{width:18px;height:18px}
  .gi-topics .tt{display:block;font-size:14.5px;font-weight:600;line-height:1.3}
  .gi-topics .tc{display:block;font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:rgba(255,255,255,.5);margin:0 0 2px}
  .gi-topics .ta{color:var(--soft);opacity:.6;flex:0 0 auto}
  @media (max-width:820px){.gi-hero .gi-grid{grid-template-columns:1fr;gap:30px}}

  /* ---- COUNTRY HUBS — minimal two-column rows (pick F) ------------- */
  .gi-hubs-wrap { padding: 52px 0 0 }
  .gi-hubs-head { margin-bottom: 14px }
  .gi-hubs {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0 48px;
    max-width: 860px;
  }
  .gi-hubs a {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    padding: 16px 2px;
    border-bottom: 1px solid var(--paper-edge);
    text-decoration: none;
    color: var(--navy);
  }
  .gi-hubs a .hn { font-size: 17px; font-weight: 600; letter-spacing: -.01em }
  .gi-hubs a:hover .hn { color: var(--cta) }
  .gi-hubs a .hr { display: flex; align-items: center; gap: 9px; font-size: 13px; color: var(--muted); white-space: nowrap }
  .gi-hubs a .arrow { color: var(--cta); transition: transform .15s ease; display: inline-flex }
  .gi-hubs a:hover .arrow { transform: translateX(3px) }
  .gi-hubs a:focus-visible { outline: 2px solid var(--cta); outline-offset: 3px }
  @media (max-width: 620px) { .gi-hubs { grid-template-columns: 1fr } }

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

{{-- HERO — navy mesh split + popular topics (pick B) --}}
@php
  $heroTopics = ($evergreen ?? collect())->take(4);
  $heroIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>';
@endphp
<section class="gi-hero">
  <div class="wrap">
    <div class="gi-grid">
      <div class="reveal">
        <p class="eyebrow">Visa guides</p>
        <h1>Plain-English guides for UK travellers</h1>
        <p class="lede">eVisas, ETAs, passport rules, documents and processing times — written clearly so you can travel with confidence.</p>
      </div>
      @if ($heroTopics->isNotEmpty())
        <nav class="gi-topics reveal" aria-label="Popular guides">
          @foreach ($heroTopics as $t)
            <a href="{{ url('/guides/'.$t->slug) }}">
              <span class="tl">
                <span class="ti">{!! $heroIcon !!}</span>
                <span><span class="tc">{{ $t->guide_type instanceof \App\Enums\GuideType ? $t->guide_type->label() : 'Guide' }}</span><span class="tt">{{ $t->title }}</span></span>
              </span>
              <span class="ta" aria-hidden="true">→</span>
            </a>
          @endforeach
        </nav>
      @endif
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
          <span class="hn">{{ $hub->name }}</span>
          <span class="hr">{{ $hub->guides_count }} {{ \Illuminate\Support\Str::plural('guide', $hub->guides_count) }}
            <span class="arrow" aria-hidden="true"><svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
          </span>
        </a>
      @endforeach
    </nav>
    <div style="height:48px"></div>
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
