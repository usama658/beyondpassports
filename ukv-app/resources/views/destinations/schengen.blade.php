@extends('layouts.public')

@section('title', 'Schengen Area & ETIAS for UK travellers | Beyond Passports')
@section('description', 'Travelling to Europe? One ETIAS will cover all Schengen countries from late 2026 — 90 days in any 180, valid 3 years. Pick your destination; we prepare and check everything. Independent service, not a government website.')
@section('canonical', url('/visa/schengen'))

@push('head')
<style>
  /* schengen hub — reuses the destinations-index hero (.di-hero) + global .pass/.dests cards. */
  .di-hero{background:linear-gradient(180deg,#EAF1F4 0%, #F2F5F6 60%, var(--paper) 100%);border-bottom:1px solid var(--paper-edge);text-align:center}
  .di-hero > .wrap{padding:56px 0 48px}
  .di-hero .eyebrow{color:var(--cta)}
  .di-hero h1{font:700 clamp(30px,4vw,46px)/1.04 var(--display);letter-spacing:-.03em;color:var(--ink);margin:0 auto 14px}
  .di-hero .lede{color:var(--muted);font-size:18px;line-height:1.5;max-width:54ch;margin:0 auto}
  .di-search{display:flex;gap:10px;max-width:480px;margin:26px auto 0}
  .di-search input{flex:1;padding:13px 16px;border:1px solid var(--paper-edge);border-radius:12px;font:inherit;font-size:15px;background:#fff;box-shadow:0 16px 40px -30px rgba(40,50,70,.5)}
  .di-empty{display:none;text-align:center;color:var(--muted);margin-top:24px}
  @media (max-width:520px){.di-search{flex-direction:column}}
  /* trust band — same treatment as the home page (.tbar-f), ETIAS facts */
  .tbar-f{padding:0;background:
      radial-gradient(520px 200px at 12% 0%, rgba(21,94,122,.45), transparent 60%),
      radial-gradient(520px 200px at 92% 100%, rgba(46,154,140,.42), transparent 60%),
      var(--navy);color:#fff}
  .tbar-f .row{display:flex;justify-content:center;gap:30px;flex-wrap:wrap;padding:16px 0}
  .tbar-f .ti{display:flex;align-items:center;gap:9px;font:600 14px var(--display);color:#fff;white-space:nowrap}
  .tbar-f .ti svg{width:20px;height:20px;color:var(--soft);flex:none}
  .tbar-f .ti b{color:var(--soft);font-weight:800}
  @media (max-width:760px){.tbar-f .row{gap:18px 22px}}
  .sh-note{display:flex;gap:14px;align-items:flex-start;background:linear-gradient(180deg,#fff8ef,#fffdf9);border:1px solid var(--paper-edge);border-left:4px solid #c8923a;border-radius:14px;padding:18px 22px;margin:28px 0 4px;box-shadow:0 10px 30px -26px rgba(40,50,70,.4)}
  .sh-note svg{flex:none;width:22px;height:22px;stroke:#c8923a;stroke-width:2;fill:none;margin-top:1px}
  .sh-note p{margin:0;font-size:15px;line-height:1.6;color:#3a4b55}.sh-note strong{color:var(--navy)}
</style>
@endpush

@section('content')

<section class="di-hero"><div class="wrap">
  <p class="eyebrow">Europe · Schengen Area</p>
  <h1>Travelling to Europe? Here's what you'll need</h1>
  <p class="lede">One ETIAS authorisation will cover all the Schengen countries. Pick your destination below — we prepare and check everything so your trip goes right.</p>
  <form class="di-search" role="search" onsubmit="return false">
    <input type="search" id="destSearch" placeholder="Search a Schengen country…" aria-label="Search Schengen countries" autocomplete="off">
    <button class="btn" type="button" onclick="document.getElementById('destSearch').focus()">Search</button>
  </form>
</div></section>

{{-- TRUST BAND — ETIAS facts, home-page treatment --}}
<section class="tbar-f"><div class="wrap"><div class="row">
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2" fill="none" stroke="currentColor" stroke-width="2"/><path d="M3 9h18" stroke="currentColor" stroke-width="2"/></svg><span><b>One ETIAS</b>, many countries</span></span>
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2" fill="none" stroke="currentColor" stroke-width="2"/><path d="M3 9h18M8 3v4M16 3v4" stroke="currentColor" stroke-width="2"/></svg><span><b>90 days</b> in any 180</span></span>
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 12 12 3h7v7l-9 9z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><circle cx="15" cy="8" r="1.4" fill="currentColor"/></svg><span><b>€20</b> · valid 3 years</span></span>
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 21V4m0 0 7 2 7-2v10l-7 2-7-2" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg><span><b>UK-based</b> team</span></span>
</div></div></section>

<section><div class="wrap">
  <div class="sh-note reveal">
    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 8v5M12 16h.01"/></svg>
    <p><strong>ETIAS isn't required yet.</strong> Right now UK citizens travel to the Schengen Area <strong>visa-free</strong> for short stays — no ETIAS, no fee. ETIAS launches in late 2026. When it opens we'll prepare and check yours, and confirm the rules before you pay. <span style="color:var(--muted)">Not a government website · the decision is the authority's.</span></p>
  </div>

  @if (! empty($activeRegion))
    <p style="margin:26px 0 0;font-size:14px;color:var(--muted)">Showing <strong style="color:var(--navy)">{{ $activeRegion }}</strong> ({{ $destinations->count() }}) · <a href="{{ url('/visa/schengen') }}" style="color:var(--cta);font-weight:600">show all 29 →</a></p>
  @endif

  @if ($destinations->isEmpty())
    <p style="color:var(--muted);margin-top:24px">Schengen destinations are being added shortly.</p>
  @else
    <div id="destGrid" class="dests" style="margin-top:26px">
      @foreach ($destinations as $destination)
        @include('partials.destination-card', ['destination' => $destination])
      @endforeach
    </div>
    <p class="di-empty" id="destEmpty">No Schengen country matches that search — try another, or <a href="{{ url('/contact') }}">ask our team</a>.</p>
  @endif

  <div class="pricenote reveal" style="margin-top:32px;background:#f7fafb;border:1px solid var(--paper-edge);border-left:3px solid var(--gold);border-radius:8px;padding:16px 20px;font-size:14px;color:#3a4b55">
    <p style="margin:0"><strong style="color:var(--navy)">One ETIAS covers the whole Schengen Area.</strong> You don't need a separate authorisation per country. We are not a government website and cannot guarantee approval — the decision is always the authority's.</p>
  </div>
</div></section>

@if ($destinations->isNotEmpty())
<script>
  // Live client-side filter over the Schengen cards (mirrors the destinations index).
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
