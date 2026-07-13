{{-- Callout block. Self-contained tone-coloured notice, scoped to .cms-callout with brand tokens.
     Editable: tone (info/success/warning), title, body, optional button. --}}
@php
  $body = trim((string) ($data['body'] ?? ''));
  $title = trim((string) ($data['title'] ?? ''));
  $tone = in_array(($data['tone'] ?? 'info'), ['info', 'success', 'warning'], true) ? $data['tone'] : 'info';
  $btnLabel = trim((string) ($data['button_label'] ?? ''));
  $btnUrl = trim((string) ($data['button_url'] ?? ''));
  $tones = [
    'info' => ['#155E7A', '#EAF4F8', '#CDE4EC'],
    'success' => ['#2E7D57', '#EAF6EF', '#CFE9DB'],
    'warning' => ['#8A5A00', '#FBF3E2', '#EFDFB8'],
  ];
  [$accent, $bg, $border] = $tones[$tone];
@endphp
@if ($body !== '')
<section class="cms-callout"><div class="wrap">
  <style>
    .cms-callout{font-family:"Outfit",system-ui,sans-serif;padding:8px 0}
    .cms-callout .co-box{border:1px solid var(--co-border);background:var(--co-bg);border-left:4px solid var(--co-accent);border-radius:14px;padding:18px 20px}
    .cms-callout .co-t{font-size:16px;font-weight:700;margin:0 0 6px;color:#16222E}
    .cms-callout .co-x{font-size:14.5px;line-height:1.6;color:#3a434d;margin:0}
    .cms-callout .co-btn{display:inline-block;margin:14px 0 0;background:var(--co-accent);color:#fff;font-weight:700;font-size:14px;text-decoration:none;padding:10px 18px;border-radius:10px}
  </style>
  <div class="co-box" style="--co-accent:{{ $accent }};--co-bg:{{ $bg }};--co-border:{{ $border }}">
    @if ($title !== '')<p class="co-t">{{ $title }}</p>@endif
    <p class="co-x">{{ $body }}</p>
    @if ($btnLabel !== '' && $btnUrl !== '')<a class="co-btn" href="{{ $btnUrl }}">{{ $btnLabel }}</a>@endif
  </div>
</div></section>
@endif
