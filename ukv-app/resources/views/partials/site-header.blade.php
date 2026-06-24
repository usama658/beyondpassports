{{-- Canonical site topbar + header. Single source of truth — included by
     layouts.public AND by the standalone pages (track, checklist-result) so the
     header is identical everywhere. Styling lives in assets/ukv.css; the
     navMenuDestinations data is supplied by a view composer bound to this partial
     (see AppServiceProvider). --}}
<div class="topbar"><div class="wrap tb-row">
  <span class="tb-note">Independent service. <strong>Not a government website</strong></span>
  <span class="tb-links">
    {{-- Trustpilot rating (real figures, manual sync). Custom compact single-line widget. --}}
    <span class="tb-tp">@include('partials.trustpilot-cta', ['align' => 'left', 'theme' => 'dark', 'margin' => '0'])</span>
    <span class="tb-div" aria-hidden="true"></span>
    <a href="tel:{{ config('ukv.phone_e164') ?: '+440000000000' }}">Call us</a>
    <a href="https://wa.me/{{ config('ukv.whatsapp') ?: '440000000000' }}">WhatsApp</a>
    @if (array_filter(config('ukv.social', [])))
      <span class="tb-div" aria-hidden="true"></span>
      <span class="tb-social">@include('partials.social-row', ['cls' => 'tb-soc', 'size' => 14])</span>
    @endif
  </span>
</div></div>
<header class="site-head"><div class="wrap">
  <a href="{{ url('/') }}" class="brand">Beyond <b>Passports</b></a>
  <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="primary-nav" aria-label="Open menu">☰</button>
  <nav class="nav" id="primary-nav" aria-label="Primary">
    {{-- Schengen-only: simple top-nav link (mega-menu removed; recoverable from git). --}}
    <a href="{{ url('/destinations') }}">Schengen visa</a>
    <a href="{{ url('/services') }}">Services</a>
    <a href="{{ url('/guides') }}">Guides</a>
    <a href="{{ url('/about') }}">About</a>
    <a href="{{ url('/contact') }}" class="btn btn--ghost" style="padding:8px 16px">Contact</a>
    <a href="{{ url('/apply') }}" class="btn" style="padding:8px 16px">Start application →</a>
  </nav>
</div></header>
