{{-- Testimonial quote block. Self-contained quote card, scoped to .cms-quote with brand tokens.
     Editable: quote, attribution, star count. --}}
@php
  $quote = trim((string) ($data['quote'] ?? ''));
  $name = trim((string) ($data['name'] ?? ''));
  $detail = trim((string) ($data['detail'] ?? ''));
  $stars = (int) ($data['stars'] ?? 5);
@endphp
@if ($quote !== '')
<section class="cms-quote"><div class="wrap">
  <style>
    .cms-quote{font-family:"Outfit",system-ui,sans-serif;padding:8px 0}
    .cms-quote .cq-card{max-width:680px;margin:0 auto;background:#fff;border:1px solid #dde3ec;border-radius:18px;
      padding:30px 32px;text-align:center;box-shadow:0 18px 44px -32px rgba(40,50,70,.5)}
    .cms-quote .cq-stars{color:#f5a623;font-size:18px;letter-spacing:2px;margin:0 0 12px}
    .cms-quote .cq-q{font-size:clamp(17px,2.2vw,21px);line-height:1.5;color:#16222E;font-weight:600;margin:0 0 14px}
    .cms-quote .cq-a{font-size:14px;color:#697079;margin:0}
    .cms-quote .cq-a b{color:#155E7A}
  </style>
  <div class="cq-card">
    @if ($stars > 0)<div class="cq-stars" aria-label="{{ $stars }} stars">{!! str_repeat('&#9733;', $stars) !!}</div>@endif
    <p class="cq-q">&ldquo;{{ $quote }}&rdquo;</p>
    @if ($name !== '' || $detail !== '')
      <p class="cq-a"><b>{{ $name }}</b>@if($name !== '' && $detail !== '') · @endif{{ $detail }}</p>
    @endif
  </div>
</div></section>
@endif
