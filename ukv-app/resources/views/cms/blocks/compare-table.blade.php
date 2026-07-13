{{-- Comparison table block. Self-contained two-column feature matrix, scoped to .cms-compare with
     brand tokens. Editable: heading, two column labels, rows (label, has_a, has_b). --}}
@php
  $items = array_values(array_filter((array) ($data['items'] ?? []), fn ($i) => trim((string) ($i['label'] ?? '')) !== ''));
  $heading = trim((string) ($data['heading'] ?? ''));
  $colA = trim((string) ($data['col_a'] ?? 'With us')) ?: 'With us';
  $colB = trim((string) ($data['col_b'] ?? 'Elsewhere')) ?: 'Elsewhere';
@endphp
@if ($items !== [])
<section class="cms-compare"><div class="wrap">
  <style>
    .cms-compare{font-family:"Outfit",system-ui,sans-serif;color:#16222E;padding:8px 0}
    .cms-compare .cc-h{font-size:clamp(22px,3vw,30px);font-weight:700;letter-spacing:-.02em;text-align:center;margin:0 0 22px;color:#16222E}
    .cms-compare .cc-wrap{max-width:680px;margin:0 auto;overflow-x:auto}
    .cms-compare table{width:100%;border-collapse:collapse;background:#fff;border:1px solid #dde3ec;border-radius:16px;overflow:hidden}
    .cms-compare th,.cms-compare td{padding:13px 16px;text-align:left;border-bottom:1px solid #eef1f6;font-size:14.5px}
    .cms-compare thead th{background:#f4f7fa;font-weight:700;color:#16222E}
    .cms-compare thead th.cc-a{color:#155E7A}
    .cms-compare th.cc-c,.cms-compare td.cc-c{text-align:center;width:110px}
    .cms-compare tbody tr:last-child td{border-bottom:0}
    .cms-compare .cc-yes{color:#2E7D57;font-weight:800}
    .cms-compare .cc-no{color:#c0c6cf;font-weight:800}
    .cms-compare .cc-feat{font-weight:600}
  </style>
  @if ($heading !== '')<h2 class="cc-h">{{ $heading }}</h2>@endif
  <div class="cc-wrap">
    <table>
      <thead><tr><th></th><th class="cc-c cc-a">{{ $colA }}</th><th class="cc-c">{{ $colB }}</th></tr></thead>
      <tbody>
        @foreach ($items as $item)
          <tr>
            <td class="cc-feat">{{ $item['label'] }}</td>
            <td class="cc-c">@if (! empty($item['has_a']))<span class="cc-yes" aria-label="Yes">&#10003;</span>@else<span class="cc-no" aria-label="No">&#10007;</span>@endif</td>
            <td class="cc-c">@if (! empty($item['has_b']))<span class="cc-yes" aria-label="Yes">&#10003;</span>@else<span class="cc-no" aria-label="No">&#10007;</span>@endif</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div></section>
@endif
