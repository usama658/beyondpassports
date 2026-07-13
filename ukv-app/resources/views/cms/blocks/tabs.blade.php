{{-- Tabs block. Self-contained no-JS tabbed panels (pure-CSS radio inputs), scoped to .cms-tabs with
     brand tokens. A per-instance group name (uniqid) keeps multiple tab blocks on one page from
     colliding. Editable: optional heading + tabs (label, body); first tab selected by default. --}}
@php
  $items = array_values(array_filter((array) ($data['items'] ?? []), fn ($i) => trim((string) ($i['label'] ?? '')) !== '' && trim((string) ($i['body'] ?? '')) !== ''));
  $heading = trim((string) ($data['heading'] ?? ''));
  $gid = 'tabs'.uniqid();
@endphp
@if (count($items) >= 2)
<section class="cms-tabs"><div class="wrap">
  <style>
    .cms-tabs{font-family:"Outfit",system-ui,sans-serif;color:#16222E;padding:8px 0}
    .cms-tabs .ctb-h{font-size:clamp(22px,3vw,30px);font-weight:700;letter-spacing:-.02em;text-align:center;margin:0 0 22px;color:#16222E}
    .cms-tabs .ctb{max-width:720px;margin:0 auto}
    .cms-tabs .ctb-labels{display:flex;flex-wrap:wrap;gap:6px;border-bottom:2px solid #e3e8ef;margin:0 0 18px}
    .cms-tabs input[type=radio]{position:absolute;opacity:0;pointer-events:none}
    .cms-tabs .ctb-label{cursor:pointer;padding:10px 18px;font-weight:700;font-size:15px;color:#697079;border-bottom:3px solid transparent;margin-bottom:-2px}
    .cms-tabs .ctb-panel{display:none;font-size:15px;line-height:1.65;color:#3a434d}
    .cms-tabs .ctb-panel p{margin:0 0 10px}
    @foreach ($items as $i => $item)
      #{{ $gid }}-{{ $i }}:checked ~ .ctb-labels label[for="{{ $gid }}-{{ $i }}"]{color:#155E7A;border-bottom-color:#155E7A}
      #{{ $gid }}-{{ $i }}:checked ~ .ctb-panels .ctb-panel-{{ $i }}{display:block}
    @endforeach
  </style>
  @if ($heading !== '')<h2 class="ctb-h">{{ $heading }}</h2>@endif
  <div class="ctb">
    @foreach ($items as $i => $item)
      <input type="radio" name="{{ $gid }}" id="{{ $gid }}-{{ $i }}" @if ($i === 0) checked @endif>
    @endforeach
    <div class="ctb-labels">
      @foreach ($items as $i => $item)
        <label class="ctb-label" for="{{ $gid }}-{{ $i }}">{{ $item['label'] }}</label>
      @endforeach
    </div>
    <div class="ctb-panels">
      @foreach ($items as $i => $item)
        <div class="ctb-panel ctb-panel-{{ $i }}">
          @foreach (preg_split('/\n{2,}/', trim((string) $item['body'])) as $para)
            <p>{{ $para }}</p>
          @endforeach
        </div>
      @endforeach
    </div>
  </div>
</div></section>
@endif
