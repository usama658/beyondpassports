<!doctype html>
<html lang="en-GB">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', 'UK visa & eVisa facilitation | UKVisaCo')</title>
<meta name="description" content="@yield('description', 'Independent UK team that prepares and checks your visa or eVisa application. Clear fixed service fees, fast handling, every step tracked. Not a government website.')">
@hasSection('canonical')
<link rel="canonical" href="@yield('canonical')">
@endif
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600&family=Inter:wght@400;500;600&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
{{-- Published copy of the coded design system (public/assets/ukv.css). --}}
<link rel="stylesheet" href="{{ asset('assets/ukv.css') }}">
@stack('head')
<noscript><style>.reveal{opacity:1!important;transform:none!important}</style></noscript>
</head>
<body>
<a class="skip-link" href="#main">Skip to main content</a>
<div class="topbar">Independent service — not a government website · <a href="tel:{{ config('ukv.phone_e164') ?: '+440000000000' }}">Call us</a> · <a href="https://wa.me/{{ config('ukv.whatsapp') ?: '440000000000' }}">WhatsApp</a></div>
<header class="site-head"><div class="wrap">
  <a href="{{ url('/') }}" class="brand">UKVisa<b>Co</b></a>
  <nav class="nav" aria-label="Primary">
    <a href="{{ url('/destinations') }}">Destinations</a>
    <a href="{{ url('/tools') }}">Visa checker</a>
    <a href="{{ url('/document-checklist') }}">Document checker</a>
    <a href="{{ url('/driving-abroad') }}">Driving abroad</a>
    <a href="{{ url('/guides') }}">Guides</a>
    <a href="{{ url('/track') }}" class="btn btn--ghost" style="padding:8px 16px">Track</a>
  </nav>
</div></header>

{{-- Reusable inline SVG symbol library: skyline silhouette + UKV stamp.
     Self-contained so the public money pages render fully without the front host's
     ukv-illustrations.js. Hidden defs only — referenced via <use href="#..."> below. --}}
@include('partials.svg-symbols')

<main id="main">
@yield('content')
</main>

<footer><div class="wrap">
  <div class="cols">
    <div>
      <div class="brand" style="color:#fff">UKVisa<b>Co</b></div>
      <p style="max-width:34ch">Independent UK visa &amp; eVisa facilitation. Not a government website.</p>
    </div>
    <div>
      <strong>Service</strong><br>
      <a href="{{ url('/destinations') }}">Destinations</a><br>
      <a href="{{ url('/tools') }}">Visa checker</a><br>
      <a href="{{ url('/apply') }}">Start an application</a><br>
      <a href="{{ url('/track') }}">Track application</a>
    </div>
    <div>
      <strong>Guides</strong><br>
      <a href="{{ url('/guides') }}">Visa guides &amp; stories</a><br>
      <a href="{{ url('/reviews') }}">Traveller reviews</a><br>
      <a href="{{ url('/driving-abroad') }}">Driving abroad (IDP)</a><br>
      <a href="{{ url('/compare') }}">Apply yourself vs us</a>
    </div>
    <div>
      <strong>Company</strong><br>
      <a href="{{ url('/about') }}">About us</a><br>
      <a href="{{ url('/contact') }}">Contact</a>
    </div>
    <div>
      <strong>Legal</strong><br>
      <a href="{{ url('/legal') }}#privacy">Privacy</a><br>
      <a href="{{ url('/legal') }}#terms">Terms</a><br>
      <a href="{{ url('/legal') }}#complaints">Complaints</a><br>
      <a href="{{ url('/legal') }}#disclaimer">Disclaimer</a>
    </div>
  </div>
</div>
<div class="mrz" style="margin-top:8px"><div class="wrap"><span>UKV&lt;INDEPENDENT&lt;SERVICE&lt;NOT&lt;A&lt;GOVERNMENT&lt;WEBSITE&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;</span></div></div>
</footer>

<script>
  // Lightweight reveal-on-scroll (graceful: .reveal is visible by default in noscript).
  (function () {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      document.querySelectorAll('.reveal').forEach(function (el) { el.classList.add('in'); });
      return;
    }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } });
    }, { rootMargin: '0px 0px -10% 0px' });
    document.querySelectorAll('.reveal').forEach(function (el) { io.observe(el); });
  })();
</script>
</body>
</html>
