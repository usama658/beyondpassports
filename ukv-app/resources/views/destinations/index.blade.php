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
  /* trust band — same treatment as the home page (.tbar-f) */
  .tbar-f{padding:0;background:
      radial-gradient(520px 200px at 12% 0%, rgba(21,94,122,.45), transparent 60%),
      radial-gradient(520px 200px at 92% 100%, rgba(46,154,140,.42), transparent 60%),
      var(--navy);color:#fff}
  .tbar-f .row{display:flex;justify-content:center;gap:30px;flex-wrap:wrap;padding:16px 0}
  .tbar-f .ti{display:flex;align-items:center;gap:9px;font:600 14px var(--display);color:#fff;white-space:nowrap}
  .tbar-f .ti svg{width:20px;height:20px;color:var(--soft);flex:none}
  .tbar-f .ti b{color:var(--soft);font-weight:800}
  @media (max-width:860px){#destGrid{grid-template-columns:1fr 1fr}}
  @media (max-width:760px){.tbar-f .row{gap:18px 22px}}
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

{{-- TRUST BAND — generic, home-page treatment --}}
<section class="tbar-f"><div class="wrap"><div class="row">
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 21V4m0 0 7 2 7-2v10l-7 2-7-2" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg><span><b>UK-based</b> team</span></span>
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="2" fill="none" stroke="currentColor" stroke-width="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3" fill="none" stroke="currentColor" stroke-width="2"/></svg><span>Secure <b>Stripe</b> payments</span></span>
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7-10-7-10-7z" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="2.6" fill="none" stroke="currentColor" stroke-width="2"/></svg><span>Every step <b>tracked</b></span></span>
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"/><path d="M3 12h18M12 3c2.5 2.6 2.5 15.4 0 18M12 3c-2.5 2.6-2.5 15.4 0 18" fill="none" stroke="currentColor" stroke-width="2"/></svg><span><b>{{ $destinations->count() }}</b> destinations &amp; growing</span></span>
</div></div></section>
<section><div class="wrap">
  @if ($destinations->isEmpty())
    <p style="color:var(--muted)">We're adding destinations shortly. In the meantime, <a href="{{ url('/track') }}">track an existing application</a> or get in touch.</p>
  @else
    <div id="destGrid" class="dests">
      @foreach ($destinations as $destination)
        @include('partials.destination-card', ['destination' => $destination])
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
