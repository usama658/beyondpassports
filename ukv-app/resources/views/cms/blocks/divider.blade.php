{{-- Divider block. Self-contained spacer / rule / dots, scoped to .cms-divider with brand tokens.
     No editable text. Editable: size (space) + style. Always renders (structural). --}}
@php
  $size = in_array(($data['size'] ?? 'm'), ['s', 'm', 'l'], true) ? $data['size'] : 'm';
  $style = in_array(($data['style'] ?? 'space'), ['space', 'line', 'dots'], true) ? $data['style'] : 'space';
  $pad = ['s' => '14px', 'm' => '30px', 'l' => '54px'][$size];
@endphp
<section class="cms-divider cms-divider-{{ $style }}" style="--cd-pad:{{ $pad }}">
  <style>
    .cms-divider{padding:var(--cd-pad) 0}
    .cms-divider-line .cd-in{max-width:1120px;margin:0 auto;padding:0 20px}
    .cms-divider-line hr{border:0;border-top:1px solid #dde3ec;margin:0}
    .cms-divider-dots{text-align:center;color:#9aa4af;font-size:14px;letter-spacing:.5em}
  </style>
  @if ($style === 'line')
    <div class="cd-in"><hr></div>
  @elseif ($style === 'dots')
    <div aria-hidden="true">&bull;&bull;&bull;</div>
  @endif
</section>
