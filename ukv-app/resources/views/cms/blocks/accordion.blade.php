{{-- Accordion block. Self-contained collapsible rows, scoped to .cms-accordion with brand tokens so
     it is safe on any surface. Editable: optional heading + ordered rows (title + body). --}}
@php
  $items = array_values(array_filter((array) ($data['items'] ?? []), fn ($i) => trim((string) ($i['title'] ?? '')) !== ''));
  $heading = trim((string) ($data['heading'] ?? ''));
@endphp
@if ($items !== [])
<section class="cms-accordion"><div class="wrap">
  <style>
    .cms-accordion{font-family:"Outfit",system-ui,sans-serif;color:#16222E;padding:8px 0}
    .cms-accordion .ca-h{font-size:clamp(22px,3vw,30px);font-weight:700;letter-spacing:-.02em;text-align:center;margin:0 0 22px;color:#16222E}
    .cms-accordion .ca-row{background:#fff;border:1px solid #dde3ec;border-radius:14px;margin:0 0 10px;overflow:hidden}
    .cms-accordion summary{list-style:none;cursor:pointer;padding:16px 18px;font-weight:700;font-size:16px;color:#16222E;display:flex;align-items:center;justify-content:space-between;gap:12px}
    .cms-accordion summary::-webkit-details-marker{display:none}
    .cms-accordion summary::after{content:"+";font-size:20px;font-weight:700;color:#155E7A;flex:0 0 auto}
    .cms-accordion details[open] summary::after{content:"\2013"}
    .cms-accordion .ca-body{padding:0 18px 16px;font-size:14.5px;line-height:1.6;color:#697079;margin:0}
  </style>
  @if ($heading !== '')<h2 class="ca-h">{{ $heading }}</h2>@endif
  @foreach ($items as $item)
    <details class="ca-row">
      <summary>{{ $item['title'] }}</summary>
      @if (trim((string) ($item['body'] ?? '')) !== '')<p class="ca-body">{{ $item['body'] }}</p>@endif
    </details>
  @endforeach
</div></section>
@endif
