{{-- Fine print block. Self-contained small muted disclaimer text, scoped to .cms-fineprint.
     Editable: the text only. Newlines become separate lines. --}}
@php
  $text = trim((string) ($data['text'] ?? ''));
@endphp
@if ($text !== '')
<section class="cms-fineprint"><div class="wrap">
  <style>
    .cms-fineprint{font-family:"Outfit",system-ui,sans-serif;padding:6px 0}
    .cms-fineprint p{max-width:760px;margin:0 auto;font-size:12.5px;line-height:1.6;color:#8a929b;text-align:center}
  </style>
  <p>{!! nl2br(e($text)) !!}</p>
</div></section>
@endif
