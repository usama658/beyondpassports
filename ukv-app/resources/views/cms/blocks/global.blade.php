{{-- Reusable block reference. Resolves the referenced GlobalBlock and renders its inner block's
     partial with the global block's data, so the output is identical to placing that block inline. --}}
@php
  $gb = \App\Models\GlobalBlock::find($data['global_id'] ?? null);
  $registry = app(\App\Cms\BlockRegistry::class);
  $innerView = $gb ? $registry->view($gb->type) : null;
@endphp
@if ($gb && $innerView)
  @include($innerView, ['data' => $gb->data ?? []])
@endif
