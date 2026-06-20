@extends('layouts.public')

@section('title', 'Schengen Area & ETIAS for UK travellers | Beyond Passports')
@section('description', 'Travelling to Europe? One ETIAS will cover all Schengen countries from late 2026 — 90 days in any 180, valid 3 years. Pick your destination; we prepare and check everything. Independent service, not a government website.')
@section('canonical', url('/visa/schengen'))

@push('head')
<style>
  /* schengen hub — page-local. Reuses the global .pass/.dests destination cards. */
  .sh-hero{position:relative;overflow:hidden;background:var(--navy);color:#fff;padding:64px 0 56px}
  .sh-hero::before{content:"";position:absolute;inset:0;background:radial-gradient(60% 80% at 12% 16%,rgba(21,94,122,.42),transparent 60%),radial-gradient(55% 75% at 88% 84%,rgba(46,154,140,.4),transparent 62%)}
  .sh-hero .wrap{position:relative;z-index:2}
  .sh-hero .eyebrow{color:var(--soft)}
  .sh-hero h1{color:#fff;max-width:18ch;margin:0 0 14px}
  .sh-hero .lede{color:rgba(255,255,255,.85);max-width:56ch;margin:0 0 20px}
  .sh-facts{display:flex;gap:10px;flex-wrap:wrap}
  .sh-facts span{background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.18);border-radius:999px;padding:8px 15px;font-size:13px;font-weight:600}
  .sh-note{display:flex;gap:14px;align-items:flex-start;background:linear-gradient(180deg,#fff8ef,#fffdf9);border:1px solid var(--paper-edge);border-left:4px solid #c8923a;border-radius:14px;padding:18px 22px;margin:28px 0 4px;box-shadow:0 10px 30px -26px rgba(40,50,70,.4)}
  .sh-note svg{flex:none;width:22px;height:22px;stroke:#c8923a;stroke-width:2;fill:none;margin-top:1px}
  .sh-note p{margin:0;font-size:15px;line-height:1.6;color:#3a4b55}.sh-note strong{color:var(--navy)}
</style>
@endpush

@section('content')

<section class="sh-hero"><div class="wrap">
  <p class="eyebrow">Europe · Schengen Area</p>
  <h1>Travelling to Europe? Here's what you'll need</h1>
  <p class="lede">One ETIAS authorisation will cover all the Schengen countries. Pick your destination below — we prepare and check everything so your trip goes right.</p>
  <div class="sh-facts">
    <span>One ETIAS, many countries</span>
    <span>90 days in any 180</span>
    <span>€20 · valid 3 years</span>
    <span>UK-based team</span>
  </div>
</div></section>

<section><div class="wrap">
  <div class="sh-note reveal">
    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 8v5M12 16h.01"/></svg>
    <p><strong>ETIAS isn't required yet.</strong> Right now UK citizens travel to the Schengen Area <strong>visa-free</strong> for short stays — no ETIAS, no fee. ETIAS launches in late 2026. When it opens we'll prepare and check yours, and confirm the rules before you pay. <span style="color:var(--muted)">Not a government website · the decision is the authority's.</span></p>
  </div>

  @if ($destinations->isEmpty())
    <p style="color:var(--muted);margin-top:24px">Schengen destinations are being added shortly.</p>
  @else
    <div class="dests" style="margin-top:26px">
      @foreach ($destinations as $destination)
        @include('partials.destination-card', ['destination' => $destination])
      @endforeach
    </div>
  @endif

  <div class="pricenote reveal" style="margin-top:32px;background:#f7fafb;border:1px solid var(--paper-edge);border-left:3px solid var(--gold);border-radius:8px;padding:16px 20px;font-size:14px;color:#3a4b55">
    <p style="margin:0"><strong style="color:var(--navy)">One ETIAS covers the whole Schengen Area.</strong> You don't need a separate authorisation per country. We are not a government website and cannot guarantee approval — the decision is always the authority's.</p>
  </div>
</div></section>

@endsection
