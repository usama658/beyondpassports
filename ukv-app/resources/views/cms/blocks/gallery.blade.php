{{-- Gallery block. Self-contained responsive image grid, scoped to .cms-gallery. Each tile resolves a
     media-library reference (media_id) first, then a one-off upload (src) — same rules as the Image
     block. Editable: optional heading + image tiles (image, alt, caption). --}}
@php
  $resolve = function (array $i): ?array {
      $media = ! empty($i['media_id']) ? \App\Models\Media::find($i['media_id']) : null;
      $alt = trim((string) ($i['alt'] ?? ''));
      if ($media) {
          return ['url' => $media->url(), 'alt' => $alt !== '' ? $alt : (string) ($media->alt ?? ''), 'caption' => trim((string) ($i['caption'] ?? ''))];
      }
      $src = trim((string) ($i['src'] ?? ''));
      if ($src === '') { return null; }
      $url = \Illuminate\Support\Str::startsWith($src, ['http://', 'https://', '/']) ? $src : asset('storage/'.$src);
      return ['url' => $url, 'alt' => $alt, 'caption' => trim((string) ($i['caption'] ?? ''))];
  };
  $items = array_values(array_filter(array_map($resolve, (array) ($data['items'] ?? []))));
  $heading = trim((string) ($data['heading'] ?? ''));
@endphp
@if ($items !== [])
<section class="cms-gallery"><div class="wrap">
  <style>
    .cms-gallery{font-family:"Outfit",system-ui,sans-serif;color:#16222E;padding:8px 0}
    .cms-gallery .cg-h{font-size:clamp(22px,3vw,30px);font-weight:700;letter-spacing:-.02em;text-align:center;margin:0 0 22px;color:#16222E}
    .cms-gallery .cg-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px}
    .cms-gallery figure{margin:0}
    .cms-gallery img{width:100%;height:100%;aspect-ratio:4/3;object-fit:cover;border-radius:14px;border:1px solid #dde3ec;display:block}
    .cms-gallery figcaption{font-size:12.5px;color:#697079;text-align:center;margin:8px 0 0}
  </style>
  @if ($heading !== '')<h2 class="cg-h">{{ $heading }}</h2>@endif
  <div class="cg-grid">
    @foreach ($items as $item)
      <figure>
        <img src="{{ $item['url'] }}" alt="{{ $item['alt'] }}" loading="lazy" decoding="async">
        @if ($item['caption'] !== '')<figcaption>{{ $item['caption'] }}</figcaption>@endif
      </figure>
    @endforeach
  </div>
</div></section>
@endif
