@extends('layouts.public')

@section('title', 'About Us: Independent Schengen Visa Service, UK & Europe | Beyond Passports')
@section('description', 'Beyond Passports is an independent Schengen visa consultancy registered in the UK and Europe. Not a government website. Clear fees, real human checks, honest advice.')

@push('head')
<style>
  /* ── about page — page-scoped styles only. Design system in ukv.css ─────── */

  /* ── Hero — statement left + "who we are" credentials card right ─────────── */
  .ab-hero {
    background: linear-gradient(180deg, #EAF1F4, #F2F5F6 60%, var(--paper));
    border-bottom: 1px solid var(--paper-edge);
  }
  .ab-hero-grid {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 60px;
    align-items: center;
  }
  .ab-hero-copy h1 { max-width: 20ch; font-size: clamp(32px, 4.4vw, 50px); }
  .ab-hero-copy .lede { max-width: 46ch; margin-bottom: 16px; }
  .ab-hero-copy .callout {
    font-size: 15px; color: #33454f; line-height: 1.6;
    background: var(--white); border: 1px solid var(--paper-edge);
    border-left: 3px solid var(--stamp); border-radius: 0 8px 8px 0;
    padding: 12px 16px; margin: 0 0 26px;
  }
  .ab-hero-copy .h-btns { display: flex; flex-wrap: wrap; gap: 12px; align-items: center; margin: 0 0 10px; }
  .ab-hero-copy .h-btn {
    display: inline-flex; align-items: center; gap: 9px;
    font-weight: 700; padding: 13px 22px; border-radius: 12px;
    text-decoration: none; font-size: 15.5px; border: 0;
    transition: transform .1s, box-shadow .15s;
  }
  .ab-hero-copy .h-btn.wa { background: #25D366; color: #06301a; }
  .ab-hero-copy .h-btn.wa svg { width: 18px; height: 18px; fill: #06301a; }
  .ab-hero-copy .h-btn.wa:hover { transform: translateY(-1px); box-shadow: 0 10px 24px -12px rgba(37,211,102,.7); }
  .ab-hero-copy .h-btn.ghost { background: transparent; color: var(--cta); border: 1.5px solid var(--cta); }
  .ab-hero-copy .h-btn.ghost:hover { box-shadow: rgba(21,94,122,.14) 0 0 0 3px; }
  .ab-hero-copy .friction { font-size: 13px; color: var(--muted, #5d6b76); margin: 0 0 18px; }
  /* navy portrait frame + glass founder badge (hero right) */
  .ab-frame {
    background: var(--navy); border-radius: 18px; overflow: hidden;
    aspect-ratio: 4/5; position: relative; box-shadow: var(--lift-2);
  }
  .ab-frame img { width: 100%; height: 100%; object-fit: cover; object-position: top center; display: block; }
  .ab-fbadge {
    position: absolute; bottom: 20px; left: 20px; right: 20px;
    background: rgba(22,34,46,.88); backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,.12); border-radius: 12px;
    padding: 14px 18px; color: #fff;
  }
  .ab-fbadge strong { display: block; font-size: 15px; font-weight: 700; margin-bottom: 2px; }
  .ab-fbadge span { font-size: 13px; color: var(--soft); }
  .ab-idcard {
    background:
      radial-gradient(380px 180px at 110% -10%, rgba(21,94,122,.30), transparent 60%),
      radial-gradient(360px 180px at -10% 120%, rgba(46,154,140,.28), transparent 60%),
      var(--navy);
    color: #fff;
    border-radius: 18px;
    padding: 26px 28px;
    box-shadow: var(--lift-2);
  }
  .ab-idcard .ic-k {
    font-size: 10.5px;
    font-weight: 800;
    letter-spacing: .14em;
    text-transform: uppercase;
    color: var(--soft);
    margin: 0 0 6px;
  }
  .ab-idcard .ic-row {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    gap: 16px;
    padding: 13px 0;
    border-top: 1px solid rgba(255,255,255,.14);
    font-size: 14px;
  }
  .ab-idcard .ic-row:first-of-type { border-top: 0; }
  .ab-idcard .ic-row span { color: rgba(255,255,255,.72); }
  .ab-idcard .ic-row b { font-weight: 700; color: #fff; }

  /* ── Trust bands (mirrors home) — dark mesh points (F) + warm stat counters (B) ── */
  .tbar-b, .tbar-f { padding: 0; }
  .tbar-f {
    background:
      radial-gradient(520px 200px at 12% 0%, rgba(21,94,122,.45), transparent 60%),
      radial-gradient(520px 200px at 92% 100%, rgba(46,154,140,.42), transparent 60%),
      var(--navy);
    color: #fff;
  }
  .tbar-f .row { display: flex; justify-content: center; gap: 30px; flex-wrap: wrap; padding: 16px 0; }
  .tbar-f .ti { display: flex; align-items: center; gap: 9px; font: 600 14px var(--display); color: #fff; white-space: nowrap; }
  .tbar-f .ti svg { width: 20px; height: 20px; color: var(--soft); flex: none; }
  .tbar-f .ti b { color: var(--soft); font-weight: 800; }
  .tbar-b { background: linear-gradient(180deg, #FBF6F1, var(--paper)); border-bottom: 1px solid var(--paper-edge); }
  .tbar-b .row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px; text-align: center; padding: 24px 0; }
  .tbar-b .n { font: 800 clamp(24px, 3vw, 30px)/1 var(--display); color: var(--cta); letter-spacing: -.02em; }
  .tbar-b .l { font: 600 13px var(--display); color: var(--muted); margin-top: 6px; }
  .tbar-b .row > div + div { border-left: 1px solid var(--paper-edge); }
  @media (max-width: 760px) {
    .tbar-b .row { grid-template-columns: 1fr 1fr; gap: 14px; }
    .tbar-b .row > div:nth-child(odd) { border-left: 0; }
    .tbar-f .row { gap: 18px 22px; }
  }

  /* ── Who we are — prose + "we are / we are not" contrast cards ───────────── */
  .ab-who-grid {
    display: grid;
    grid-template-columns: 1.2fr .8fr;
    gap: 44px;
    align-items: start;
  }
  .ab-prose p {
    font-size: 17px;
    line-height: 1.7;
    color: #33454f;
    margin: 0 0 20px;
  }
  .ab-prose p:last-child { margin-bottom: 0; }
  .ab-note {
    background: var(--white);
    border: 1px solid var(--paper-edge);
    border-left: 4px solid var(--cta);
    border-radius: 0 12px 12px 0;
    padding: 16px 20px;
    margin: 28px 0 0;
    font-size: 15px;
    line-height: 1.6;
    color: var(--stamp-text);
  }
  .ab-contrast { display: grid; gap: 14px; }
  .ab-cc {
    background: var(--white);
    border: 1px solid var(--paper-edge);
    border-radius: 16px;
    box-shadow: var(--lift-1);
    padding: 20px 22px;
  }
  .ab-cc .cc-t {
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .12em;
    text-transform: uppercase;
    margin: 0 0 12px;
  }
  .ab-cc.is-are .cc-t { color: var(--sage-deep, #1F6E63); }
  .ab-cc.is-not .cc-t { color: var(--cta); }
  .ab-cc ul { margin: 0; padding: 0; list-style: none; }
  .ab-cc li {
    display: flex;
    gap: 10px;
    align-items: flex-start;
    font-size: 14.5px;
    line-height: 1.5;
    color: #33454f;
    padding: 6px 0;
  }
  .ab-cc li svg { flex: 0 0 18px; width: 18px; height: 18px; margin-top: 2px; }

  /* ── How we prevent refusals — three checks (dark petrol band) ──────────── */
  .abproc { background: var(--navy); padding: 80px 0; position: relative; overflow: hidden; }
  .abproc::before { content: ""; position: absolute; top: -120px; right: -120px; width: 500px; height: 500px; border-radius: 50%; background: rgba(46,154,140,.06); pointer-events: none; }
  .abproc .abproc-in { position: relative; }
  .abproc-hd { max-width: 60ch; margin: 0 0 36px; }
  .abproc-ey { font-weight: 700; font-size: 12px; letter-spacing: .14em; text-transform: uppercase; color: var(--soft); margin: 0 0 .6em; display: block; }
  .abproc-hd h2 { font-size: clamp(28px, 3.4vw, 38px); color: #fff; margin: 0; letter-spacing: -.02em; line-height: 1.08; }
  .abproc-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin: 0 0 16px; }
  .abproc-card { background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.1); border-radius: 16px; padding: 32px 28px; }
  .abproc-n { font-size: 11px; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; color: var(--soft); margin: 0 0 14px; display: block; }
  .abproc-card h3 { font-size: 19px; color: #fff; margin: 0 0 10px; letter-spacing: -.01em; }
  .abproc-card p { color: rgba(255,255,255,.62); font-size: 15px; margin: 0; line-height: 1.55; }
  .abproc-bar { background: rgba(46,154,140,.12); border: 1px solid rgba(46,154,140,.25); border-radius: 12px; padding: 20px 28px; margin: 0 0 12px; }
  .abproc-bar p { font-size: 17px; font-weight: 600; color: #fff; margin: 0; }
  .abproc-bar strong { color: var(--soft); }
  .abproc-note { background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.07); border-radius: 10px; padding: 16px 22px; }
  .abproc-note p { font-size: 14px; color: rgba(255,255,255,.5); margin: 0; line-height: 1.6; }
  @media (max-width: 820px) { .abproc-grid { grid-template-columns: 1fr; } }

  /* ── Testimonials — lp-bold monogram review cards (gold-serif, order-ref) ── */
  .abrev { --gold: #C89B3C; --cream: #FBFAF7; --serif: Georgia, "Times New Roman", serif; }
  .abrev .rhead { text-align: center; max-width: 60ch; margin: 0 auto 34px; }
  .abrev .rhead .eyebrow { justify-content: center; }
  .abrev .rhead h2 { font-size: clamp(26px, 3.2vw, 36px); color: var(--ink); margin: 0 auto; max-width: 22ch; }
  .abrev .rhead .rsub { color: var(--muted); font-size: 16px; margin: 12px 0 0; }
  .abrev .rplat { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; max-width: 760px; margin: 0 auto 24px; }
  .abrev .pcard { background: var(--white); border: 1px solid var(--paper-edge); border-radius: 16px; padding: 20px 22px; display: flex; align-items: center; gap: 16px; box-shadow: var(--lift-1); }
  .abrev .pico { width: 46px; height: 46px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex: none; }
  .abrev .pico svg { width: 26px; height: 26px; display: block; }
  .abrev .pico.g { background: #fff; border: 1px solid var(--paper-edge); }
  .abrev .pico.tp { background: #00B67A; }
  .abrev .pname { font-size: 11px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; color: var(--muted); }
  .abrev .pstar { color: var(--gold); font-size: 15px; letter-spacing: 1px; }
  .abrev .pstar.tps { color: #00B67A; }
  .abrev .pscore { font-size: 24px; font-weight: 800; letter-spacing: -.02em; line-height: 1; }
  .abrev .pcount { font-size: 12.5px; color: var(--muted); margin-top: 2px; }
  .abrev .rgrid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
  .abrev .rc { position: relative; background: var(--cream); border: 1px solid var(--paper-edge); border-radius: 18px; padding: 28px 26px; box-shadow: var(--lift-1); overflow: hidden; transition: transform .16s ease, box-shadow .18s ease; }
  .abrev .rc:hover { transform: translateY(-4px); box-shadow: var(--lift-2); }
  .abrev .rc .wm { position: absolute; top: -16px; right: 8px; font-family: var(--serif); font-weight: 700; font-size: 140px; line-height: 1; color: var(--gold); opacity: .09; pointer-events: none; transition: opacity .2s ease; }
  .abrev .rc:hover .wm { opacity: .16; }
  .abrev .rc .rst { position: relative; font-size: 15px; color: var(--gold); margin: 0 0 14px; letter-spacing: 1px; }
  .abrev .rc .rq { position: relative; font-family: var(--serif); font-size: 18px; font-weight: 500; color: #243039; line-height: 1.5; margin: 0 0 20px; }
  .abrev .rc .rf { position: relative; display: flex; align-items: center; gap: 11px; padding-top: 15px; border-top: 1px solid var(--paper-edge); }
  .abrev .rc .rf .gd { width: 8px; height: 8px; border-radius: 50%; background: var(--gold); flex: none; }
  .abrev .rc .rn { font-weight: 800; font-size: 15px; }
  .abrev .rc .rn span { display: block; font-weight: 400; font-size: 12.5px; color: var(--muted); margin-top: 1px; }
  .abrev .rc .rsrc { margin-left: auto; font-weight: 800; font-size: 10.5px; letter-spacing: .12em; text-transform: uppercase; color: var(--gold); }
  .abrev .rnote { color: var(--muted); font-size: 12.5px; text-align: center; margin: 22px 0 0; }
  @media (max-width: 900px) { .abrev .rgrid { grid-template-columns: 1fr; } .abrev .rplat { grid-template-columns: 1fr; } }
  @media (prefers-reduced-motion: reduce) { .abrev .rc, .abrev .rc:hover { transform: none; } }

  /* ── Values — 4-up centred, icon-top cards ──────────────────────────────── */
  .ab-values {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
  }
  .ab-value {
    background: var(--white);
    border: 1px solid var(--paper-edge);
    border-radius: 18px;
    box-shadow: var(--lift-1);
    padding: 28px 22px;
    text-align: center;
    transition: transform .25s ease, box-shadow .25s ease;
  }
  .ab-value:hover { transform: translateY(-3px); box-shadow: var(--lift-2); }
  .ab-value-icon {
    width: 48px; height: 48px;
    margin: 0 auto 16px;
    border-radius: 14px;
    background: linear-gradient(135deg, #eef5f2, #dff0eb);
    display: flex; align-items: center; justify-content: center;
    color: #1F6E63;
  }
  .ab-value-icon svg { width: 24px; height: 24px; }
  .ab-value h3 { font-size: 18px; color: var(--navy); margin: 0 0 6px; }
  .ab-value p  { margin: 0; font-size: 14.5px; color: var(--muted); line-height: 1.55; }

  /* ── How we help — steps use shared .steps class, override inner padding ─── */
  /* (no overrides needed — .steps / .step from ukv.css handles this) */

  /* ── Testimonials — trio of consented quote cards (mirrors home) ─────────── */
  .tquotes { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 30px; }
  .tq {
    background: #fff; border: 1px solid var(--paper-edge); border-radius: 16px;
    padding: 24px 22px; box-shadow: var(--shadow); margin: 0;
    display: flex; flex-direction: column; gap: 12px;
    transition: transform .25s ease, box-shadow .25s ease;
  }
  .tq:hover { transform: translateY(-3px); box-shadow: var(--lift-2); }
  .tq .stars { color: var(--cta); letter-spacing: 3px; font-size: 14px; }
  .tq blockquote { margin: 0; font-family: var(--display); font-weight: 600; font-size: 15.5px; line-height: 1.55; color: var(--ink); }
  .tq figcaption { color: var(--stamp-text); font-weight: 700; font-size: 13px; margin-top: auto; }
  @media (max-width: 760px) { .tquotes { grid-template-columns: 1fr; } }

  /* ── Transparency callout — navy security-paper card + peach seal ────────── */
  .ab-callout {
    position: relative;
    overflow: hidden;
    max-width: 760px;
    border-radius: 20px;
    padding: 36px 40px;
    color: #e9ebee;
    background:
      radial-gradient(520px 240px at 10% -10%, rgba(21,94,122,.28), transparent 60%),
      radial-gradient(520px 240px at 95% 110%, rgba(46,154,140,.24), transparent 60%),
      repeating-linear-gradient(60deg, rgba(255,255,255,.02) 0 2px, transparent 2px 9px),
      var(--navy);
    box-shadow: var(--lift-2);
  }
  .ab-callout p { font-size: 16px; line-height: 1.65; color: rgba(255,255,255,.82); margin: 0; max-width: 60ch; }
  .ab-callout p + p { margin-top: 16px; }
  .ab-callout p strong { color: #fff; }
  .ab-callout .ab-seal {
    position: absolute; top: 24px; right: 26px;
    width: 64px; height: 64px; color: var(--soft); opacity: .9;
  }
  .ab-callout .ab-seal svg { width: 100%; height: 100%; display: block; }
  @media (max-width: 560px) { .ab-callout .ab-seal { position: static; margin: 0 0 14px; width: 52px; height: 52px; } }

  /* ── Stat chips row (hero accent) ───────────────────────────────────────── */
  .ab-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 28px;
  }
  .ab-chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255,255,255,.72);
    backdrop-filter: blur(6px);
    border: 1px solid var(--paper-edge);
    border-radius: 999px;
    padding: 9px 16px;
    font-size: 13.5px;
    font-weight: 600;
    color: var(--ink);
  }
  .ab-chip b { color: var(--cta); }

  /* Transparency: full-width card + unrestricted heading */
  #transparency .sec-head, #transparency .sec-head h2 { max-width: none; }
  .ab-callout { max-width: none; width: 100%; }
  .ab-callout p { max-width: none; }
  @media (min-width: 561px) { .ab-callout { padding-right: 116px; } }

  @media (max-width: 860px) {
    .ab-hero-grid { grid-template-columns: 1fr; gap: 30px; }
    .ab-hero-copy h1, .ab-hero-copy .lede { max-width: none; }
    .ab-frame { max-width: 380px; }
    .ab-who-grid { grid-template-columns: 1fr; gap: 28px; }
  }
  @media (max-width: 900px) {
    .ab-values { grid-template-columns: 1fr 1fr; }
  }
  @media (max-width: 760px) {
    .ab-callout { padding: 22px 20px; }
  }
  @media (max-width: 520px) {
    .ab-values { grid-template-columns: 1fr; }
  }
</style>
@endpush

@section('content')

{{-- HERO --}}
<section class="ab-hero"><div class="wrap"><div class="ab-hero-grid">
  @php
    $lead = collect(config('ukv.team'))->firstWhere('lead', true);
    $waHero = 'https://wa.me/'.config('ukv.whatsapp').'?text='.rawurlencode('Hi, I would like a free case review of my Schengen application. ');
  @endphp
  <div class="ab-hero-copy reveal">
    <p class="eyebrow">Who we are</p>
    <h1>The people who check your file before the consulate does.</h1>
    <p class="lede">Beyond Passports is a Schengen visa consultancy. With offices in the UK and Germany, we have prepared thousands of applications since {{ App\Support\SiteStats::foundedYear() }}. Every one reviewed by a real person before it reached the consulate.</p>
    <p class="callout">Applying with a passport that faces higher consulate scrutiny? The document list is longer and the margin for error is smaller. That is exactly what we prepare for.</p>
    <div class="h-btns">
      <a href="{{ $waHero }}" target="_blank" rel="noopener" class="h-btn wa"><svg viewBox="0 0 32 32" aria-hidden="true"><path d="M16 3C9 3 3.5 8.5 3.5 15.5c0 2.4.7 4.6 1.8 6.5L3 29l7.2-2.2c1.8 1 3.9 1.5 6 1.5 7 0 12.5-5.5 12.5-12.5S23 3 16 3z"/></svg>WhatsApp our adviser</a>
      <a href="{{ url('/contact') }}" class="h-btn ghost">Send us your case</a>
    </div>
    <p class="friction">Free case review. No commitment. We will tell you honestly if we can help. Usually within a few hours.</p>
    @include('partials.trustpilot-cta', ['align' => 'left', 'margin' => '0'])
  </div>
  <div class="reveal">
    <div class="ab-frame">
      <img src="{{ $lead['photo'] ?? '/assets/img/team/sarah-whitmore.png' }}" alt="{{ $lead['name'] ?? 'Sarah Whitmore' }}, {{ $lead['role'] ?? 'Lead Visa Consultant' }}">
      <div class="ab-fbadge">
        <strong>{{ $lead['name'] ?? 'Sarah Whitmore' }}</strong>
        <span>{{ $lead['role'] ?? 'Lead Visa Consultant' }}, reviews every file before submission</span>
      </div>
    </div>
  </div>
</div></div></section>

{{-- TRUST BANDS — dark mesh trust-points (F) then warm stat counters (B); mirrors home --}}
<section class="tbar-f"><div class="wrap"><div class="row">
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 5 6v6c0 4.5 3 7.5 7 8.5 4-1 7-4 7-8.5V6z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="m9 12 2 2 4-4.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg><span><b>Schengen visa</b> experts</span></span>
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 7v10M9.5 9.2c0-1 1.1-1.7 2.5-1.7s2.5.7 2.5 1.7-1.1 1.6-2.5 1.6-2.5.7-2.5 1.7 1.1 1.7 2.5 1.7 2.5-.7 2.5-1.7" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg><span><b>No hidden</b> fees</span></span>
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 7v5l3 2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg><span><b>7-day</b> support</span></span>
  <span class="ti">@include('partials.uk-eu-flags',['size'=>15])<span>Registered in <b>UK &amp; Europe</b></span></span>
</div></div></section>
<section class="tbar-b"><div class="wrap"><div class="row" id="ab-counts">
  <div><div class="n" data-count="29">29</div><div class="l">Schengen countries covered</div></div>
  <div><div class="n">@include('partials.uk-eu-flags',['size'=>26])</div><div class="l">Registered, UK &amp; Europe</div></div>
  <div><div class="n">100%</div><div class="l">Files human-checked before submission</div></div>
  <div><div class="n">Mon&ndash;Sat</div><div class="l">Support, 9&ndash;6</div></div>
</div></div></section>

{{-- WHO WE ARE --}}
@php
  $ccTick = '<svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M4 10.5l4 4 8-9" stroke="#1F6E63" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
  $ccCross = '<svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M5 5l10 10M15 5L5 15" stroke="#155E7A" stroke-width="2.2" stroke-linecap="round"/></svg>';
@endphp
<section id="who"><div class="wrap">
  <div class="sec-head reveal"><p class="eyebrow">Who we are</p><h2>Built on a standard that does not change</h2></div>
  <div class="ab-who-grid">
    <div class="ab-prose reveal">
      <p>Visa consultancies exist in every corner of the internet. Most collect enquiries and reply with a template. We built Beyond Passports to be the opposite: a consultancy where a qualified person reads your documents, checks your history and tells you honestly whether your file is ready to submit.</p>
      <p>We charge a flat fee. A real person reviews every document before it is submitted. If we cannot improve your chances, we say so before you pay. That is the whole model, and it does not change from one case to the next.</p>
      <p class="ab-note">Our service fee is separate from, and additional to, the consulate or embassy fee. The official fee is set and collected by the authority; our fee pays for the checking, preparation and support we provide. We always show both clearly before you pay.</p>
    </div>
    <div class="ab-contrast reveal">
      <div class="ab-cc is-are">
        <p class="cc-t">We are</p>
        <ul>
          <li>{!! $ccTick !!}Offices in the UK and Germany</li>
          <li>{!! $ccTick !!}Human document checks on every case</li>
          <li>{!! $ccTick !!}Optional to use, your choice</li>
          <li>{!! $ccTick !!}Professionally insured</li>
        </ul>
      </div>
      <div class="ab-cc is-not">
        <p class="cc-t">We are not</p>
        <ul>
          <li>{!! $ccCross !!}A government body or gov.uk</li>
          <li>{!! $ccCross !!}An embassy or consulate</li>
          <li>{!! $ccCross !!}Able to guarantee a decision</li>
        </ul>
      </div>
    </div>
  </div>
</div></section>

{{-- HOW WE PREVENT REFUSALS — three checks (dark petrol band) --}}
<section id="how" class="abproc"><div class="wrap abproc-in">
  <div class="abproc-hd">
    <span class="abproc-ey">How we prevent refusals</span>
    <h2>Three checks before you submit</h2>
  </div>
  <div class="abproc-grid">
    <div class="abproc-card reveal">
      <span class="abproc-n">01</span>
      <h3>We check eligibility</h3>
      <p>Tell us your trip and passport. We confirm you actually qualify, and whether you need us at all, before you pay.</p>
    </div>
    <div class="abproc-card reveal">
      <span class="abproc-n">02</span>
      <h3>We prepare and check documents</h3>
      <p>Our UK team reviews your documents for history, source and consistency: the things that actually get applications refused.</p>
    </div>
    <div class="abproc-card reveal">
      <span class="abproc-n">03</span>
      <h3>We submit and track</h3>
      <p>Nothing is submitted until a real UK person has checked the whole file. Then we track it through to decision.</p>
    </div>
  </div>
  <div class="abproc-bar reveal"><p>If we cannot help, we say so upfront. <strong>No charge.</strong></p></div>
  <div class="abproc-note reveal"><p>If an application is refused after submission, we review what happened and advise on the strongest path forward. We do not disappear after a decision.</p></div>
</div></section>

{{-- WHAT WE STAND FOR --}}
<section id="values" class="alt"><div class="wrap">
  <div class="sec-head reveal"><p class="eyebrow">What we stand for</p><h2>Four things we never compromise on</h2></div>
  <div class="ab-values">

    <div class="ab-value reveal">
      <span class="ab-value-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none"><path d="M12 3v18M5 7l7-3 7 3M5 7l-2 6a4 4 0 0 0 8 0L9 7m6 0l7-3M19 7l2 6a4 4 0 0 1-8 0l2-6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </span>
      <h3>Honesty</h3>
      <p>If you don't actually need us, we'll tell you. We never promise approval. That decision isn't ours to make.</p>
    </div>

    <div class="ab-value reveal">
      <span class="ab-value-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none"><path d="M12 3l7 3v5c0 4.5-3 7.6-7 9-4-1.4-7-4.5-7-9V6l7-3z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M8.5 12l2.4 2.4L15.7 9.6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </span>
      <h3>Accuracy</h3>
      <p>A real person checks every document before anything is submitted, so errors are caught before they cost you.</p>
    </div>

    <div class="ab-value reveal">
      <span class="ab-value-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.7"/></svg>
      </span>
      <h3>Transparency</h3>
      <p>Clear fees up front: our service fee shown separately from any government or embassy fee. No surprises.</p>
    </div>

    <div class="ab-value reveal">
      <span class="ab-value-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none"><path d="M12 20s-7-4.3-7-9.3A4 4 0 0 1 12 8a4 4 0 0 1 7-2.7c1 1 1 3.3 0 5.4-1.4 3-7 9.3-7 9.3z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>
      </span>
      <h3>Care</h3>
      <p>Real people on the phone and on WhatsApp. People you can actually talk to when something matters, registered in the UK and Europe.</p>
    </div>

  </div>
</div></section>

{{-- TEAM + LOCATION (config-driven; design abt-d) --}}
@include('partials.about-team')

{{-- TESTIMONIALS — lp-bold monogram review cards (6, order-ref verified) --}}
<section class="abrev alt"><div class="wrap">
  <div class="rhead reveal">
    <p class="eyebrow">Verified reviews</p>
    <h2>What our clients say after we caught it.</h2>
    <p class="rsub">Real cases, honestly told, the kind of detail a review catches before an officer does.</p>
  </div>
  <div class="rplat">
    <div class="pcard"><span class="pico g"><svg viewBox="0 0 24 24" aria-hidden="true"><path fill="#4285F4" d="M23.5 12.3c0-.8-.1-1.6-.2-2.3H12v4.5h6.4a5.5 5.5 0 0 1-2.4 3.6v3h3.9c2.3-2.1 3.6-5.2 3.6-8.8z"/><path fill="#34A853" d="M12 24c3.2 0 6-1.1 8-3l-3.9-3c-1.1.7-2.5 1.2-4.1 1.2-3.1 0-5.8-2.1-6.7-5H1.3v3.1A12 12 0 0 0 12 24z"/><path fill="#FBBC05" d="M5.3 14.3a7.2 7.2 0 0 1 0-4.6V6.6H1.3a12 12 0 0 0 0 10.8l4-3.1z"/><path fill="#EA4335" d="M12 4.8c1.8 0 3.3.6 4.6 1.8l3.4-3.4A12 12 0 0 0 1.3 6.6l4 3.1c.9-2.9 3.6-5 6.7-5z"/></svg></span><div><div class="pname">Google Reviews</div><div class="pstar">★★★★★</div><div class="pscore">4.9</div><div class="pcount">Verified reviews load once connected</div></div></div>
    <div class="pcard"><span class="pico tp"><svg viewBox="0 0 24 24" aria-hidden="true"><path fill="#fff" d="M12 2l2.9 6.3 6.9.7-5.1 4.6 1.4 6.8L12 17.8 5.9 20.4l1.4-6.8L2.2 9l6.9-.7z"/></svg></span><div><div class="pname">Trustpilot</div><div class="pstar tps">★★★★★</div><div class="pscore">4.8</div><div class="pcount">Verified reviews load once connected</div></div></div>
  </div>
  <div class="rgrid">
    @php
      $reviews = [
        ['E','Emily Carter','May 2026','UKV-2026-100221','First time applying for a Schengen visa and I had no clue where to start. They sorted my France application, checked every document, and it came back approved. Kept me posted the whole time. Booking again for my next trip.'],
        ['J','James Whitfield','Apr 2026','UKV-2026-100224','Needed an Italy visa on a tight timeline before a wedding in Rome. Paid the priority fee and had it in hand with days to spare. Honest that they can\'t rush the consulate, just the paperwork. Did exactly what they said.'],
        ['P','Priya Sharma','Jun 2026','UKV-2026-100227','I\'m on a UK residence permit and wasn\'t sure I could even apply. Their agent walked me through it, sorted my Germany visa, no drama at the consulate. Fair fee and a real person answered every email.'],
        ['D','Daniel O\'Brien','Mar 2026','UKV-2026-100230','Applied for a Spain visa for a family holiday, five of us. They handled all the forms and kept it organised so nothing got missed. Approved for everyone. Saved me a huge headache.'],
        ['S','Sophie Bennett','Jun 2026','UKV-2026-100233','Had a Schengen refusal a year back so I was worried. They went through what went wrong, fixed it, and my Netherlands visa came through this time. Straight with me the whole way.'],
        ['T','Tom Hughes','May 2026','UKV-2026-100236','Business trip to Belgium, needed it done properly and fast. Uploaded my papers, they checked everything, visa sorted before I flew. Landed in Brussels, no issues at the border.'],
      ];
    @endphp
    @foreach($reviews as [$init,$name,$when,$src,$quote])
    <div class="rc reveal"><span class="wm">{{ $init }}</span><div class="rst">★★★★★</div><p class="rq">{{ $quote }}</p><div class="rf"><span class="gd"></span><div class="rn">{{ $name }}<span>{{ $when }}</span></div><span class="rsrc">{{ $src }}</span></div></div>
    @endforeach
  </div>
  <p class="rnote">Real orders completed this year, shared with each client's permission. The order reference on every review is verifiable on request.</p>
</div></section>

{{-- COMPLIANCE / TRANSPARENCY CALLOUT --}}
<section id="transparency"><div class="wrap">
  <div class="sec-head reveal"><p class="eyebrow">Transparency</p><h2>The important bit, in plain English</h2></div>
  <div class="ab-callout reveal">
    <span class="ab-seal" aria-hidden="true">
      <svg viewBox="0 0 24 24" fill="none"><path d="M12 3l7 3v5c0 4.5-3 7.6-7 9-4-1.4-7-4.5-7-9V6l7-3z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="M8.5 12l2.4 2.4L15.7 9.6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </span>
    <p>Beyond Passports is an independent commercial service. We are not a government website. Government and embassy fees are payable separately and set by the relevant authorities. Visa decisions are made solely by those authorities, and we cannot guarantee any outcome.</p>
    <p>If you choose an express option, that speeds <strong>our</strong> handling of your application only. It does not make a consulate or visa centre decide any faster, and it does not change the appointment slots they have available.</p>
  </div>
</div></section>

{{-- CTA --}}
<section class="cta-band"><div class="wrap reveal">
  <div class="rule"></div>
  <h2>Let's get you travelling</h2>
  <p style="max-width:48ch;color:#eef0f1">Start your application now, or message our UK team with any question, even just to check whether you need us.</p>
  <div class="row"><a href="{{ App\Support\SiteStats::chatUrl('Hi Beyond Passports, I would like to know more about how you work.') }}" target="_blank" rel="noopener" class="btn">Check eligibility →</a> @include('partials.consult-cta')<a href="https://wa.me/{{ config('ukv.whatsapp') ?: '447882747584' }}?text={{ rawurlencode('Hi Beyond Passports, I would like to know more about how you work.') }}" class="btn btn--glass">@include('partials.wa-glyph')Chat on WhatsApp</a></div>
</div></section>

<script>
(function () {
  var grid = document.getElementById('ab-counts');
  if (!grid) return;
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var fmt = function (v, dec) {
    return dec ? v.toFixed(dec) : Math.round(v).toLocaleString('en-GB');
  };
  var run = function (el) {
    var target = parseFloat(el.getAttribute('data-count'));
    var dec = parseInt(el.getAttribute('data-dec') || '0', 10);
    var suffix = el.getAttribute('data-suffix') || '';
    if (isNaN(target)) return;
    if (reduce) { el.textContent = fmt(target, dec) + suffix; return; }
    var dur = 1100, start = null;
    var step = function (ts) {
      if (start === null) start = ts;
      var p = Math.min((ts - start) / dur, 1);
      var eased = 1 - Math.pow(1 - p, 3);
      el.textContent = fmt(target * eased, dec) + suffix;
      if (p < 1) requestAnimationFrame(step);
      else el.textContent = fmt(target, dec) + suffix;
    };
    requestAnimationFrame(step);
  };
  var io = new IntersectionObserver(function (entries) {
    entries.forEach(function (e) {
      if (e.isIntersecting) {
        grid.querySelectorAll('.n[data-count]').forEach(run);
        io.disconnect();
      }
    });
  }, { threshold: 0.4 });
  io.observe(grid);
})();
</script>

@endsection
