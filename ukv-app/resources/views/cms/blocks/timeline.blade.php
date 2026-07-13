{{-- Timeline block. Self-contained vertical milestone rail, scoped to .cms-timeline with brand tokens.
     Editable: optional heading + ordered rows (label, title, text). --}}
@php
  $items = array_values(array_filter((array) ($data['items'] ?? []), fn ($i) => trim((string) ($i['title'] ?? '')) !== ''));
  $heading = trim((string) ($data['heading'] ?? ''));
@endphp
@if ($items !== [])
<section class="cms-timeline"><div class="wrap">
  <style>
    .cms-timeline{font-family:"Outfit",system-ui,sans-serif;color:#16222E;padding:8px 0}
    .cms-timeline .cl-h{font-size:clamp(22px,3vw,30px);font-weight:700;letter-spacing:-.02em;text-align:center;margin:0 0 26px;color:#16222E}
    .cms-timeline .cl-list{max-width:640px;margin:0 auto;position:relative;padding:0 0 0 30px}
    .cms-timeline .cl-list::before{content:"";position:absolute;left:9px;top:6px;bottom:6px;width:2px;background:#CDE4EC}
    .cms-timeline .cl-row{position:relative;padding:0 0 22px}
    .cms-timeline .cl-row:last-child{padding-bottom:0}
    .cms-timeline .cl-dot{position:absolute;left:-30px;top:3px;width:20px;height:20px;border-radius:50%;background:#fff;border:3px solid #155E7A}
    .cms-timeline .cl-label{font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#155E7A;margin:0 0 2px}
    .cms-timeline .cl-t{font-size:16px;font-weight:700;color:#16222E;margin:0 0 4px;line-height:1.3}
    .cms-timeline .cl-x{font-size:14px;color:#697079;margin:0;line-height:1.55}
  </style>
  @if ($heading !== '')<h2 class="cl-h">{{ $heading }}</h2>@endif
  <div class="cl-list">
    @foreach ($items as $item)
      <div class="cl-row">
        <span class="cl-dot"></span>
        @if (trim((string) ($item['label'] ?? '')) !== '')<p class="cl-label">{{ $item['label'] }}</p>@endif
        <p class="cl-t">{{ $item['title'] }}</p>
        @if (trim((string) ($item['text'] ?? '')) !== '')<p class="cl-x">{{ $item['text'] }}</p>@endif
      </div>
    @endforeach
  </div>
</div></section>
@endif
