{{-- Canonical site topbar + header. Single source of truth — included by
     layouts.public AND by the standalone pages (track, checklist-result) so the
     header is identical everywhere. Styling lives in assets/ukv.css; the
     navMenuDestinations data is supplied by a view composer bound to this partial
     (see AppServiceProvider). --}}
<div class="topbar"><div class="wrap tb-row">
  <span class="tb-spacer" aria-hidden="true"></span>
  {{-- Trustpilot rating (real figures, manual sync) — centred. --}}
  <span class="tb-tp">@include('partials.trustpilot-cta', ['align' => 'center', 'theme' => 'dark', 'margin' => '0'])</span>
  <span class="tb-links">
    <a href="tel:{{ config('ukv.phone_e164') ?: '+447882747584' }}">@include('partials.call-glyph')<b>UK Team:</b>&nbsp;{{ config('ukv.phone') ?: '+44' }}</a>
    @if(config('ukv.show_de_phone'))<a href="tel:{{ config('ukv.phone_de_e164') ?: '+490000000000' }}">@include('partials.call-glyph')<b>Europe Team:</b>&nbsp;{{ config('ukv.phone_de') ?: '+49' }}</a>@endif
    <a href="https://wa.me/{{ config('ukv.whatsapp') ?: '447882747584' }}">@include('partials.wa-glyph')WhatsApp</a>
  </span>
</div></div>
<header class="site-head"><div class="wrap">
  <a href="{{ url('/') }}" class="brand" aria-label="Beyond Passports home"><img src="{{ asset('assets/brand/bp-logo-v2.svg') }}" alt="Beyond Passports" width="150" height="38" style="display:block;height:38px;width:auto"></a>
  <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="primary-nav" aria-label="Open menu">☰</button>
  <nav class="nav" id="primary-nav" aria-label="Primary">
    {{-- Schengen-only: simple top-nav link (mega-menu removed; recoverable from git). --}}
    <a href="{{ url('/destinations') }}">Schengen visa</a>
    <a href="{{ url('/tour-packages') }}">{{ config('ukv.tours.nav_label', 'Plan a trip') }}</a>
    <a href="{{ url('/about') }}">Who we are</a>
    <a href="{{ url('/contact') }}" class="btn btn--ghost" style="padding:8px 16px">Contact</a>
    <a href="{{ App\Support\SiteStats::chatUrl() }}" target="_blank" rel="noopener" class="btn" style="padding:8px 16px">Check eligibility →</a>
  </nav>
</div></header>
