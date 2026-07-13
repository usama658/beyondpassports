{{-- Split block. Self-contained two-column image + text, scoped to .cms-split with brand tokens.
     Editable: image (media library), flip side, eyebrow, heading, body, button. --}}
@php
  $heading = trim((string) ($data['heading'] ?? ''));
  $media = ! empty($data['media_id']) ? \App\Models\Media::find($data['media_id']) : null;
  $eyebrow = trim((string) ($data['eyebrow'] ?? ''));
  $body = trim((string) ($data['body'] ?? ''));
  $btnLabel = trim((string) ($data['button_label'] ?? ''));
  $btnUrl = trim((string) ($data['button_url'] ?? ''));
  $flip = ! empty($data['flip']);
@endphp
@if ($heading !== '')
<section class="cms-split"><div class="wrap">
  <style>
    .cms-split{font-family:"Outfit",system-ui,sans-serif;padding:8px 0}
    .cms-split .csp-row{display:grid;grid-template-columns:1fr 1fr;gap:32px;align-items:center}
    .cms-split .csp-row.flip .csp-media{order:2}
    .cms-split .csp-media img{width:100%;height:auto;border-radius:16px;display:block;box-shadow:0 18px 44px -30px rgba(40,50,70,.55)}
    .cms-split .csp-eyebrow{font-weight:700;font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:#155E7A;margin:0 0 6px}
    .cms-split .csp-h{font-size:clamp(22px,3vw,32px);font-weight:700;letter-spacing:-.02em;color:#16222E;margin:0 0 12px;line-height:1.15}
    .cms-split .csp-b{font-size:15.5px;color:#697079;line-height:1.6;margin:0 0 18px}
    .cms-split .csp-btn{display:inline-flex;align-items:center;background:#155E7A;color:#fff;text-decoration:none;font-weight:600;
      border-radius:11px;padding:12px 22px;font-size:15px}
    .cms-split .csp-btn:hover{background:#124f66}
    @media (max-width:720px){.cms-split .csp-row,.cms-split .csp-row.flip{grid-template-columns:1fr}.cms-split .csp-row.flip .csp-media{order:0}}
  </style>
  <div class="csp-row{{ $flip ? ' flip' : '' }}">
    <div class="csp-media">
      @if ($media)<img src="{{ $media->url() }}" alt="{{ $media->alt }}" loading="lazy" decoding="async">@endif
    </div>
    <div class="csp-text">
      @if ($eyebrow !== '')<p class="csp-eyebrow">{{ $eyebrow }}</p>@endif
      <h2 class="csp-h">{{ $heading }}</h2>
      @if ($body !== '')<p class="csp-b">{{ $body }}</p>@endif
      @if ($btnLabel !== '')<a class="csp-btn" href="{{ $btnUrl !== '' ? $btnUrl : '#' }}">{{ $btnLabel }}</a>@endif
    </div>
  </div>
</div></section>
@endif
