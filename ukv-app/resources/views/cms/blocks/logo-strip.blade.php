{{-- Logo strip block. Self-contained muted logo row, scoped to .cms-logos. Each logo resolves a
     media-library reference first, then a one-off upload, and may link out. Editable: heading +
     logos (image, name/alt, optional link). --}}
@php
  $resolve = function (array $i): ?array {
      $media = ! empty($i['media_id']) ? \App\Models\Media::find($i['media_id']) : null;
      $name = trim((string) ($i['name'] ?? ''));
      $url = trim((string) ($i['url'] ?? ''));
      if ($media) { return ['src' => $media->url(), 'name' => $name, 'url' => $url]; }
      $src = trim((string) ($i['src'] ?? ''));
      if ($src === '') { return null; }
      $s = \Illuminate\Support\Str::startsWith($src, ['http://', 'https://', '/']) ? $src : asset('storage/'.$src);
      return ['src' => $s, 'name' => $name, 'url' => $url];
  };
  $items = array_values(array_filter(array_map($resolve, (array) ($data['items'] ?? []))));
  $heading = trim((string) ($data['heading'] ?? ''));
@endphp
@if ($items !== [])
<section class="cms-logos"><div class="wrap">
  <style>
    .cms-logos{font-family:"Outfit",system-ui,sans-serif;padding:8px 0}
    .cms-logos .cx-h{font-size:11px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:#697079;text-align:center;margin:0 0 16px}
    .cms-logos .cx-row{display:flex;flex-wrap:wrap;align-items:center;justify-content:center;gap:28px 40px}
    .cms-logos .cx-item{display:inline-flex}
    .cms-logos img{height:34px;width:auto;max-width:150px;object-fit:contain;filter:grayscale(1);opacity:.65;transition:filter .2s,opacity .2s}
    .cms-logos .cx-item:hover img{filter:grayscale(0);opacity:1}
  </style>
  @if ($heading !== '')<p class="cx-h">{{ $heading }}</p>@endif
  <div class="cx-row">
    @foreach ($items as $item)
      @if ($item['url'] !== '')
        <a class="cx-item" href="{{ $item['url'] }}" rel="nofollow noopener" target="_blank"><img src="{{ $item['src'] }}" alt="{{ $item['name'] }}" loading="lazy" decoding="async"></a>
      @else
        <span class="cx-item"><img src="{{ $item['src'] }}" alt="{{ $item['name'] }}" loading="lazy" decoding="async"></span>
      @endif
    @endforeach
  </div>
</div></section>
@endif
