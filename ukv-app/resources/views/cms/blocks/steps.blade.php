{{-- Steps block. Self-contained numbered "how it works" sequence, scoped to .cms-steps with brand
     tokens so it is safe on any surface. Editable: eyebrow, heading, ordered steps (auto-numbered). --}}
@php
  $items = array_values(array_filter((array) ($data['items'] ?? []), fn ($i) => trim((string) ($i['title'] ?? '')) !== ''));
  $heading = trim((string) ($data['heading'] ?? ''));
  $eyebrow = trim((string) ($data['eyebrow'] ?? ''));
@endphp
@if ($items !== [])
<section class="cms-steps"><div class="wrap">
  <style>
    .cms-steps{font-family:"Outfit",system-ui,sans-serif;color:#16222E;padding:8px 0}
    .cms-steps .cs-eyebrow{font-weight:700;font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:#155E7A;margin:0 0 6px;text-align:center}
    .cms-steps .cs-h{font-size:clamp(22px,3vw,30px);font-weight:700;letter-spacing:-.02em;text-align:center;margin:0 0 26px;color:#16222E}
    .cms-steps .cs-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px}
    .cms-steps .cs-card{background:#fff;border:1px solid #dde3ec;border-radius:16px;padding:22px 22px 20px;box-shadow:0 16px 40px -34px rgba(40,50,70,.5)}
    .cms-steps .cs-n{display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:10px;background:#155E7A;color:#fff;font-weight:800;font-size:16px;margin:0 0 12px}
    .cms-steps .cs-t{font-size:17px;font-weight:700;margin:0 0 6px;color:#16222E;line-height:1.25}
    .cms-steps .cs-x{font-size:14.5px;color:#697079;margin:0;line-height:1.55}
  </style>
  @if ($eyebrow !== '')<p class="cs-eyebrow">{{ $eyebrow }}</p>@endif
  @if ($heading !== '')<h2 class="cs-h">{{ $heading }}</h2>@endif
  <div class="cs-grid">
    @foreach ($items as $i => $item)
      <div class="cs-card">
        <span class="cs-n">{{ $i + 1 }}</span>
        <p class="cs-t">{{ $item['title'] }}</p>
        @if (trim((string) ($item['text'] ?? '')) !== '')<p class="cs-x">{{ $item['text'] }}</p>@endif
      </div>
    @endforeach
  </div>
</div></section>
@endif
