@extends('layouts.public')

@section('title', 'Guides & stories — visa guides and real journeys | UKVisaCo')
@section('description', 'Plain-English visa guides and anonymised traveller stories for UK travellers — eVisas, ETAs, passport-validity tips and document prep. Independent service, not a government website. General info only; requirements depend on your nationality and residence.')

@section('canonical', url('/guides'))

@push('head')
<style>
  /* Page-local layout only — design system + palette live in assets/ukv.css */
  .page-hero{padding:64px 0 8px}
  .page-hero h1{font-family:var(--display);font-size:clamp(34px,5vw,54px);color:var(--navy);letter-spacing:-.015em;margin:.1em 0 .35em}
  .page-hero p.sub{font-size:18px;max-width:52ch;color:#33454f;margin:0}

  /* Category filter chips */
  .filters{display:flex;flex-wrap:wrap;gap:10px;margin:8px 0 4px}
  .chip{font-family:var(--mono);font-size:12px;letter-spacing:.08em;text-transform:uppercase;
    padding:9px 18px;border-radius:999px;border:1.5px solid var(--navy);background:transparent;
    color:var(--navy);cursor:pointer;transition:background .12s ease,color .12s ease}
  .chip:hover{background:rgba(10,37,64,.07)}
  .chip[aria-pressed="true"]{background:var(--navy);color:#fff}
  .chip:focus-visible{outline:2px solid var(--cta);outline-offset:2px}

  /* Article card grid */
  .articles{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:22px;margin:26px 0 4px}
  a.article{display:flex;flex-direction:column;text-decoration:none;color:inherit;
    background:var(--white);border:1px solid var(--paper-edge);border-radius:10px;overflow:hidden;
    transition:transform .12s ease,box-shadow .15s ease}
  a.article:hover{transform:translateY(-4px);box-shadow:0 14px 30px rgba(10,37,64,.10)}
  a.article:focus-visible{outline:2px solid var(--cta);outline-offset:3px}
  .article .band{height:8px;background:var(--navy)}
  .article[data-cat="guides"] .band{background:var(--cta)}
  .article[data-cat="tips"] .band{background:var(--stamp)}
  .article[data-cat="stories"] .band{background:var(--gold)}
  .article .body{padding:20px 20px 18px;display:flex;flex-direction:column;flex:1}
  .article .cat{font-family:var(--mono);font-size:11px;letter-spacing:.1em;text-transform:uppercase;color:var(--stamp)}
  .article h3{font-family:var(--display);font-size:20px;color:var(--navy);margin:8px 0 6px;line-height:1.18}
  .article .excerpt{font-size:15px;color:#33454f;margin:0 0 16px;flex:1}
  .article .meta{font-family:var(--mono);font-size:11px;letter-spacing:.06em;color:#6b7d87;
    display:flex;gap:10px;align-items:center;border-top:1px solid var(--paper-edge);padding-top:12px}
  .article .meta .dot{opacity:.6}

  .compliance{font-family:var(--mono);font-size:12px;color:#6b7d87;margin:22px 0 0;max-width:66ch}
</style>
@endpush

@section('content')

{{-- HERO --}}
<section class="page-hero"><div class="wrap">
  <p class="eyebrow">Guides &amp; stories</p>
  <h1>Travel-ready: visa guides &amp; real journeys</h1>
  <p class="sub">Plain-English guides to eVisas, ETAs and entry rules — plus anonymised stories from UK travellers we've helped. Practical, honest and jargon-free.</p>
</div></section>
<div class="mrz"><div class="wrap"><span>P&lt;GBR&lt;READ&lt;BEFORE&lt;YOU&lt;TRAVEL&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;</span></div></div>

{{-- ARTICLE GRID + CATEGORY FILTERS --}}
<section><div class="wrap">
  <div class="filters" role="group" aria-label="Filter guides by category">
    <button type="button" class="chip" data-filter="all" aria-pressed="true">All</button>
    <button type="button" class="chip" data-filter="guides" aria-pressed="false">Guides</button>
    <button type="button" class="chip" data-filter="tips" aria-pressed="false">Destination tips</button>
    <button type="button" class="chip" data-filter="stories" aria-pressed="false">Traveller stories</button>
  </div>

  <div class="articles" id="article-grid">
    @foreach ($guides as $slug => $guide)
      <a class="article reveal" href="{{ url('/guides/'.$slug) }}" data-cat="{{ $guide['category'] }}">
        <div class="band"></div>
        <div class="body">
          <div class="cat">{{ $guide['category_label'] }}</div>
          <h3>{{ $guide['title'] }}</h3>
          <p class="excerpt">{{ $guide['excerpt'] }}</p>
          <div class="meta">
            <span>{{ $guide['read_time'] }}</span>
            <span class="dot">·</span>
            <span>{{ $guide['date'] }}</span>
          </div>
        </div>
      </a>
    @endforeach
  </div>

  <p class="compliance">UKVisaCo is an independent service and is not a government website. Guides are general information only — exact requirements depend on your nationality, residence and trip, so always confirm before you travel. Traveller stories are anonymised; names and identifying details have been removed.</p>
</div></section>

{{-- CTA --}}
<section class="cta-band"><div class="wrap reveal">
  <div class="rule"></div>
  <h2>Ready when you are</h2>
  <p style="max-width:48ch;color:#cdd9e1">Read up, then let our UK team confirm exactly what your trip needs.</p>
  <div class="row">
    <a href="{{ url('/apply') }}" class="btn">Start my application →</a>
    <a href="{{ url('/tools') }}" class="btn btn--ghost" style="color:#fff;border-color:#fff">Check what I need</a>
  </div>
</div></section>

<script>
(function () {
  // Category filter chips — client-side, no network.
  var chips = Array.prototype.slice.call(document.querySelectorAll('.chip'));
  var cards = Array.prototype.slice.call(document.querySelectorAll('#article-grid .article'));
  function apply(filter) {
    cards.forEach(function (c) {
      var show = (filter === 'all' || c.getAttribute('data-cat') === filter);
      c.style.display = show ? '' : 'none';
    });
    chips.forEach(function (b) {
      b.setAttribute('aria-pressed', b.getAttribute('data-filter') === filter ? 'true' : 'false');
    });
  }
  chips.forEach(function (b) {
    b.addEventListener('click', function () { apply(b.getAttribute('data-filter')); });
  });
})();
</script>

@endsection
