{{-- Contact cards block. Self-contained channel tile grid, scoped to .cms-contact with brand tokens.
     Editable: optional heading + cards (title, text, button label, button url). --}}
@php
  $items = array_values(array_filter((array) ($data['items'] ?? []), fn ($i) => trim((string) ($i['title'] ?? '')) !== ''));
  $heading = trim((string) ($data['heading'] ?? ''));
@endphp
@if ($items !== [])
<section class="cms-contact"><div class="wrap">
  <style>
    .cms-contact{font-family:"Outfit",system-ui,sans-serif;color:#16222E;padding:8px 0}
    .cms-contact .cn-h{font-size:clamp(22px,3vw,30px);font-weight:700;letter-spacing:-.02em;text-align:center;margin:0 0 24px;color:#16222E}
    .cms-contact .cn-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px}
    .cms-contact .cn-card{background:#fff;border:1px solid #dde3ec;border-radius:16px;padding:22px;display:flex;flex-direction:column;box-shadow:0 16px 40px -34px rgba(40,50,70,.5)}
    .cms-contact .cn-t{font-size:17px;font-weight:700;margin:0 0 6px;color:#16222E}
    .cms-contact .cn-x{font-size:14px;color:#697079;margin:0 0 16px;line-height:1.5;flex:1}
    .cms-contact .cn-btn{display:inline-block;align-self:flex-start;background:#155E7A;color:#fff;font-weight:700;font-size:14px;text-decoration:none;padding:10px 18px;border-radius:10px}
  </style>
  @if ($heading !== '')<h2 class="cn-h">{{ $heading }}</h2>@endif
  <div class="cn-grid">
    @foreach ($items as $item)
      <div class="cn-card">
        <p class="cn-t">{{ $item['title'] }}</p>
        @if (trim((string) ($item['text'] ?? '')) !== '')<p class="cn-x">{{ $item['text'] }}</p>@endif
        @php $bl = trim((string) ($item['button_label'] ?? '')); $bu = trim((string) ($item['button_url'] ?? '')); @endphp
        @if ($bl !== '' && $bu !== '')<a class="cn-btn" href="{{ $bu }}">{{ $bl }}</a>@endif
      </div>
    @endforeach
  </div>
</div></section>
@endif
