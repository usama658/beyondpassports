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
<div class="topbar">Independent service — not a government website · <a href="tel:+440000000000">Call us</a> · <a href="https://wa.me/440000000000">WhatsApp</a></div>
<header class="site-head"><div class="wrap">
  <a href="{{ url('/') }}" class="brand">UKVisa<b>Co</b></a>
  <nav class="nav" aria-label="Primary">
    <a href="{{ url('/#how') }}">How it works</a>
    <a href="{{ url('/destinations') }}">Destinations</a>
    <a href="{{ url('/#why') }}">Why us</a>
    <a href="{{ url('/track') }}" class="btn btn--ghost" style="padding:8px 16px">Track</a>
  </nav>
</div></header>

{{-- Reusable inline SVG symbol library: skyline silhouette + UKV stamp.
     Self-contained so the public money pages render fully without the front host's
     ukv-illustrations.js. Hidden defs only — referenced via <use href="#..."> below. --}}
<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
  <symbol id="ukv-skyline" viewBox="0 0 240 96" preserveAspectRatio="xMidYMax meet">
    <g fill="#C8A24A">
      <rect x="6"  y="58" width="16" height="38"/>
      <rect x="28" y="44" width="14" height="52"/>
      <rect x="48" y="66" width="18" height="30"/>
      <rect x="72" y="36" width="12" height="60"/>
      <polygon points="78,36 84,22 90,36"/>
      <rect x="96" y="52" width="20" height="44"/>
      <rect x="122" y="30" width="10" height="66"/>
      <circle cx="127" cy="26" r="6"/>
      <rect x="140" y="60" width="22" height="36"/>
      <rect x="168" y="40" width="14" height="56"/>
      <polygon points="168,40 175,28 182,40"/>
      <rect x="190" y="54" width="18" height="42"/>
      <rect x="214" y="46" width="16" height="50"/>
    </g>
  </symbol>
  <symbol id="ukv-stamp" viewBox="0 0 48 48">
    <g fill="none" stroke="#0E6E6E" stroke-width="2.5">
      <circle cx="24" cy="24" r="20" stroke-dasharray="4 3"/>
      <path d="M14 25l7 7 13-15" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
    </g>
  </symbol>
</svg>

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
      <a href="{{ url('/#how') }}">How it works</a><br>
      <a href="{{ url('/destinations') }}">Destinations</a><br>
      <a href="{{ url('/track') }}">Track application</a>
    </div>
    <div>
      <strong>Legal</strong><br>
      <a href="{{ url('/#privacy') }}">Privacy</a><br>
      <a href="{{ url('/#terms') }}">Terms</a><br>
      <a href="{{ url('/#complaints') }}">Complaints</a>
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
