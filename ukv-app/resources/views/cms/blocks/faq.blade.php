{{-- FAQ block. Renders the themed .faq-e accordion (styled by ukv.css) so it matches the coded
     pages. Editable: eyebrow, heading, and a list of question/answer pairs. --}}
@php
  $items = array_values(array_filter((array) ($data['items'] ?? []), fn ($i) => trim((string) ($i['q'] ?? '')) !== ''));
  $heading = trim((string) ($data['heading'] ?? ''));
  $eyebrow = trim((string) ($data['eyebrow'] ?? '')) ?: 'Questions';
@endphp
@if ($heading !== '' && $items !== [])
<section class="faq-e"><div class="wrap">
  <div class="sec-head reveal"><p class="eyebrow">{{ $eyebrow }}</p><h2>{{ $heading }}</h2></div>
  <div class="faq-panel reveal">
    <div class="faqd">
    @foreach ($items as $item)
      <details>
        <summary>{{ $item['q'] }}</summary>
        <p>{{ $item['a'] ?? '' }}</p>
      </details>
    @endforeach
    </div>
  </div>
</div></section>
@endif
