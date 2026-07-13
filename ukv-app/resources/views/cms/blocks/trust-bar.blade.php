{{-- Trust bar block. Renders the themed .tbar-f strip (styled by ukv.css) so it matches the coded
     pages. Each item = a check icon + bold lead + rest. --}}
@php
  $items = array_values(array_filter((array) ($data['items'] ?? []), fn ($i) => trim((string) ($i['bold'] ?? '')) !== ''));
@endphp
@if ($items !== [])
<section class="tbar-f"><div class="wrap"><div class="row">
@foreach ($items as $item)
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 5 6v6c0 4.5 3 7.5 7 8.5 4-1 7-4 7-8.5V6z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="m9 12 2 2 4-4.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg><span><b>{{ $item['bold'] }}</b>@if(trim((string)($item['rest'] ?? '')) !== '') {{ $item['rest'] }}@endif</span></span>
@endforeach
</div></div></section>
@endif
