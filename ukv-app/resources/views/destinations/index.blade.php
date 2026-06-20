@extends('layouts.public')

@section('title', 'Visa & eVisa destinations for UK travellers | Beyond Passports')
@section('description', 'Browse the destinations we prepare and check visa & eVisa applications for. Clear fixed service fees, fast handling, every step tracked. Independent service — not a government website.')

@push('head')
<style>
  /* Destinations index — soft-sky hero + live search + glass card grid (option C). */
  .di-hero{background:linear-gradient(180deg,#EAF1F4 0%, #F2F5F6 60%, var(--paper) 100%);border-bottom:1px solid var(--paper-edge);text-align:center}
  .di-hero > .wrap{padding:56px 0 48px}
  .di-hero .eyebrow{color:var(--cta)}
  .di-hero h1{font:700 clamp(30px,4vw,46px)/1.04 var(--display);letter-spacing:-.03em;color:var(--ink);margin:0 auto 14px}
  .di-hero .lede{color:var(--muted);font-size:18px;line-height:1.5;max-width:54ch;margin:0 auto}
  .di-search{display:flex;gap:10px;max-width:480px;margin:26px auto 0}
  .di-search input{flex:1;padding:13px 16px;border:1px solid var(--paper-edge);border-radius:12px;font:inherit;font-size:15px;background:#fff;box-shadow:0 16px 40px -30px rgba(40,50,70,.5)}
  #destGrid{display:grid;grid-template-columns:repeat(3,1fr);gap:18px;margin-top:8px}
  #destGrid .pass{height:240px}
  .di-empty{display:none;color:var(--muted);margin-top:18px}
  @media (max-width:860px){#destGrid{grid-template-columns:1fr 1fr}}
  @media (max-width:520px){#destGrid{grid-template-columns:1fr}.di-search{flex-direction:column}}
</style>
@endpush

@section('content')

{{-- HERO + SEARCH --}}
<section class="di-hero"><div class="wrap">
  <p class="eyebrow">Destinations</p>
  <h1>Where are you travelling?</h1>
  <p class="lede">Pick your destination to see exactly what's needed{{ config('ukv.show_prices') ? ', our fixed service fees,' : '' }} and how we prepare and check your application before it's submitted.</p>
  <form class="di-search" role="search" onsubmit="return false">
    <input type="search" id="destSearch" placeholder="Search a destination…" aria-label="Search destinations" autocomplete="off">
    <button class="btn" type="button" onclick="document.getElementById('destSearch').focus()">Search</button>
  </form>
</div></section>

{{-- DESTINATION CARDS --}}
<section><div class="wrap">
  @if ($destinations->isEmpty())
    <p style="color:var(--muted)">We're adding destinations shortly. In the meantime, <a href="{{ url('/track') }}">track an existing application</a> or get in touch.</p>
  @else
    <div id="destGrid" class="dests">
      @foreach ($destinations as $destination)
        @php $fromFee = $destination->tier_standard_gbp; @endphp
        <a class="pass reveal" href="{{ url('/visa/'.$destination->slug) }}" data-name="{{ strtolower($destination->name) }}" style="text-decoration:none;color:inherit">
          <div class="sky">@if ($destination->image_path)<img src="{{ asset(ltrim($destination->image_path, '/')) }}" alt="{{ $destination->name }}" loading="lazy">@else<svg viewBox="0 0 240 96" preserveAspectRatio="xMidYMax meet" role="img" aria-label="{{ $destination->name }} skyline"><use href="#ukv-skyline"></use></svg>@endif</div>
          <div class="lower">
            <div class="main">
              <span class="k">{{ $destination->visa_type ? strtoupper($destination->visa_type) : 'Visa' }}</span>
              <h3>{{ $destination->name }}</h3>
              <p class="t">Prepared &amp; checked by our UK team</p>
            </div>
            <div class="stub">
              @if (config('ukv.show_prices'))
                <span class="fee">@if (! is_null($fromFee)) £{{ rtrim(rtrim(number_format((float) $fromFee, 2), '0'), '.') }} @else — @endif</span>
                <span class="lab">{{ ! is_null($fromFee) ? 'from / service' : 'service fee' }}</span>
              @else
                <span class="fee">View&nbsp;→</span>
                <span class="lab">details</span>
              @endif
            </div>
          </div>
        </a>
      @endforeach
    </div>
    <p class="di-empty" id="destEmpty">No destinations match that search — try another, or <a href="{{ url('/contact') }}">ask our team</a>.</p>
  @endif

  <div class="pricenote reveal" style="margin-top:32px;background:#f7fafb;border:1px solid var(--paper-edge);border-left:3px solid var(--gold);border-radius:8px;padding:16px 20px;font-size:14px;color:#3a4b55">
    <p style="margin:0 0 6px"><strong style="color:var(--navy)">Our service fee is separate from any government fee.</strong> Each destination's government charges its own fee, shown clearly before you pay anything.</p>
    <p style="margin:0">We prepare, check and submit your application — we are not a government website and cannot guarantee approval. The decision is always the destination authority's.</p>
  </div>
</div></section>

@if ($destinations->isNotEmpty())
<script>
  // Live client-side filter over the destination cards (no backend round-trip).
  (function () {
    var input = document.getElementById('destSearch');
    var grid = document.getElementById('destGrid');
    var empty = document.getElementById('destEmpty');
    if (!input || !grid) return;
    var cards = Array.prototype.slice.call(grid.querySelectorAll('.pass'));
    input.addEventListener('input', function () {
      var q = input.value.trim().toLowerCase();
      var shown = 0;
      cards.forEach(function (c) {
        var hit = !q || (c.getAttribute('data-name') || '').indexOf(q) !== -1;
        c.style.display = hit ? '' : 'none';
        if (hit) shown++;
      });
      if (empty) empty.style.display = shown ? 'none' : 'block';
    });
  })();
</script>
@endif

@endsection
