<!doctype html>
<html lang="en-GB">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', 'UK visa & eVisa facilitation | Beyond Passports')</title>
<meta name="description" content="@yield('description', 'Independent UK team that prepares and checks your visa or eVisa application. Clear fixed service fees, fast handling, every step tracked. Not a government website.')">
@hasSection('canonical')
<link rel="canonical" href="@yield('canonical')">
@endif
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
{{-- Published copy of the coded design system (public/assets/ukv.css). --}}
<link rel="stylesheet" href="{{ asset('assets/ukv.css') }}">
@stack('head')
<noscript><style>.reveal{opacity:1!important;transform:none!important}</style></noscript>
</head>
<body>
<a class="skip-link" href="#main">Skip to main content</a>
<div class="topbar">Independent service — not a government website · <a href="tel:{{ config('ukv.phone_e164') ?: '+440000000000' }}">Call us</a> · <a href="https://wa.me/{{ config('ukv.whatsapp') ?: '440000000000' }}">WhatsApp</a></div>
<header class="site-head"><div class="wrap">
  <a href="{{ url('/') }}" class="brand">Beyond <b>Passports</b></a>
  <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="primary-nav" aria-label="Open menu">☰</button>
  <nav class="nav" id="primary-nav" aria-label="Primary">
    {{-- Destinations mega-menu: photo cards (top destinations + "from" fee) --}}
    <details class="mega">
      <summary class="mlink" role="button" aria-haspopup="true">Destinations <span class="ch" aria-hidden="true">▾</span></summary>
      <div class="mega-panel"><div class="wrap">
        <div class="mega-grid">
          @foreach ($navMenuDestinations ?? [] as $d)
          <a class="mega-card" href="{{ url('/visa/'.$d->slug) }}">
            <div class="pic"@if ($d->image_path) style="background-image:url('{{ asset(ltrim($d->image_path, '/')) }}')"@endif></div>
            <div class="tx"><b>{{ $d->name }} {{ $d->visa_type }}</b>@if ((float) $d->tier_standard_gbp > 0)<span>from £{{ number_format((float) $d->tier_standard_gbp, 0) }}</span>@endif</div>
          </a>
          @endforeach
        </div>
        <p class="mega-foot"><a class="rlink" href="{{ url('/destinations') }}">See all destinations &amp; fixed fees →</a></p>
      </div></div>
    </details>
    {{-- Tools mega-menu: the checkers + finders, with one-line descriptions --}}
    <details class="mega">
      <summary class="mlink" role="button" aria-haspopup="true">Tools <span class="ch" aria-hidden="true">▾</span></summary>
      <div class="mega-panel"><div class="wrap">
        <div class="mega-list">
          <a class="mega-item" href="{{ url('/tools') }}"><b>Visa checker</b><span>Tell us your trip — we confirm exactly what you need.</span></a>
          <a class="mega-item" href="{{ url('/document-checklist') }}"><b>Document checker</b><span>A personalised document checklist for your destination.</span></a>
          <a class="mega-item" href="{{ url('/driving-abroad') }}"><b>Driving abroad (IDP)</b><span>Check if you need an International Driving Permit.</span></a>
          <a class="mega-item" href="{{ url('/find-a-centre') }}"><b>Find a centre</b><span>Your nearest IDP / visa centre by postcode.</span></a>
        </div>
      </div></div>
    </details>
    <a href="{{ url('/guides') }}">Guides</a>
    <a href="{{ url('/about') }}">About</a>
    <a href="{{ url('/track') }}" class="btn btn--ghost" style="padding:8px 16px">Track</a>
    <a href="{{ url('/apply') }}" class="btn" style="padding:8px 16px">Start application →</a>
  </nav>
</div></header>

{{-- Reusable inline SVG symbol library: skyline silhouette + UKV stamp.
     Self-contained so the public money pages render fully without the front host's
     ukv-illustrations.js. Hidden defs only — referenced via <use href="#..."> below. --}}
@include('partials.svg-symbols')

<main id="main">
@yield('content')
</main>

