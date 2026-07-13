{{-- Checklist block. Self-contained ticked list, scoped to .cms-checklist with brand tokens.
     Editable: optional heading + points (text). --}}
@php
  $items = array_values(array_filter((array) ($data['items'] ?? []), fn ($i) => trim((string) ($i['text'] ?? '')) !== ''));
  $heading = trim((string) ($data['heading'] ?? ''));
@endphp
@if ($items !== [])
<section class="cms-checklist"><div class="wrap">
  <style>
    .cms-checklist{font-family:"Outfit",system-ui,sans-serif;color:#16222E;padding:8px 0}
    .cms-checklist .ck-h{font-size:clamp(22px,3vw,30px);font-weight:700;letter-spacing:-.02em;text-align:center;margin:0 0 22px;color:#16222E}
    .cms-checklist ul{list-style:none;margin:0 auto;padding:0;max-width:600px;display:grid;gap:10px}
    .cms-checklist li{display:flex;align-items:flex-start;gap:12px;font-size:15.5px;line-height:1.5;color:#3a434d}
    .cms-checklist .ck-tick{flex:0 0 auto;width:24px;height:24px;border-radius:50%;background:#EAF6EF;color:#2E7D57;font-weight:800;font-size:14px;display:inline-flex;align-items:center;justify-content:center;margin-top:1px}
  </style>
  @if ($heading !== '')<h2 class="ck-h">{{ $heading }}</h2>@endif
  <ul>
    @foreach ($items as $item)
      <li><span class="ck-tick" aria-hidden="true">&#10003;</span><span>{{ $item['text'] }}</span></li>
    @endforeach
  </ul>
</div></section>
@endif
