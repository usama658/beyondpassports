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
    $metaTitle = $guide->meta_title ?: ($guide->title.' | UKVisaCo');
    $metaDesc  = $guide->meta_description ?: $guide->excerpt;
    $eyebrow   = $isCountry ? $destination->name.' · '.$type->label() : 'Guide';

    // Read-time from the body word count (~200 wpm).
    $words    = str_word_count(trim(strip_tags((string) $guide->body)));
    $readTime = max(1, (int) ceil($words / 200)).' min read';

    // Dates for schema + byline.
    $published = $guide->published_at ? Carbon::parse($guide->published_at) : null;
    $modified  = $guide->reviewed_at ? Carbon::parse($guide->reviewed_at) : ($guide->updated_at ?? $published);
    $reviewedAt = $guide->reviewed_at ? Carbon::parse($guide->reviewed_at) : null;

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
        'author'    => ['@type' => 'Organization', 'name' => 'UKVisaCo'],
        'publisher' => ['@type' => 'Organization', 'name' => 'UKVisaCo', 'url' => url('/')],
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
  /* Page-local layout only — design system lives in assets/ukv.css */
  .article-head{padding:56px 0 0}
  .breadcrumb{font-family:var(--mono);font-size:12px;letter-spacing:.06em;color:var(--muted);margin:0 0 22px}
  .breadcrumb a{color:var(--stamp-text)}
  .breadcrumb span[aria-current]{color:var(--ink)}
  .article-head h1{font-size:clamp(32px,4.6vw,52px);color:var(--navy);letter-spacing:-.015em;max-width:20ch}
  .article-meta{font-family:var(--mono);font-size:12px;letter-spacing:.08em;color:var(--muted);margin:14px 0 0}
  .standfirst{font-size:20px;line-height:1.5;color:#33454f;max-width:60ch;margin:18px 0 0}
  /* readable long-form column */
  .article-body{max-width:70ch;margin:0 auto;padding:8px 0}
  .article-body p{font-size:18px;line-height:1.75;color:var(--ink);margin:0 0 1.2em}
  .article-body h2{font-size:clamp(24px,3vw,30px);color:var(--navy);margin:1.7em 0 .5em;line-height:1.2}
  .article-body h3{font-size:21px;color:var(--navy);margin:1.4em 0 .4em;line-height:1.25}
  .article-body ul,.article-body ol{margin:0 0 1.3em;padding-left:1.2em}
  .article-body li{font-size:18px;line-height:1.7;margin:0 0 .5em}
  .article-body strong{color:var(--navy)}
  .article-body a{text-decoration:underline}
  /* callout — uses .alt surface tokens */
  .callout{background:var(--white);border:1px solid var(--paper-edge);border-left:4px solid var(--stamp);border-radius:10px;padding:22px 24px;margin:1.6em 0 1.8em;box-shadow:var(--shadow)}
  .callout .k{font-family:var(--mono);font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:var(--stamp-text);margin:0 0 .5em}
  .callout p{font-size:17px;margin:0;line-height:1.65}
  /* E-E-A-T byline */
  .byline{font-family:var(--mono);font-size:12px;letter-spacing:.04em;color:var(--muted);
    border-top:1px solid var(--paper-edge);border-bottom:1px solid var(--paper-edge);
    padding:12px 0;margin:1.6em 0;max-width:70ch}
  .byline strong{color:var(--navy)}
  /* mandatory compliance disclaimer block */
  .disclaimer{max-width:70ch;margin:28px auto;background:#f7fafb;border:1px solid var(--paper-edge);
    border-left:3px solid var(--gold);border-radius:8px;padding:16px 20px}
  .disclaimer p{font-size:13.5px;line-height:1.6;color:#3a4b55;margin:0 0 8px}
  .disclaimer p:last-child{margin-bottom:0}
  .disclaimer strong{color:var(--navy)}
  /* live checklist wrapper */
  .live-checklist{max-width:70ch;margin:24px auto}
  /* hub-and-spoke spoke links */
  .spokes{max-width:70ch;margin:0 auto}
  .spokes ul{list-style:none;margin:0;padding:0}
  .spokes li{border-top:1px solid var(--paper-edge);padding:14px 0}
  .spokes li:last-child{border-bottom:1px solid var(--paper-edge)}
  .spokes a{font-family:var(--display);font-size:19px;color:var(--navy);font-weight:500;text-decoration:none}
  .spokes a:hover{color:var(--cta)}
  .spokes .k{font-family:var(--mono);font-size:11px;letter-spacing:.1em;color:var(--muted);text-transform:uppercase;display:block;margin-top:3px}
  /* inline CTA band */
  .cta-inline{max-width:70ch;margin:32px auto;border-radius:12px;padding:26px 28px}
  .cta-inline h2{font-size:24px;color:var(--navy);margin:0 0 .3em}
  .cta-inline p{font-size:16px;color:#33454f;margin:0 0 16px;max-width:52ch}
  .cta-inline .row{display:flex;gap:12px;flex-wrap:wrap}
  @media (max-width:860px){
    .article-body p,.article-body li{font-size:17px}
    .standfirst{font-size:18px}
  }
</style>
@endpush

@section('content')

<article>
{{-- ARTICLE HEADER + BREADCRUMB --}}
<header class="article-head"><div class="wrap">
  <nav class="breadcrumb" aria-label="Breadcrumb">
    @foreach ($crumbs as $i => $crumb)
      @if ($loop->last)
        <span aria-current="page">{{ $crumb['name'] }}</span>
      @else
        <a href="{{ $crumb['url'] }}">{{ $crumb['name'] }}</a> /
      @endif
    @endforeach
  </nav>
  <p class="eyebrow">{{ $eyebrow }}</p>
  <h1>{{ $guide->title }}</h1>
  <p class="article-meta">
    @if ($published){{ $published->toDateString() }} · @endif{{ $readTime }} · by UKVisaCo team
  </p>
  <p class="standfirst">{{ $guide->excerpt }}</p>
</div></header>
<div class="mrz"><div class="wrap"><span>UKV&lt;GUIDE&lt;PLAIN&lt;ENGLISH&lt;READ&lt;BEFORE&lt;YOU&lt;TRAVEL&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;</span></div></div>

{{-- ARTICLE BODY --}}
<section><div class="wrap">
<div class="article-body">

  {{-- QUICK ANSWER --}}
  @if ($guide->quick_answer)
  <div class="callout">
    <p class="k">Quick answer</p>
    <p>{!! $guide->quick_answer !!}</p>
  </div>
  @endif

  {{-- BODY (trusted, reviewer-approved HTML from the model) --}}
  {!! $guide->body !!}

  {{-- E-E-A-T BYLINE --}}
  @if ($reviewedAt && $guide->reviewed_by)
  <p class="byline">Reviewed {{ $reviewedAt->isoFormat('D MMMM YYYY') }} by <strong>{{ $guide->reviewed_by }}</strong> — facts checked against the official source.</p>
  @endif

</div>
</div></section>

{{-- LIVE DOCUMENT CHECKLIST — documents type only, never stale (RequirementService::preview) --}}
@if ($type === GuideType::Documents && $destination)
  @php
    $docItems = app(RequirementService::class)->preview($destination);
  @endphp
  @if (! empty($docItems))
  <section><div class="wrap">
    <div class="live-checklist reveal">
      @include('partials.doc-checklist', ['items' => $docItems, 'personalised' => false])
    </div>
  </div></section>
  @endif
@endif

{{-- MANDATORY COMPLIANCE DISCLAIMER --}}
<div class="wrap">
<div class="disclaimer">
  <p><strong>UKVisaCo is an independent service and is not a government website</strong> or affiliated with any government or official body.</p>
  <p>This guide is general information only — exact requirements depend on your nationality, residence and trip, so always confirm the current rules at the official source before you travel.</p>
  <p>Our service fee is <strong>separate from any government fee</strong>, which is shown clearly before you pay. We prepare and check your application for that fee.</p>
  <p><strong>No service can guarantee a government decision.</strong> The outcome of any application is decided solely by the relevant authorities.</p>
</div>
</div>

{{-- INLINE CTA (DOWN: apply) --}}
<div class="wrap">
<div class="cta-inline alt">
  <h2>Not sure what your trip needs?</h2>
  <p>Answer a few quick questions and our free checker shows whether your trip needs an ETA, a visa, or nothing at all.</p>
  <div class="row">
    <a href="{{ $checklistUrl }}" class="btn">Use our free checker →</a>
    <a href="{{ $applyUrl }}" class="btn btn--ghost">Ready? Start your application</a>
  </div>
</div>
</div>

{{-- HUB-AND-SPOKE LINKS --}}
<section><div class="wrap">
<div class="spokes">
  <div class="sec-head reveal" style="margin-bottom:18px">
    <p class="eyebrow">Keep reading</p>
    <h2 style="font-size:clamp(24px,3vw,30px);color:var(--navy)">Related</h2>
  </div>
  <ul>
    {{-- ACROSS: sibling cluster --}}
    @foreach ($siblings as $sib)
      <li>
        <a href="{{ url('/visa/'.$destination->slug.'/'.($sib->guide_type instanceof GuideType ? $sib->guide_type->topicSlug() : GuideType::from($sib->guide_type)->topicSlug())) }}">{{ $sib->title }}</a>
        <span class="k">{{ $destination->name }} · {{ $sib->guide_type instanceof GuideType ? $sib->guide_type->label() : 'Guide' }}</span>
      </li>
    @endforeach

    {{-- UP: country money-page hub --}}
    @if ($moneyUrl)
    <li>
      <a href="{{ $moneyUrl }}">{{ $destination->name }} visa — prepared &amp; checked</a>
      <span class="k">Up to the {{ $destination->name }} hub</span>
    </li>
    @endif

    {{-- DOWN: money page already linked above for country; tools + all guides for everyone --}}
    <li>
      <a href="{{ $checklistUrl }}">Check what your trip needs</a>
      <span class="k">Free document &amp; visa checker</span>
    </li>
    <li>
      <a href="{{ url('/guides') }}">All travel guides</a>
      <span class="k">Index</span>
    </li>
  </ul>
</div>
</div></section>

</article>

{{-- CTA BAND --}}
<section class="cta-band"><div class="wrap reveal">
  <div class="rule"></div>
  <h2>Let's get you travelling</h2>
  <p style="max-width:48ch;color:#cdd9e1">Check what your trip needs, or start your application with our UK-based team.</p>
  <div class="row">
    <a href="{{ $applyUrl }}" class="btn">Start my application →</a>
    <a href="{{ $checklistUrl }}" class="btn btn--ghost" style="color:#fff;border-color:#fff">Check what I need</a>
  </div>
</div></section>

@endsection
