{{-- Locked include block. Renders a whitelisted existing partial verbatim (no editable internals). --}}
@php
  $view = \App\Cms\Blocks\LockedIncludeBlock::PARTIALS[$data['partial'] ?? ''] ?? null;
@endphp
@if ($view)
  @includeIf($view)
@endif
