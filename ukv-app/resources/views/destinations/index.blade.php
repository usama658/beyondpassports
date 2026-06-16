@extends('layouts.public')

@section('title', 'Visa & eVisa destinations for UK travellers | Beyond Passports')
@section('description', 'Browse the destinations we prepare and check visa & eVisa applications for. Clear fixed service fees, fast handling, every step tracked. Independent service — not a government website.')

@push('head')
<style>.dests .pass .sky{height:160px;overflow:hidden;background:#e9edf0}.dests .pass .sky img{width:100%;height:100%;object-fit:cover;display:block;transition:transform .3s ease}.dests .pass:hover .sky img{transform:scale(1.05)}</style>
@endpush

@section('content')

{{-- HERO --}}
<section class="mesh-hero mesh-hero--sm"><div class="wrap"><div class="mh-grid"><div class="mh-copy">
  <p class="eyebrow">Destinations</p>
  <h1>Where are you travelling?</h1>
  <p class="lede">Pick your destination to see exactly what's needed, our fixed service fees, and how we prepare and check your application before it's submitted.</p>
</div></div></div></section>

{{-- DESTINATION CARDS --}}
<section><div class="wrap">
  <div class="sec-head reveal">
    <p class="eyebrow">Choose a destination</p>
    <h2>Visa &amp; eVisa pages</h2>
  </div>

  @if ($destinations->isEmpty())
    <p style="color:var(--muted)">We're adding destinations shortly. In the meantime, <a href="{{ url('/track') }}">track an existing application</a> or get in touch.</p>
  @else
    <div class="dests">
      @foreach ($destinations as $destination)
        @php
          $fromFee = $destination->tier_standard_gbp;
        @endphp
        <a class="pass reveal" href="{{ url('/visa/'.$destination->slug) }}" style="text-decoration:none;color:inherit">
          <div class="sky">@if ($destination->image_path)<img src="{{ asset(ltrim($destination->image_path, '/')) }}" alt="{{ $destination->name }}" loading="lazy">@else<svg viewBox="0 0 240 96" preserveAspectRatio="xMidYMax meet" role="img" aria-label="{{ $destination->name }} skyline"><use href="#ukv-skyline"></use></svg>@endif</div>
          <div class="lower">
            <div class="main">
              <span class="k">{{ $destination->visa_type ? strtoupper($destination->visa_type) : 'Visa' }}</span>
              <h3>{{ $destination->name }}</h3>
              <p class="t">Prepared &amp; checked by our UK team</p>
            </div>
            <div class="stub">
              <span class="fee">@if (! is_null($fromFee)) £{{ rtrim(rtrim(number_format((float) $fromFee, 2), '0'), '.') }} @else — @endif</span>
              <span class="lab">{{ ! is_null($fromFee) ? 'from / service' : 'service fee' }}</span>
            </div>
          </div>
        </a>
      @endforeach
    </div>
  @endif

  <div class="pricenote reveal" style="margin-top:32px;background:#f7fafb;border:1px solid var(--paper-edge);border-left:3px solid var(--gold);border-radius:8px;padding:16px 20px;font-size:14px;color:#3a4b55">
    <p style="margin:0 0 6px"><strong style="color:var(--navy)">Our service fee is separate from any government fee.</strong> Each destination's government charges its own fee, shown clearly before you pay anything.</p>
    <p style="margin:0">We prepare, check and submit your application — we are not a government website and cannot guarantee approval. The decision is always the destination authority's.</p>
  </div>
</div></section>

@endsection
