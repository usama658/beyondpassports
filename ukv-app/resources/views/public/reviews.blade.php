@extends('layouts.public')

@section('title', 'Traveller Reviews: What UK Travellers Say | Beyond Passports')
@section('description', 'Anonymised, consented reviews from UK travellers we have helped with eVisas, ETAs and IDPs. Independent UK service, not a government website. No approval guarantees.')
@section('canonical', url('/reviews'))

@push('head')
<style>
  /* reviews — page-local layout only. Design system lives in assets/ukv.css */

  /* HERO — honest-promise split (pick C) */
  .rv-hero{background:var(--white);padding:56px 0;border-bottom:1px solid var(--paper-edge)}
  .rv-hero .eyebrow{color:var(--cta)}
  .rv-hero h1{font-size:clamp(30px,4.2vw,48px);font-weight:800;letter-spacing:-.03em;color:var(--navy);line-height:1.05;margin:0;max-width:20ch}
  .rv-hero h1 em{font-style:normal;color:var(--cta)}
  .rv-hero .lede{font-size:17px;line-height:1.6;color:#46505a;max-width:54ch;margin:16px 0 0}
  .rv-promise{display:flex;gap:10px;flex-wrap:wrap;margin:22px 0 0}
  .rv-promise .p{display:inline-flex;align-items:center;gap:8px;background:#E2F1EE;border:1px solid #cfe6e0;border-radius:999px;padding:8px 15px;font-size:13px;font-weight:700;color:var(--stamp-text)}
  .rv-promise .p svg{width:15px;height:15px;flex:0 0 15px;fill:none;stroke:var(--stamp);stroke-width:2.2;stroke-linecap:round;stroke-linejoin:round}
  .rv-hero .micro{font-family:var(--mono);font-size:12px;color:var(--muted);margin:18px 0 0;letter-spacing:.01em}

  /* About-these-reviews — navy shield panel (pick B) */
  .rv-about{position:relative;overflow:hidden;background:var(--navy);color:#fff;border-radius:18px;padding:30px;display:grid;grid-template-columns:auto 1fr;gap:22px;align-items:center;box-shadow:0 24px 60px -42px rgba(0,0,0,.6)}
  .rv-about::before{content:"";position:absolute;inset:0;background:radial-gradient(70% 80% at 92% 0,rgba(21,94,122,.30),transparent 60%),radial-gradient(60% 70% at 0 100%,rgba(46,154,140,.30),transparent 62%)}
  .rv-about > *{position:relative;z-index:2}
  .rv-about .shield{width:72px;height:72px;border-radius:16px;background:rgba(169,204,218,.16);color:var(--soft);display:flex;align-items:center;justify-content:center;flex:0 0 72px}
  .rv-about .shield svg{width:34px;height:34px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
  .rv-about .eyebrow{color:var(--soft);margin:0 0 6px}
  .rv-about h2{font-size:22px;letter-spacing:-.02em;color:#fff;margin:0 0 8px}
  .rv-about p{margin:0;font-size:14px;line-height:1.6;color:rgba(255,255,255,.82)}
  @media (max-width:560px){.rv-about{grid-template-columns:1fr;text-align:left}}
</style>
@endpush

{{-- No Review / AggregateRating JSON-LD: these are curated, anonymised marketing
     quotes, not a verified ratings dataset. Emitting an aggregate would invent
     data we cannot substantiate. Schema intentionally omitted. --}}

@section('content')

{{-- HERO — honest-promise split (pick C) --}}
<section class="rv-hero"><div class="wrap">
  <p class="eyebrow">Traveller reviews</p>
  <h1>We won't promise an approval. <em>Here's what we do promise.</em></h1>
  <p class="lede">Every decision is the authorities' to make. What our travellers keep telling us is simpler: we check carefully, we explain plainly, and a real UK team picks up.</p>
  <div class="rv-promise">
    <span class="p"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>Every document checked</span>
    <span class="p"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>Plain-English answers</span>
    <span class="p"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>A UK team you can reach</span>
  </div>
  @include('partials.trustpilot-cta', ['align' => 'left', 'margin' => '18px 0 0'])
  <p class="micro">Consented &amp; anonymised · independent service · not a government website</p>
</div></section>

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
  <div class="rv-about reveal">
    <span class="shield" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 2 4 5v6c0 5 3.5 8 8 11 4.5-3 8-6 8-11V5l-8-3z"/><path d="m9 12 2 2 4-4"/></svg></span>
    <div>
      <p class="eyebrow">About these reviews</p>
      <h2>Honest, anonymised, consented</h2>
      <p>Every review on this page is shared with the traveller’s permission and anonymised. We never publish full names or identifying details. Beyond Passports is an independent commercial service, not a government website. Visa and travel-authorisation decisions are made solely by the relevant authorities, and a good experience with us is not a promise of any particular outcome for you.</p>
    </div>
  </div>
</div></section>

{{-- CTA --}}
<section class="cta-band"><div class="wrap reveal">
  <div class="rule"></div>
  <h2>Let’s get you travelling</h2>
  <p style="max-width:48ch;color:#eef0f1">Start your application now, or message our UK team with any question, even just to check whether you need us.</p>
  <div class="row"><a href="{{ App\Support\SiteStats::chatUrl() }}" target="_blank" rel="noopener" class="btn">Check eligibility →</a> @include('partials.consult-cta')<a href="https://wa.me/{{ config('ukv.whatsapp') ?: '440000000000' }}" class="btn btn--glass">@include('partials.wa-glyph')Chat on WhatsApp</a></div>
</div></section>

@endsection
