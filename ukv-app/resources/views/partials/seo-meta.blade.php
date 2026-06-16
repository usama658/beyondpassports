{{--
  Reusable SEO meta partial.

  Drive it from the view that @includes it, e.g.:

      @include('partials.seo-meta', [
          'title'       => 'Turkey eVisa for UK Travellers — Prepared & Checked',
          'description' => 'Independent UK team that prepares and checks your Turkey eVisa...',
          'canonical'   => url()->current(),          // optional, defaults below
          'ogImage'     => asset('images/og/turkey.jpg'), // optional
          'ogType'      => 'website',                  // 'website' | 'article' | 'product'
          'noindex'     => false,                      // optional
      ])

  All variables are optional — sensible site-wide defaults are used when omitted.
  Set sane defaults in the layout's @section if you prefer.
--}}
@php
    $siteName    = config('app.name', 'Beyond Passports');
    $metaTitle   = trim($title ?? '') !== '' ? $title : $siteName;
    $fullTitle   = \Illuminate\Support\Str::contains($metaTitle, $siteName)
                        ? $metaTitle
                        : $metaTitle . ' | ' . $siteName;
    $metaDesc    = $description ?? 'Independent UK visa preparation and checking service. Clear fixed service fees, fast handling, every step tracked. Not a government website.';
    $canonical   = $canonical ?? url()->current();
    $ogType      = $ogType ?? 'website';
    $ogImage     = $ogImage ?? asset('images/og-default.jpg');
    $noindex     = $noindex ?? false;
    $twitterCard = ($ogImage ?? null) ? 'summary_large_image' : 'summary';
@endphp

<title>{{ $fullTitle }}</title>
<meta name="description" content="{{ $metaDesc }}">
<link rel="canonical" href="{{ $canonical }}">
@if ($noindex)
<meta name="robots" content="noindex, nofollow">
@else
<meta name="robots" content="index, follow">
@endif

{{-- Open Graph --}}
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:locale" content="en_GB">
<meta property="og:type" content="{{ $ogType }}">
<meta property="og:title" content="{{ $fullTitle }}">
<meta property="og:description" content="{{ $metaDesc }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:image" content="{{ $ogImage }}">

{{-- Twitter / X --}}
<meta name="twitter:card" content="{{ $twitterCard }}">
<meta name="twitter:title" content="{{ $fullTitle }}">
<meta name="twitter:description" content="{{ $metaDesc }}">
<meta name="twitter:image" content="{{ $ogImage }}">
