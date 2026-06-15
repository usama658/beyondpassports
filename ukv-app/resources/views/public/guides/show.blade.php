@extends('layouts.public')

@section('title', $guide['title'].' | UKVisaCo Guides')
@section('description', $guide['excerpt'].' Independent service, not a government website. General guidance only.')

@section('canonical', url('/guides/'.$slug))

@push('head')
<script type="application/ld+json">
{!! json_encode([
  '@context' => 'https://schema.org',
  '@type' => 'Article',
  'headline' => $guide['title'],
  'description' => $guide['excerpt'],
  'datePublished' => $guide['date_iso'],
  'dateModified' => $guide['date_iso'],
  'inLanguage' => 'en-GB',
  'author' => ['@type' => 'Organization', 'name' => 'UKVisaCo'],
  'publisher' => ['@type' => 'Organization', 'name' => 'UKVisaCo'],
  'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => url('/guides/'.$slug)],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
<style>
  /* Page-local layout only — design system lives in assets/ukv.css */
  .article-head{padding:56px 0 0}
  .breadcrumb{font-family:var(--mono);font-size:12px;letter-spacing:.06em;color:var(--muted);margin:0 0 22px}
  .breadcrumb a{color:var(--stamp-text)}
  .breadcrumb span[aria-current]{color:var(--ink)}
  .article-head h1{font-size:clamp(32px,4.6vw,52px);color:var(--navy);letter-spacing:-.015em;max-width:18ch}
  .article-meta{font-family:var(--mono);font-size:12px;letter-spacing:.08em;color:var(--muted);margin:14px 0 0}
  .standfirst{font-size:20px;line-height:1.5;color:#33454f;max-width:60ch;margin:18px 0 0}
  /* readable long-form column */
  .article-body{max-width:70ch;margin:0 auto;padding:8px 0}
  .article-body p{font-size:18px;line-height:1.75;color:var(--ink);margin:0 0 1.2em}
  .article-body h2{font-size:clamp(24px,3vw,30px);color:var(--navy);margin:1.7em 0 .5em;line-height:1.2}
  .article-body h3{font-size:21px;color:var(--navy);margin:1.4em 0 .4em;line-height:1.25}
  .article-body ul{margin:0 0 1.3em;padding-left:1.2em}
  .article-body li{font-size:18px;line-height:1.7;margin:0 0 .5em}
  .article-body strong{color:var(--navy)}
  .article-body a{text-decoration:underline}
  .inline-note{font-size:15px;color:var(--muted);border-left:3px solid var(--gold);padding:4px 0 4px 14px;margin:0 0 1.3em;font-style:italic}
  /* callout — uses .alt surface tokens */
  .callout{background:var(--white);border:1px solid var(--paper-edge);border-left:4px solid var(--stamp);border-radius:10px;padding:22px 24px;margin:1.6em 0 1.8em;box-shadow:var(--shadow)}
  .callout .k{font-family:var(--mono);font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:var(--stamp-text);margin:0 0 .5em}
  .callout p{font-size:17px;margin:0;line-height:1.65}
  /* related guides */
  .related{max-width:70ch;margin:0 auto}
  .related ul{list-style:none;margin:0;padding:0}
  .related li{border-top:1px solid var(--paper-edge);padding:14px 0}
  .related li:last-child{border-bottom:1px solid var(--paper-edge)}
  .related a{font-family:var(--display);font-size:19px;color:var(--navy);font-weight:500;text-decoration:none}
  .related a:hover{color:var(--cta)}
  .related .k{font-family:var(--mono);font-size:11px;letter-spacing:.1em;color:var(--muted);text-transform:uppercase;display:block;margin-top:3px}
  /* inline CTA band */
  .cta-inline{max-width:70ch;margin:32px auto;border-radius:12px;padding:26px 28px}
  .cta-inline h2{font-size:24px;color:var(--navy);margin:0 0 .3em}
  .cta-inline p{font-size:16px;color:#33454f;margin:0 0 16px;max-width:52ch}
  .cta-inline .row{display:flex;gap:12px;flex-wrap:wrap}
  /* Page-local gold focus ring removed: gold-on-white ≈2:1 failed 1.4.11 non-text contrast.
     The canonical 3px --cta ring in ukv.css (and its light dark-surface variant) now applies. (audit S5) */
  @media (max-width:860px){
    .article-body p,.article-body li{font-size:17px}
    .standfirst{font-size:18px}
  }
</style>
@endpush

@section('content')

<article>
{{-- ARTICLE HEADER --}}
<header class="article-head"><div class="wrap">
  <nav class="breadcrumb" aria-label="Breadcrumb">
    <a href="{{ url('/guides') }}">Guides</a> / <span aria-current="page">{{ $guide['title'] }}</span>
  </nav>
  <p class="eyebrow">{{ $guide['category_label'] }}</p>
  <h1>{{ $guide['title'] }}</h1>
  <p class="article-meta">{{ $guide['date_iso'] }} · {{ $guide['read_time'] }} · by UKVisaCo team</p>
  <p class="standfirst">{{ $guide['excerpt'] }}</p>
</div></header>
<div class="mrz"><div class="wrap"><span>UKV&lt;GUIDE&lt;PLAIN&lt;ENGLISH&lt;READ&lt;BEFORE&lt;YOU&lt;TRAVEL&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;</span></div></div>

{{-- ARTICLE BODY --}}
<section><div class="wrap">
<div class="article-body">

  <div class="callout">
    <p class="k">Quick answer</p>
    <p>{!! $guide['quick_answer'] !!}</p>
  </div>

  @php
    $bodyView = $guide['body_view'] ?? null;
    $bodyPartial = ($bodyView && view()->exists('public.guides.articles.'.$bodyView))
      ? 'public.guides.articles.'.$bodyView
      : 'public.guides.articles._template';
  @endphp
  @include($bodyPartial, ['guide' => $guide])

</div>
</div></section>

{{-- INLINE CTA --}}
<div class="wrap">
<div class="cta-inline alt">
  <h2>Not sure what your trip needs?</h2>
  <p>Answer a few quick questions and our free checker shows whether your trip needs an ETA, a visa, or nothing at all.</p>
  <div class="row">
    <a href="{{ url('/tools') }}" class="btn">Use our free checker →</a>
    <a href="{{ url('/apply') }}" class="btn btn--ghost">Ready? Start your application</a>
  </div>
</div>
</div>

{{-- OPTIONAL: FCA-safe travel-insurance introducer (signpost only, no charge). --}}
<div class="wrap">
<div style="max-width:70ch;margin:0 auto">
  @include('partials.insurance-introducer', ['compact' => true])
</div>
</div>

{{-- RELATED GUIDES --}}
@if (!empty($related))
<section><div class="wrap">
<div class="related">
  <div class="sec-head reveal" style="margin-bottom:18px">
    <p class="eyebrow">Keep reading</p>
    <h2 style="font-size:clamp(24px,3vw,30px);color:var(--navy)">Related guides</h2>
  </div>
  <ul>
    @foreach ($related as $relatedSlug => $relatedGuide)
      <li>
        <a href="{{ url('/guides/'.$relatedSlug) }}">{{ $relatedGuide['title'] }}</a>
        <span class="k">{{ $relatedGuide['category_label'] }} · {{ $relatedGuide['read_time'] }}</span>
      </li>
    @endforeach
    <li>
      <a href="{{ url('/guides') }}">All travel guides</a>
      <span class="k">Index</span>
    </li>
  </ul>
</div>
</div></section>
@endif

</article>

{{-- CTA BAND --}}
<section class="cta-band"><div class="wrap reveal">
  <div class="rule"></div>
  <h2>Let's get you travelling</h2>
  <p style="max-width:48ch;color:#cdd9e1">Check what your trip needs, or start your application with our UK-based team.</p>
  <div class="row">
    <a href="{{ url('/apply') }}" class="btn">Start my application →</a>
    <a href="{{ url('/tools') }}" class="btn btn--ghost" style="color:#fff;border-color:#fff">Check what I need</a>
  </div>
</div></section>

@endsection
