<!doctype html>
<html lang="en-GB">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
@if (config('ukv.pinterest_verify'))<meta name="p:domain_verify" content="{{ config('ukv.pinterest_verify') }}">@endif
@if (config('ukv.google_site_verification'))<meta name="google-site-verification" content="{{ config('ukv.google_site_verification') }}">@endif
@include('partials.meta-pixel')
<title>@yield('title', 'Travel visa & eVisa help for UK travellers | Beyond Passports')</title>
<meta name="description" content="@yield('description', 'Independent UK team that prepares and checks your travel visa or eVisa application before you go abroad. Clear fixed service fees, fast handling, every step tracked. Not a government website.')">
@hasSection('canonical')
<link rel="canonical" href="@yield('canonical')">
@endif
{{-- Published copy of the coded design system (public/assets/ukv.css). --}}
<link rel="stylesheet" href="{{ asset('assets/ukv.css') }}">
@stack('head')
<noscript><style>.reveal{opacity:1!important;transform:none!important}</style></noscript>
</head>
<body>
<a class="skip-link" href="#main">Skip to main content</a>
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

@include('partials.site-scripts')
</body>
</html>
