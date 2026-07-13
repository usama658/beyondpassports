{{-- Video block. Parses a YouTube or Vimeo URL into a safe responsive iframe (youtube-nocookie for
     privacy). Only those two hosts resolve to an embed; anything else renders nothing, so an editor
     can never inject an arbitrary iframe. Editable: heading, url, caption. --}}
@php
  $url = trim((string) ($data['url'] ?? ''));
  $heading = trim((string) ($data['heading'] ?? ''));
  $caption = trim((string) ($data['caption'] ?? ''));
  $embed = null;

  // YouTube: watch?v=ID, youtu.be/ID, /embed/ID, /shorts/ID.
  if (preg_match('~(?:youtube(?:-nocookie)?\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{6,20})~i', $url, $m)) {
      $embed = 'https://www.youtube-nocookie.com/embed/'.$m[1];
  }
  // Vimeo: vimeo.com/ID (all-numeric).
  elseif (preg_match('~vimeo\.com/(?:video/)?(\d{6,12})~i', $url, $m)) {
      $embed = 'https://player.vimeo.com/video/'.$m[1];
  }
@endphp
@if ($embed !== null)
<section class="cms-video"><div class="wrap">
  <style>
    .cms-video{font-family:"Outfit",system-ui,sans-serif;color:#16222E;padding:8px 0}
    .cms-video .cv-h{font-size:clamp(22px,3vw,30px);font-weight:700;letter-spacing:-.02em;text-align:center;margin:0 0 20px;color:#16222E}
    .cms-video .cv-frame{position:relative;width:100%;aspect-ratio:16/9;border-radius:16px;overflow:hidden;border:1px solid #dde3ec;background:#0d151c}
    .cms-video .cv-frame iframe{position:absolute;inset:0;width:100%;height:100%;border:0}
    .cms-video .cv-cap{font-size:13px;color:#697079;text-align:center;margin:12px 0 0}
  </style>
  @if ($heading !== '')<h2 class="cv-h">{{ $heading }}</h2>@endif
  <div class="cv-frame">
    <iframe src="{{ $embed }}" title="{{ $heading !== '' ? $heading : 'Video' }}" loading="lazy"
      allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
      allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe>
  </div>
  @if ($caption !== '')<p class="cv-cap">{{ $caption }}</p>@endif
</div></section>
@endif
