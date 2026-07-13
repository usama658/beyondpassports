{{-- Testimonials block. Self-contained quote grid, scoped to .cms-testimonials with brand tokens.
     Only genuine, consented testimonials. Editable: heading + quote cards (quote, name, detail). --}}
@php
  $items = array_values(array_filter((array) ($data['items'] ?? []), fn ($i) => trim((string) ($i['quote'] ?? '')) !== ''));
  $heading = trim((string) ($data['heading'] ?? ''));
@endphp
@if ($items !== [])
<section class="cms-testimonials"><div class="wrap">
  <style>
    .cms-testimonials{font-family:"Outfit",system-ui,sans-serif;color:#16222E;padding:8px 0}
    .cms-testimonials .ct-h{font-size:clamp(22px,3vw,30px);font-weight:700;letter-spacing:-.02em;text-align:center;margin:0 0 26px;color:#16222E}
    .cms-testimonials .ct-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:18px}
    .cms-testimonials .ct-card{background:#fff;border:1px solid #dde3ec;border-radius:16px;padding:22px 22px 20px;box-shadow:0 16px 40px -34px rgba(40,50,70,.5)}
    .cms-testimonials .ct-q{font-size:15.5px;line-height:1.6;color:#3a434d;margin:0 0 14px}
    .cms-testimonials .ct-q::before{content:"\201C";color:#155E7A;font-weight:800;margin-right:2px}
    .cms-testimonials .ct-n{font-size:14px;font-weight:700;color:#16222E;margin:0}
    .cms-testimonials .ct-d{font-size:13px;color:#697079;margin:2px 0 0}
  </style>
  @if ($heading !== '')<h2 class="ct-h">{{ $heading }}</h2>@endif
  <div class="ct-grid">
    @foreach ($items as $item)
      <figure class="ct-card">
        <blockquote class="ct-q">{{ $item['quote'] }}</blockquote>
        <figcaption>
          <p class="ct-n">{{ $item['name'] }}</p>
          @if (trim((string) ($item['detail'] ?? '')) !== '')<p class="ct-d">{{ $item['detail'] }}</p>@endif
        </figcaption>
      </figure>
    @endforeach
  </div>
</div></section>
@endif
