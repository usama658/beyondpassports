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
    @php $tbIco = trim((string) config('ukv.compliance.ico_number', '')); @endphp
    @if($tbIco !== '')<a href="https://ico.org.uk/ESDWebPages/Entry/{{ rawurlencode($tbIco) }}" target="_blank" rel="noopener" title="Verify our ICO data-protection registration on the ICO register"><svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;margin-right:5px"><path d="M12 3 5 6v6c0 4.5 3 7.5 7 8.5 4-1 7-4 7-8.5V6z"/><path d="m9 12 2 2 4-4.5"/></svg><b>ICO:</b>&nbsp;{{ $tbIco }}</a>@endif
  </span>
</div></div>
<header class="site-head"><div class="wrap">
  <a href="{{ url('/') }}" class="brand" aria-label="Beyond Passports home"><img src="{{ asset('assets/brand/bp-logo-v2.svg') }}" alt="Beyond Passports" width="150" height="38" style="display:block;height:38px;width:auto"></a>
  <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="primary-nav" aria-label="Open menu">☰</button>
  <nav class="nav" id="primary-nav" aria-label="Primary">
    {{-- Schengen-only: simple top-nav link (mega-menu removed; recoverable from git). --}}
@foreach (\App\Support\NavService::primary() as $item)
    <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
@endforeach
@foreach (\App\Support\NavService::ctas() as $cta)
    <a href="{{ $cta['url'] }}"@if(!empty($cta['external'])) target="_blank" rel="noopener"@endif class="btn{{ ($cta['variant'] ?? '') === 'ghost' ? ' btn--ghost' : '' }}" style="padding:8px 16px">{{ $cta['label'] }}</a>
@endforeach
  </nav>
</div></header>
