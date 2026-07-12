{{-- Image block. Lazy-loaded; width/height (when set) reserve space to avoid layout shift.
     A media-library reference (media_id) takes precedence over a one-off upload (src), and supplies
     its default alt text when the block doesn't override it. --}}
@php
  $media = ! empty($data['media_id']) ? \App\Models\Media::find($data['media_id']) : null;
  $url = null;
  $alt = trim((string) ($data['alt'] ?? ''));
  if ($media) {
      $url = $media->url();
      if ($alt === '') { $alt = (string) ($media->alt ?? ''); }
  } else {
      $src = trim((string) ($data['src'] ?? ''));
      if ($src !== '') {
          $url = \Illuminate\Support\Str::startsWith($src, ['http://', 'https://', '/']) ? $src : asset('storage/'.$src);
      }
  }
@endphp
@if ($url)
<figure class="cms-image"><div class="wrap">
  <img src="{{ $url }}" alt="{{ $alt }}" loading="lazy" decoding="async"@if(!empty($data['width'])) width="{{ (int) $data['width'] }}"@endif @if(!empty($data['height'])) height="{{ (int) $data['height'] }}"@endif style="max-width:100%;height:auto;border-radius:12px;display:block;margin:0 auto">
  @if(!empty($data['caption']))<figcaption style="text-align:center;color:var(--muted,#5d6b76);font-size:13.5px;margin-top:10px">{{ $data['caption'] }}</figcaption>@endif
</div></figure>
@endif
