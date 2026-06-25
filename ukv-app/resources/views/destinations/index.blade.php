@extends('layouts.public')

@section('title', 'Schengen visa from the UK, sorted | Beyond Passports')
@section('description', 'Applying for a Schengen visa from the UK? An independent UK team checks every document, completes the forms and books your appointment. Service fee separate from the embassy fee. Not a government website.')

@php
  // WhatsApp deep-link (same pattern as the other public pages).
  $waNumber = config('ukv.whatsapp') ?: '440000000000';
  $waLink = 'https://wa.me/'.$waNumber.'?text='.rawurlencode('Hi Beyond Passports, I need help with a Schengen visa');
  // Inline WhatsApp glyph (copied from services.blade.php — sized via .wa-g so it never blows up).
  $waGlyph = '<svg viewBox="0 0 24 24" aria-hidden="true" class="wa-g"><path d="M.057 24l1.687-6.163a11.867 11.867 0 0 1-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.817 11.817 0 0 1 8.413 3.488 11.824 11.824 0 0 1 3.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 0 1-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884a9.86 9.86 0 0 0 1.51 5.26l-.999 3.648 3.978-1.607zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>';
@endphp

@push('head')
<style>
  /* Schengen hub — page-scoped only (sg- prefix). Reuses the site design system in ukv.css. */

  /* Hero — soft-sky split: copy left, white "Start here" action card right */
  .sg-hero{background:linear-gradient(180deg,#EAF1F4 0%, #F2F5F6 55%, var(--paper) 100%);
    border-bottom:1px solid var(--paper-edge)}
  .sg-hero > .wrap{padding:34px 0 32px}
  .sg-hero-grid{display:grid;grid-template-columns:1.15fr .85fr;gap:46px;align-items:center}
  .sg-hero .eyebrow{color:var(--cta)}
  .sg-hero h1{font:700 clamp(30px,4.2vw,46px)/1.05 var(--display);letter-spacing:-.03em;color:var(--ink);margin:0 0 16px;max-width:15ch}
  .sg-hero .lede{color:var(--muted);font-size:18px;line-height:1.5;max-width:46ch;margin:0}
  .btn .wa-g{width:17px;height:17px;fill:currentColor;flex:none;vertical-align:-3px}
  /* Start-here card */
  .sg-hcard{background:#fff;border:1px solid var(--paper-edge);border-radius:18px;padding:24px;
    box-shadow:0 30px 64px -34px rgba(40,50,70,.5)}
  .sg-hcard h3{font:800 17px var(--display);color:var(--ink);margin:0 0 4px}
  .sg-hcard > p{font-size:13.5px;color:var(--muted);margin:0 0 16px}
  .sg-hcard .acts{display:flex;flex-direction:column;gap:12px}
  .sg-hcard .btn{width:100%;display:inline-flex;align-items:center;justify-content:center;gap:8px}
  .sg-hcard .btn--wa{background:#25D366;border:0;color:#fff}
  .sg-hcard .btn--wa:hover{background:#1da851}
  .sg-hcard .btn--ghost{background:#fff;border:1px solid var(--paper-edge);color:var(--ink)}
  /* Trustpilot rating fits the narrow card: smaller stars, centred, tidy wrap */
  .sg-hcard .tpr{justify-content:center;gap:6px 9px}
  .sg-hcard .tpr .tpr-box{width:18px;height:18px}
  .sg-hcard .tpr .tpr-box svg{width:12px;height:12px}
  .sg-hcard .tpr .tpr-word{font-size:13px}
  .sg-hcard .tpr .tpr-meta,.sg-hcard .tpr .tpr-logo{font-size:12px}
  @media (max-width:760px){.sg-hero-grid{grid-template-columns:1fr;gap:26px}}

  /* Trust band — dark mesh band (matches home / services .tbar-f) */
  .tbar-f{padding:0;background:
      radial-gradient(520px 200px at 12% 0%, rgba(21,94,122,.45), transparent 60%),
      radial-gradient(520px 200px at 92% 100%, rgba(46,154,140,.42), transparent 60%),
      var(--navy);color:#fff}
  .tbar-f .row{display:flex;justify-content:center;gap:30px;flex-wrap:wrap;padding:16px 0}
  .tbar-f .ti{display:flex;align-items:center;gap:9px;font:600 14px var(--display);color:#fff;white-space:nowrap}
  .tbar-f .ti svg{width:20px;height:20px;color:var(--soft);flex:none}
  .tbar-f .ti b{color:var(--soft);font-weight:800}
  @media (max-width:560px){.tbar-f .row{gap:14px 22px}}

  /* "What it covers" — explainer card + country example chips */
  #sg-covers .sec-head{text-align:center;max-width:60ch;margin:0 auto}
  #sg-covers .sec-head .lede{margin:12px auto 0;max-width:58ch}
  .sg-chips{display:flex;flex-wrap:wrap;gap:9px 11px;justify-content:center;margin:24px auto 0;max-width:60ch;padding:0;list-style:none}
  .sg-chips li{display:inline-flex;align-items:center;gap:8px;background:#fff;border:1px solid var(--paper-edge);
    border-radius:10px;padding:8px 14px;font:600 14px var(--display);color:var(--ink);
    box-shadow:0 6px 16px -12px rgba(40,50,70,.5)}
  .sg-chips .dot{width:7px;height:7px;border-radius:50%;background:var(--cta);flex:none}
  .sg-chips .more{color:var(--muted);box-shadow:none;background:transparent;border-style:dashed}

  /* BROWSE — searchable Schengen country grid + region tabs (mirrors home #destinations) */
  #sg-browse .sec-head{text-align:center;max-width:60ch;margin:0 auto}
  .sg-tabs{display:flex;flex-wrap:wrap;gap:10px;justify-content:center;margin:26px 0 0}
  .sg-tab{display:inline-flex;align-items:center;gap:8px;flex:0 0 auto;white-space:nowrap;background:#fff;border:1px solid var(--paper-edge);color:var(--ink);
    border-radius:999px;padding:9px 16px;font:700 14px var(--display);cursor:pointer;transition:border-color .2s ease,color .2s ease,background .2s ease}
  .sg-tab .c{font-size:12px;font-weight:800;color:var(--muted)}
  .sg-tab:hover{border-color:var(--soft);color:var(--cta)}
  .sg-tab.active{background:var(--cta);border-color:var(--cta);color:#fff;box-shadow:0 12px 26px -14px rgba(21,94,122,.6)}
  .sg-tab.active .c{color:rgba(255,255,255,.82)}
  .sg-search{display:flex;gap:10px;max-width:480px;margin:18px auto 0}
  .sg-search input{flex:1;padding:13px 16px;border:1px solid var(--paper-edge);border-radius:12px;font:inherit;font-size:15px;background:#fff;box-shadow:0 16px 40px -30px rgba(40,50,70,.5)}
  .sg-empty{display:none;text-align:center;color:var(--muted);margin-top:24px}
  @media (max-width:520px){.sg-search{flex-direction:column}}

  /* WHAT A SCHENGEN VISA COVERS — navy passport card (Variant C) */
  #sg-covers .sg-card{position:relative;overflow:hidden;border-radius:22px;padding:40px 44px;color:#fff;
    background:radial-gradient(520px 240px at 8% -10%,rgba(21,94,122,.5),transparent 60%),
      radial-gradient(520px 240px at 96% 110%,rgba(46,154,140,.42),transparent 60%),var(--navy);
    box-shadow:0 40px 80px -50px rgba(20,30,50,.7)}
  #sg-covers .sg-card .eyebrow{color:var(--soft)}
  #sg-covers .sg-card h2{color:#fff;margin:0 0 14px}
  #sg-covers .sg-card .lede{color:rgba(255,255,255,.82);max-width:62ch;font-size:17px;line-height:1.6}
  #sg-covers .sg-card-stamp{position:absolute;top:28px;right:30px;width:62px;height:62px;border-radius:14px;
    border:2px solid rgba(169,204,218,.5);display:grid;place-items:center;color:var(--soft);transform:rotate(-8deg);
    font:800 10px var(--display);text-align:center;letter-spacing:.08em;opacity:.85;line-height:1.25}
  #sg-covers .sg-card-facts{display:flex;flex-wrap:wrap;gap:6px 34px;margin-top:24px;padding-top:20px;border-top:1px solid rgba(255,255,255,.16)}
  #sg-covers .sg-card-facts .f{padding:6px 0}
  #sg-covers .sg-card-facts .n{font:800 22px var(--display);color:#fff}
  #sg-covers .sg-card-facts .l{font-size:12.5px;color:rgba(255,255,255,.7);margin-top:2px}
  @media (max-width:560px){#sg-covers .sg-card{padding:30px 24px}#sg-covers .sg-card-stamp{display:none}}

  /* WHAT WE DO — warm stamp-card grid (matches home "What we do") */
  #sg-do{background:linear-gradient(180deg,#FBF6F1,var(--paper))}
  #sg-do .sec-head{text-align:center;max-width:60ch;margin-left:auto;margin-right:auto}
  #sg-do .sec-head .lede{margin:12px auto 0;max-width:52ch}
  #sg-do .ticks{margin-top:30px}
  #sg-do .tick{background:#fff;border:1px solid var(--paper-edge);border-radius:16px;padding:22px;gap:14px;
    box-shadow:0 10px 30px -22px rgba(40,50,70,.5);transition:transform .25s ease,box-shadow .25s ease}
  #sg-do .tick:hover{transform:translateY(-3px);box-shadow:var(--lift-2)}
  #sg-do .tick .stamp{flex:0 0 44px;width:44px;height:44px;padding:9px;border-radius:11px;
    background:rgba(46,154,140,.12);color:var(--stamp-text);box-sizing:border-box}

  /* FAQ — tinted panel accordion (matches services / money pages) */
  .faq-e{background:var(--paper)}
  .faq-e .sec-head{text-align:center;max-width:60ch;margin-left:auto;margin-right:auto}
  .faq-panel{background:var(--white);border:1px solid var(--paper-edge);border-radius:18px;padding:6px 30px;
    max-width:80ch;margin:24px auto 0;box-shadow:0 16px 40px -30px rgba(40,50,70,.5)}
  .faqd{max-width:none}
  .faqd details{border-bottom:1px solid var(--paper-edge);padding:18px 0}
  .faqd details:last-child{border-bottom:0}
  .faqd summary{font-family:var(--display);font-size:19px;color:var(--navy);font-weight:600;cursor:pointer;
    list-style:none;display:flex;justify-content:space-between;align-items:center;gap:16px}
  .faqd summary::-webkit-details-marker{display:none}
  .faqd summary::after{content:"+";font-size:22px;color:var(--cta);flex:0 0 auto;font-weight:700;transition:transform .15s ease}
  .faqd details[open] summary::after{content:"\2013"}
  .faqd p{margin:12px 0 0;color:#3a4b55;font-size:16px;line-height:1.65}

  /* Final CTA WhatsApp button glyph */
  .cta-band .row .btn{display:inline-flex;align-items:center;gap:8px}
  .cta-band .row .wa-g{width:18px;height:18px;fill:currentColor;flex:none}

  /* ── Appointment availability board (region-grouped tiles) ──────────────── */
  #sg-appts .sec-head{text-align:center;max-width:62ch;margin:0 auto}
  #sg-appts .sec-head .lede{margin:12px auto 0;max-width:58ch}
  #sg-appts .ap-note{display:inline-flex;align-items:center;gap:9px;margin:16px auto 0;font-size:12.5px;color:var(--muted);
    background:#fff;border:1px solid var(--paper-edge);border-radius:999px;padding:8px 15px}
  #sg-appts .ap-note .d{width:8px;height:8px;border-radius:50%;background:var(--stamp);flex:none;box-shadow:0 0 0 4px rgba(92,154,123,.16)}
  #sg-appts .ap-region{margin-top:30px}
  #sg-appts .ap-region h3{font:800 12px var(--display);letter-spacing:.1em;text-transform:uppercase;color:#8997a0;margin:0 0 14px;display:flex;align-items:center;gap:12px}
  #sg-appts .ap-region h3::after{content:"";flex:1;height:1px;background:var(--paper-edge)}
  #sg-appts .ap-tiles{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}
  @media (max-width:820px){#sg-appts .ap-tiles{grid-template-columns:1fr 1fr}}
  @media (max-width:480px){#sg-appts .ap-tiles{grid-template-columns:1fr}}
  #sg-appts .ap-tile{display:block;text-decoration:none;background:#fff;border:1px solid var(--paper-edge);border-radius:15px;
    padding:16px 17px;box-shadow:0 12px 30px -26px rgba(40,50,70,.5);transition:transform .18s,box-shadow .18s}
  #sg-appts .ap-tile:hover{transform:translateY(-3px);box-shadow:var(--lift-2)}
  #sg-appts .ap-tp{display:flex;justify-content:space-between;align-items:center;margin-bottom:13px;gap:10px}
  #sg-appts .ap-tile h4{font:800 16px var(--display);color:var(--ink);margin:0}
  #sg-appts .ap-st{display:inline-flex;align-items:center;gap:6px;font:800 10px var(--display);letter-spacing:.05em;text-transform:uppercase;padding:4px 9px;border-radius:999px;white-space:nowrap}
  #sg-appts .ap-st .dot{width:6px;height:6px;border-radius:50%}
  #sg-appts .ap-st.ok{background:rgba(46,154,140,.14);color:#1F6E63}#sg-appts .ap-st.ok .dot{background:#2E9A8C}
  #sg-appts .ap-st.lim{background:rgba(200,146,58,.16);color:#946100}#sg-appts .ap-st.lim .dot{background:#c8923a}
  #sg-appts .ap-st.ask{background:rgba(21,94,122,.12);color:var(--cta)}#sg-appts .ap-st.ask .dot{background:var(--cta)}
  #sg-appts .ap-bar{height:6px;border-radius:999px;background:#e7edf0;overflow:hidden}
  #sg-appts .ap-bar>i{display:block;height:100%;border-radius:999px}
  #sg-appts .ap-bar>i.ok{background:linear-gradient(90deg,#2E9A8C,#5C9A7B)}
  #sg-appts .ap-bar>i.lim{background:linear-gradient(90deg,#c8923a,#e0b15f)}
  #sg-appts .ap-bar>i.ask{background:repeating-linear-gradient(90deg,#cdd7dc 0 6px,transparent 6px 12px)}
  #sg-appts .ap-dt{font:700 14px var(--display);color:var(--ink);margin-top:11px}
  #sg-appts .ap-lb{font-size:11px;color:var(--muted);margin-top:3px}
  #sg-appts .ap-legend{display:flex;flex-wrap:wrap;justify-content:center;gap:18px;margin-top:28px;font-size:12px;color:var(--muted)}
  #sg-appts .ap-legend span{display:inline-flex;align-items:center;gap:7px}
  #sg-appts .ap-legend i{width:9px;height:9px;border-radius:50%;display:inline-block}
</style>
@endpush

@section('content')

{{-- 1) HERO — soft-sky centred, WhatsApp + free-checker CTAs --}}
<section class="sg-hero"><div class="wrap"><div class="sg-hero-grid">
  <div>
    <p class="eyebrow">Schengen visa · Europe</p>
    <h1>Your Schengen visa, sorted from the UK</h1>
    <p class="lede">One visa for most of Europe, prepared and checked by a real UK team. We review every document, complete the forms and book your appointment, so a small mistake does not cost you the trip.</p>
  </div>
  <div class="sg-hcard">
    <h3>Start here</h3>
    <p>Message our UK team. No account needed.</p>
    <div class="acts">
      <a class="btn btn--wa" href="{{ $waLink }}" target="_blank" rel="noopener">{!! $waGlyph !!} WhatsApp us</a>
      <a class="btn btn--ghost" href="{{ url('/tools') }}">Run the free checker</a>
    </div>
    @include('partials.trustpilot-cta', ['align' => 'center', 'margin' => '14px 0 0'])
  </div>
</div></div></section>

{{-- 2) TRUST BAND — dark mesh band (.tbar-f), Schengen-relevant proof points --}}
<section class="tbar-f"><div class="wrap"><div class="row">
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 5 6v6c0 4.5 3 7.5 7 8.5 4-1 7-4 7-8.5V6z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="m9 12 2 2 4-4.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg><span><b>Schengen visa</b> experts</span></span>
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 7v10M9.5 9.2c0-1 1.1-1.7 2.5-1.7s2.5.7 2.5 1.7-1.1 1.6-2.5 1.6-2.5.7-2.5 1.7 1.1 1.7 2.5 1.7 2.5-.7 2.5-1.7" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg><span><b>No hidden</b> fees</span></span>
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 7v5l3 2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg><span><b>7-day</b> support</span></span>
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 21h18M5 21V9l7-5 7 5v12M9 21v-6h6v6" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg><span>Registered in <b>UK &amp; Germany</b></span></span>
</div></div></section>

{{-- APPOINTMENT AVAILABILITY — region-grouped tiles, real SlotService data (honest "ask" when none) --}}
<section id="sg-appts"><div class="wrap">
  <div class="sec-head reveal">
    <p class="eyebrow">Appointments</p>
    <h2>Where slots are opening now</h2>
    <p class="lede">For most Schengen visas the biometric appointment is the real bottleneck, not the visa. Here is a recent snapshot by country, soonest first. Start early so you do not miss your window.</p>
    <div><span class="ap-note"><span class="d"></span>Indicative only. We confirm live availability with the centre before you pay.</span></div>
  </div>

  @foreach ($byRegion as $region => $group)
    @if($region)
    <div class="ap-region">
      <h3>{{ $region }}</h3>
      <div class="ap-tiles">
        @foreach ($group as $d)
          @php
            $a = $availability[$d->id] ?? ['status' => 'ask', 'next_slot_at' => null, 'count_30d' => 0];
            $status = $a['status'];
            $label = ['ok' => 'Available', 'lim' => 'Limited', 'ask' => 'Ask us'][$status];
            $width = $status === 'ok' ? min(100, max(60, $a['count_30d'] * 8)) : ($status === 'lim' ? min(55, max(18, $a['count_30d'] * 7)) : 100);
          @endphp
          <a class="ap-tile" href="{{ url('/visa/'.$d->slug) }}">
            <div class="ap-tp">
              <h4>{{ $d->name }}</h4>
              <span class="ap-st {{ $status }}"><span class="dot"></span>{{ $label }}</span>
            </div>
            <div class="ap-bar"><i class="{{ $status }}" style="width:{{ $width }}%"></i></div>
            @if($a['next_slot_at'])
              <div class="ap-dt">{{ $a['next_slot_at']->format('j M Y') }}</div>
              <div class="ap-lb">Next available</div>
            @else
              <div class="ap-dt">On request</div>
              <div class="ap-lb">We check live for you</div>
            @endif
          </a>
        @endforeach
      </div>
    </div>
    @endif
  @endforeach

  <div class="ap-legend">
    <span><i style="background:#2E9A8C"></i>Available</span>
    <span><i style="background:#c8923a"></i>Limited</span>
    <span><i style="background:#155E7A"></i>Ask us, we check live</span>
  </div>
</div></section>

{{-- WHAT A SCHENGEN VISA COVERS — navy passport card, before the destinations grid --}}
<section id="sg-covers"><div class="wrap">
  <div class="sg-card reveal">
    <span class="sg-card-stamp" aria-hidden="true">SCHENGEN<br>AREA</span>
    <p class="eyebrow">The basics</p>
    <h2>What a Schengen visa covers</h2>
    <p class="lede">One short-stay visa lets you travel across the whole Schengen Area, 29 European countries, for up to 90 days in any 180-day period. It is for tourism, visiting family or friends, and most business trips. You apply to one embassy, then move freely between the countries once you are in.</p>
    <div class="sg-card-facts">
      <div class="f"><div class="n">29 countries</div><div class="l">whole Schengen Area</div></div>
      <div class="f"><div class="n">90 / 180 days</div><div class="l">short-stay limit</div></div>
      <div class="f"><div class="n">1 application</div><div class="l">via your main destination</div></div>
    </div>
  </div>
</div></section>

{{-- BROWSE — searchable Schengen country grid (boarding-pass cards) --}}
<section id="sg-browse"><div class="wrap">
  <div class="sec-head reveal">
    <p class="eyebrow">Browse</p>
    <h2>Pick your main destination</h2>
    <p class="lede" style="margin:12px auto 0;max-width:56ch">Search the 29 Schengen countries. You apply through your main-destination country and one visa covers the whole Area.</p>
  </div>
  @php
    $regionOrder = ['Western Europe', 'Southern Europe', 'Northern Europe', 'Central & Eastern Europe'];
    $regionCounts = [];
    foreach ($destinations as $d) { $r = $d->region ?: 'Other'; $regionCounts[$r] = ($regionCounts[$r] ?? 0) + 1; }
    $regionsPresent = collect($regionOrder)->filter(fn ($r) => ! empty($regionCounts[$r]))->values();
  @endphp
  @if ($destinations->isNotEmpty())
  <div class="sg-tabs" id="sgTabs">
    <button type="button" class="sg-tab active" data-region="all">All <span class="c">{{ $destinations->count() }}</span></button>
    @foreach ($regionsPresent as $rk)
      <button type="button" class="sg-tab" data-region="{{ $rk }}">{{ str_replace(' Europe', '', $rk) }} <span class="c">{{ $regionCounts[$rk] }}</span></button>
    @endforeach
  </div>
  @endif
  <form class="sg-search" role="search" onsubmit="return false">
    <input type="search" id="destSearch" placeholder="Search a Schengen country…" aria-label="Search Schengen countries" autocomplete="off">
    <button class="btn" type="button" onclick="document.getElementById('destSearch').focus()">Search</button>
  </form>
  @if ($destinations->isEmpty())
    <p style="text-align:center;color:var(--muted);margin-top:24px">Schengen destinations are being added shortly.</p>
  @else
    <div id="destGrid" class="dests" style="margin-top:26px">
      @foreach ($destinations as $destination)
        @include('partials.destination-card', ['destination' => $destination])
      @endforeach
    </div>
    <p class="sg-empty" id="destEmpty">No Schengen country matches that search. Try another, or <a href="{{ url('/contact') }}">ask our team</a>.</p>
  @endif
</div></section>

{{-- 4) WHAT WE DO — six-service stamp grid (.ticks / .tick / #ukv-stamp) --}}
<section id="sg-do"><div class="wrap">
  <div class="sec-head reveal">
    <p class="eyebrow">What we do</p>
    <h2>Everything your Schengen application needs</h2>
    <p class="lede">End-to-end help, from checking you can apply to the embassy door.</p>
  </div>
  <div class="ticks">
    <div class="tick reveal"><svg class="stamp" width="38" height="38" viewBox="0 0 48 48" role="img" aria-label="Checked"><use href="#ukv-stamp"></use></svg><div><h3>Eligibility check</h3><p>We confirm you can apply, and on the right visa, before you spend anything.</p></div></div>
    <div class="tick reveal"><svg class="stamp" width="38" height="38" viewBox="0 0 48 48" role="img" aria-label="Checked"><use href="#ukv-stamp"></use></svg><div><h3>Document review</h3><p>Every document checked by hand against the current embassy rules.</p></div></div>
    <div class="tick reveal"><svg class="stamp" width="38" height="38" viewBox="0 0 48 48" role="img" aria-label="Checked"><use href="#ukv-stamp"></use></svg><div><h3>Form completion</h3><p>We fill and check the application so small errors do not creep in.</p></div></div>
    <div class="tick reveal"><svg class="stamp" width="38" height="38" viewBox="0 0 48 48" role="img" aria-label="Checked"><use href="#ukv-stamp"></use></svg><div><h3>Appointment booking</h3><p>We find a biometric slot in time for your travel date.</p></div></div>
    <div class="tick reveal"><svg class="stamp" width="38" height="38" viewBox="0 0 48 48" role="img" aria-label="Checked"><use href="#ukv-stamp"></use></svg><div><h3>Submission &amp; follow-up</h3><p>We track your application and keep you posted at every step.</p></div></div>
    <div class="tick reveal"><svg class="stamp" width="38" height="38" viewBox="0 0 48 48" role="img" aria-label="Checked"><use href="#ukv-stamp"></use></svg><div><h3>Refused before?</h3><p>We work out why and fix it before you reapply.</p></div></div>
  </div>
</div></section>

{{-- 5) HOW IT WORKS — shared .steps --}}
<section id="how"><div class="wrap">
  <div class="sec-head reveal" style="text-align:center;max-width:60ch;margin:0 auto 36px">
    <p class="eyebrow">How it works</p>
    <h2>Four simple steps to your appointment</h2>
    <p class="lede" style="margin:12px auto 0;max-width:54ch">A straightforward process with a real UK specialist guiding you at every stage.</p>
  </div>
  <div class="steps">
    <div class="step reveal"><div class="num">01</div><div class="rule"></div><h3>Tell us about your trip</h3><p>Where you are going, when, and why. No card and no account to get started.</p></div>
    <div class="step reveal"><div class="num">02</div><div class="rule"></div><h3>We prepare and check everything</h3><p>Documents, forms and the details that get people refused, all reviewed by hand.</p></div>
    <div class="step reveal"><div class="num">03</div><div class="rule"></div><h3>You attend the appointment</h3><p>We book a biometric slot in time and tell you exactly what to bring.</p></div>
    <div class="step reveal"><div class="num">04</div><div class="rule"></div><h3>We track it to a decision</h3><p>We follow your application and keep you posted until the embassy decides.</p></div>
  </div>
  <div style="text-align:center;margin-top:28px"><a class="btn" href="{{ $waLink }}" target="_blank" rel="noopener">Tell us about your trip &rarr;</a></div>
</div></section>

{{-- 6) FAQ — tinted panel accordion (.faq-e / .faqd) --}}
<section id="faq" class="faq-e"><div class="wrap">
  <div class="sec-head reveal"><p class="eyebrow">Questions</p><h2>Schengen visa questions</h2></div>
  <div class="faq-panel reveal">
    <div class="faqd">
      <details><summary>Which country do I apply to?</summary><p>You apply to the country that is your main destination, where you will spend the most time. If you are spending equal time in several, you apply to the country you enter first. We help you work this out before you book anything.</p></details>
      <details><summary>How long does a Schengen visa take?</summary><p>Embassies usually decide within about 15 calendar days, though it can take longer in busy periods or if extra checks are needed. The biggest delay is often getting an appointment, so it is best to start early. We help you book in good time for your travel date.</p></details>
      <details><summary>What documents do I need?</summary><p>Typically a valid passport, recent photos, travel insurance with at least 25,000 euro of medical cover, proof of funds, your travel and accommodation plans, and proof of your ties to the UK such as a job letter or return ticket. We give you a checklist for your exact circumstances and check each item by hand.</p></details>
      <details><summary>Can you guarantee my visa will be approved?</summary><p>No, and you should be careful of anyone who says they can. The embassy makes the decision. What we do is remove the avoidable reasons they say no, by getting your documents and application right before you submit.</p></details>
      <details><summary>What does it cost?</summary><p>Our service fee is a clear amount shown before you pay anything. It is separate from the embassy visa fee, which is set by the authorities and paid to them. We tell you both so there are no surprises.</p></details>
      <details><summary>I was refused a Schengen visa before, can you help?</summary><p>Yes. A second refusal is harder, so the reapplication has to fix the real reason for the first no. We work out what went wrong and put it right before you reapply.</p></details>
    </div>
  </div>
</div></section>

{{-- 7) FINAL CTA — .cta-band, WhatsApp + free checker --}}
<section class="cta-band"><div class="wrap reveal">
  <div class="rule"></div>
  <h2>Tell us about your trip</h2>
  <p style="max-width:48ch;color:#eef0f1">Message our UK team on WhatsApp and we'll tell you exactly what your Schengen application needs, or run the free checker first.</p>
  <div class="row"><a href="{{ $waLink }}" target="_blank" rel="noopener" class="btn">{!! $waGlyph !!} Chat on WhatsApp</a><a href="{{ url('/tools') }}" class="btn btn--glass">Run the free checker</a></div>
</div></section>

@if ($destinations->isNotEmpty())
<script>
  // Region tabs + live search, combined, over the Schengen country cards.
  (function () {
    var input = document.getElementById('destSearch');
    var grid = document.getElementById('destGrid');
    var empty = document.getElementById('destEmpty');
    var tabs = Array.prototype.slice.call(document.querySelectorAll('#sgTabs .sg-tab'));
    if (!grid) return;
    var cards = Array.prototype.slice.call(grid.querySelectorAll('.pass'));
    var region = 'all';
    function apply() {
      var q = (input && input.value.trim().toLowerCase()) || '';
      var shown = 0;
      cards.forEach(function (c) {
        var regOk = region === 'all' || (c.getAttribute('data-region') || '') === region;
        var nameOk = !q || (c.getAttribute('data-name') || '').indexOf(q) !== -1;
        var hit = regOk && nameOk;
        c.style.display = hit ? '' : 'none';
        if (hit) shown++;
      });
      if (empty) empty.style.display = shown ? 'none' : 'block';
    }
    tabs.forEach(function (t) {
      t.addEventListener('click', function () {
        tabs.forEach(function (x) { x.classList.remove('active'); });
        t.classList.add('active');
        region = t.getAttribute('data-region');
        apply();
      });
    });
    if (input) input.addEventListener('input', apply);
  })();
</script>
@endif

@endsection
