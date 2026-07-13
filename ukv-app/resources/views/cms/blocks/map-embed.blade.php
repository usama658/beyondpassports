{{-- Map embed block. Builds a safe responsive Google Maps iframe from a place query or a pasted
     Google Maps embed URL. A pasted URL must be on google.com/maps to be honoured; otherwise a
     query is turned into a maps.google.com/maps?output=embed URL. Any non-Google URL renders
     nothing, so an editor can never inject an arbitrary iframe. Editable: heading, query/URL. --}}
@php
  $query = trim((string) ($data['query'] ?? ''));
  $heading = trim((string) ($data['heading'] ?? ''));
  $src = null;
  if ($query !== '') {
      if (\Illuminate\Support\Str::startsWith($query, ['http://', 'https://'])) {
          // Only accept a pasted URL if it's a Google Maps host.
          $host = strtolower((string) parse_url($query, PHP_URL_HOST));
          if (in_array($host, ['www.google.com', 'google.com', 'maps.google.com'], true)) {
              $src = $query;
          }
      } else {
          $src = 'https://maps.google.com/maps?q='.urlencode($query).'&output=embed';
      }
  }
@endphp
@if ($src !== null)
<section class="cms-map"><div class="wrap">
  <style>
    .cms-map{font-family:"Outfit",system-ui,sans-serif;color:#16222E;padding:8px 0}
    .cms-map .cm-h{font-size:clamp(22px,3vw,30px);font-weight:700;letter-spacing:-.02em;text-align:center;margin:0 0 18px;color:#16222E}
    .cms-map .cm-frame{position:relative;width:100%;aspect-ratio:16/9;border-radius:16px;overflow:hidden;border:1px solid #dde3ec}
    .cms-map .cm-frame iframe{position:absolute;inset:0;width:100%;height:100%;border:0}
  </style>
  @if ($heading !== '')<h2 class="cm-h">{{ $heading }}</h2>@endif
  <div class="cm-frame">
    <iframe src="{{ $src }}" title="{{ $heading !== '' ? $heading : 'Map' }}" loading="lazy"
      referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
  </div>
</div></section>
@endif
