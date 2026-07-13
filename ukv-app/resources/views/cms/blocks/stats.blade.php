{{-- Stats band block. Self-contained row of big-number metrics, scoped to .cms-stats with brand
     tokens. Editable: the stats (number + label). --}}
@php
  $items = array_values(array_filter((array) ($data['items'] ?? []), fn ($i) => trim((string) ($i['number'] ?? '')) !== ''));
@endphp
@if ($items !== [])
<section class="cms-stats"><div class="wrap">
  <style>
    .cms-stats{font-family:"Outfit",system-ui,sans-serif;padding:8px 0}
    .cms-stats .cst-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:16px;
      background:linear-gradient(135deg,#155E7A,#124f66);border-radius:18px;padding:26px 22px;box-shadow:0 20px 44px -30px rgba(21,94,122,.7)}
    .cms-stats .cst{text-align:center;color:#fff}
    .cms-stats .cst-n{font-size:clamp(28px,4vw,40px);font-weight:800;letter-spacing:-.02em;line-height:1}
    .cms-stats .cst-l{font-size:13.5px;color:#dff0f5;margin-top:8px;line-height:1.4}
  </style>
  <div class="cst-row">
    @foreach ($items as $item)
      <div class="cst">
        <div class="cst-n">{{ $item['number'] }}</div>
        <div class="cst-l">{{ $item['label'] ?? '' }}</div>
      </div>
    @endforeach
  </div>
</div></section>
@endif
