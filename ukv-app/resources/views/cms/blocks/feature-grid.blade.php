{{-- Feature grid block. Self-contained responsive card grid, scoped to .cms-features with brand
     tokens. Editable: eyebrow, heading, feature cards (title + text, each with a check tick). --}}
@php
  $items = array_values(array_filter((array) ($data['items'] ?? []), fn ($i) => trim((string) ($i['title'] ?? '')) !== ''));
  $heading = trim((string) ($data['heading'] ?? ''));
  $eyebrow = trim((string) ($data['eyebrow'] ?? ''));
@endphp
@if ($items !== [])
<section class="cms-features"><div class="wrap">
  <style>
    .cms-features{font-family:"Outfit",system-ui,sans-serif;color:#16222E;padding:8px 0}
    .cms-features .cf-eyebrow{font-weight:700;font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:#155E7A;margin:0 0 6px;text-align:center}
    .cms-features .cf-h{font-size:clamp(22px,3vw,30px);font-weight:700;letter-spacing:-.02em;text-align:center;margin:0 0 26px;color:#16222E}
    .cms-features .cf-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:18px}
    .cms-features .cf-card{background:#fff;border:1px solid #dde3ec;border-radius:16px;padding:22px;box-shadow:0 16px 40px -34px rgba(40,50,70,.5)}
    .cms-features .cf-ic{width:26px;height:26px;color:#1F6E63;margin:0 0 10px;display:block}
    .cms-features .cf-t{font-size:17px;font-weight:700;margin:0 0 6px;color:#16222E;line-height:1.25}
    .cms-features .cf-x{font-size:14.5px;color:#697079;margin:0;line-height:1.55}
  </style>
  @if ($eyebrow !== '')<p class="cf-eyebrow">{{ $eyebrow }}</p>@endif
  @if ($heading !== '')<h2 class="cf-h">{{ $heading }}</h2>@endif
  <div class="cf-grid">
    @foreach ($items as $item)
      <div class="cf-card">
        <svg class="cf-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z"/><path d="M9 12l2 2 4-4"/></svg>
        <p class="cf-t">{{ $item['title'] }}</p>
        @if (trim((string) ($item['text'] ?? '')) !== '')<p class="cf-x">{{ $item['text'] }}</p>@endif
      </div>
    @endforeach
  </div>
</div></section>
@endif
