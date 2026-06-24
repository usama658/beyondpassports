{{-- Canonical site topbar + header. Single source of truth — included by
     layouts.public AND by the standalone pages (track, checklist-result) so the
     header is identical everywhere. Styling lives in assets/ukv.css; the
     navMenuDestinations data is supplied by a view composer bound to this partial
     (see AppServiceProvider). --}}
<div class="topbar"><div class="wrap tb-row">
  <span class="tb-note">Independent service. <strong>Not a government website</strong></span>
  <span class="tb-links">
    <span class="tb-rate">@include('partials.trustpilot', ['template' => 'micro', 'width' => '180px', 'height' => '20px', 'margin' => '0'])</span>
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
    {{-- Destinations mega-menu: grouped — Popular money pages + Europe by region --}}
    <details class="mega">
      <summary class="mlink" role="button" aria-haspopup="true">Destinations <span class="ch" aria-hidden="true">▾</span></summary>
      <div class="mega-panel mega-panel--wide"><div class="wrap">
        <div class="mega-cols">
          {{-- Popular: curated money pages as photo cards --}}
          <div>
            <p class="mega-colhead">Popular destinations</p>
            <div class="mega-grid">
              @foreach ($navMenuPopular ?? [] as $d)
              <a class="mega-card" href="{{ url('/visa/'.$d->slug) }}">
                <div class="pic"@if ($d->image_path) style="background-image:url('{{ asset(ltrim($d->image_path, '/')) }}')"@endif></div>
                <div class="tx"><b>{{ $d->name }} {{ $d->visa_type }}</b>@if (config('ukv.show_prices') && (float) $d->tier_standard_gbp > 0)<span>from £{{ number_format((float) $d->tier_standard_gbp, 0) }}</span>@elseif (! config('ukv.show_prices') && $d->max_stay_days)<span>up to {{ $d->max_stay_days }} days</span>@endif</div>
              </a>
              @endforeach
            </div>
            <p class="mega-foot"><a class="rlink" href="{{ url('/destinations') }}">See all destinations @if(config('ukv.show_prices'))&amp; fixed fees @endif→</a></p>
          </div>
          {{-- Europe: Schengen / ETIAS regions linking to the filtered hub --}}
          <div class="mega-eu">
            <div class="mega-euhead"><p class="mega-colhead">Europe</p><span class="mega-pill">One ETIAS · 29</span></div>
            <div class="mega-reg">
              @foreach ($navMenuRegions ?? [] as $r)
              <a href="{{ url('/visa/schengen').'?region='.urlencode($r['name']) }}"><b>{{ $r['name'] }}</b><span class="c">{{ $r['count'] }} &rarr;</span></a>
              @endforeach
              <a class="hub" href="{{ url('/visa/schengen') }}"><b>See the Europe hub &rarr;</b></a>
            </div>
          </div>
        </div>
      </div></div>
    </details>
    {{-- Tools moved to the footer; mega-menu design saved as partials/nav-mega for reuse. --}}
    <a href="{{ url('/services') }}">Services</a>
    <a href="{{ url('/guides') }}">Guides</a>
    <a href="{{ url('/about') }}">About</a>
    <a href="{{ url('/contact') }}" class="btn btn--ghost" style="padding:8px 16px">Contact</a>
    <a href="{{ url('/apply') }}" class="btn" style="padding:8px 16px">Start application →</a>
  </nav>
</div></header>
