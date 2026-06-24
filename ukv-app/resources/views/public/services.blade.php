@extends('layouts.public')

@section('title', 'Our Services: UK Visa, eVisa, ETA & IDP Help | Beyond Passports')
@section('description', 'Every Beyond Passports service in one place: Schengen and eVisa preparation, appointments, documents, refusal prevention, driving permits and free tools. Independent UK team. Not a government website.')

@push('head')
<style>
  /* ── services hub (journey stepper) — page-scoped only. Design system in ukv.css ── */

  /* Hero — dark concierge */
  .sv-hero {
    background:
      radial-gradient(680px 320px at 88% -10%, rgba(46,154,140,.5), transparent 60%),
      radial-gradient(620px 320px at -5% 120%, rgba(21,94,122,.6), transparent 60%),
      var(--navy);
    color: #fff;
  }
  .sv-hero .wrap { padding-top: 46px; padding-bottom: 40px; }
  .sv-hero .eyebrow { color: var(--soft); }
  .sv-hero h1 { max-width: 19ch; color: #fff; }
  .sv-hero .lede { max-width: 54ch; color: rgba(255,255,255,.82); }
  .sv-hero .row { margin-top: 18px; }
  .sv-hero .btn--ghost { border-color: rgba(255,255,255,.4); color: #fff; }

  /* Hero split + start-here card */
  .sv-hero-grid { display: grid; grid-template-columns: 1.2fr .8fr; gap: 48px; align-items: center; }
  .sv-hero-copy h1 { max-width: 16ch; }
  .sv-hero-copy .lede { margin-bottom: 0; }
  .sv-hcard { background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.18); border-radius: 18px; padding: 20px; }
  .sv-hcard h3 { margin: 0 0 3px; font-size: 17px; color: #fff; }
  .sv-hcard > p { margin: 0 0 12px; color: rgba(255,255,255,.72); font-size: 14px; }
  .sv-hbtns { display: flex; gap: 10px; }
  .sv-hcard .btn { flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: 7px; white-space: nowrap; padding: 11px 12px; }
  .sv-hbtns .wa-g { width: 16px; height: 16px; fill: currentColor; flex: none; }
  .cta-band .row .btn { display: inline-flex; align-items: center; gap: 8px; }
  .cta-band .row .wa-g { width: 18px; height: 18px; fill: currentColor; flex: none; }
  .btn--wa { background: #25D366; border: 0; color: #fff; }
  .btn--wa:hover { background: #1da851; }
  .sv-hsteps { margin-top: 12px; display: flex; flex-wrap: wrap; gap: 8px 16px; }
  .sv-hsteps span { display: inline-flex; align-items: center; gap: 8px; font-size: 13.5px; color: rgba(255,255,255,.82); }
  .sv-hsteps b { background: rgba(255,255,255,.14); width: 22px; height: 22px; border-radius: 50%; display: grid; place-items: center; font-size: 12px; font-weight: 800; flex: none; }
  @media (max-width: 820px) { .sv-hero-grid { grid-template-columns: 1fr; gap: 28px; } }
  /* Trust bar — dark mesh band under hero (matches home .tbar-f) */
  .tbar-f { padding: 0; background:
      radial-gradient(520px 200px at 12% 0%, rgba(21,94,122,.45), transparent 60%),
      radial-gradient(520px 200px at 92% 100%, rgba(46,154,140,.42), transparent 60%),
      var(--navy); color: #fff; border-top: 1px solid rgba(255,255,255,.10); }
  .tbar-f .row { display: flex; justify-content: center; gap: 30px; flex-wrap: wrap; padding: 16px 0; }
  .tbar-f .ti { display: flex; align-items: center; gap: 9px; font: 600 14px var(--display); color: #fff; white-space: nowrap; }
  .tbar-f .ti svg { width: 20px; height: 20px; color: var(--soft); flex: none; }
  .tbar-f .ti b { color: var(--soft); font-weight: 800; }
  @media (max-width: 560px) { .tbar-f .row { gap: 14px 22px; } }

  /* Layout — sticky stepper + content */
  .sv-layout { display: grid; grid-template-columns: 250px 1fr; gap: 52px; align-items: start; padding: 56px 0 20px; }

  /* Stepper index */
  .sv-step { position: sticky; top: 88px; }
  .sv-step .lbl { font-size: 11px; font-weight: 800; letter-spacing: .13em; text-transform: uppercase; color: var(--ink-soft); margin: 0 0 18px; }
  .sv-step .item { display: flex; gap: 14px; position: relative; padding-bottom: 22px; color: inherit; }
  .sv-step .item:last-of-type { padding-bottom: 0; }
  .sv-step .item::before { content: ""; position: absolute; left: 13px; top: 28px; bottom: -2px; width: 2px; background: var(--paper-edge); }
  .sv-step .item:last-of-type::before { display: none; }
  .sv-step .dot { width: 28px; height: 28px; border-radius: 50%; border: 2px solid var(--paper-edge); background: #fff; display: grid; place-items: center; font-size: 12px; font-weight: 800; color: var(--ink-soft); flex: none; z-index: 1; transition: .18s; }
  .sv-step .item:hover .dot, .sv-step .item.on .dot { border-color: var(--stamp); background: var(--stamp); color: #fff; }
  .sv-step .tx b { display: block; font-size: 14.5px; font-weight: 700; line-height: 1.2; margin-top: 3px; color: var(--ink); }
  .sv-step .tx span { font-size: 12px; color: var(--ink-soft); }
  .sv-step .item.on .tx b { color: var(--stamp-text); }
  .sv-step .panel { margin-top: 24px; background: linear-gradient(160deg, #eef5f4, #fff); border: 1px solid var(--paper-edge); border-radius: 14px; padding: 18px; }
  .sv-step .panel b { display: block; font-size: 14px; margin-bottom: 6px; }
  .sv-step .panel p { margin: 0 0 12px; font-size: 12.5px; color: var(--ink-soft); }
  .sv-step .panel a { display: inline-block; background: var(--cta); color: #fff; padding: 9px 14px; border-radius: 9px; font-size: 12.5px; font-weight: 700; }

  /* Silo blocks */
  .sv-silo { scroll-margin-top: 88px; padding-bottom: 44px; margin-bottom: 44px; border-bottom: 1px solid var(--paper-edge); }
  .sv-silo:last-child { border-bottom: 0; margin-bottom: 0; }
  .sv-silo .sv-head { display: flex; align-items: baseline; gap: 12px; flex-wrap: wrap; }
  .sv-kicker { font-size: 11px; font-weight: 800; letter-spacing: .13em; text-transform: uppercase; color: var(--stamp-text); margin: 0 0 8px; flex-basis: 100%; }
  .sv-silo h2 { margin: 0; font-size: 27px; letter-spacing: -.01em; }
  .sv-silo .sv-star { font-size: 11px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: var(--stamp-text); border: 1px solid var(--stamp); border-radius: 999px; padding: 3px 10px; }
  .sv-silo .sv-intro { margin: 8px 0 22px; color: var(--ink-soft); font-size: 15.5px; max-width: 62ch; }

  /* Split card silo: sticky header left, cards right */
  .sv-silo--split { display: grid; grid-template-columns: .8fr 2fr; gap: 36px; align-items: start; }
  .sv-silo--split .sv-silo-head { position: sticky; top: 88px; }
  .sv-silo--split .sv-intro { margin: 8px 0 0; max-width: 34ch; }
  .sv-silo--split .sv-silo-cta { margin-top: 16px; }
  .sv-silo--split .sv-grid { grid-template-columns: 1fr 1fr; }
  @media (max-width: 1100px) { .sv-silo--split .sv-grid { grid-template-columns: 1fr; } }
  @media (max-width: 900px) { .sv-silo--split { grid-template-columns: 1fr; gap: 16px; } .sv-silo--split .sv-silo-head { position: static; } .sv-silo--split .sv-grid { grid-template-columns: 1fr 1fr; } }
  @media (max-width: 560px) { .sv-silo--split .sv-grid { grid-template-columns: 1fr; } }

  .sv-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
  .sv-card { position: relative; display: flex; flex-direction: column; gap: 8px; background: #fff; border: 1px solid var(--paper-edge); border-radius: 14px; padding: 20px; box-shadow: var(--lift-1); overflow: hidden; transition: transform .16s, box-shadow .16s, border-color .16s; }
  .sv-card::before { content: ""; position: absolute; inset: 0 auto 0 0; width: 3px; background: linear-gradient(var(--stamp), var(--cta)); opacity: 0; transition: opacity .16s; }
  a.sv-card:hover { transform: translateY(-3px); box-shadow: var(--lift-2); border-color: var(--stamp); }
  a.sv-card:hover::before { opacity: 1; }
  .sv-card .sv-chip { align-self: flex-start; }
  .sv-card h3 { margin: 0; font-size: 16.5px; line-height: 1.3; }
  .sv-card p { margin: 0; font-size: 13.5px; color: var(--ink-soft); line-height: 1.5; flex: 1; }
  .sv-card { transition: padding-bottom .18s ease, transform .16s, box-shadow .16s, border-color .16s; }
  .sv-card .sv-fab { position: absolute; left: 20px; bottom: 16px; }
  a.sv-card:hover { padding-bottom: 60px; }

  /* WhatsApp chat pill: hidden, appears only when the tile is hovered (shared by cards + rows) */
  .sv-fab { display: inline-flex; align-items: center; height: 38px; flex: none; background: #25D366; color: #fff; border-radius: 999px; box-shadow: 0 10px 22px -12px rgba(37,211,102,.85); opacity: 0; transform: translateY(4px); transition: opacity .18s ease, transform .18s ease; pointer-events: none; }
  .sv-fab .wa-g { width: 19px; height: 19px; fill: currentColor; flex: none; margin: 0 8px 0 12px; }
  .sv-fab .l { font-weight: 800; font-size: 13px; white-space: nowrap; padding-right: 16px; }
  a.sv-card:hover .sv-fab, a.sv-row:hover .sv-fab { opacity: 1; transform: none; pointer-events: auto; }
  @media (hover: none) { .sv-fab { opacity: 1; transform: none; pointer-events: auto; } }
  .sv-silo-cta { display: inline-flex; align-items: center; gap: 11px; margin-top: 18px; color: var(--ink); }
  .sv-silo-cta-ic { width: 38px; height: 38px; border-radius: 11px; background: #25D366; color: #fff; display: grid; place-items: center; flex: none; box-shadow: 0 10px 22px -12px rgba(37,211,102,.8); transition: background .15s, transform .15s; }
  .sv-silo-cta .wa-g { width: 18px; height: 18px; fill: currentColor; }
  .sv-silo-cta:hover .sv-silo-cta-ic { background: #1da851; transform: translateY(-1px); }
  .sv-silo-cta-tx { display: flex; flex-direction: column; line-height: 1.15; }
  .sv-silo-cta-tx b { font-size: 14px; font-weight: 800; }
  .sv-silo-cta-tx small { font-size: 11.5px; font-weight: 600; color: var(--ink-soft); }
  /* WhatsApp glyph inside hero / CTA buttons */
  .btn .wa-g { width: 17px; height: 17px; fill: currentColor; flex: none; margin-right: 2px; vertical-align: -3px; }

  /* Editorial-row layout (categories with layout = rows) */
  .sv-list { display: flex; flex-direction: column; }
  .sv-row { display: grid; grid-template-columns: 5px 1fr auto; align-items: center; gap: 18px; padding: 20px 4px; border-top: 1px solid var(--paper-edge); transition: padding .15s; }
  .sv-row:first-child { border-top: 0; }
  a.sv-row { color: inherit; }
  a.sv-row:hover { padding-left: 12px; }
  .sv-rail { align-self: stretch; min-height: 42px; border-radius: 4px; background: var(--stamp); }
  .sv-row h3 { margin: 0 0 5px; font-size: 16.5px; font-weight: 700; }
  .sv-row p { margin: 0; font-size: 13.5px; color: var(--ink-soft); max-width: 60ch; line-height: 1.5; }
  .sv-row .sv-fab { align-self: center; }

  /* Status chips */
  .sv-chip { font-size: 10.5px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; padding: 3px 8px; border-radius: 6px; border: 1px solid transparent; }
  .sv-chip--available  { color: #1f6b4f; background: rgba(92,154,123,.14); border-color: rgba(92,154,123,.35); }
  .sv-chip--coming-soon { color: #8a6d1f; background: rgba(202,166,68,.16); border-color: rgba(202,166,68,.4); }
  .sv-chip--on-request { color: #155e7a; background: rgba(21,94,122,.12); border-color: rgba(21,94,122,.3); }

  /* How it works + Why us reuse shared .steps / .ticks from ukv.css */

  /* FAQ — tinted panel accordion (matches driving-abroad / money pages) */
  .faq-e { background: var(--paper); }
  .faq-e .sec-head { text-align: center; max-width: 60ch; margin-left: auto; margin-right: auto; }
  .faq-panel { background: var(--white); border: 1px solid var(--paper-edge); border-radius: 18px; padding: 6px 30px; max-width: 80ch; margin: 0 auto; box-shadow: 0 16px 40px -30px rgba(40,50,70,.5); }
  .faqd { max-width: none; }
  .faqd details { border-bottom: 1px solid var(--paper-edge); padding: 18px 0; }
  .faqd details:last-child { border-bottom: 0; }
  .faqd summary { font-family: var(--display); font-size: 19px; color: var(--navy); font-weight: 600; cursor: pointer; list-style: none; display: flex; justify-content: space-between; align-items: center; gap: 16px; }
  .faqd summary::-webkit-details-marker { display: none; }
  .faqd summary::after { content: "+"; font-size: 22px; color: var(--cta); flex: 0 0 auto; font-weight: 700; transition: transform .15s ease; }
  .faqd details[open] summary::after { content: "\2013"; }
  .faqd p { margin: 12px 0 0; color: #3a4b55; font-size: 16px; line-height: 1.65; }

  @media (max-width: 900px) {
    .sv-layout { grid-template-columns: 1fr; gap: 28px; padding-top: 36px; }
    .sv-step { position: static; }
    .sv-step .panel { display: none; }
    .sv-hero h1 { font-size: 34px; }
  }
  @media (max-width: 560px) {
    .sv-grid { grid-template-columns: 1fr; }
  }
</style>
@endpush

@section('content')

@php
  $statusLabels = ['available' => 'Available', 'coming-soon' => 'Coming soon', 'on-request' => 'On request'];
  // Phase 1: show only the top 6 silos; the rest are kept in config and revealed later.
  // To show more, raise the limit or remove the array_slice.
  $catalogue = array_slice(config('ukv.services', []), 0, 6);
  // Every service routes to the WhatsApp chat (number set in config('ukv.whatsapp')).
  $waNumber = config('ukv.whatsapp') ?: '440000000000';
  $waFor = fn (string $title): string => 'https://wa.me/'.$waNumber.'?text='.rawurlencode("Hi Beyond Passports, I'd like help with: ".$title);
  $waGlyph = '<svg viewBox="0 0 24 24" aria-hidden="true" class="wa-g"><path d="M.057 24l1.687-6.163a11.867 11.867 0 0 1-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.817 11.817 0 0 1 8.413 3.488 11.824 11.824 0 0 1 3.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 0 1-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884a9.86 9.86 0 0 0 1.51 5.26l-.999 3.648 3.978-1.607zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>';
@endphp

{{-- Hero — split with "start here" card --}}
<section class="sv-hero"><div class="wrap"><div class="sv-hero-grid">
  <div class="sv-hero-copy">
    <p class="eyebrow">Our services</p>
    <h1>Visa and travel services, all in one place</h1>
    <p class="lede">Tell us where you are going. We sort the visa, the documents and the appointment so your trip goes ahead.</p>
  </div>
  <div class="sv-hcard">
    <h3>Start here</h3>
    <p>Message our UK team. No account needed.</p>
    <div class="sv-hbtns">
      <a href="{{ $waFor('my upcoming trip') }}" target="_blank" rel="noopener" class="btn btn--wa">{!! $waGlyph !!} WhatsApp us</a>
      <a href="{{ url('/tools') }}" class="btn btn--ghost">Free check</a>
    </div>
    <div class="sv-hsteps">
      <span><b>1</b> Pick a service</span>
      <span><b>2</b> Message us</span>
      <span><b>3</b> We sort it</span>
    </div>
  </div>
</div></div></section>

{{-- Trust bar — dark mesh band (matches home .tbar-f) --}}
<section class="tbar-f"><div class="wrap"><div class="row">
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 5 6v6c0 4.5 3 7.5 7 8.5 4-1 7-4 7-8.5V6z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="m9 12 2 2 4-4.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg><span><b>Independent</b> UK team</span></span>
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 21h18M5 21V9l7-5 7 5v12M9 21v-6h6v6" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg><span><b>Not</b> a government website</span></span>
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 7v10M9.5 9.2c0-1 1.1-1.7 2.5-1.7s2.5.7 2.5 1.7-1.1 1.6-2.5 1.6-2.5.7-2.5 1.7 1.1 1.7 2.5 1.7 2.5-.7 2.5-1.7" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg><span>Service fee <b>separate</b> from any government fee</span></span>
</div></div></section>

{{-- Stepper + catalogue --}}
<div class="wrap"><div class="sv-layout">

  <aside class="sv-step" aria-label="Our services">
    <p class="lbl">Our services</p>
    @foreach ($catalogue as $cat)
      <a class="item @if($loop->first) on @endif" href="#{{ $cat['key'] }}" data-target="{{ $cat['key'] }}">
        <span class="dot">{{ $loop->iteration }}</span>
        <span class="tx"><b>{{ $cat['label'] }}</b>@if(!empty($cat['kicker']))<span>{{ $cat['kicker'] }}</span>@endif</span>
      </a>
    @endforeach
    <div class="panel">
      <b>Not sure where to start?</b>
      <p>The free checker tells you exactly what your trip needs.</p>
      <a href="{{ url('/tools') }}">Open the checker &rarr;</a>
    </div>
  </aside>

  <div class="sv-content">
    @foreach ($catalogue as $cat)
    @php $isCards = ($cat['layout'] ?? 'rows') === 'cards'; @endphp
    <section class="sv-silo @if($isCards) sv-silo--split @endif" id="{{ $cat['key'] }}">
      <div class="sv-silo-head">
        <div class="sv-head">
          @if (!empty($cat['kicker']))<p class="sv-kicker">{{ $cat['kicker'] }}</p>@endif
          <h2>{{ $cat['label'] }}</h2>
          @if (!empty($cat['featured']))<span class="sv-star">Most important</span>@endif
        </div>
        @if (!empty($cat['intro']))<p class="sv-intro">{{ $cat['intro'] }}</p>@endif
        @if ($isCards)
          <a class="sv-silo-cta" href="{{ $waFor($cat['label']) }}" target="_blank" rel="noopener">
            <span class="sv-silo-cta-ic">{!! $waGlyph !!}</span>
            <span class="sv-silo-cta-tx"><b>Chat about this</b><small>Reply in minutes on WhatsApp</small></span>
          </a>
        @endif
      </div>
      <div class="sv-silo-body">
        @if ($isCards)
        <div class="sv-grid">
          @foreach ($cat['items'] as $item)
            <a class="sv-card" href="{{ $waFor($item['title']) }}" target="_blank" rel="noopener">
              <h3>{{ $item['title'] }}</h3>
              <p>{{ $item['desc'] }}</p>
              <span class="sv-fab">{!! $waGlyph !!}<span class="l">Chat to start</span></span>
            </a>
          @endforeach
        </div>
        @else
        <div class="sv-list">
          @foreach ($cat['items'] as $item)
            <a class="sv-row" href="{{ $waFor($item['title']) }}" target="_blank" rel="noopener">
              <span class="sv-rail"></span>
              <div>
                <h3>{{ $item['title'] }}</h3>
                <p>{{ $item['desc'] }}</p>
              </div>
              <span class="sv-fab">{!! $waGlyph !!}<span class="l">Chat to start</span></span>
            </a>
          @endforeach
        </div>
        @unless ($isCards)
          <a class="sv-silo-cta" href="{{ $waFor($cat['label']) }}" target="_blank" rel="noopener">
            <span class="sv-silo-cta-ic">{!! $waGlyph !!}</span>
            <span class="sv-silo-cta-tx"><b>Chat about this</b><small>Reply in minutes on WhatsApp</small></span>
          </a>
        @endunless
        @endif
      </div>
    </section>
    @endforeach
  </div>

</div></div>

{{-- How it works (shared .steps design, matches home) --}}
<section id="how" class="alt"><div class="wrap">
  <div class="sec-head reveal" style="text-align:center;max-width:60ch;margin:0 auto 36px">
    <p class="eyebrow">How it works</p>
    <h2>Three steps, whichever service you take</h2>
  </div>
  <div class="steps">
    <div class="step reveal"><div class="num">01</div><div class="rule"></div><h3>Tell us your trip</h3><p>Use the free checker or a quick form. No card, no account.</p></div>
    <div class="step reveal"><div class="num">02</div><div class="rule"></div><h3>We check &amp; prepare</h3><p>Documents, forms, appointment, the things that get people refused.</p></div>
    <div class="step reveal"><div class="num">03</div><div class="rule"></div><h3>You travel</h3><p>Trackable every step, with tracked passport return.</p></div>
  </div>
  <div style="text-align:center;margin-top:28px"><a href="{{ url('/tools') }}" class="btn">Start with the free checker &rarr;</a></div>
</div></section>

{{-- Why us (shared .ticks design, matches home "What we do") --}}
<style>
  #why{background:linear-gradient(180deg,#FBF6F1,var(--paper))}
  #why .sec-head{text-align:center;max-width:60ch;margin-left:auto;margin-right:auto}
  #why .sec-head .lede{margin:12px auto 0;max-width:52ch}
  #why .ticks{margin-top:30px}
  #why .tick{background:#fff;border:1px solid var(--paper-edge);border-radius:16px;padding:22px;gap:14px;
    box-shadow:0 10px 30px -22px rgba(40,50,70,.5);transition:transform .25s ease,box-shadow .25s ease}
  #why .tick:hover{transform:translateY(-3px);box-shadow:var(--lift-2)}
  #why .tick .stamp{flex:0 0 44px;width:44px;height:44px;padding:9px;border-radius:11px;
    background:rgba(46,154,140,.12);color:var(--stamp-text);box-sizing:border-box}
</style>
<section id="why"><div class="wrap">
  <div class="sec-head reveal"><p class="eyebrow">Why us</p><h2>Why choose us</h2><p class="lede">We do the hard parts, keep it simple, and tell you the truth.</p></div>
  <div class="ticks">
    <div class="tick reveal"><svg class="stamp" width="38" height="38" viewBox="0 0 48 48" role="img" aria-label="Checked"><use href="#ukv-stamp"></use></svg><div><h3>We help avoid refusals</h3><p>We catch the mistakes that get people refused.</p></div></div>
    <div class="tick reveal"><svg class="stamp" width="38" height="38" viewBox="0 0 48 48" role="img" aria-label="Checked"><use href="#ukv-stamp"></use></svg><div><h3>One clear fee</h3><p>Shown upfront. Separate from the government fee.</p></div></div>
    <div class="tick reveal"><svg class="stamp" width="38" height="38" viewBox="0 0 48 48" role="img" aria-label="Checked"><use href="#ukv-stamp"></use></svg><div><h3>Honest advice</h3><p>No one can promise a visa. We never pretend otherwise.</p></div></div>
    <div class="tick reveal"><svg class="stamp" width="38" height="38" viewBox="0 0 48 48" role="img" aria-label="Checked"><use href="#ukv-stamp"></use></svg><div><h3>Real UK team</h3><p>Talk to real people on phone and WhatsApp.</p></div></div>
  </div>
</div></section>

{{-- FAQ — tinted panel accordion (matches driving-abroad / money pages) --}}
<section id="faq" class="faq-e"><div class="wrap">
  <div class="sec-head reveal"><p class="eyebrow">Questions</p><h2>Before you start</h2></div>
  <div class="faq-panel reveal">
    <div class="faqd">
      <details><summary>Can I use one service or do I have to take everything?</summary><p>Either. Pick exactly what you need: one service, several, or the whole journey.</p></details>
      <details><summary>Is the checker really free?</summary><p>Yes. No card, no account. It tells you what your trip needs in about a minute.</p></details>
      <details><summary>Can you guarantee my visa?</summary><p>No. The embassy makes the decision. What we do is remove the avoidable reasons they say no.</p></details>
      <details><summary>What does it cost?</summary><p>A clear fixed service fee, shown before you pay. It is separate from any government or embassy fee, which is set by the authorities.</p></details>
      <details><summary>I'm not a British citizen, can you still help?</summary><p>Yes. We help UK residents travelling on any passport.</p></details>
      <details><summary>How do I get started?</summary><p>Message our UK team on WhatsApp with your trip, or run the free checker first. We'll tell you exactly what you need and what it costs before you commit.</p></details>
    </div>
  </div>
</div></section>

{{-- Final CTA --}}
<section class="cta-band"><div class="wrap reveal">
  <div class="rule"></div>
  <h2>Tell us about your trip</h2>
  <p style="max-width:48ch;color:#eef0f1">Message our UK team on WhatsApp and we'll tell you exactly what your trip needs, or run the free checker first.</p>
  <div class="row"><a href="{{ $waFor('my upcoming trip') }}" target="_blank" rel="noopener" class="btn">{!! $waGlyph !!} Chat on WhatsApp</a><a href="{{ url('/tools') }}" class="btn btn--glass">Run the free checker</a></div>
</div></section>

{{-- Scroll-spy: highlight the active silo in the stepper --}}
<script>
(function () {
  var steps = Array.prototype.slice.call(document.querySelectorAll('.sv-step .item'));
  var sections = Array.prototype.slice.call(document.querySelectorAll('.sv-silo'));
  if (!steps.length || !sections.length || !('IntersectionObserver' in window)) return;
  var map = {};
  steps.forEach(function (s) { map[s.getAttribute('data-target')] = s; });
  var obs = new IntersectionObserver(function (entries) {
    entries.forEach(function (e) {
      if (!e.isIntersecting) return;
      steps.forEach(function (s) { s.classList.remove('on'); });
      var active = map[e.target.id];
      if (active) active.classList.add('on');
    });
  }, { rootMargin: '-45% 0px -50% 0px', threshold: 0 });
  sections.forEach(function (sec) { obs.observe(sec); });
})();
</script>

{{-- Structured data: service catalogue --}}
<script type="application/ld+json">
@php
  $els = [];
  $pos = 1;
  foreach ($catalogue as $cat) {
    foreach ($cat['items'] as $item) {
      $els[] = [
        '@type' => 'ListItem',
        'position' => $pos++,
        'item' => array_filter([
          '@type' => 'Service',
          'name' => $item['title'],
          'description' => $item['desc'],
          'category' => $cat['label'],
          'provider' => ['@type' => 'Organization', 'name' => 'Beyond Passports'],
          'url' => ($item['status'] === 'available' && !empty($item['url'])) ? url($item['url']) : null,
        ]),
      ];
    }
  }
  $jsonld = [
    '@context' => 'https://schema.org',
    '@type' => 'OfferCatalog',
    'name' => 'Beyond Passports services',
    'itemListElement' => $els,
  ];
@endphp
{!! json_encode($jsonld, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>

@endsection