<footer style="padding:0">
  {{-- CTA band fused to the top of the footer (Footer 2) --}}
  <section class="ft-cta"><div class="wrap">
    <h2>Ready to travel?</h2>
    <p>Start your application now, or message our UK team with any question.</p>
    <div class="row">
      <a href="{{ url('/apply') }}" class="btn">Start my application →</a>
      <a href="https://wa.me/{{ config('ukv.whatsapp') ?: '440000000000' }}" class="btn btn--glass">Chat on WhatsApp</a>
    </div>
  </div></section>

  <div class="ft-main"><div class="wrap">
    <div class="cols">
      <div>
        <div class="brand" style="color:#fff">Beyond <b>Passports</b></div>
        <p style="max-width:30ch">Independent UK visa &amp; eVisa facilitation. Not a government website.</p>
        {{-- Consent-gated newsletter opt-in (works with no JS; flashes status on return) --}}
        <form method="POST" action="{{ route('subscribe.store') }}">
          @csrf
          <div class="ft-cap">
            <label for="sub-email" class="mrz">Your email</label>
            <input id="sub-email" type="email" name="email" placeholder="Get visa-rule updates by email" value="{{ old('email') }}" required>
            <button class="btn" type="submit" style="padding:11px 16px">Join</button>
          </div>
          <label class="ft-consent"><input type="checkbox" name="consent" value="1" required> <span>I agree to receive occasional email updates. <a href="{{ url('/legal') }}#privacy">Privacy notice</a>. Unsubscribe any time.</span></label>
          @if (session('subscribe_status'))<p class="ft-ok">{{ session('subscribe_status') }}</p>@endif
          @error('email')<p class="ft-err">{{ $message }}</p>@enderror
          @error('consent')<p class="ft-err">{{ $message }}</p>@enderror
        </form>
      </div>
      <div>
        <strong>Service</strong>
        <a href="{{ url('/destinations') }}">Destinations</a>
        <a href="{{ url('/tools') }}">Visa checker</a>
        <a href="{{ url('/apply') }}">Start an application</a>
        <a href="{{ url('/track') }}">Track application</a>
      </div>
      <div>
        <strong>Guides</strong>
        <a href="{{ url('/guides') }}">Visa guides &amp; stories</a>
        <a href="{{ url('/reviews') }}">Traveller reviews</a>
        <a href="{{ url('/driving-abroad') }}">Driving abroad (IDP)</a>
        <a href="{{ url('/compare') }}">Apply yourself vs us</a>
      </div>
      <div>
        <strong>Company &amp; legal</strong>
        <a href="{{ url('/about') }}">About us</a>
        <a href="{{ url('/contact') }}">Contact</a>
        <a href="{{ url('/legal') }}#privacy">Privacy</a>
        <a href="{{ url('/legal') }}#terms">Terms</a>
        <a href="{{ url('/legal') }}#complaints">Complaints</a>
        <a href="{{ url('/legal') }}#disclaimer">Disclaimer</a>
      </div>
    </div>
    <div class="ft-bottom">
      <span>© Beyond Passports. Service fee separate from any government fee. Express speeds our handling, not the government’s decision. No approval guarantee.</span>
      <span>UK-based team · ★ 4.9 rated</span>
    </div>
  </div></div>
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

<script>
  // Mega-menu: only one dropdown open at a time; close on outside-click or Esc.
  // Built on native <details> so it still opens/closes with no JS and via keyboard.
  (function () {
    var menus = Array.prototype.slice.call(document.querySelectorAll('.nav details.mega'));
    menus.forEach(function (d) {
      d.addEventListener('toggle', function () {
        if (d.open) { menus.forEach(function (o) { if (o !== d) o.open = false; }); }
      });
    });
    document.addEventListener('click', function (e) {
      menus.forEach(function (d) { if (d.open && !d.contains(e.target)) d.open = false; });
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') menus.forEach(function (d) { d.open = false; });
    });
    // Mobile hamburger: toggle the primary nav.
    var btn = document.querySelector('.nav-toggle'), nav = document.getElementById('primary-nav');
    if (btn && nav) {
      btn.addEventListener('click', function () {
        var open = nav.classList.toggle('open');
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
      });
    }
  })();
</script>
</body>
</html>
