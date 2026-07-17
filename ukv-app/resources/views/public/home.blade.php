@extends('layouts.public')

@section('title', 'Schengen Visas — Sorted Without the Stress | Beyond Passports')
@section('description', 'Independent UK Schengen visa service. We check, prepare and submit your Schengen application. Registered in UK & Europe, clear fixed fees, every step tracked. Not a government website.')

@push('head')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "Organization",
  "name": "Beyond Passports",
  "url": "{{ url('/') }}",
  "description": "Independent UK Schengen visa facilitation service. Not a government website.",
  "areaServed": "GB"
}
</script>
@endpush

@push('head')
<style>
  /* Home hero — "Editorial centred" treatment. Page-scoped (hp- prefix). Soft terracotta→sage
     gradient-mesh on near-white; big centred headline, the visa-check form as one inline bar
     (destination · passport · button), trust chips + ★rating + a row of destination thumbnails. */
  .hp-hero{position:relative;color:var(--ink);overflow:hidden;border-bottom:1px solid var(--paper-edge);text-align:center;
    background:linear-gradient(180deg,#EAF1F4 0%, #F2F5F6 45%, var(--paper) 100%);}
  .hp-hero > .wrap{position:relative;z-index:2;padding:36px 0 44px}
  .hp-hero .eyebrow{color:var(--cta)}
  .hp-hero h1{color:var(--ink);font:700 clamp(32px,4.6vw,52px)/1.02 var(--display);letter-spacing:-.03em;margin:0 auto 16px}
  .hp-hero .lede{color:var(--muted);font-size:19px;line-height:1.5;max-width:52ch;margin:0 auto}
  /* inline visa-check form bar */
  .hp-bar{display:flex;gap:12px;align-items:flex-end;background:#fff;border:1px solid var(--paper-edge);border-radius:18px;
    box-shadow:0 30px 64px -30px rgba(40,50,70,.45);padding:18px;max-width:780px;margin:28px auto 0;text-align:left}
  .hp-bar .f{flex:1;min-width:0}
  .hp-bar label{display:block;font:700 12px var(--display);margin:0 0 5px;color:var(--ink)}
  .hp-bar select,.hp-bar input{width:100%;box-sizing:border-box;padding:12px;border:1px solid var(--paper-edge);border-radius:11px;font:inherit;font-size:15px;background:#fff;color:var(--ink)}
  /* Passport picker: a custom listbox (not a native <select>) so its caret flips on
     open AND close, exactly like the destination combobox. */
  .hp-bar .hp-selbtn{width:100%;box-sizing:border-box;padding:12px;padding-right:38px;min-height:45px;
    border:1px solid var(--paper-edge);border-radius:11px;font:inherit;font-size:15px;background:#fff;color:var(--ink);
    cursor:pointer;display:flex;align-items:center;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .hp-bar .hp-selbtn:focus-visible{outline:2px solid var(--cta);outline-offset:1px}
  .hp-sel .hpc-panel li[role=option][aria-selected="true"]{font-weight:700;color:var(--cta)}
  .hp-bar input[readonly]{background:var(--paper);color:var(--muted);cursor:default}
  .hp-bar .btn{white-space:nowrap}
  /* Hero destination combobox — click to drop the full grouped list, or type to filter */
  .hp-combo{position:relative}
  .hp-combo input{padding-right:40px}
  .hp-combo .hpc-caret{position:absolute;right:6px;bottom:6px;width:32px;height:34px;display:flex;align-items:center;justify-content:center;padding:0;border:0;background:transparent;color:var(--muted);cursor:pointer;border-radius:8px}
  .hp-combo .hpc-caret svg{width:15px;height:15px;transition:transform .18s ease}
  .hp-combo.open .hpc-caret svg{transform:rotate(180deg)}
  .hp-combo .hpc-caret:hover{background:var(--paper);color:var(--ink)}
  .hpc-panel{position:absolute;left:0;right:0;top:calc(100% + 6px);z-index:50;margin:0;padding:6px;list-style:none;
    background:#fff;border:1px solid var(--paper-edge);border-radius:12px;box-shadow:0 26px 50px -24px rgba(22,34,46,.55);
    max-height:300px;overflow-y:auto;overscroll-behavior:contain}
  .hpc-panel[hidden]{display:none}
  .hpc-panel li[role=option]{padding:9px 12px;border-radius:8px;font:600 14.5px var(--display);color:var(--ink);cursor:pointer;display:flex;align-items:center;gap:10px}
  .hpc-panel li[role=option][hidden]{display:none}
  .hpc-panel li[role=option]:hover,.hpc-panel li[role=option].active{background:rgba(21,94,122,.08)}
  .hpc-panel .hpc-all{font-weight:700;color:var(--cta)}
  .hpc-panel .flag{width:20px;text-align:center;flex:none;font-size:15px;line-height:1}
  .hpc-panel .hpc-grp{font:800 10px var(--display);letter-spacing:.12em;text-transform:uppercase;color:var(--muted);padding:10px 12px 4px}
  .hpc-panel .hpc-grp[hidden]{display:none}
  .hpc-panel .hpc-none{padding:10px 12px;color:var(--muted);font-size:14px}
  .hpc-panel .hpc-none[hidden]{display:none}
  /* signature passport-stamp accent on the form card's top-right corner */
  .hp-bar{position:relative}
  .hp-bar .stamp{position:absolute;top:-22px;right:-18px;background:#fff;z-index:3}
  .hp-barhint{color:var(--muted);font-size:12px;letter-spacing:.02em;margin:12px 0 0}
  @media (max-width:520px){.hp-bar .stamp{display:none}}
  /* popular destination name quick-links — flag-dot tags (D) */
  .hp-names{display:flex;flex-wrap:wrap;gap:9px 11px;justify-content:center;align-items:center;margin:26px 0 0}
  .hp-names a,.hp-names .vchip{display:inline-flex;align-items:center;gap:8px;background:#fff;border:1px solid var(--paper-edge);border-radius:10px;padding:8px 14px;font:600 14px var(--display);color:var(--ink);text-decoration:none;box-shadow:0 6px 16px -12px rgba(40,50,70,.5);transition:border-color .2s ease,color .2s ease}
  .hp-names a .dot{width:7px;height:7px;border-radius:50%;background:var(--cta);flex:none}
  .hp-names .tick{width:15px;height:15px;flex:none;color:var(--cta)}
  .hp-names a:hover{color:var(--cta);border-color:var(--soft)}
  @media (max-width:720px){
    .hp-hero > .wrap{padding:28px 0 34px}
    .hp-bar{flex-direction:column;align-items:stretch}
    /* Keep the check form edge-to-edge (full width), but give the heading + text
       their side margins back so they don't hug the screen edges on mobile. */
    .hp-hero > .wrap > :not(.hp-bar){padding-left:20px;padding-right:20px;box-sizing:border-box}
  }
</style>
@endpush


@push('head')
<style>
  /* Destinations section — "split intro + 2×2 carousel" (E): heading/blurb/button left;
     right is a 2-row horizontal scroller showing 2×2 cards, paging through the rest. */
  /* DESTINATIONS — map-texture backdrop + centred 3-up glass grid (option D) */
  #destinations{background:
    radial-gradient(circle at 18% 26%, rgba(46,154,140,.10), transparent 42%),
    radial-gradient(circle at 82% 74%, rgba(21,94,122,.10), transparent 42%),
    repeating-linear-gradient(0deg, rgba(34,40,43,.03) 0 1px, transparent 1px 26px),
    var(--paper)}
  #destinations .sec-head{text-align:center;max-width:60ch;margin:0 auto}
  #destinations .sec-head .lede{margin:12px auto 0;max-width:54ch}
  #destinations .dests{display:grid;grid-template-columns:repeat(3,1fr);gap:18px;margin-top:32px}
  #destinations .pass{height:240px}
  #destinations .dest-more{text-align:center;margin-top:28px}
  @media (max-width:820px){#destinations .dests{grid-template-columns:1fr 1fr}}
  @media (max-width:520px){#destinations .dests{grid-template-columns:1fr}}

  /* WHY section — warm stamp cards on a peach tint (option C) */
  #why{background:linear-gradient(180deg,#FBF6F1,var(--paper))}
  #why .sec-head{text-align:center;max-width:60ch;margin-left:auto;margin-right:auto}
  #why .ticks{margin-top:30px}
  #why .tick{background:#fff;border:1px solid var(--paper-edge);border-radius:16px;padding:22px;gap:14px;
    box-shadow:0 10px 30px -22px rgba(40,50,70,.5);transition:transform .25s ease,box-shadow .25s ease}
  #why .tick:hover{transform:translateY(-3px);box-shadow:var(--lift-2)}
  #why .tick .stamp{flex:0 0 44px;width:44px;height:44px;padding:9px;border-radius:11px;
    background:rgba(46,154,140,.12);color:var(--stamp-text);box-sizing:border-box}

  /* TESTIMONIALS — trio of consented quote cards (option D) */
  .tquotes{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-top:30px}
  .tq{background:#fff;border:1px solid var(--paper-edge);border-radius:16px;padding:24px 22px;box-shadow:var(--shadow);margin:0;display:flex;flex-direction:column;gap:12px;transition:transform .25s ease,box-shadow .25s ease}
  .tq:hover{transform:translateY(-3px);box-shadow:var(--lift-2)}
  .tq .stars{color:var(--cta);letter-spacing:3px;font-size:14px}
  .tq blockquote{margin:0;font-family:var(--display);font-weight:600;font-size:15.5px;line-height:1.55;color:var(--ink)}
  .tq figcaption{color:var(--stamp-text);font-weight:700;font-size:13px;margin-top:auto}
  @media (max-width:760px){.tquotes{grid-template-columns:1fr}}

  /* APPOINTMENTS — map-texture backdrop + centred finder + pin motif (option C) */
  #appointments{background:
    radial-gradient(circle at 18% 30%, rgba(46,154,140,.10), transparent 42%),
    radial-gradient(circle at 82% 70%, rgba(21,94,122,.10), transparent 42%),
    repeating-linear-gradient(0deg, rgba(34,40,43,.03) 0 1px, transparent 1px 26px),
    var(--paper)}
  #appointments .sec-head{text-align:center;max-width:none}
  #appointments .sec-head h2{max-width:none}
  #appointments .sec-head p{max-width:58ch;margin-left:auto;margin-right:auto}
  #appointments .pin{display:block;margin:0 auto 12px;color:var(--cta)}
  #appointments form{margin-left:auto;margin-right:auto;justify-content:center}
  #appointments .hint{text-align:center}

  /* TRUST BAR — under hero: dark mesh micro-band (F) then warm stat band (B) */
  .tbar-b,.tbar-f{padding:0}
  .tbar-f{background:
      radial-gradient(520px 200px at 12% 0%, rgba(21,94,122,.45), transparent 60%),
      radial-gradient(520px 200px at 92% 100%, rgba(46,154,140,.42), transparent 60%),
      var(--navy);color:#fff}
  .tbar-f .row{display:flex;justify-content:center;gap:30px;flex-wrap:wrap;padding:16px 0}
  .tbar-f .ti{display:flex;align-items:center;gap:9px;font:600 14px var(--display);color:#fff;white-space:nowrap}
  .tbar-f .ti svg{width:20px;height:20px;color:var(--soft);flex:none}
  .tbar-f .ti b{color:var(--soft);font-weight:800}
  .tbar-b{background:linear-gradient(180deg,#FBF6F1,var(--paper));border-bottom:1px solid var(--paper-edge)}
  .tbar-b .row{display:grid;grid-template-columns:repeat(4,1fr);gap:18px;text-align:center;padding:24px 0}
  .tbar-b .n{font:800 clamp(24px,3vw,30px)/1 var(--display);color:var(--cta);letter-spacing:-.02em}
  .tbar-b .l{font:600 13px var(--display);color:var(--muted);margin-top:6px}
  .tbar-b .row>div+div{border-left:1px solid var(--paper-edge)}
  @media (max-width:760px){
    .tbar-b .row{grid-template-columns:1fr 1fr;gap:14px}
    .tbar-b .row>div:nth-child(odd){border-left:0}
    /* .tbar-f mobile carousel is now global (ukv.css) + auto-run in partials.site-scripts. */
  }
</style>
@endpush

@section('content')

{{-- HERO — "Editorial centred": big headline + inline visa-check form bar + popular destination names --}}
@php
  // Schengen-only pivot: the public site surfaces only Schengen / ETIAS destinations.
  // The composer still supplies the full $navDestinations list (reversible) — we filter here.
  $schengenDests = ($navDestinations ?? collect())->where('visa_type', 'Schengen')->values();
  // Popular destination quick-links (names, not images).
  $hpDests = $schengenDests->take(8);

  // Hero destination picker — group the real covered destinations by region + flag.
  // DB-driven: only countries we actually cover show up. Names must match DB spelling.
  $regionOrder = ['Western Europe', 'Southern Europe', 'Northern Europe', 'Central & Eastern Europe'];
  $regionOf = [
    'France'=>'Western Europe','Germany'=>'Western Europe','Netherlands'=>'Western Europe','Austria'=>'Western Europe','Belgium'=>'Western Europe','Luxembourg'=>'Western Europe','Switzerland'=>'Western Europe','Liechtenstein'=>'Western Europe',
    'Spain'=>'Southern Europe','Italy'=>'Southern Europe','Portugal'=>'Southern Europe','Greece'=>'Southern Europe','Croatia'=>'Southern Europe','Malta'=>'Southern Europe','Slovenia'=>'Southern Europe',
    'Denmark'=>'Northern Europe','Sweden'=>'Northern Europe','Iceland'=>'Northern Europe','Norway'=>'Northern Europe','Finland'=>'Northern Europe','Estonia'=>'Northern Europe','Latvia'=>'Northern Europe','Lithuania'=>'Northern Europe',
    'Poland'=>'Central & Eastern Europe','Czechia'=>'Central & Eastern Europe','Hungary'=>'Central & Eastern Europe','Slovakia'=>'Central & Eastern Europe','Bulgaria'=>'Central & Eastern Europe','Romania'=>'Central & Eastern Europe',
  ];
  $flagOf = [
    'France'=>'🇫🇷','Germany'=>'🇩🇪','Netherlands'=>'🇳🇱','Austria'=>'🇦🇹','Belgium'=>'🇧🇪','Luxembourg'=>'🇱🇺','Switzerland'=>'🇨🇭','Liechtenstein'=>'🇱🇮',
    'Spain'=>'🇪🇸','Italy'=>'🇮🇹','Portugal'=>'🇵🇹','Greece'=>'🇬🇷','Croatia'=>'🇭🇷','Malta'=>'🇲🇹','Slovenia'=>'🇸🇮',
    'Denmark'=>'🇩🇰','Sweden'=>'🇸🇪','Iceland'=>'🇮🇸','Norway'=>'🇳🇴','Finland'=>'🇫🇮','Estonia'=>'🇪🇪','Latvia'=>'🇱🇻','Lithuania'=>'🇱🇹',
    'Poland'=>'🇵🇱','Czechia'=>'🇨🇿','Hungary'=>'🇭🇺','Slovakia'=>'🇸🇰','Bulgaria'=>'🇧🇬','Romania'=>'🇷🇴',
  ];
  $groupedDests = [];
  foreach ($schengenDests as $d) {
    $groupedDests[$regionOf[$d->name] ?? 'Other Schengen'][] = $d;
  }
  // Order: known regions first (in $regionOrder), any leftover ('Other Schengen') appended.
  $orderedGroups = [];
  foreach ($regionOrder as $r) { if (!empty($groupedDests[$r])) { $orderedGroups[$r] = $groupedDests[$r]; } }
  foreach ($groupedDests as $r => $list) { if (!isset($orderedGroups[$r])) { $orderedGroups[$r] = $list; } }
@endphp
<section class="hp-hero"><div class="wrap">
  <p class="eyebrow">Schengen visas</p>
  @push('head')<style>@media(max-width:640px){.hp-hero .h1-break{display:none}}</style>@endpush
  <h1>Don't lose the trip to a refused visa <br class="h1-break">or a missing appointment.</h1>
  <p class="lede">A real UK specialist gets your Schengen visa right, and books the appointment. Every document checked by hand before you submit, and you can talk to them any time.</p>

  {{-- inline visa-check form → opens a WhatsApp chat with the trip pre-filled --}}
  <form class="hp-bar" onsubmit="return false">
    <span class="stamp" aria-hidden="true">CHECKED<br>&amp; READY</span>
    <div class="f hp-combo" id="hpc">
      <label for="dest">Where are you going?</label>
      <input id="dest" type="text" autocomplete="off" placeholder="Search a country, or all of Schengen"
             role="combobox" aria-expanded="false" aria-controls="hp-destlist" aria-autocomplete="list">
      <button type="button" class="hpc-caret" tabindex="-1" aria-label="Show destination list">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
      </button>
      <ul class="hpc-panel" id="hp-destlist" role="listbox" aria-label="Schengen destinations" hidden>
        <li class="hpc-all" role="option" data-v="Anywhere in the Schengen Area" data-s="anywhere schengen area all everywhere"><span class="flag" aria-hidden="true">🇪🇺</span>Anywhere in the Schengen Area</li>
        @foreach ($orderedGroups as $region => $list)
          <li class="hpc-grp" aria-hidden="true">{{ $region }}</li>
          @foreach ($list as $d)
            <li role="option" data-v="{{ $d->name }}" data-s="{{ strtolower($d->name) }}"><span class="flag" aria-hidden="true">{{ $flagOf[$d->name] ?? '·' }}</span>{{ $d->name }}</li>
          @endforeach
        @endforeach
        <li class="hpc-none" aria-hidden="true" hidden>No match — we cover all of Schengen.</li>
      </ul>
    </div>
    <div class="f hp-combo hp-sel" id="npc">
      <label id="nat-label">Your passport</label>
      <div class="hp-selbtn" id="nat-btn" role="combobox" tabindex="0" aria-haspopup="listbox" aria-expanded="false" aria-controls="nat-list" aria-labelledby="nat-label nat-text">
        <span id="nat-text">United Kingdom</span>
      </div>
      <button type="button" class="hpc-caret" tabindex="-1" aria-label="Show passport options">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
      </button>
      <input type="hidden" id="nat" value="a UK">
      <ul class="hpc-panel" id="nat-list" role="listbox" aria-label="Your passport" hidden>
        <li role="option" data-v="a UK" data-label="United Kingdom" aria-selected="true">United Kingdom</li>
        <li role="option" data-v="a non-UK" data-label="Other (we'll confirm your rules)" aria-selected="false">Other (we'll confirm your rules)</li>
      </ul>
    </div>
    <button class="btn" type="button" id="hp-chat">Show my documents →</button>
  </form>
  <p class="hp-barhint">A named UK visa specialist replies · usually within minutes, Mon–Sat 9–6</p>
  <div style="margin:12px 0 0">@include('partials.ico-badge', ['variant' => 'chip'])</div>

  {{-- proof pills tight under CTA (getbrazilvisa-style credibility row) --}}
  @push('head')<style>
    .hp-proof{display:flex;flex-wrap:wrap;gap:10px 18px;justify-content:center;margin:18px 0 0;padding:0;list-style:none}
    .hp-proof li{display:inline-flex;align-items:center;gap:7px;font:600 14px var(--body);color:var(--ink)}
    .hp-proof svg{width:17px;height:17px;flex:none;color:var(--stamp-text)}
    @media(max-width:560px){.hp-proof li{font-size:13px}}
  </style>@endpush
  <div class="hp-names">
    <span class="vchip"><svg class="tick" viewBox="0 0 24 24" aria-hidden="true"><path d="m5 13 4 4 10-10" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/></svg>Appointments found in time</span>
    <span class="vchip"><svg class="tick" viewBox="0 0 24 24" aria-hidden="true"><path d="m5 13 4 4 10-10" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/></svg>Refusals prevented</span>
    <span class="vchip"><svg class="tick" viewBox="0 0 24 24" aria-hidden="true"><path d="m5 13 4 4 10-10" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/></svg>A real UK specialist, by name</span>
  </div>
  @include('partials.trustpilot-cta', ['align' => 'center', 'margin' => '18px 0 0'])
  <script>
    // Hero destination combobox: opens the full grouped list on click, filters as you type,
    // full keyboard support, and feeds the pick into the WhatsApp message.
    (function () {
      var combo = document.getElementById('hpc');
      if (!combo) return;
      var input = combo.querySelector('input'),
          panel = combo.querySelector('.hpc-panel'),
          caret = combo.querySelector('.hpc-caret'),
          opts  = Array.prototype.slice.call(panel.querySelectorAll('li[role=option]')),
          grps  = Array.prototype.slice.call(panel.querySelectorAll('.hpc-grp')),
          none  = panel.querySelector('.hpc-none'),
          active = -1;
      function isOpen(){ return combo.classList.contains('open'); }
      function open(){ combo.classList.add('open'); panel.hidden = false; input.setAttribute('aria-expanded','true'); }
      function close(){ combo.classList.remove('open'); panel.hidden = true; input.setAttribute('aria-expanded','false'); setActive(-1); }
      function visible(){ return opts.filter(function(o){ return !o.hidden; }); }
      function setActive(i){
        opts.forEach(function(o){ o.classList.remove('active'); });
        var vis = visible(); active = i;
        if (i >= 0 && i < vis.length){ vis[i].classList.add('active'); vis[i].scrollIntoView({block:'nearest'}); }
      }
      function filter(){
        var q = input.value.trim().toLowerCase(), shown = 0;
        opts.forEach(function(o){ var m = o.dataset.s.indexOf(q) !== -1; o.hidden = !m; if (m) shown++; });
        grps.forEach(function(g){
          var any = false, n = g.nextElementSibling;
          while (n && !n.classList.contains('hpc-grp')){ if (n.getAttribute('role') === 'option' && !n.hidden){ any = true; break; } n = n.nextElementSibling; }
          g.hidden = !any;
        });
        none.hidden = shown > 0;
        setActive(shown > 0 ? 0 : -1);
      }
      function choose(o){ input.value = o.dataset.v; close(); input.focus(); }
      input.addEventListener('focus', function(){ filter(); open(); });
      input.addEventListener('input', function(){ filter(); open(); });
      caret.addEventListener('mousedown', function(e){ e.preventDefault(); if (isOpen()){ close(); } else { filter(); open(); input.focus(); } });
      panel.addEventListener('mousedown', function(e){ var o = e.target.closest('li[role=option]'); if (o){ e.preventDefault(); choose(o); } });
      input.addEventListener('keydown', function(e){
        if (e.key === 'ArrowDown'){ e.preventDefault(); if (!isOpen()){ filter(); open(); } else { setActive(Math.min(active + 1, visible().length - 1)); } }
        else if (e.key === 'ArrowUp'){ e.preventDefault(); if (isOpen()){ setActive(Math.max(active - 1, 0)); } }
        else if (e.key === 'Enter'){ var vis = visible(); if (isOpen() && active >= 0 && vis[active]){ e.preventDefault(); choose(vis[active]); } }
        else if (e.key === 'Escape'){ close(); }
      });
      document.addEventListener('click', function(e){ if (!combo.contains(e.target)) close(); });
    })();

    // Passport picker: custom listbox so the caret flips on open AND close (a native
    // <select> keeps focus after picking, so its arrow only drops on outside-click).
    (function () {
      var combo = document.getElementById('npc');
      if (!combo) return;
      var btn   = document.getElementById('nat-btn'),
          caret = combo.querySelector('.hpc-caret'),
          panel = combo.querySelector('.hpc-panel'),
          hidden= document.getElementById('nat'),
          text  = document.getElementById('nat-text'),
          opts  = Array.prototype.slice.call(panel.querySelectorAll('li[role=option]'));
      function isOpen(){ return combo.classList.contains('open'); }
      function open(){ combo.classList.add('open'); panel.hidden = false; btn.setAttribute('aria-expanded','true'); }
      function close(){ combo.classList.remove('open'); panel.hidden = true; btn.setAttribute('aria-expanded','false'); }
      function choose(o){
        hidden.value = o.dataset.v; text.textContent = o.dataset.label;
        opts.forEach(function(x){ x.setAttribute('aria-selected', x === o ? 'true' : 'false'); });
        close(); btn.focus();
      }
      btn.addEventListener('click', function(){ isOpen() ? close() : open(); });
      caret.addEventListener('mousedown', function(e){ e.preventDefault(); isOpen() ? close() : open(); });
      panel.addEventListener('mousedown', function(e){ var o = e.target.closest('li[role=option]'); if (o){ e.preventDefault(); choose(o); } });
      btn.addEventListener('keydown', function(e){
        if (e.key === 'ArrowDown' || e.key === 'Enter' || e.key === ' '){ e.preventDefault(); if (!isOpen()) open(); }
        else if (e.key === 'Escape'){ close(); }
      });
      document.addEventListener('click', function(e){ if (!combo.contains(e.target)) close(); });
    })();

    // "See what I need" → open a WhatsApp chat with the trip pre-filled.
    (function () {
      var WA = @json(config('ukv.whatsapp') ?: '447882747584');
      var btn = document.getElementById('hp-chat');
      if (!btn) return;
      btn.addEventListener('click', function () {
        var nat = document.getElementById('nat');
        var pass = nat && nat.value ? nat.value : 'a UK';
        var destEl = document.getElementById('dest');
        var raw = destEl && destEl.value.trim() ? destEl.value.trim() : '';
        // "Anywhere in the Schengen Area" reads awkwardly in the sentence — normalise it.
        var dest = (!raw || /^anywhere/i.test(raw)) ? 'the Schengen Area' : raw;
        var msg = 'Hi Beyond Passports, I am applying for a Schengen visa on ' + pass + ' passport for ' + dest + '. What do I need?';
        window.open('https://wa.me/' + WA + '?text=' + encodeURIComponent(msg), '_blank', 'noopener');
      });
    })();
  </script>

</div></section>

{{-- READINESS CHECK — lead-magnet front door, just after hero --}}
@push('head')
<style>
  #readiness{background:linear-gradient(180deg,var(--paper),#FBF6F1)}
  #readiness .scard{background:#fff;border:1px solid var(--paper-edge);border-radius:22px;padding:30px;
    box-shadow:0 26px 60px -38px rgba(30,40,60,.6);display:grid;grid-template-columns:1.05fr .95fr;gap:30px;align-items:center}
  #readiness .pill{display:inline-flex;align-items:center;gap:7px;background:rgba(46,154,140,.12);color:var(--stamp-text);
    font:800 12px var(--display);letter-spacing:.06em;text-transform:uppercase;padding:6px 12px;border-radius:999px}
  #readiness h2{font:800 clamp(26px,3.4vw,36px)/1.08 var(--display);color:var(--ink);letter-spacing:-.02em;margin:13px 0 0}
  #readiness .sub{color:var(--muted);font-size:16px;line-height:1.5;margin:10px 0 0;max-width:42ch}
  #readiness .micro{color:var(--muted);font-size:12px;margin:12px 0 0}
  #readiness .qbox{background:var(--paper);border:1px solid var(--paper-edge);border-radius:16px;padding:20px}
  #readiness .qbox label{font:700 13px var(--display);color:var(--ink);display:block;margin:0 0 5px}
  #readiness .qbox select,#readiness .qbox input{width:100%;box-sizing:border-box;padding:11px 12px;
    border:1px solid var(--paper-edge);border-radius:10px;font-size:15px;background:#fff;margin-bottom:13px}
  #readiness .qbox .btn{width:100%;justify-content:center}
  #readiness .qmeta{color:var(--muted);font-size:12.5px;margin:10px 0 0;text-align:center}
  @media(max-width:760px){#readiness .scard{grid-template-columns:1fr;gap:22px}}
</style>
@endpush
{{-- TRUST BAR — dark mesh trust-points band (F) then warm stat band (B) --}}
<section class="tbar-f"><div class="wrap"><div class="row">
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 5 6v6c0 4.5 3 7.5 7 8.5 4-1 7-4 7-8.5V6z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="m9 12 2 2 4-4.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg><span><b>Schengen visa</b> experts</span></span>
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 7v10M9.5 9.2c0-1 1.1-1.7 2.5-1.7s2.5.7 2.5 1.7-1.1 1.6-2.5 1.6-2.5.7-2.5 1.7 1.1 1.7 2.5 1.7 2.5-.7 2.5-1.7" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg><span><b>No hidden</b> fees</span></span>
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 7v5l3 2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg><span><b>7-day</b> support</span></span>
  <span class="ti">@include('partials.uk-eu-flags',['size'=>15])<span>Registered in <b>UK &amp; Europe</b></span></span>
</div></div></section>
<section class="tbar-b"><div class="wrap"><div class="row">
  <div><div class="n">4.9★</div><div class="l">Average rating</div></div>
  <div><div class="n">{{ App\Support\SiteStats::applications() }}</div><div class="l">Applications filed in {{ App\Support\SiteStats::yearsActive() }} years</div></div>
  <div><div class="n">{{ $schengenDests->count() }}</div><div class="l">Destinations &amp; growing</div></div>
  @include('partials.ico-stat-cell')
</div></div></section>

{{-- PROBLEM + READINESS CHECK — joined: fears on the left, the check that answers them on the right --}}
@push('head')
<style>
  #joined{background:#fff;border-block:1px solid var(--paper-edge)}
  #joined .row{display:grid;grid-template-columns:1fr .92fr;gap:50px;align-items:center}
  #joined .eyebrow{color:var(--cta)}
  #joined h2{font:800 clamp(27px,3.5vw,40px)/1.06 var(--display);color:var(--ink);letter-spacing:-.025em;margin:10px 0 0}
  #joined .lead{color:var(--muted);font-size:16.5px;line-height:1.5;margin:13px 0 22px;max-width:40ch}
  #joined .it{display:flex;gap:14px;align-items:center;padding:15px 0;border-top:1px solid var(--paper-edge)}
  #joined .it:first-of-type{border-top:0}
  #joined .ic{flex:none;width:40px;height:40px;border-radius:11px;background:rgba(180,101,74,.10);color:#B4654A;display:flex;align-items:center;justify-content:center}
  #joined .ic svg{width:21px;height:21px}
  #joined .it b{display:block;font:800 16px var(--display);color:var(--ink);margin-bottom:2px}
  #joined .it p{color:var(--muted);font-size:14px;line-height:1.45;margin:0}
  #joined .card{background:linear-gradient(180deg,#fff,#FBF7F2);border:1px solid var(--paper-edge);border-radius:20px;padding:28px;box-shadow:0 30px 70px -42px rgba(30,40,60,.65)}
  #joined .card .pill{display:inline-flex;align-items:center;gap:7px;background:rgba(46,154,140,.12);color:var(--stamp-text);font:800 12px var(--display);letter-spacing:.05em;text-transform:uppercase;padding:6px 12px;border-radius:999px}
  #joined .card h3{font:800 22px/1.15 var(--display);color:var(--ink);margin:13px 0 0;letter-spacing:-.01em}
  #joined .card .sub{color:var(--muted);font-size:14.5px;line-height:1.5;margin:8px 0 0}
  #joined .card label{display:block;font:700 13px var(--display);color:var(--ink);margin:16px 0 5px}
  #joined .card select,#joined .card input{width:100%;box-sizing:border-box;padding:11px 12px;border:1px solid var(--paper-edge);border-radius:10px;font-size:15px;background:#fff}
  #joined .card .btn{display:block;width:100%;text-align:center;margin-top:16px}
  #joined .card .micro{color:var(--muted);font-size:12px;margin:11px 0 0;text-align:center}
  @media(max-width:860px){#joined .row{grid-template-columns:1fr;gap:30px}}
</style>
@endpush
<section id="joined"><div class="wrap"><div class="row">
  <div class="reveal">
    <p class="eyebrow">Why this goes wrong</p>
    <h2>Don't hand the embassy a reason to say no.</h2>
    <p class="lead">A refusal is usually one missed detail, or an appointment booked too late.</p>
    <div class="it"><span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="m9 15 2 2 4-4"/></svg></span><div><b>One wrong document</b><p>A statement, an insurance date, a mis-ticked box. The embassy says no.</p></div></div>
    <div class="it"><span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span><div><b>No appointment in time</b><p>Slots vanish in minutes. The next one lands after you fly.</p></div></div>
    <div class="it"><span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.3 3.6 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.6a2 2 0 0 0-3.4 0z"/><path d="M12 9v4M12 17h.01"/></svg></span><div><b>Refused once already</b><p>A second no is harder. The reapplication has to fix the real reason.</p></div></div>
  </div>
  @push('head')
  <style>
    #joined .card .rk-prog{height:5px;border-radius:999px;background:var(--paper-edge);overflow:hidden;margin:14px 0 16px}
    #joined .card .rk-prog span{display:block;height:100%;width:0;background:var(--cta);transition:width .25s ease}
    #joined .card .rk-step{display:none}
    #joined .card .rk-step.active{display:block;animation:rkfade .2s ease}
    @keyframes rkfade{from{opacity:0;transform:translateY(4px)}to{opacity:1;transform:none}}
    #joined .card .rk-q{font:700 16px/1.35 var(--display);color:var(--ink);margin:0 0 12px}
    #joined .card .rk-opts{display:flex;flex-direction:column;gap:8px}
    #joined .card .rk-opt{text-align:left;background:#fff;border:1px solid var(--paper-edge);border-radius:11px;padding:13px 15px;font:600 14.5px var(--display);color:var(--ink);cursor:pointer;transition:border-color .15s,background .15s}
    #joined .card .rk-opt:hover{border-color:var(--soft)}
    #joined .card .rk-opt.sel{border-color:var(--cta);background:rgba(21,94,122,.06)}
    #joined .card .rk-nav{display:flex;gap:10px;margin-top:16px}
    #joined .card .rk-nav .btn{flex:1;justify-content:center}
    #joined .card .rk-band{display:inline-block;font:800 12px var(--display);letter-spacing:.06em;text-transform:uppercase;padding:6px 12px;border-radius:999px;margin-bottom:10px}
    #joined .card .rk-band.low{background:rgba(46,154,140,.14);color:var(--stamp-text)}
    #joined .card .rk-band.med{background:rgba(180,101,74,.12);color:#B4654A}
    #joined .card .rk-band.high{background:rgba(180,60,60,.12);color:#b23b3b}
    #joined .card .rk-gap{display:flex;gap:10px;padding:11px 0;border-top:1px solid var(--paper-edge)}
    #joined .card .rk-gap:first-of-type{border-top:0}
    #joined .card .rk-gap b{display:block;font:800 14px var(--display);color:var(--ink)}
    #joined .card .rk-gap span{font-size:13px;color:var(--muted)}
    #joined .card .rk-timing{margin:14px 0 0;padding:11px 13px;border-radius:10px;background:var(--paper);font:600 13.5px var(--display);color:var(--ink)}
  </style>
  @endpush
  <div class="card reveal" id="rk">
    <span class="pill">60 seconds · no sign-up</span>
    <h3>Will your visa pass? Check before you submit.</h3>
    <p class="sub" id="rk-sub">Answer 6 quick questions. We show the gaps that get Schengen visas refused, and whether your travel date is realistic.</p>

    <div class="rk-prog" aria-hidden="true"><span id="rk-bar"></span></div>
    <form id="rk-form" onsubmit="return false">
      <div class="rk-step active" data-step="0">
        <label for="rk-dest">Where are you going?</label>
        <div class="hp-combo rk-combo" id="rkc">
          <input id="rk-dest" type="text" autocomplete="off" placeholder="Search a country…"
                 role="combobox" aria-expanded="false" aria-controls="rk-destlist" aria-autocomplete="list">
          <button type="button" class="hpc-caret" tabindex="-1" aria-label="Show destination list">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
          </button>
          <ul class="hpc-panel" id="rk-destlist" role="listbox" aria-label="Schengen destinations" hidden>
            @foreach ($orderedGroups as $region => $list)
              <li class="hpc-grp" aria-hidden="true">{{ $region }}</li>
              @foreach ($list as $d)
                <li role="option" data-v="{{ $d->name }}" data-s="{{ strtolower($d->name) }}"><span class="flag" aria-hidden="true">{{ $flagOf[$d->name] ?? '·' }}</span>{{ $d->name }}</li>
              @endforeach
            @endforeach
            <li class="hpc-none" aria-hidden="true" hidden>No match — we cover all of Schengen.</li>
          </ul>
        </div>
        <label for="rk-date" style="margin-top:12px">When do you travel?</label>
        <input id="rk-date" type="date">
      </div>

      <div class="rk-step" data-step="1">
        <p class="rk-q">Do you have recent bank statements showing enough funds for the trip (roughly £80 to £100 a day, plus a buffer)?</p>
        <div class="rk-opts" data-q="funds"><button type="button" class="rk-opt" data-w="0">Yes, ready</button><button type="button" class="rk-opt" data-w="1">Not sure</button><button type="button" class="rk-opt" data-w="1">No</button></div>
      </div>
      <div class="rk-step" data-step="2">
        <p class="rk-q">Travel insurance: at least {{ App\Support\SiteStats::insuranceMin() }} medical cover, valid across all Schengen countries, for your full dates?</p>
        <div class="rk-opts" data-q="insurance"><button type="button" class="rk-opt" data-w="0">Yes</button><button type="button" class="rk-opt" data-w="1">Not sure</button><button type="button" class="rk-opt" data-w="1">No / not yet</button></div>
      </div>
      <div class="rk-step" data-step="3">
        <p class="rk-q">Your passport: issued under 10 years ago, valid for 3+ months after you return, and with 2 blank pages?</p>
        <div class="rk-opts" data-q="passport"><button type="button" class="rk-opt" data-w="0">Yes, all three</button><button type="button" class="rk-opt" data-w="1">Not sure</button><button type="button" class="rk-opt" data-w="1">No</button></div>
      </div>
      <div class="rk-step" data-step="4">
        <p class="rk-q">Can you show ties to the UK (job or study letter, return ticket, proof of home)?</p>
        <div class="rk-opts" data-q="ties"><button type="button" class="rk-opt" data-w="0">Yes</button><button type="button" class="rk-opt" data-w="1">Some of it</button><button type="button" class="rk-opt" data-w="1">No</button></div>
      </div>
      <div class="rk-step" data-step="5">
        <p class="rk-q">Have you been refused a visa before (any country)?</p>
        <div class="rk-opts" data-q="refused"><button type="button" class="rk-opt" data-w="0">No</button><button type="button" class="rk-opt" data-w="2">Yes</button></div>
      </div>

      <div class="rk-step" data-step="6" id="rk-result"></div>

      <div class="rk-nav">
        <button type="button" class="btn btn--ghost" id="rk-back" style="display:none">← Back</button>
        <button type="button" class="btn" id="rk-next">Check my readiness →</button>
      </div>
    </form>
    <p class="micro" id="rk-micro">A readiness indicator, not an approval prediction. Your answers stay private.</p>
    @include('partials.trustpilot-cta', ['align' => 'center', 'margin' => '14px 0 0'])
  </div>
</div></div>
  <script>
    // Readiness "Where are you going?" — same grouped, searchable combobox as the hero.
    (function () {
      var combo = document.getElementById('rkc');
      if (!combo) return;
      var input = combo.querySelector('input'),
          panel = combo.querySelector('.hpc-panel'),
          caret = combo.querySelector('.hpc-caret'),
          opts  = Array.prototype.slice.call(panel.querySelectorAll('li[role=option]')),
          grps  = Array.prototype.slice.call(panel.querySelectorAll('.hpc-grp')),
          none  = panel.querySelector('.hpc-none'),
          active = -1;
      function isOpen(){ return combo.classList.contains('open'); }
      function open(){ combo.classList.add('open'); panel.hidden = false; input.setAttribute('aria-expanded','true'); }
      function close(){ combo.classList.remove('open'); panel.hidden = true; input.setAttribute('aria-expanded','false'); setActive(-1); }
      function visible(){ return opts.filter(function(o){ return !o.hidden; }); }
      function setActive(k){ opts.forEach(function(o){ o.classList.remove('active'); }); var vis = visible(); active = k; if (k >= 0 && k < vis.length){ vis[k].classList.add('active'); vis[k].scrollIntoView({block:'nearest'}); } }
      function filter(){
        var q = input.value.trim().toLowerCase(), shown = 0;
        opts.forEach(function(o){ var m = o.dataset.s.indexOf(q) !== -1; o.hidden = !m; if (m) shown++; });
        grps.forEach(function(g){ var any = false, n = g.nextElementSibling; while (n && !n.classList.contains('hpc-grp')){ if (n.getAttribute('role') === 'option' && !n.hidden){ any = true; break; } n = n.nextElementSibling; } g.hidden = !any; });
        none.hidden = shown > 0; setActive(shown > 0 ? 0 : -1);
      }
      function choose(o){ input.value = o.dataset.v; close(); input.focus(); }
      input.addEventListener('focus', function(){ filter(); open(); });
      input.addEventListener('input', function(){ filter(); open(); });
      caret.addEventListener('mousedown', function(e){ e.preventDefault(); if (isOpen()){ close(); } else { filter(); open(); input.focus(); } });
      panel.addEventListener('mousedown', function(e){ var o = e.target.closest('li[role=option]'); if (o){ e.preventDefault(); choose(o); } });
      input.addEventListener('keydown', function(e){
        if (e.key === 'ArrowDown'){ e.preventDefault(); if (!isOpen()){ filter(); open(); } else { setActive(Math.min(active + 1, visible().length - 1)); } }
        else if (e.key === 'ArrowUp'){ e.preventDefault(); if (isOpen()) setActive(Math.max(active - 1, 0)); }
        else if (e.key === 'Enter'){ var vis = visible(); if (isOpen() && active >= 0 && vis[active]){ e.preventDefault(); choose(vis[active]); } }
        else if (e.key === 'Escape'){ close(); }
      });
      document.addEventListener('click', function(e){ if (!combo.contains(e.target)) close(); });
    })();

    (function () {
      var WA = @json(config('ukv.whatsapp') ?: '447882747584');
      var root = document.getElementById('rk');
      if (!root) return;
      var steps = root.querySelectorAll('.rk-step');
      var bar = document.getElementById('rk-bar');
      var nextBtn = document.getElementById('rk-next');
      var backBtn = document.getElementById('rk-back');
      var sub = document.getElementById('rk-sub');
      var micro = document.getElementById('rk-micro');
      var RESULT = steps.length - 1;          // index of result step
      var QCOUNT = steps.length - 2;          // number of question steps (excludes step0 + result)
      var i = 0;
      var answers = {};                        // q -> weight

      var QS = [
        { q:'funds',     gap:'Funds evidence',   fix:'Bank statements that clearly cover your trip.' },
        { q:'insurance', gap:'Travel insurance', fix:'£25k+ medical, all Schengen, your full dates.' },
        { q:'passport',  gap:'Passport validity',fix:'Under 10 years old, valid 3 months after return, 2 blank pages.' },
        { q:'ties',      gap:'Ties to the UK',   fix:'Job or study letter, return ticket, proof of home.' },
        { q:'refused',   gap:'Previous refusal', fix:'We find the real reason and fix it before you reapply.' }
      ];

      function show(n) {
        i = n;
        steps.forEach(function (s, idx) { s.classList.toggle('active', idx === n); });
        bar.style.width = Math.round((n / RESULT) * 100) + '%';
        backBtn.style.display = (n > 0 && n < RESULT) ? '' : 'none';
        if (n === 0) { nextBtn.style.display = ''; nextBtn.textContent = 'Check my readiness →'; }
        else if (n < RESULT) { nextBtn.style.display = 'none'; }     // question steps auto-advance
        else { nextBtn.style.display = 'none'; }
        if (n === RESULT) { sub.style.display = 'none'; micro.style.display = 'none'; }
        else { sub.style.display = ''; micro.style.display = ''; }
      }

      // option click → record + advance
      root.querySelectorAll('.rk-opts').forEach(function (grp) {
        grp.querySelectorAll('.rk-opt').forEach(function (opt) {
          opt.addEventListener('click', function () {
            grp.querySelectorAll('.rk-opt').forEach(function (o){ o.classList.remove('sel'); });
            opt.classList.add('sel');
            answers[grp.dataset.q] = parseInt(opt.dataset.w, 10) || 0;
            setTimeout(function(){ (i + 1 >= RESULT) ? finish() : show(i + 1); }, 160);
          });
        });
      });

      nextBtn.addEventListener('click', function () { if (i === 0) show(1); });
      backBtn.addEventListener('click', function () { if (i > 0) show(i - 1); });

      function daysUntil(v){ if(!v) return null; var d=new Date(v+'T00:00:00'), n=new Date(); n.setHours(0,0,0,0); return Math.round((d-n)/864e5); }

      function finish() {
        var dest = (document.getElementById('rk-dest')||{}).value || '';
        var date = (document.getElementById('rk-date')||{}).value || '';
        var total = 0, gaps = [];
        QS.forEach(function (it) { var w = answers[it.q] || 0; total += w; if (w > 0) gaps.push(it); });

        var band, blurb;
        if (total === 0)      { band='low';  blurb='Looks solid. Worth a final hand-check before you submit.'; }
        else if (total <= 2)  { band='med';  blurb='A couple of gaps that commonly cause refusals. Fixable now.'; }
        else                  { band='high'; blurb='Several refusal triggers here. Get these sorted before you submit.'; }

        var label = band==='low' ? 'Low risk' : band==='med' ? 'Some gaps' : 'High risk';

        // timing
        var dy = daysUntil(date), timing='';
        if (dy !== null) {
          if (dy < 28)      timing = 'Travel in ' + dy + ' days: tight. Appointments go fast, start now.';
          else if (dy < 56) timing = 'Travel in ' + dy + ' days: workable, but do not wait to book.';
          else              timing = 'Travel in ' + dy + ' days: comfortable timing if you start soon.';
        }

        var html = '<span class="rk-band '+band+'">'+label+'</span>'
          + '<h3 style="margin:0 0 6px">'+(dest? 'Your '+dest+' check' : 'Your readiness check')+'</h3>'
          + '<p class="sub" style="margin:0 0 12px">'+blurb+'</p>';
        if (gaps.length) {
          html += gaps.map(function(g){ return '<div class="rk-gap"><div><b>'+g.gap+'</b><span>'+g.fix+'</span></div></div>'; }).join('');
        } else {
          html += '<div class="rk-gap"><div><b>No obvious gaps flagged</b><span>The common refusal triggers look covered.</span></div></div>';
        }
        if (timing) html += '<div class="rk-timing">'+timing+'</div>';

        var msg = 'Hi Beyond Passports, I did the free readiness check.'
          + (dest? ' Destination: '+dest+'.' : '')
          + (date? ' Travel date: '+date+'.' : '')
          + ' Result: '+label+'.'
          + (gaps.length? ' To fix: '+gaps.map(function(g){return g.gap;}).join(', ')+'.' : '')
          + ' Can you help me get this right before I submit?';
        var href = 'https://wa.me/'+WA+'?text='+encodeURIComponent(msg);

        html += '<a class="btn wa" style="display:block;text-align:center;margin-top:16px;background:#25D366" href="'+href+'" target="_blank" rel="noopener">Get a specialist to fix this →</a>'
          + '<p class="micro" style="margin-top:10px">A readiness indicator, not an approval prediction. The embassy decides. <a href="#" id="rk-restart">Start over</a></p>';

        document.getElementById('rk-result').innerHTML = html;
        show(RESULT);
        var rs = document.getElementById('rk-restart');
        if (rs) rs.addEventListener('click', function(e){ e.preventDefault(); answers={}; root.querySelectorAll('.rk-opt.sel').forEach(function(o){o.classList.remove('sel');}); show(0); });
      }

      show(0);
    })();
  </script>
</section>

{{-- HOW --}}
<section id="how"><div class="wrap">
  <div class="sec-head reveal" style="text-align:center;max-width:60ch;margin:0 auto 36px">
    <p class="eyebrow">How it works</p>
    <h2>3 simple steps to get started</h2>
    <p class="lede" style="margin:12px auto 0;max-width:54ch">A straightforward process that helps UK residents prepare for their Schengen visa journey, with expert guidance at every stage.</p>
  </div>
  <div class="steps">
    <div class="step reveal" id="step-01"><div class="num">01</div><div class="rule"></div><h3>Embassy match</h3><p>We identify the right embassy and visa category for your travel plans.</p></div>
    <div class="step reveal" id="step-02"><div class="num">02</div><div class="rule"></div><h3>Document checklist</h3><p>A personalised document checklist for your circumstances, every item checked by hand.</p></div>
    <div class="step reveal" id="step-03"><div class="num">03</div><div class="rule"></div><h3>Booking support</h3><p>Expert guidance through the appointment booking process and your next steps.</p></div>
  </div>
  <div style="text-align:center;margin-top:28px"><a class="btn" href="https://wa.me/{{ config('ukv.whatsapp') ?: '447882747584' }}?text={{ urlencode('Hi Beyond Passports, I would like to start my Schengen application.') }}" target="_blank" rel="noopener">@include('partials.wa-glyph')Start your journey →</a></div>
</div></section>

{{-- DESTINATIONS — map-texture backdrop + centred 3-up glass grid (D), region-tab filtered --}}
@php
  // Region tabs are driven by the real `region` column (set by SchengenSeeder).
  $regionOrder = ['Western Europe', 'Southern Europe', 'Northern Europe', 'Central & Eastern Europe'];
  $regionCounts = [];
  foreach ($schengenDests as $d) {
    $r = $d->region ?: 'Worldwide';
    $regionCounts[$r] = ($regionCounts[$r] ?? 0) + 1;
  }
  // Preferred order first, then any extras (e.g. Worldwide for non-Schengen).
  $regionsPresent = collect($regionOrder)->filter(fn ($r) => ! empty($regionCounts[$r]))
      ->merge(collect(array_keys($regionCounts))->reject(fn ($r) => in_array($r, $regionOrder, true)))
      ->values();
  // "See all" target: the /schengen-visa hub (its own region tabs + searchable grid handle
  // filtering). /visa/schengen was drafted (301 -> /schengen-visa), so link the canonical page.
  $regionUrl = fn ($r) => url('/schengen-visa');
@endphp
@push('head')
<style>
  #destinations .dtabs{display:flex;flex-wrap:nowrap;gap:10px;justify-content:center;margin:30px 0 28px;
    overflow-x:auto;scrollbar-width:none;-webkit-overflow-scrolling:touch;padding-bottom:2px}
  #destinations .dtabs::-webkit-scrollbar{display:none}
  @media(max-width:720px){#destinations .dtabs{justify-content:flex-start}}
  #destinations .dtab{display:inline-flex;align-items:center;gap:8px;flex:0 0 auto;white-space:nowrap;background:#fff;border:1px solid var(--paper-edge);color:var(--ink);
    font:700 14px var(--display);padding:9px 15px;border-radius:10px;cursor:pointer;
    box-shadow:0 6px 16px -12px rgba(40,50,70,.5);transition:border-color .2s,color .2s,background .2s,box-shadow .2s}
  #destinations .dtab .tk{width:15px;height:15px;flex:none;opacity:0;transition:opacity .2s}
  #destinations .dtab .tk svg{width:100%;height:100%;fill:none;stroke:currentColor;stroke-width:2.6;stroke-linecap:round;stroke-linejoin:round}
  #destinations .dtab .c{font-size:12px;font-weight:800;color:var(--muted)}
  #destinations .dtab:hover{border-color:var(--soft);color:var(--cta)}
  #destinations .dtab.active{background:var(--cta);border-color:var(--cta);color:#fff;box-shadow:0 12px 26px -14px rgba(21,94,122,.6)}
  #destinations .dtab.active .tk{opacity:1}
  #destinations .dtab.active .c{color:rgba(255,255,255,.82)}
  #destinations .dest-empty{display:none;text-align:center;color:var(--muted);padding:24px 0}
</style>
@endpush
<section id="destinations"><div class="wrap">
  <div class="sec-head reveal">
    <p class="eyebrow">Popular destinations</p>
    <h2>Every Schengen requirement, checked{{ config('ukv.show_prices') ? ' at a fixed fee' : '' }}.</h2>
    <p class="lede">Pick your destination. Every document reviewed by hand before you submit.</p>
  </div>
  <div class="dtabs" id="dtabs">
    <button type="button" class="dtab active" data-region="all" data-url="{{ url('/schengen-visa') }}" data-label="destinations"><span class="tk"><svg viewBox="0 0 24 24"><path d="m5 13 4 4 10-10"/></svg></span>Popular</button>
    @foreach ($regionsPresent as $rk)
      <button type="button" class="dtab" data-region="{{ $rk }}" data-url="{{ $regionUrl($rk) }}" data-label="{{ $rk }}"><span class="tk"><svg viewBox="0 0 24 24"><path d="m5 13 4 4 10-10"/></svg></span>{{ str_replace(' Europe', '', $rk) }} <span class="c">{{ $regionCounts[$rk] }}</span></button>
    @endforeach
  </div>
  <div class="dests" id="dests">
  @foreach ($schengenDests as $d)
    @php $dWa = 'https://wa.me/'.config('ukv.whatsapp').'?text='.rawurlencode("Hi, I'd like help with my Schengen visa for {$d->name}. "); @endphp
    <a class="pass reveal" data-region="{{ $d->region ?: 'Worldwide' }}" @if ($loop->index < 6) data-pop="1" @endif href="{{ $dWa }}" target="_blank" rel="noopener" aria-label="Ask about a {{ $d->name }} Schengen visa on WhatsApp"><div class="sky">@if ($d->image_path)<img src="{{ asset(ltrim($d->image_path, '/')) }}" alt="{{ $d->name }}" loading="lazy">@else<svg viewBox="0 0 240 96" preserveAspectRatio="xMidYMax meet" role="img" aria-label="{{ $d->name }} skyline"><use href="#ukv-skyline"></use></svg>@endif</div><div class="lower"><div class="main"><div class="k">{{ $d->visa_type }}</div><h3>{{ $d->name }}</h3><div class="t">UK citizens{{ $d->max_stay_days ? ' · up to '.$d->max_stay_days.' days' : '' }}</div></div><div class="stub"><div class="fee">Chat&nbsp;→</div><div class="lab">WHATSAPP</div></div></div></a>
  @endforeach
  </div>
  <p class="dest-empty" id="dest-empty">More destinations in this region coming soon.</p>
  <div class="dest-more"><a id="dest-see-all" class="rlink" style="font-weight:600" href="{{ url('/schengen-visa') }}">See all destinations @if(config('ukv.show_prices'))&amp; fixed fees @endif→</a></div>
  <script>
    (function () {
      var tabs = document.querySelectorAll('#dtabs .dtab');
      var cards = document.querySelectorAll('#dests .pass');
      var empty = document.getElementById('dest-empty');
      var seeAll = document.getElementById('dest-see-all');
      var feeSuffix = @json(config('ukv.show_prices') ? ' & fixed fees' : '');
      function apply(tab) {
        var region = tab.dataset.region, shown = 0;
        cards.forEach(function (c) {
          var match = region === 'all' ? c.dataset.pop === '1' : c.dataset.region === region;
          var show = match && shown < 6;
          c.style.display = show ? '' : 'none';
          if (show) shown++;
        });
        if (empty) empty.style.display = shown ? 'none' : 'block';
        if (seeAll) {
          seeAll.href = tab.dataset.url;
          seeAll.textContent = 'See all ' + (tab.dataset.label || 'destinations') + feeSuffix + ' →';
        }
      }
      tabs.forEach(function (t) {
        t.addEventListener('click', function () {
          tabs.forEach(function (x) { x.classList.remove('active'); });
          t.classList.add('active');
          apply(t);
        });
      });
      var first = document.querySelector('#dtabs .dtab.active') || tabs[0];
      if (first) apply(first);
    })();
  </script>
</div></section>

{{-- WHAT WE DO — six-service grid (Why-style cards), below destinations --}}
@push('head')
<style>
  #services{background:linear-gradient(180deg,#FBF6F1,var(--paper))}
  #services .sec-head{text-align:center;max-width:60ch;margin-left:auto;margin-right:auto}
  #services .sec-head .lede{margin:12px auto 0;max-width:52ch}
  #services .ticks{margin-top:30px}
  #services .tick{background:#fff;border:1px solid var(--paper-edge);border-radius:16px;padding:22px;gap:14px;
    box-shadow:0 10px 30px -22px rgba(40,50,70,.5);transition:transform .25s ease,box-shadow .25s ease}
  #services .tick:hover{transform:translateY(-3px);box-shadow:var(--lift-2)}
  #services .tick .stamp{flex:0 0 44px;width:44px;height:44px;padding:9px;border-radius:11px;
    background:rgba(46,154,140,.12);color:var(--stamp-text);box-sizing:border-box}
</style>
@endpush
<section id="services"><div class="wrap">
  <div class="sec-head reveal"><p class="eyebrow">What we do</p><h2>Everything your Schengen application needs</h2><p class="lede">End-to-end help, from eligibility to the embassy door.</p></div>
  <div class="ticks">
    <div class="tick reveal"><svg class="stamp" width="38" height="38" viewBox="0 0 48 48" role="img" aria-label="Checked"><use href="#ukv-stamp"></use></svg><div><h3>Eligibility check</h3><p>We confirm you can apply, and on the right visa, before you spend anything.</p></div></div>
    <div class="tick reveal"><svg class="stamp" width="38" height="38" viewBox="0 0 48 48" role="img" aria-label="Checked"><use href="#ukv-stamp"></use></svg><div><h3>Document review</h3><p>Every document checked by hand against the current embassy rules.</p></div></div>
    <div class="tick reveal"><svg class="stamp" width="38" height="38" viewBox="0 0 48 48" role="img" aria-label="Checked"><use href="#ukv-stamp"></use></svg><div><h3>Form completion</h3><p>We fill and check the application so small errors do not creep in.</p></div></div>
    <div class="tick reveal"><svg class="stamp" width="38" height="38" viewBox="0 0 48 48" role="img" aria-label="Checked"><use href="#ukv-stamp"></use></svg><div><h3>Appointment booking</h3><p>We find a biometric slot in time for your travel date.</p></div></div>
    <div class="tick reveal"><svg class="stamp" width="38" height="38" viewBox="0 0 48 48" role="img" aria-label="Checked"><use href="#ukv-stamp"></use></svg><div><h3>Submission &amp; follow-up</h3><p>We track your application and keep you posted at every step.</p></div></div>
    <div class="tick reveal"><svg class="stamp" width="38" height="38" viewBox="0 0 48 48" role="img" aria-label="Checked"><use href="#ukv-stamp"></use></svg><div><h3>Refused before?</h3><p>We work out why and fix it before you reapply.</p></div></div>
  </div>
</div></section>

{{-- HOW WE PREVENT REFUSALS --}}
@include('partials.home-prevention-b')

{{-- TESTIMONIALS — trio of consented quote cards (real anonymised reviews, single source) --}}
@php $homeQuotes = array_slice(\App\Http\Controllers\ReviewController::all(), 0, 3); @endphp
<section class="alt"><div class="wrap">
  <div class="sec-head reveal" style="text-align:center;max-width:60ch;margin:0 auto 6px">
    <p class="eyebrow">Trusted by UK travellers</p>
    <h2>Real people, really sorted</h2>
    <div style="display:flex;justify-content:center;margin-top:10px">@include('partials.trustpilot-cta', ['align' => 'center', 'margin' => '0'])</div>
  </div>
  <div class="tquotes">
    @foreach ($homeQuotes as $t)
    <figure class="tq reveal">
      <div class="stars" aria-label="{{ $t['rating'] ?? 5 }} out of 5 stars">{!! str_repeat('★', $t['rating'] ?? 5) !!}</div>
      <blockquote>{{ $t['quote'] }}</blockquote>
      <figcaption>— {{ $t['attribution'] }}</figcaption>
    </figure>
    @endforeach
  </div>
  <p style="text-align:center;margin-top:24px"><a class="rlink" style="font-weight:600" href="{{ url('/reviews') }}">Read more traveller reviews →</a></p>
</div></section>

{{-- APPOINTMENTS / NEAREST CENTRE --}}
<section id="appointments" class="alt"><div class="wrap reveal">
  <div class="sec-head">
    <svg class="pin" width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
    <p class="eyebrow">In-person centres</p>
    @if (($slotSummary['available_count'] ?? 0) > 0)
      <h2>Grab an appointment before it's gone</h2>
      @push('head')<style>
        #appointments .ap-chips{display:flex;flex-wrap:wrap;gap:10px;justify-content:center;margin:14px 0 0;padding:0;list-style:none}
        #appointments .ap-chips li{display:inline-flex;align-items:center;gap:8px;font:700 14px var(--display);padding:9px 15px;border-radius:999px}
        #appointments .ap-chips .live{background:rgba(46,154,140,.10);border:1px solid rgba(46,154,140,.22);color:var(--stamp-text)}
        #appointments .ap-chips .meta{background:#fff;border:1px solid var(--paper-edge);color:var(--ink)}
        #appointments .ap-chips .meta svg{width:16px;height:16px;color:var(--cta);flex:none}
        #appointments .ap-chips .pulse{width:8px;height:8px;border-radius:50%;background:var(--stamp);box-shadow:0 0 0 0 rgba(46,154,140,.55);animation:apPulse 2s infinite;flex:none}
        @keyframes apPulse{0%{box-shadow:0 0 0 0 rgba(46,154,140,.5)}70%{box-shadow:0 0 0 7px rgba(46,154,140,0)}100%{box-shadow:0 0 0 0 rgba(46,154,140,0)}}
      </style>@endpush
      <ul class="ap-chips">
        <li class="live"><span class="pulse" aria-hidden="true"></span>{{ $slotSummary['available_count'] }} appointment{{ $slotSummary['available_count'] === 1 ? '' : 's' }} available</li>
        @if (! empty($slotSummary['next_slot_at']))<li class="meta"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>Next on {{ $slotSummary['next_slot_at']->format('j M Y') }}</li>@endif
        @if (($slotSummary['centre_count'] ?? 0) > 0)<li class="meta"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2a7 7 0 0 0-7 7c0 5 7 13 7 13s7-8 7-13a7 7 0 0 0-7-7z"/><circle cx="12" cy="9" r="2.5"/></svg>Across {{ $slotSummary['centre_count'] }} centre{{ $slotSummary['centre_count'] === 1 ? '' : 's' }}</li>@endif
      </ul>
      <p style="max-width:58ch;color:var(--muted);margin-top:12px">Enter your postcode to find the one nearest you.</p>
    @else
      <h2>Find your nearest centre</h2>
      <p style="max-width:58ch;color:var(--muted)">Schengen visas need an in-person biometric appointment. Enter your postcode and we'll show the closest centre, so you don't have to go hunting.</p>
    @endif
  </div>
  <form method="GET" action="{{ route('centre.search') }}" style="display:flex;flex-wrap:wrap;gap:10px;margin-top:8px;max-width:520px">
    <input type="text" name="postcode" placeholder="e.g. SW1A 1AA" autocomplete="postal-code" required aria-label="Your postcode"
           style="flex:1;min-width:200px;padding:12px;border:1px solid var(--paper-edge);border-radius:8px;font:inherit;font-size:15px">
    <button type="submit" class="btn">Find nearest →</button>
  </form>
  <p class="hint" style="margin-top:10px"><a href="{{ url('/find-a-centre') }}">Browse the full centre finder →</a> · Every Schengen visa needs a biometric appointment at a visa centre.</p>
</div></section>

{{-- CTA — hidden for now (per request) --}}
@if (false)
<section class="cta-band"><div class="wrap reveal">
  <div class="rule"></div>
  <h2>Let's get you travelling</h2>
  <p style="max-width:48ch;color:rgba(255,255,255,.85)">Start your application now, or message our UK team with any question.</p>
  <div class="row"><a href="{{ App\Support\SiteStats::chatUrl('Hi Beyond Passports, I would like help with my Schengen visa.') }}" target="_blank" rel="noopener" class="btn">Check eligibility →</a> @include('partials.consult-cta')<a href="https://wa.me/{{ config('ukv.whatsapp') ?: '447882747584' }}?text={{ rawurlencode('Hi Beyond Passports, I would like help with my Schengen visa.') }}" class="btn btn--glass">@include('partials.wa-glyph')Chat on WhatsApp</a></div>
</div></section>
@endif

@endsection
