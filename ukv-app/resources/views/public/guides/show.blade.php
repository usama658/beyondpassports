@extends('layouts.public')

@php
    use App\Enums\GuideType;
    use App\Services\RequirementService;
    use Illuminate\Support\Carbon;

    /** @var \App\Models\Guide $guide */

    // --- Resolve type + URLs (country guide vs evergreen) --------------------------------
    $type        = $guide->guide_type instanceof GuideType
        ? $guide->guide_type
        : ($guide->guide_type ? GuideType::tryFrom($guide->guide_type) : null);
    $destination = $guide->destination;            // null for evergreen
    $isCountry   = $destination !== null && $type !== null;

    // Canonical public URL for this guide.
    $guideUrl = $isCountry
        ? url('/visa/'.$destination->slug.'/'.$type->topicSlug())
        : url('/guides/'.$guide->slug);

    // --- Meta + display copy -------------------------------------------------------------
    $metaTitle = $guide->meta_title ?: ($guide->title.' | Beyond Passports');
    $metaDesc  = $guide->meta_description ?: $guide->excerpt;
    $eyebrow   = $isCountry ? $destination->name.' · '.$type->label() : 'Guide';

    // Read-time from the body word count (~200 wpm).
    $words    = str_word_count(trim(strip_tags((string) $guide->body)));
    $readTime = max(1, (int) ceil($words / 200)).' min read';

    // Dates for schema + byline.
    $published = $guide->published_at ? Carbon::parse($guide->published_at) : null;
    $modified  = $guide->reviewed_at ? Carbon::parse($guide->reviewed_at) : ($guide->updated_at ?? $published);
    $reviewedAt = $guide->reviewed_at ? Carbon::parse($guide->reviewed_at) : null;

    // Byline reviewer: the guide's named reviewer if set, else the UK case lead from config.
    // Photo/role resolved from the team list by name (falls back to the lead's own).
    $team        = collect(config('ukv.team', []));
    $lead        = $team->firstWhere('lead', true) ?? $team->first();
    $reviewerName = $guide->reviewed_by ?: data_get($lead, 'name');
    $reviewerCard = $team->firstWhere('name', $reviewerName) ?? $lead;
    $reviewerPhoto = data_get($reviewerCard, 'photo');
    $reviewerRole  = data_get($reviewerCard, 'role', 'UK Case Lead');

    // --- FAQ rows (cast to array on the model) ------------------------------------------
    $faqs = collect($guide->faq ?? [])
        ->map(fn ($f) => is_array($f) ? ['q' => $f['q'] ?? null, 'a' => $f['a'] ?? null] : null)
        ->filter(fn ($f) => $f && $f['q'] && $f['a'])
        ->values();

    // --- Hub-and-spoke link targets ------------------------------------------------------
    // DOWN: /apply?destination + the money page + the document-checklist tool.
    $applyUrl     = $isCountry ? url('/apply').'?destination='.urlencode($destination->slug) : url('/apply');
    $moneyUrl     = $isCountry ? url('/visa/'.$destination->slug) : null;
    $checklistUrl = url('/tools');     // public document-checklist / visa-checker tool

    // ACROSS: sibling cluster (other published guides for the same destination).
    $siblings = collect();
    if ($isCountry) {
        $siblings = app(\App\Services\GuideService::class)
            ->clusterFor($destination)
            ->reject(fn ($g) => $g->is($guide))
            ->values();
    }

    // --- JSON-LD (built as PHP arrays, json_encode'd; no literal @ in Blade text) --------
    $articleLd = [
        '@context' => 'https://schema.org',
        '@type'    => 'Article',
        'headline' => $guide->title,
        'description' => $guide->excerpt,
        'inLanguage' => 'en-GB',
        'datePublished' => $published?->toDateString(),
        'dateModified'  => $modified?->toDateString(),
        'author'    => ['@type' => 'Organization', 'name' => 'Beyond Passports'],
        'publisher' => ['@type' => 'Organization', 'name' => 'Beyond Passports', 'url' => url('/')],
        'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $guideUrl],
    ];
    if ($guide->reviewed_by) {
        $articleLd['reviewedBy'] = ['@type' => 'Person', 'name' => $guide->reviewed_by];
    }

    // BreadcrumbList: home > destinations > country > guide  (country crumbs only when relevant)
    $crumbs = [['name' => 'Home', 'url' => url('/')]];
    if ($isCountry) {
        $crumbs[] = ['name' => 'Destinations', 'url' => url('/destinations')];
        $crumbs[] = ['name' => $destination->name, 'url' => url('/visa/'.$destination->slug)];
        $crumbs[] = ['name' => $type->label(), 'url' => $guideUrl];
    } else {
        $crumbs[] = ['name' => 'Guides', 'url' => url('/guides')];
        $crumbs[] = ['name' => $guide->title, 'url' => $guideUrl];
    }
    $breadcrumbLd = [
        '@context' => 'https://schema.org',
        '@type'    => 'BreadcrumbList',
        'itemListElement' => collect($crumbs)->values()->map(fn ($c, $i) => [
            '@type'    => 'ListItem',
            'position' => $i + 1,
            'name'     => $c['name'],
            'item'     => $c['url'],
        ])->all(),
    ];

    $faqLd = null;
    if ($faqs->isNotEmpty()) {
        $faqLd = [
            '@context' => 'https://schema.org',
            '@type'    => 'FAQPage',
            'mainEntity' => $faqs->map(fn ($f) => [
                '@type' => 'Question',
                'name'  => $f['q'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
            ])->all(),
        ];
    }

    // HowTo schema — only for the how_to_apply type.
    $howToLd = null;
    if ($type === GuideType::HowToApply) {
        $howToLd = [
            '@context' => 'https://schema.org',
            '@type'    => 'HowTo',
            'name'     => $guide->title,
            'description' => $guide->excerpt,
            'step'     => [
                ['@type' => 'HowToStep', 'position' => 1, 'name' => 'Confirm what you need', 'text' => 'Check your nationality, passport and trip against the current entry rules for your destination.'],
                ['@type' => 'HowToStep', 'position' => 2, 'name' => 'Prepare your documents', 'text' => 'Gather the documents on your checklist — passport, photo and trip details — before you start.'],
                ['@type' => 'HowToStep', 'position' => 3, 'name' => 'Complete and submit', 'text' => 'Enter your details accurately and submit your application, then keep your reference to track it.'],
            ],
        ];
    }
@endphp

@section('title', $metaTitle)
@section('description', $metaDesc)
@section('canonical', $guideUrl)

@push('head')
<script type="application/ld+json">{!! json_encode($articleLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode($breadcrumbLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@if ($faqLd)
<script type="application/ld+json">{!! json_encode($faqLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endif
@if ($howToLd)
<script type="application/ld+json">{!! json_encode($howToLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endif
<style>
  /* guides/show — page-local layout only. Design system lives in assets/ukv.css */

  /* ---- ARTICLE HEADER ---------------------------------------------- */
  .gs-head {
    background: linear-gradient(180deg, #EAF1F4 0%, #F2F5F6 60%, var(--paper) 100%);
    border-bottom: 1px solid var(--paper-edge);
    padding: 56px 0 64px;
  }

  /* breadcrumb */
  .gs-crumb {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0 4px;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: var(--muted);
    margin: 0 0 26px;
  }
  .gs-crumb a { color: var(--stamp-text); text-decoration: none }
  .gs-crumb a:hover { text-decoration: underline }
  .gs-crumb .sep { color: var(--paper-edge); margin: 0 2px; font-weight: 400 }
  .gs-crumb [aria-current] { color: var(--ink) }

  /* title block */
  .gs-head h1 {
    font-size: clamp(32px, 4.6vw, 52px);
    font-weight: 800;
    letter-spacing: -.03em;
    color: var(--navy);
    max-width: 22ch;
    line-height: 1.1;
    margin: 10px 0 0;
  }
  .gs-standfirst {
    font-size: 20px;
    line-height: 1.6;
    color: #33454f;
    max-width: 62ch;
    margin: 18px 0 0;
    font-weight: 400;
  }
  .gs-meta {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px 18px;
    margin: 20px 0 0;
  }
  .gs-meta-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--white);
    border: 1px solid var(--paper-edge);
    border-radius: 999px;
    padding: 6px 14px;
    font-size: 12.5px;
    font-weight: 600;
    color: var(--muted);
    box-shadow: 0 2px 6px -3px rgba(40,50,70,.12);
  }

  /* author/reviewer byline under the standfirst (E-E-A-T) */
  .gs-byline{display:flex;align-items:center;gap:11px;margin:18px 0 0}
  .gs-byline img{width:40px;height:40px;border-radius:50%;object-fit:cover;border:2px solid #fff;box-shadow:0 5px 14px -8px rgba(30,40,60,.5)}
  .gs-byline .b1{font:700 13.5px var(--display);color:var(--ink)}
  .gs-byline .b1 a{color:var(--stamp-text);text-decoration:none}
  .gs-byline .b1 a:hover{text-decoration:underline}
  .gs-byline .b2{font-size:12.5px;color:var(--muted);margin-top:1px}
  .gs-byline .dot{color:var(--paper-edge)}

  /* split header — copy + "At a glance" facts card (pick C) */
  .gs-head .gs-grid{display:grid;grid-template-columns:1.5fr .9fr;gap:38px;align-items:start;margin-top:8px}
  @media (max-width:820px){.gs-head .gs-grid{grid-template-columns:1fr;gap:26px}}
  /* balance the two columns: smaller title + standfirst than the full-width default */
  .gs-head .gs-grid h1{font-size:clamp(28px,3.8vw,42px);font-weight:700;max-width:18ch}
  .gs-head .gs-grid .gs-standfirst{font-size:17px;max-width:48ch}
  .gs-head .gs-grid .eyebrow{color:var(--cta);font-weight:800;font-size:12px;letter-spacing:.14em;text-transform:uppercase}
  .gs-facts{background:var(--white);border:1px solid var(--paper-edge);border-radius:16px;padding:20px 22px;box-shadow:0 16px 40px -32px rgba(40,50,70,.5)}
  .gs-facts .k{font-family:var(--body);font-size:11px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--stamp);margin:0 0 12px}
  .gs-facts .row{display:flex;align-items:center;gap:10px;padding:10px 0;border-top:1px solid var(--paper-edge);font-size:13.5px}
  .gs-facts .row:first-of-type{border-top:0}
  .gs-facts .row svg{width:16px;height:16px;flex:0 0 16px;fill:none;stroke:var(--cta);stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
  .gs-facts .row .lab{color:var(--muted)}
  .gs-facts .row .val{margin-left:auto;color:var(--navy);font-weight:700;text-align:right}
  .gs-meta-chip b { color: var(--ink) }

  /* ---- ARTICLE BODY LAYOUT ----------------------------------------- */
  .gs-layout {
    display: grid;
    grid-template-columns: 1fr min(70ch, 100%) 1fr;
    padding: 56px 0 0;
  }
  .gs-layout > * { grid-column: 2 }

  /* full-bleed within the centred column */
  .gs-bleed {
    grid-column: 1 / -1;
    padding: 0 24px;
  }

  /* ---- BODY PROSE -------------------------------------------------- */
  .gs-body {
    max-width: 70ch;
    margin: 0 auto;
    padding: 0 24px;
  }
  .gs-body p {
    font-size: 17px;
    line-height: 1.7;
    color: var(--ink);
    margin: 0 0 1.2em;
  }
  .gs-body h2 {
    font-size: clamp(22px, 2.4vw, 27px);
    font-weight: 600;
    color: var(--navy);
    letter-spacing: -.015em;
    margin: 1.8em 0 .5em;
    line-height: 1.2;
  }
  .gs-body h3 {
    font-size: 19px;
    font-weight: 600;
    color: var(--navy);
    letter-spacing: -.01em;
    margin: 1.5em 0 .4em;
    line-height: 1.25;
  }
  .gs-body ul,
  .gs-body ol {
    margin: 0 0 1.4em;
    padding-left: 1.3em;
  }
  .gs-body li {
    font-size: 17px;
    line-height: 1.62;
    margin: 0 0 .5em;
  }
  .gs-body strong { color: var(--navy) }
  .gs-body a { color: var(--stamp-text); text-decoration: underline }
  .gs-body a:hover { color: var(--cta) }

  /* ---- QUICK-ANSWER CALLOUT --------------------------------------- */
  /* Quick answer — navy mini-card (pick B) */
  .gs-callout {
    position: relative;
    overflow: hidden;
    background: var(--navy);
    border: 0;
    border-radius: 16px;
    padding: 22px 24px;
    margin: 12px 0 2em;
    color: #fff;
    box-shadow: 0 20px 50px -34px rgba(0,0,0,.6);
  }
  .gs-callout::before {
    content: "";
    position: absolute;
    inset: 0;
    background:
      radial-gradient(70% 80% at 92% 0, rgba(21,94,122,.30), transparent 60%),
      radial-gradient(60% 70% at 0 100%, rgba(46,154,140,.28), transparent 62%);
  }
  .gs-callout > * { position: relative; z-index: 2 }
  .gs-callout-head { display: flex; align-items: center; gap: 10px; margin: 0 0 8px }
  .gs-callout-head .i {
    width: 30px; height: 30px; border-radius: 8px;
    background: rgba(169,204,218,.16); color: var(--soft);
    display: flex; align-items: center; justify-content: center; flex: 0 0 30px;
  }
  .gs-callout-head .i svg { width: 17px; height: 17px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round }
  .gs-callout .gs-callout-label {
    font-family: var(--body);
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: var(--soft);
    margin: 0;
  }
  .gs-callout p {
    font-size: 18px;
    font-weight: 400;
    line-height: 1.6;
    margin: 0;
    color: rgba(255,255,255,.9);
  }

  /* ---- E-E-A-T BYLINE ---------------------------------------------- */
  .gs-byline {
    display: flex;
    align-items: center;
    gap: 14px;
    border-top: 1px solid var(--paper-edge);
    border-bottom: 1px solid var(--paper-edge);
    padding: 16px 0;
    margin: 2em 0 1.6em;
  }
  .gs-byline-icon {
    flex: 0 0 36px;
    height: 36px;
    background: var(--stamp);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 15px;
    font-weight: 800;
  }
  .gs-byline-text {
    font-size: 13px;
    color: var(--muted);
    line-height: 1.5;
  }
  .gs-byline-text strong { color: var(--navy) }

  /* ---- COMPLIANCE DISCLAIMER --------------------------------------- */
  .gs-disclaimer {
    max-width: 70ch;
    margin: 36px auto;
    background: #f7fafb;
    border: 1px solid var(--paper-edge);
    border-left: 3px solid var(--gold);
    border-radius: 12px;
    padding: 20px 24px;
  }
  .gs-disclaimer p {
    font-size: 13.5px;
    line-height: 1.65;
    color: #3a4b55;
    margin: 0 0 10px;
  }
  .gs-disclaimer p:last-child { margin-bottom: 0 }
  .gs-disclaimer strong { color: var(--navy) }

  /* ---- LIVE CHECKLIST ---------------------------------------------- */
  .gs-checklist {
    max-width: 70ch;
    margin: 32px auto;
    padding: 0 24px;
  }

  /* ---- INLINE CTA -------------------------------------------------- */
  .gs-cta-inline {
    max-width: 70ch;
    margin: 40px auto;
    padding: 32px 36px;
    background: var(--white);
    border: 1px solid var(--paper-edge);
    border-radius: 18px;
    box-shadow: var(--shadow);
  }
  .gs-cta-inline h2 {
    font-size: 26px;
    font-weight: 700;
    color: var(--navy);
    margin: 0 0 .4em;
    letter-spacing: -.02em;
  }
  .gs-cta-inline p {
    font-size: 16px;
    color: #33454f;
    margin: 0 0 20px;
    max-width: 52ch;
    line-height: 1.6;
  }
  .gs-cta-inline .row { display: flex; gap: 12px; flex-wrap: wrap }

  /* ---- HUB SPOKES -------------------------------------------------- */
  .gs-spokes {
    max-width: 70ch;
    margin: 0 auto;
    padding: 0 24px 64px;
  }
  .gs-spokes-head { margin-bottom: 10px }
  .gs-spokes ul { list-style: none; margin: 0; padding: 0 }
  .gs-spokes li {
    border-top: 1px solid var(--paper-edge);
    padding: 16px 0;
  }
  .gs-spokes li:last-child { border-bottom: 1px solid var(--paper-edge) }
  .gs-spokes a {
    font-size: 17px;
    font-weight: 600;
    color: var(--navy);
    text-decoration: none;
    display: inline-flex;
    align-items: baseline;
    gap: 7px;
    transition: color .12s ease;
  }
  .gs-spokes a:hover { color: var(--cta) }
  .gs-spokes a::after { content: '→'; opacity: .45; font-size: 14px; transition: opacity .12s ease, transform .12s ease }
  .gs-spokes a:hover::after { opacity: 1; transform: translateX(3px) }
  .gs-spokes .gs-spokes-label {
    display: block;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: var(--muted);
    margin-top: 4px;
  }

  /* ---- TWO-COLUMN SHELL — article + sticky merged sidebar (pick B) ------ */
  .gs-shell{display:grid;grid-template-columns:minmax(0,1fr) 320px;gap:48px;align-items:start;padding:48px 0 64px}
  .gs-shell .gs-body{max-width:none;margin:0;padding:0}
  .gs-rail{position:sticky;top:20px}
  .gs-rail-panel{border:1px solid var(--paper-edge);border-radius:16px;background:var(--white);overflow:hidden;box-shadow:0 16px 40px -34px rgba(40,50,70,.5)}
  /* navy checker header */
  .gs-rail-cta{position:relative;overflow:hidden;background:var(--navy);color:#fff;padding:22px 22px}
  .gs-rail-cta::before{content:"";position:absolute;inset:0;background:radial-gradient(70% 80% at 92% 0,rgba(21,94,122,.32),transparent 60%),radial-gradient(60% 70% at 0 100%,rgba(46,154,140,.3),transparent 62%)}
  .gs-rail-cta>*{position:relative;z-index:2}
  .gs-rail-cta .k{font-family:var(--body);font-size:11px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:var(--soft);margin:0 0 6px}
  .gs-rail-cta h3{font-family:var(--display);font-size:17px;font-weight:700;letter-spacing:-.01em;color:#fff;margin:0 0 8px}
  .gs-rail-cta p{font-size:13.5px;line-height:1.5;color:rgba(255,255,255,.8);margin:0 0 14px}
  .gs-rail-cta .rb{display:flex;align-items:center;justify-content:center;gap:7px;width:100%;font-family:var(--body);font-weight:700;font-size:14px;border-radius:11px;padding:12px;text-decoration:none}
  .gs-rail-cta .rb-primary{background:var(--cta);color:#fff}
  .gs-rail-cta .rb-ghost{background:transparent;border:1px solid rgba(255,255,255,.25);color:#fff;margin-top:8px}
  .gs-rail-cta .rb svg{width:16px;height:16px;flex:0 0 16px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
  /* rail sections */
  .gs-rail-sec{padding:18px 22px;border-top:1px solid var(--paper-edge)}
  .gs-rail-sec .k{font-family:var(--body);font-size:11px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--stamp-text);margin:0 0 4px}
  .gs-rail-sec ul{list-style:none;margin:0;padding:0}
  .gs-rail-sec li a{display:block;padding:11px 0;border-top:1px solid var(--paper-edge);text-decoration:none}
  .gs-rail-sec li:first-child a{border-top:0}
  .gs-rail-sec li a b{display:block;font-size:14px;font-weight:700;color:var(--navy);line-height:1.3}
  .gs-rail-sec li a .gs-rail-lab{display:block;font-size:11.5px;color:var(--muted);margin-top:2px}
  .gs-rail-sec li a:hover b{color:var(--cta)}
  .gs-rail-comp .cbadge{display:inline-flex;align-items:center;gap:6px;font-size:10px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:var(--stamp-text);margin:0 0 8px}
  .gs-rail-comp .cbadge svg{width:13px;height:13px;flex:0 0 13px;fill:none;stroke:var(--stamp);stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
  .gs-rail-comp p{font-size:11.5px;line-height:1.55;color:var(--muted);margin:0 0 8px}
  .gs-rail-comp p:last-child{margin-bottom:0}
  .gs-rail-comp strong{color:var(--navy)}

  @media (max-width: 980px){
    .gs-shell{grid-template-columns:1fr;gap:32px;padding-top:36px}
    .gs-rail{position:static}
  }
  @media (max-width: 860px) {
    .gs-head { padding: 40px 0 48px }
    .gs-body p, .gs-body li { font-size: 17px }
    .gs-standfirst { font-size: 18px }
    .gs-layout { padding-top: 40px }
    .gs-cta-inline { padding: 24px 20px }
  }
</style>
@endpush

@section('content')

<article itemscope itemtype="https://schema.org/Article">

{{-- ARTICLE HEADER —  sky-gradient title block --}}
<header class="gs-head">
  <div class="wrap">

    {{-- Breadcrumb --}}
    <nav class="gs-crumb" aria-label="Breadcrumb">
      @foreach ($crumbs as $crumb)
        @if ($loop->last)
          <span aria-current="page">{{ $crumb['name'] }}</span>
        @else
          <a href="{{ $crumb['url'] }}">{{ $crumb['name'] }}</a>
          <span class="sep" aria-hidden="true">/</span>
        @endif
      @endforeach
    </nav>

    <div class="gs-grid">
      <div>
        <p class="eyebrow">{{ $eyebrow }}</p>
        <h1 itemprop="headline">{{ $guide->title }}</h1>
        <p class="gs-standfirst" itemprop="description">{{ $guide->excerpt }}</p>
        @if ($reviewerName)
        <div class="gs-byline">
          @if ($reviewerPhoto)<img src="{{ asset(ltrim($reviewerPhoto, '/')) }}" alt="{{ $reviewerName }}">@endif
          <div>
            <div class="b1">{{ $reviewedAt ? 'Reviewed' : 'Written' }} by <a href="{{ url('/about') }}#where-we-are">{{ $reviewerName }}</a></div>
            <div class="b2">{{ $reviewerRole }} <span class="dot">·</span> {{ $readTime }}@if ($reviewedAt || $published) <span class="dot">·</span> {{ $reviewedAt ? 'Updated' : 'Published' }} {{ ($reviewedAt ?? $published)->isoFormat('D MMM YYYY') }}@endif</div>
          </div>
        </div>
        @endif
      </div>

      {{-- At-a-glance facts card (pick C) --}}
      <aside class="gs-facts" aria-label="At a glance">
        <p class="k">At a glance</p>
        <div class="row">
          <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M3 7v5l9 9 7-7-9-9H3z"/><circle cx="7.5" cy="7.5" r="1.3"/></svg>
          <span class="lab">Topic</span><span class="val">{{ $type ? $type->label() : 'Guide' }}</span>
        </div>
        <div class="row">
          <svg aria-hidden="true" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
          <span class="lab">Read time</span><span class="val">{{ $readTime }}</span>
        </div>
        @if ($published)
        <div class="row">
          <svg aria-hidden="true" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
          <span class="lab">{{ $reviewedAt ? 'Updated' : 'Published' }}</span><span class="val">{{ ($reviewedAt ?? $published)->isoFormat('D MMM YYYY') }}</span>
        </div>
        @endif
        <div class="row">
          <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M12 2 4 5v6c0 5 3.5 8 8 11 4.5-3 8-6 8-11V5l-8-3z"/><path d="m9 12 2 2 4-4"/></svg>
          <span class="lab">Source</span><span class="val">{{ $reviewedAt ? 'Checked vs gov.uk' : 'Official sources' }}</span>
        </div>
      </aside>
    </div>

  </div>
</header>

{{-- ARTICLE BODY + STICKY SIDEBAR (pick B) --}}
<div class="wrap gs-shell">

  <div class="gs-main">
    <div class="gs-body" itemprop="articleBody">

      {{-- QUICK ANSWER --}}
      @if ($guide->quick_answer)
        <div class="gs-callout">
          <div class="gs-callout-head">
            <span class="i" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M13 2 4 14h6l-1 8 9-12h-6l1-8z"/></svg></span>
            <p class="gs-callout-label">Quick answer</p>
          </div>
          <p>{!! $guide->quick_answer !!}</p>
        </div>
      @endif

      {{-- BODY (trusted, reviewer-approved HTML from the model) --}}
      {!! $guide->body !!}

      {{-- E-E-A-T BYLINE --}}
      @if ($reviewedAt && $guide->reviewed_by)
        <div class="gs-byline">
          <div class="gs-byline-icon" aria-hidden="true">✓</div>
          <p class="gs-byline-text">
            Reviewed <strong>{{ $reviewedAt->isoFormat('D MMMM YYYY') }}</strong>
            by <strong>{{ $guide->reviewed_by }}</strong> — facts checked against the official source.
          </p>
        </div>
      @endif

    </div>{{-- /.gs-body --}}

    {{-- LIVE DOCUMENT CHECKLIST — documents type only, never stale (RequirementService::preview) --}}
    @if ($type === GuideType::Documents && $destination)
      @php
        $docItems = app(RequirementService::class)->preview($destination);
      @endphp
      @if (! empty($docItems))
        <div class="gs-checklist reveal">
          @include('partials.doc-checklist', ['items' => $docItems, 'personalised' => false])
        </div>
      @endif
    @endif
  </div>{{-- /.gs-main --}}

  {{-- STICKY MERGED SIDEBAR --}}
  <aside class="gs-rail" aria-label="More from Beyond Passports">
    <div class="gs-rail-panel reveal">

      {{-- Checker CTA --}}
      <div class="gs-rail-cta">
        <p class="k">Not sure?</p>
        <h3>What does your trip need?</h3>
        <p>Answer a few quick questions — our free checker shows whether you need an ETA, a visa, or nothing at all.</p>
        <a href="{{ $checklistUrl }}" class="rb rb-primary">Use the free checker <svg viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
        <a href="{{ $applyUrl }}" class="rb rb-ghost">Start an application</a>
      </div>

      {{-- Related guides --}}
      <div class="gs-rail-sec">
        <p class="k">Keep reading</p>
        <ul>
          @foreach ($siblings as $sib)
            <li><a href="{{ url('/visa/'.$destination->slug.'/'.($sib->guide_type instanceof GuideType ? $sib->guide_type->topicSlug() : GuideType::from($sib->guide_type)->topicSlug())) }}"><b>{{ $sib->title }}</b><span class="gs-rail-lab">{{ $destination->name }} · {{ $sib->guide_type instanceof GuideType ? $sib->guide_type->label() : 'Guide' }}</span></a></li>
          @endforeach
          @if ($moneyUrl)
            <li><a href="{{ $moneyUrl }}"><b>{{ $destination->name }} visa — prepared &amp; checked</b><span class="gs-rail-lab">Up to the {{ $destination->name }} hub</span></a></li>
          @endif
          <li><a href="{{ $checklistUrl }}"><b>Check what your trip needs</b><span class="gs-rail-lab">Free document &amp; visa checker</span></a></li>
          <li><a href="{{ url('/guides') }}"><b>All travel guides</b><span class="gs-rail-lab">Index</span></a></li>
        </ul>
      </div>

      {{-- Compliance --}}
      <div class="gs-rail-sec gs-rail-comp">
        <p class="cbadge"><svg viewBox="0 0 24 24"><path d="M12 2 4 5v6c0 5 3.5 8 8 11 4.5-3 8-6 8-11V5l-8-3z"/><path d="m9 12 2 2 4-4"/></svg>Not a govt site</p>
        <p><strong>Beyond Passports is an independent service and is not a government website</strong> or affiliated with any official body.</p>
        <p>General information only — exact requirements depend on your nationality, residence and trip, so confirm the current rules at the official source.</p>
        <p>Our service fee is <strong>separate from any government fee</strong>. <strong>No service can guarantee a government decision</strong> — the outcome is decided solely by the relevant authorities.</p>
      </div>

    </div>
  </aside>

</div>{{-- /.gs-shell --}}

</article>

{{-- CTA BAND --}}
<section class="cta-band"><div class="wrap reveal">
  <div class="rule"></div>
  <h2>Let's get you travelling</h2>
  <p style="max-width:48ch;color:#eef0f1">Check what your trip needs, or start your application — our UK-based team removes the avoidable causes of refusal before it's submitted.</p>
  <div class="row">
    <a href="{{ $applyUrl }}" class="btn">Start my application →</a>
    <a href="{{ $checklistUrl }}" class="btn btn--glass">Check what I need</a>
  </div>
</div></section>

@endsection
