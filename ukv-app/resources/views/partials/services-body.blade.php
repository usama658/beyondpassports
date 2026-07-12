{{-- Services body (shared, locked). Everything below the hero: trust bar, config-driven service
     catalogue, how, why, FAQ, final CTA, scroll-spy + structured data. Rendered verbatim by the
     coded services page AND the CMS locked-include block, so both are byte-identical. Not
     CMS-editable (config-driven / structural). --}}
@php
  $statusLabels = ['available' => 'Available', 'coming-soon' => 'Coming soon', 'on-request' => 'On request'];
  $catalogue = array_slice(config('ukv.services', []), 0, 6);
  $waNumber = config('ukv.whatsapp') ?: '447882747584';
  $waFor = fn (string $title): string => 'https://wa.me/'.$waNumber.'?text='.rawurlencode("Hi Beyond Passports, I'd like help with: ".$title);
  $waGlyph = '<svg viewBox="0 0 24 24" aria-hidden="true" class="wa-g"><path d="M.057 24l1.687-6.163a11.867 11.867 0 0 1-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.817 11.817 0 0 1 8.413 3.488 11.824 11.824 0 0 1 3.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 0 1-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884a9.86 9.86 0 0 0 1.51 5.26l-.999 3.648 3.978-1.607zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>';
@endphp

{{-- Trust bar — dark mesh band (matches home .tbar-f) --}}
<section class="tbar-f"><div class="wrap"><div class="row">
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 5 6v6c0 4.5 3 7.5 7 8.5 4-1 7-4 7-8.5V6z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="m9 12 2 2 4-4.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg><span><b>Schengen visa</b> experts</span></span>
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 7v10M9.5 9.2c0-1 1.1-1.7 2.5-1.7s2.5.7 2.5 1.7-1.1 1.6-2.5 1.6-2.5.7-2.5 1.7 1.1 1.7 2.5 1.7 2.5-.7 2.5-1.7" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg><span><b>No hidden</b> fees</span></span>
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 7v5l3 2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg><span><b>7-day</b> support</span></span>
  <span class="ti">@include('partials.uk-eu-flags',['size'=>15])<span>Registered in <b>UK &amp; Europe</b></span></span>
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
  <div class="row"><a href="{{ $waFor('my upcoming trip') }}" target="_blank" rel="noopener" class="btn">{!! $waGlyph !!} Chat on WhatsApp</a><a href="{{ url('/tools') }}" class="btn btn--glass">Run the free checker</a>@include('partials.consult-cta')</div>
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
