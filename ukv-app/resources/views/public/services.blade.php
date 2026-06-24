@extends('layouts.public')

@section('title', 'Our Services: UK Visa, eVisa, ETA & IDP Help | Beyond Passports')
@section('description', 'Every Beyond Passports service in one place: Schengen and eVisa preparation, appointments, documents, refusal prevention, driving permits and free tools. Independent UK team. Not a government website.')

@push('head')
<style>
  /* ── services hub — page-scoped only. Design system in ukv.css ───────────── */

  /* Hero */
  .sv-hero {
    background: linear-gradient(180deg, #EAF1F4, #F2F5F6 60%, var(--paper));
    border-bottom: 1px solid var(--paper-edge);
  }
  .sv-hero .wrap { padding-top: 56px; padding-bottom: 48px; }
  .sv-hero h1 { max-width: 20ch; }
  .sv-hero .lede { max-width: 56ch; }
  .sv-hero .row { margin-top: 22px; }
  .sv-trust {
    display: flex; flex-wrap: wrap; gap: 10px 22px;
    margin-top: 24px; padding-top: 18px;
    border-top: 1px solid var(--paper-edge);
    font-size: 13.5px; color: var(--ink-soft);
  }
  .sv-trust span { display: inline-flex; align-items: center; gap: 7px; }
  .sv-trust svg { width: 16px; height: 16px; color: var(--stamp); flex: none; }

  /* Jump nav — the 14 silos as anchor chips */
  .sv-jump { border-bottom: 1px solid var(--paper-edge); background: var(--paper); }
  .sv-jump .wrap { padding-top: 18px; padding-bottom: 18px; }
  .sv-chips { display: flex; flex-wrap: wrap; gap: 8px; }
  .sv-chips a {
    font-size: 13px; font-weight: 600; color: var(--ink);
    padding: 7px 13px; border: 1px solid var(--paper-edge);
    border-radius: 999px; background: #fff; transition: .15s;
  }
  .sv-chips a:hover { border-color: var(--stamp); color: var(--stamp); }

  /* Category block — editorial rows */
  .sv-cat { scroll-margin-top: 86px; }
  .sv-cat + .sv-cat { border-top: 1px solid var(--paper-edge); }
  .sv-cat .wrap { display: grid; grid-template-columns: .8fr 2fr; gap: 44px; align-items: start; }
  .sv-side { position: sticky; top: 86px; }
  .sv-side .sv-star {
    display: inline-block; margin-bottom: 12px; font-size: 11px; font-weight: 800;
    letter-spacing: .08em; text-transform: uppercase; color: var(--stamp-text);
    border: 1px solid var(--stamp); border-radius: 999px; padding: 3px 10px;
  }
  .sv-kicker { margin: 0 0 8px; font-size: 11px; font-weight: 800; letter-spacing: .13em; text-transform: uppercase; color: var(--stamp-text); }
  .sv-side h2 { margin: 0 0 10px; font-size: 27px; line-height: 1.15; letter-spacing: -.01em; }
  .sv-side .sv-intro { margin: 0; color: var(--ink-soft); font-size: 15px; max-width: 40ch; }
  .sv-side-cta { display: inline-flex; align-items: center; gap: 7px; margin-top: 18px; font-size: 14px; font-weight: 700; color: var(--cta); transition: gap .15s; }
  .sv-side-cta:hover { gap: 11px; }

  .sv-list { display: flex; flex-direction: column; }
  .sv-row { display: grid; grid-template-columns: 5px 1fr auto; align-items: center; gap: 20px; padding: 22px 4px; border-top: 1px solid var(--paper-edge); transition: padding .15s; }
  .sv-row:first-child { border-top: 0; }
  a.sv-row { color: inherit; }
  a.sv-row:hover { padding-left: 14px; }
  .sv-rail { align-self: stretch; min-height: 44px; border-radius: 4px; background: var(--paper-edge); }
  .sv-rail--available  { background: var(--stamp); }
  .sv-rail--coming-soon { background: #caa644; }
  .sv-rail--on-request { background: var(--cta); }
  .sv-row h3 { margin: 0 0 5px; font-size: 17px; font-weight: 700; display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
  .sv-row p { margin: 0; font-size: 13.5px; color: var(--ink-soft); max-width: 56ch; line-height: 1.5; }
  .sv-arrow { width: 38px; height: 38px; border-radius: 50%; border: 1px solid var(--paper-edge); display: grid; place-items: center; color: var(--stamp-text); flex: none; transition: .15s; }
  .sv-arrow svg { width: 16px; height: 16px; }
  a.sv-row:hover .sv-arrow { background: var(--stamp); border-color: var(--stamp); color: #fff; }

  /* Signature-card layout (categories with layout = cards) */
  .sv-cat--cards .wrap { display: block; }
  .sv-cat--cards .sv-side { position: static; margin-bottom: 24px; }
  .sv-cat--cards .sv-side .sv-intro { max-width: 60ch; }
  .sv-cardgrid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
  .sv-card { position: relative; display: flex; flex-direction: column; gap: 9px; padding: 22px; background: #fff; border: 1px solid var(--paper-edge); border-radius: 14px; box-shadow: var(--lift-1); overflow: hidden; transition: transform .16s, box-shadow .16s, border-color .16s; }
  .sv-card::before { content: ""; position: absolute; inset: 0 auto 0 0; width: 3px; background: linear-gradient(var(--stamp), var(--cta)); opacity: 0; transition: opacity .16s; }
  a.sv-card:hover { transform: translateY(-3px); box-shadow: var(--lift-2); border-color: var(--stamp); }
  a.sv-card:hover::before { opacity: 1; }
  .sv-card .sv-chip { align-self: flex-start; }
  .sv-card h3 { margin: 0; font-size: 16.5px; font-weight: 700; line-height: 1.3; }
  .sv-card p { margin: 0; font-size: 13.5px; color: var(--ink-soft); flex: 1; line-height: 1.5; }
  .sv-card .sv-go { margin-top: 2px; font-size: 13px; font-weight: 700; color: var(--stamp-text); }

  /* Status chips */
  .sv-chip {
    font-size: 10.5px; font-weight: 800;
    letter-spacing: .06em; text-transform: uppercase;
    padding: 3px 8px; border-radius: 6px; border: 1px solid transparent;
  }
  .sv-chip--available  { color: #1f6b4f; background: rgba(92,154,123,.14); border-color: rgba(92,154,123,.35); }
  .sv-chip--coming-soon { color: #8a6d1f; background: rgba(202,166,68,.16); border-color: rgba(202,166,68,.4); }
  .sv-chip--on-request { color: #155e7a; background: rgba(21,94,122,.12); border-color: rgba(21,94,122,.3); }

  /* How it works */
  .sv-steps { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; }
  .sv-step { padding: 22px; border: 1px solid var(--paper-edge); border-radius: 14px; background: #fff; }
  .sv-step .n {
    width: 34px; height: 34px; border-radius: 9px; display: grid; place-items: center;
    background: var(--navy); color: #fff; font-weight: 800; margin-bottom: 12px;
  }
  .sv-step h3 { margin: 0 0 6px; font-size: 16px; }
  .sv-step p { margin: 0; font-size: 14px; color: var(--ink-soft); }

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
  .sv-faq details[open] summary::after { content: '–'; }
  .sv-faq p { margin: 12px 0 0; color: var(--ink-soft); font-size: 14.5px; }

  @media (max-width: 900px) {
    .sv-cat .wrap { grid-template-columns: 1fr; gap: 24px; }
    .sv-side { position: static; }
    .sv-side .sv-intro { max-width: none; }
    .sv-steps { grid-template-columns: repeat(2, 1fr); }
    .sv-why { grid-template-columns: 1fr; }
  }
  @media (max-width: 560px) {
    .sv-steps { grid-template-columns: 1fr; }
    .sv-row { gap: 14px; }
  }
</style>
@endpush

@section('content')

@php
  $statusLabels = ['available' => 'Available', 'coming-soon' => 'Coming soon', 'on-request' => 'On request'];
  $catalogue = config('ukv.services', []);
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

{{-- Jump nav --}}
<section class="sv-jump"><div class="wrap">
  <div class="sv-chips">
    @foreach ($catalogue as $cat)
      <a href="#{{ $cat['key'] }}">{{ $cat['label'] }}</a>
    @endforeach
  </div>
</div></section>

{{-- Intro --}}
<section><div class="wrap" style="max-width:720px">
  <p class="lede" style="margin:0">Most visa problems are avoidable: unclear funds, missing documents, the wrong embassy, a weak travel story. Every service below exists to catch those <strong>before</strong> they cost you the fee, the slot, or the trip.</p>
</div></section>

{{-- Category blocks — editorial rows by default, signature cards where layout=cards --}}
@foreach ($catalogue as $cat)
  @php $isCards = ($cat['layout'] ?? 'rows') === 'cards'; @endphp
<section class="sv-cat @if($isCards) sv-cat--cards @endif @if($loop->even) alt @endif" id="{{ $cat['key'] }}"><div class="wrap">
  <div class="sv-side">
    @if (!empty($cat['featured']))<span class="sv-star">Most important</span>@endif
    @if (!empty($cat['kicker']))<p class="sv-kicker">{{ $cat['kicker'] }}</p>@endif
    <h2>{{ $cat['label'] }}</h2>
    @if (!empty($cat['intro']))<p class="sv-intro">{{ $cat['intro'] }}</p>@endif
    @if (!empty($cat['cta']['url']))
      <a class="sv-side-cta" href="{{ url($cat['cta']['url']) }}">{{ $cat['cta']['label'] }} <span aria-hidden="true">&rarr;</span></a>
    @endif
  </div>

  @if ($isCards)
  <div class="sv-cardgrid">
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
</div></section>
@endforeach

{{-- How it works --}}
<section id="how"><div class="wrap">
  <div class="sec-head"><p class="eyebrow">How it works</p><h2>Three steps, whichever service you take</h2></div>
  <div class="sv-steps">
    <div class="sv-step"><div class="n">1</div><h3>Tell us your trip</h3><p>Use the free checker or a quick form. No card, no account.</p></div>
    <div class="sv-step"><div class="n">2</div><h3>We check &amp; prepare</h3><p>Documents, forms, appointment, the things that get people refused.</p></div>
    <div class="sv-step"><div class="n">3</div><h3>You travel</h3><p>Trackable every step, with tracked passport return.</p></div>
  </div>
  <div class="row" style="margin-top:22px"><a href="{{ url('/tools') }}" class="btn">Start with the free checker &rarr;</a></div>
</div></section>

{{-- Why us --}}
<section class="alt"><div class="wrap">
  <div class="sec-head"><p class="eyebrow">Why us</p><h2>Prevention-led, not just paperwork</h2></div>
  <div class="sv-why">
    <div><h3>Prevention-led</h3><p>We remove the avoidable reasons for refusal. Most agents just file it.</p></div>
    <div><h3>Transparent fixed fee</h3><p>Separate from the government fee, and shown upfront.</p></div>
    <div><h3>Honest</h3><p>No one can guarantee a government decision, and we never pretend otherwise.</p></div>
    <div><h3>UK-based team</h3><p>Real people on phone and WhatsApp.</p></div>
  </div>
</div></section>

{{-- FAQ --}}
<section id="faq"><div class="wrap">
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
