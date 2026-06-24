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
  .sv-hero .wrap { padding-top: 70px; padding-bottom: 58px; }
  .sv-hero .eyebrow { color: var(--soft); }
  .sv-hero h1 { max-width: 19ch; color: #fff; }
  .sv-hero .lede { max-width: 54ch; color: rgba(255,255,255,.82); }
  .sv-hero .row { margin-top: 24px; }
  .sv-hero .btn--ghost { border-color: rgba(255,255,255,.4); color: #fff; }
  .sv-trust { display: flex; flex-wrap: wrap; gap: 12px 26px; margin-top: 32px; padding-top: 24px; border-top: 1px solid rgba(255,255,255,.16); font-size: 13.5px; color: rgba(255,255,255,.75); }
  .sv-trust span { display: inline-flex; align-items: center; gap: 8px; }
  .sv-trust svg { width: 16px; height: 16px; color: #7fe0cf; flex: none; }

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

  .sv-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
  .sv-card { position: relative; display: flex; flex-direction: column; gap: 8px; background: #fff; border: 1px solid var(--paper-edge); border-radius: 14px; padding: 20px; box-shadow: var(--lift-1); overflow: hidden; transition: transform .16s, box-shadow .16s, border-color .16s; }
  .sv-card::before { content: ""; position: absolute; inset: 0 auto 0 0; width: 3px; background: linear-gradient(var(--stamp), var(--cta)); opacity: 0; transition: opacity .16s; }
  a.sv-card:hover { transform: translateY(-3px); box-shadow: var(--lift-2); border-color: var(--stamp); }
  a.sv-card:hover::before { opacity: 1; }
  .sv-card .sv-chip { align-self: flex-start; }
  .sv-card h3 { margin: 4px 0 0; font-size: 16.5px; line-height: 1.3; }
  .sv-card p { margin: 0; font-size: 13.5px; color: var(--ink-soft); line-height: 1.5; flex: 1; }
  .sv-card .sv-go { margin-top: 2px; font-size: 13px; font-weight: 700; color: var(--stamp-text); }
  .sv-silo-cta { display: inline-flex; align-items: center; gap: 7px; margin-top: 18px; font-size: 14px; font-weight: 700; color: var(--cta); transition: gap .15s; }
  .sv-silo-cta:hover { gap: 11px; }

  /* Editorial-row layout (categories with layout = rows) */
  .sv-list { display: flex; flex-direction: column; }
  .sv-row { display: grid; grid-template-columns: 5px 1fr auto; align-items: center; gap: 18px; padding: 20px 4px; border-top: 1px solid var(--paper-edge); transition: padding .15s; }
  .sv-row:first-child { border-top: 0; }
  a.sv-row { color: inherit; }
  a.sv-row:hover { padding-left: 12px; }
  .sv-rail { align-self: stretch; min-height: 42px; border-radius: 4px; background: var(--paper-edge); }
  .sv-rail--available  { background: var(--stamp); }
  .sv-rail--coming-soon { background: #caa644; }
  .sv-rail--on-request { background: var(--cta); }
  .sv-row h3 { margin: 0 0 5px; font-size: 16.5px; font-weight: 700; display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
  .sv-row p { margin: 0; font-size: 13.5px; color: var(--ink-soft); max-width: 60ch; line-height: 1.5; }
  .sv-arrow { width: 38px; height: 38px; border-radius: 50%; border: 1px solid var(--paper-edge); display: grid; place-items: center; color: var(--stamp-text); flex: none; transition: .15s; }
  .sv-arrow svg { width: 16px; height: 16px; }
  a.sv-row:hover .sv-arrow { background: var(--stamp); border-color: var(--stamp); color: #fff; }

  /* Status chips */
  .sv-chip { font-size: 10.5px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; padding: 3px 8px; border-radius: 6px; border: 1px solid transparent; }
  .sv-chip--available  { color: #1f6b4f; background: rgba(92,154,123,.14); border-color: rgba(92,154,123,.35); }
  .sv-chip--coming-soon { color: #8a6d1f; background: rgba(202,166,68,.16); border-color: rgba(202,166,68,.4); }
  .sv-chip--on-request { color: #155e7a; background: rgba(21,94,122,.12); border-color: rgba(21,94,122,.3); }

  /* How it works */
  .sv-steps { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; }
  .sv-stepc { padding: 22px; border: 1px solid var(--paper-edge); border-radius: 14px; background: #fff; }
  .sv-stepc .n { width: 34px; height: 34px; border-radius: 9px; display: grid; place-items: center; background: var(--navy); color: #fff; font-weight: 800; margin-bottom: 12px; }
  .sv-stepc h3 { margin: 0 0 6px; font-size: 16px; }
  .sv-stepc p { margin: 0; font-size: 14px; color: var(--ink-soft); }

  /* Why-us */
  .sv-why { display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px; }
  .sv-why div { padding: 18px 20px; border-left: 3px solid var(--stamp); background: #fff; border-radius: 0 10px 10px 0; box-shadow: var(--lift-1); }
  .sv-why h3 { margin: 0 0 4px; font-size: 15px; }
  .sv-why p { margin: 0; font-size: 13.5px; color: var(--ink-soft); }

  /* FAQ */
  .sv-faq { max-width: 760px; }
  .sv-faq details { border-bottom: 1px solid var(--paper-edge); padding: 16px 0; }
  .sv-faq summary { font-weight: 700; cursor: pointer; list-style: none; display: flex; justify-content: space-between; gap: 16px; }
  .sv-faq summary::-webkit-details-marker { display: none; }
  .sv-faq summary::after { content: '+'; color: var(--stamp); font-weight: 800; }
  .sv-faq details[open] summary::after { content: '\2013'; }
  .sv-faq p { margin: 12px 0 0; color: var(--ink-soft); font-size: 14.5px; }

  @media (max-width: 900px) {
    .sv-layout { grid-template-columns: 1fr; gap: 28px; padding-top: 36px; }
    .sv-step { position: static; }
    .sv-step .panel { display: none; }
    .sv-steps { grid-template-columns: repeat(2, 1fr); }
    .sv-why { grid-template-columns: 1fr; }
    .sv-hero h1 { font-size: 34px; }
  }
  @media (max-width: 560px) {
    .sv-grid, .sv-steps { grid-template-columns: 1fr; }
  }
</style>
@endpush

@section('content')

@php
  $statusLabels = ['available' => 'Available', 'coming-soon' => 'Coming soon', 'on-request' => 'On request'];
  // Phase 1: show only the top 6 silos; the rest are kept in config and revealed later.
  // To show more, raise the limit or remove the array_slice.
  $catalogue = array_slice(config('ukv.services', []), 0, 6);
@endphp

{{-- Hero --}}
<section class="sv-hero"><div class="wrap">
  <p class="eyebrow">Everything we do, in one place</p>
  <h1>One UK team for the whole journey, from "do I need a visa?" to passport back in your hand</h1>
  <p class="lede">Travel-visa and eVisa preparation built around one goal: removing the avoidable reasons applications get refused. Take a single service, or hand us the whole journey.</p>
  <div class="row">
    <a href="{{ url('/tools') }}" class="btn">Check what my trip needs &rarr;</a>
    <a href="{{ url('/contact') }}" class="btn btn--ghost">Message our UK team</a>
  </div>
  <div class="sv-trust">
    <span><svg viewBox="0 0 24 24" fill="none"><path d="M12 3l7 3v5c0 4.5-3 7.6-7 9-4-1.4-7-4.5-7-9V6l7-3z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="M8.5 12l2.4 2.4L15.7 9.6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg> Independent UK team</span>
    <span><svg viewBox="0 0 24 24" fill="none"><path d="M12 3l7 3v5c0 4.5-3 7.6-7 9-4-1.4-7-4.5-7-9V6l7-3z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="M8.5 12l2.4 2.4L15.7 9.6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg> Not a government website</span>
    <span><svg viewBox="0 0 24 24" fill="none"><path d="M12 3l7 3v5c0 4.5-3 7.6-7 9-4-1.4-7-4.5-7-9V6l7-3z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="M8.5 12l2.4 2.4L15.7 9.6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg> Service fee separate from any government fee</span>
  </div>
</div></section>

{{-- Intro --}}
<section><div class="wrap" style="max-width:760px">
  <p class="lede" style="margin:0">Most visa problems are avoidable: unclear funds, missing documents, the wrong embassy, a weak travel story. Every service below exists to catch those <strong>before</strong> they cost you the fee, the slot, or the trip.</p>
</div></section>

{{-- Stepper + catalogue --}}
<div class="wrap"><div class="sv-layout">

  <aside class="sv-step" aria-label="Service areas">
    <p class="lbl">The journey</p>
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
    <section class="sv-silo" id="{{ $cat['key'] }}">
      <div class="sv-head">
        @if (!empty($cat['kicker']))<p class="sv-kicker">{{ $cat['kicker'] }}</p>@endif
        <h2>{{ $cat['label'] }}</h2>
        @if (!empty($cat['featured']))<span class="sv-star">Most important</span>@endif
      </div>
      @if (!empty($cat['intro']))<p class="sv-intro">{{ $cat['intro'] }}</p>@endif
      @php $isCards = ($cat['layout'] ?? 'rows') === 'cards'; @endphp
      @if ($isCards)
      <div class="sv-grid">
        @foreach ($cat['items'] as $item)
          @php
            $href = $item['url'] ?? null;
            $isLink = in_array($item['status'], ['available', 'on-request'], true) && $href;
            $tag = $isLink ? 'a' : 'div';
          @endphp
          <{{ $tag }} class="sv-card" @if($isLink) href="{{ url($href) }}" @endif>
            <span class="sv-chip sv-chip--{{ $item['status'] }}">{{ $statusLabels[$item['status']] ?? $item['status'] }}</span>
            <h3>{{ $item['title'] }}</h3>
            <p>{{ $item['desc'] }}</p>
            @if ($item['status'] === 'available' && $href)
              <span class="sv-go">Open &rarr;</span>
            @elseif ($item['status'] === 'on-request' && $href)
              <span class="sv-go">Ask us &rarr;</span>
            @endif
          </{{ $tag }}>
        @endforeach
      </div>
      @else
      <div class="sv-list">
        @foreach ($cat['items'] as $item)
          @php
            $href = $item['url'] ?? null;
            $isLink = in_array($item['status'], ['available', 'on-request'], true) && $href;
            $tag = $isLink ? 'a' : 'div';
          @endphp
          <{{ $tag }} class="sv-row" @if($isLink) href="{{ url($href) }}" @endif>
            <span class="sv-rail sv-rail--{{ $item['status'] }}"></span>
            <div>
              <h3>{{ $item['title'] }} <span class="sv-chip sv-chip--{{ $item['status'] }}">{{ $statusLabels[$item['status']] ?? $item['status'] }}</span></h3>
              <p>{{ $item['desc'] }}</p>
            </div>
            @if ($isLink)
              <span class="sv-arrow"><svg viewBox="0 0 24 24" fill="none"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
            @else
              <span aria-hidden="true"></span>
            @endif
          </{{ $tag }}>
        @endforeach
      </div>
      @endif
      @if (!empty($cat['cta']['url']))
        <a class="sv-silo-cta" href="{{ url($cat['cta']['url']) }}">{{ $cat['cta']['label'] }} <span aria-hidden="true">&rarr;</span></a>
      @endif
    </section>
    @endforeach
  </div>

</div></div>

{{-- How it works --}}
<section id="how" class="alt"><div class="wrap">
  <div class="sec-head"><p class="eyebrow">How it works</p><h2>Three steps, whichever service you take</h2></div>
  <div class="sv-steps">
    <div class="sv-stepc"><div class="n">1</div><h3>Tell us your trip</h3><p>Use the free checker or a quick form. No card, no account.</p></div>
    <div class="sv-stepc"><div class="n">2</div><h3>We check &amp; prepare</h3><p>Documents, forms, appointment, the things that get people refused.</p></div>
    <div class="sv-stepc"><div class="n">3</div><h3>You travel</h3><p>Trackable every step, with tracked passport return.</p></div>
  </div>
  <div class="row" style="margin-top:22px"><a href="{{ url('/tools') }}" class="btn">Start with the free checker &rarr;</a></div>
</div></section>

{{-- Why us --}}
<section><div class="wrap">
  <div class="sec-head"><p class="eyebrow">Why us</p><h2>Prevention-led, not just paperwork</h2></div>
  <div class="sv-why">
    <div><h3>Prevention-led</h3><p>We remove the avoidable reasons for refusal. Most agents just file it.</p></div>
    <div><h3>Transparent fixed fee</h3><p>Separate from the government fee, and shown upfront.</p></div>
    <div><h3>Honest</h3><p>No one can guarantee a government decision, and we never pretend otherwise.</p></div>
    <div><h3>UK-based team</h3><p>Real people on phone and WhatsApp.</p></div>
  </div>
</div></section>

{{-- FAQ --}}
<section id="faq" class="alt"><div class="wrap">
  <div class="sec-head"><p class="eyebrow">Questions</p><h2>Before you start</h2></div>
  <div class="sv-faq">
    <details><summary>Can I use one service or do I have to take everything?</summary><p>Either. Pick exactly what you need: one service, several, or the whole journey.</p></details>
    <details><summary>Is the checker really free?</summary><p>Yes. No card, no account. It tells you what your trip needs in about a minute.</p></details>
    <details><summary>Can you guarantee my visa?</summary><p>No. The embassy makes the decision. What we do is remove the avoidable reasons they say no.</p></details>
    <details><summary>What does it cost?</summary><p>A clear fixed service fee, shown before you pay. It is separate from any government or embassy fee, which is set by the authorities.</p></details>
    <details><summary>I'm not a British citizen, can you still help?</summary><p>Yes. We help UK residents travelling on any passport.</p></details>
    <details><summary>A service is marked "Coming soon", what now?</summary><p>Message us. We can often still help, or we'll tell you when it goes live.</p></details>
  </div>
</div></section>

{{-- Final CTA --}}
<section class="cta-band"><div class="wrap reveal">
  <div class="rule"></div>
  <h2>Not sure where to start?</h2>
  <p style="max-width:48ch;color:#eef0f1">Run the free 60-second checker and we'll tell you exactly what your trip needs.</p>
  <div class="row"><a href="{{ url('/tools') }}" class="btn">Check what my trip needs &rarr;</a><a href="{{ url('/contact') }}" class="btn btn--glass">Message our UK team</a></div>
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
