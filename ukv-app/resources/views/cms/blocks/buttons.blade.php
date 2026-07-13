{{-- Button group block. Self-contained centred CTA row, scoped to .cms-buttons with brand tokens.
     Editable: optional heading + buttons (label, url, style: primary/secondary). --}}
@php
  $items = array_values(array_filter((array) ($data['items'] ?? []), fn ($i) => trim((string) ($i['label'] ?? '')) !== '' && trim((string) ($i['url'] ?? '')) !== ''));
  $heading = trim((string) ($data['heading'] ?? ''));
@endphp
@if ($items !== [])
<section class="cms-buttons"><div class="wrap">
  <style>
    .cms-buttons{font-family:"Outfit",system-ui,sans-serif;color:#16222E;padding:8px 0;text-align:center}
    .cms-buttons .cb-h{font-size:clamp(20px,2.6vw,26px);font-weight:700;letter-spacing:-.02em;margin:0 0 18px;color:#16222E}
    .cms-buttons .cb-row{display:flex;flex-wrap:wrap;gap:12px;justify-content:center}
    .cms-buttons a{display:inline-block;font-weight:700;font-size:15px;text-decoration:none;padding:12px 24px;border-radius:11px;border:2px solid #155E7A}
    .cms-buttons a.cb-primary{background:#155E7A;color:#fff}
    .cms-buttons a.cb-secondary{background:transparent;color:#155E7A}
  </style>
  @if ($heading !== '')<h2 class="cb-h">{{ $heading }}</h2>@endif
  <div class="cb-row">
    @foreach ($items as $item)
      @php $style = ($item['style'] ?? 'primary') === 'secondary' ? 'cb-secondary' : 'cb-primary'; @endphp
      <a class="{{ $style }}" href="{{ $item['url'] }}">{{ $item['label'] }}</a>
    @endforeach
  </div>
</div></section>
@endif
