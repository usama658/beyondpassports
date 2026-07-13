{{-- Notice bar block. Self-contained full-width announcement strip, scoped to .cms-notice with brand
     tokens. Editable: tone (brand/dark/warning), text, optional inline link. --}}
@php
  $text = trim((string) ($data['text'] ?? ''));
  $tone = in_array(($data['tone'] ?? 'brand'), ['brand', 'dark', 'warning'], true) ? $data['tone'] : 'brand';
  $ll = trim((string) ($data['link_label'] ?? ''));
  $lu = trim((string) ($data['link_url'] ?? ''));
  $tones = [
    'brand' => ['#155E7A', '#ffffff', 'rgba(255,255,255,.85)'],
    'dark' => ['#16222E', '#ffffff', 'rgba(255,255,255,.85)'],
    'warning' => ['#F4E3BE', '#5A4300', '#5A4300'],
  ];
  [$bg, $fg, $link] = $tones[$tone];
@endphp
@if ($text !== '')
<section class="cms-notice" style="--nb-bg:{{ $bg }};--nb-fg:{{ $fg }};--nb-link:{{ $link }}">
  <style>
    .cms-notice{background:var(--nb-bg);color:var(--nb-fg);font-family:"Outfit",system-ui,sans-serif}
    .cms-notice .nb-in{max-width:1120px;margin:0 auto;padding:11px 20px;text-align:center;font-size:14.5px;font-weight:600}
    .cms-notice a{color:var(--nb-link);font-weight:700;text-decoration:underline;margin-left:8px;white-space:nowrap}
  </style>
  <div class="nb-in">
    <span>{{ $text }}</span>@if ($ll !== '' && $lu !== '')<a href="{{ $lu }}">{{ $ll }}</a>@endif
  </div>
</section>
@endif
