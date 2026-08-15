<!doctype html>
<html lang="en-GB">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
{{-- Brand v2 favicons / icons. Root /favicon.ico first (Google favicon spec: 48x48 min,
     crawlable at the domain root); PNG + SVG variants after for browsers that prefer them. --}}
<link rel="icon" href="/favicon.ico" sizes="48x48">
<link rel="icon" type="image/png" sizes="48x48" href="{{ asset('assets/brand/bp-symbol-48.png') }}">
<link rel="icon" href="{{ asset('assets/brand/favicon.svg') }}" type="image/svg+xml">
<link rel="apple-touch-icon" href="{{ asset('assets/brand/apple-touch-icon.png') }}">
<meta name="theme-color" content="#155E7A">
{{-- Trustpilot TrustBox bootstrap (in <head> per Trustpilot's guidance). Gated by the master
     ukv.trustpilot.enabled flag so the whole widget can be toggled off site-wide. --}}
@if (config('ukv.trustpilot.enabled') && config('ukv.trustpilot.business_unit_id'))<script type="text/javascript" src="https://widget.trustpilot.com/bootstrap/v5/tp.widget.bootstrap.min.js" async></script>@endif
@if (config('ukv.pinterest_verify'))<meta name="p:domain_verify" content="{{ config('ukv.pinterest_verify') }}">@endif
@if (config('ukv.google_site_verification'))<meta name="google-site-verification" content="{{ config('ukv.google_site_verification') }}">@endif
@include('partials.meta-pixel')
@include('partials.analytics-head')
@php
  $__ogTitle = trim($__env->yieldContent('title')) ?: 'Travel visa & eVisa help for UK travellers | Beyond Passports';
  $__ogDesc  = trim($__env->yieldContent('description')) ?: 'Independent UK team that prepares and checks your travel visa or eVisa application before you go abroad. Clear fixed service fees, fast handling, every step tracked. Not a government website.';
  $__ogImg   = trim($__env->yieldContent('ogImage')) ?: asset('images/og-default.jpg').'?v='.(@filemtime(public_path('images/og-default.jpg')) ?: '1');
  $__ogUrl   = trim($__env->yieldContent('canonical')) ?: url()->current();
@endphp
<title>{{ $__ogTitle }}</title>
<meta name="description" content="{{ $__ogDesc }}">
<link rel="canonical" href="{{ $__ogUrl }}">
{{-- Open Graph / Twitter (defaults; pages override via @section('title'|'description'|'ogImage'|'canonical'|'ogType')) --}}
<meta property="og:site_name" content="Beyond Passports">
<meta property="og:locale" content="en_GB">
<meta property="og:type" content="@yield('ogType', 'website')">
<meta property="og:title" content="{{ $__ogTitle }}">
<meta property="og:description" content="{{ $__ogDesc }}">
<meta property="og:url" content="{{ $__ogUrl }}">
<meta property="og:image" content="{{ $__ogImg }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $__ogTitle }}">
<meta name="twitter:description" content="{{ $__ogDesc }}">
<meta name="twitter:image" content="{{ $__ogImg }}">
{{-- Perf: preload the two font weights that render above the fold (400 body, 800 headings)
     so text paint doesn't wait on CSS parse -> font discovery. Other weights load on demand. --}}
<link rel="preload" href="{{ asset('fonts/outfit-400.woff2') }}" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="{{ asset('fonts/outfit-800.woff2') }}" as="font" type="font/woff2" crossorigin>
<link rel="preconnect" href="https://flagcdn.com">
{{-- Published copy of the coded design system (public/assets/ukv.css).
     Cache-bust by file mtime so CSS edits reach browsers/CDN without a manual purge. --}}
<link rel="stylesheet" href="{{ asset('assets/ukv.css') }}?v={{ @filemtime(public_path('assets/ukv.css')) ?: '1' }}">
@stack('head')
<noscript><style>.reveal{opacity:1!important;transform:none!important}</style></noscript>
</head>
<body data-page="{{ Route::currentRouteName() ?: trim(request()->path(), '/') ?: 'home' }}" data-page-path="/{{ ltrim(request()->path(), '/') }}">
@if (! config('ukv.cookie_banner', false) && config('ukv.gtm_id'))<noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ config('ukv.gtm_id') }}" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>@endif
<a class="skip-link" href="#main">Skip to main content</a>
@include('partials.announcement-bar')
@include('partials.site-header')

{{-- Reusable inline SVG symbol library: skyline silhouette + UKV stamp.
     Self-contained so the public money pages render fully without the front host's
     ukv-illustrations.js. Hidden defs only — referenced via <use href="#..."> below. --}}
@include('partials.svg-symbols')

<main id="main">
@yield('content')
</main>

@include('partials.wa-float')

@include('partials.site-footer')

@include('partials.gtm-tracking')
@include('partials.site-scripts')
@include('partials.select-enhance')
@include('partials.trustpilot-invite')
</body>
</html>
