{{--
    Shared document checklist — Document Requirements Engine.

    PURE PRESENTATIONAL. No DB / service calls here. Compute $items in the
    controller/closure (RequirementService::preview() for destination + apply,
    ::for() for confirmation + track) and pass it in.

    Expected variables:
      $items         list<array{document_key:string,label:string,note:?string,category:string,mandatory:bool}>
      $personalised  bool (default false) — true = "Your document checklist" (post-apply,
                     from ::for()); false = "Documents you'll likely need" (pre-apply preview).

    Include with:
      @@include('partials.doc-checklist', ['items' => $items, 'personalised' => true])

    Empty $items => a gentle reassurance line (preview) / nothing heavy. Never errors.
--}}
@php
    /** @var array<int, array{document_key:string,label:string,note:?string,category:string,mandatory:bool}> $items */
    $items        = $items ?? [];
    $personalised = $personalised ?? false;

    // Friendly, ordered grouping labels. Any category not listed falls through to "Other documents".
    $categoryLabels = [
        'identity'  => 'Identity',
        'funding'   => 'Funding & finances',
        'logistics' => 'Travel & accommodation',
        'health'    => 'Health',
        'core'      => 'Core documents',
    ];

    // Group by category, preserving service order (already sorted by sort_order then label).
    $grouped = [];
    foreach ($items as $item) {
        $cat = $item['category'] ?? 'core';
        $grouped[$cat] ??= [];
        $grouped[$cat][] = $item;
    }

    // Order groups by the known label order, then any extras alphabetically.
    $orderedCats = array_keys($categoryLabels);
    uksort($grouped, function ($a, $b) use ($orderedCats) {
        $ia = array_search($a, $orderedCats, true);
        $ib = array_search($b, $orderedCats, true);
        $ia = $ia === false ? PHP_INT_MAX : $ia;
        $ib = $ib === false ? PHP_INT_MAX : $ib;
        return $ia === $ib ? strcmp($a, $b) : $ia <=> $ib;
    });

    $heading = $personalised ? 'Your document checklist' : "Documents you'll likely need";
@endphp

<div class="doc-checklist" role="region" aria-label="{{ $heading }}">
  <style>
    /* doc-checklist partial — self-contained, palette-matched (navy #0f2747 / gold #c8a24a, Inter).
       Scoped to .doc-checklist so it is safe to drop on any surface (some use ukv.css vars,
       some are standalone documents like confirmation/track). Uses literal colours, not vars. */
    .doc-checklist{font-family:Inter,system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;color:#1c2b33}
    .doc-checklist .dc-head{font-family:"Space Mono",ui-monospace,monospace;font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:#0e6e6e;margin:0 0 6px}
    .doc-checklist .dc-title{font-size:20px;font-weight:600;color:#0f2747;margin:0 0 4px;line-height:1.3}
    .doc-checklist .dc-intro{font-size:14px;color:#5d6f79;margin:0 0 18px;max-width:60ch}
    .doc-checklist .dc-group{margin:0 0 20px}
    .doc-checklist .dc-group:last-child{margin-bottom:0}
    .doc-checklist .dc-group-name{font-family:"Space Mono",ui-monospace,monospace;font-size:11px;letter-spacing:.1em;text-transform:uppercase;color:#0f2747;border-bottom:1px solid #dfe6ea;padding:0 0 7px;margin:0 0 12px}
    .doc-checklist .dc-sub{font-size:12px;font-weight:600;color:#5d6f79;margin:14px 0 8px;letter-spacing:.01em}
    .doc-checklist ul.dc-list{list-style:none;margin:0;padding:0}
    .doc-checklist ul.dc-list li{position:relative;padding:0 0 0 26px;margin:0 0 12px}
    .doc-checklist ul.dc-list li:last-child{margin-bottom:0}
    .doc-checklist ul.dc-list li::before{content:"";position:absolute;left:2px;top:6px;width:9px;height:9px;border-radius:2px;background:#c8a24a}
    .doc-checklist ul.dc-list.is-recommended li::before{background:transparent;border:1.5px solid #c8a24a}
    .doc-checklist .dc-label{display:block;font-weight:600;font-size:15px;color:#1c2b33;line-height:1.4}
    .doc-checklist .dc-note{display:block;font-size:13px;color:#5d6f79;margin-top:2px;line-height:1.5}
    .doc-checklist .dc-empty{font-size:14px;color:#5d6f79;background:#f7fafb;border:1px dashed #dfe6ea;border-left:3px solid #c8a24a;border-radius:8px;padding:14px 16px;margin:0}
  </style>

  <p class="dc-head">{{ $personalised ? 'Your checklist' : 'Before you apply' }}</p>
  <h2 class="dc-title">{{ $heading }}</h2>

  @if (empty($items))
    <p class="dc-empty">We'll confirm your exact documents after you apply — once we know your destination and your situation, we'll tell you precisely what to prepare.</p>
  @else
    <p class="dc-intro">
      @if ($personalised)
        Based on your details, here's what to have ready. We'll always confirm anything case-specific by email before submission.
      @else
        A general guide for this trip. Your exact list is confirmed once you apply and we know your full situation.
      @endif
    </p>

    @foreach ($grouped as $cat => $groupItems)
      @php
        $catLabel    = $categoryLabels[$cat] ?? 'Other documents';
        $mandatory   = array_values(array_filter($groupItems, fn ($i) => ! empty($i['mandatory'])));
        $recommended = array_values(array_filter($groupItems, fn ($i) => empty($i['mandatory'])));
      @endphp
      <div class="dc-group">
        <p class="dc-group-name">{{ $catLabel }}</p>

        @if (! empty($mandatory))
          <ul class="dc-list">
            @foreach ($mandatory as $item)
              <li>
                <span class="dc-label">{{ $item['label'] }}</span>
                @if (! empty($item['note']))
                  <span class="dc-note">{{ $item['note'] }}</span>
                @endif
              </li>
            @endforeach
          </ul>
        @endif

        @if (! empty($recommended))
          <p class="dc-sub">Recommended / if applicable</p>
          <ul class="dc-list is-recommended">
            @foreach ($recommended as $item)
              <li>
                <span class="dc-label">{{ $item['label'] }}</span>
                @if (! empty($item['note']))
                  <span class="dc-note">{{ $item['note'] }}</span>
                @endif
              </li>
            @endforeach
          </ul>
        @endif
      </div>
    @endforeach
  @endif
</div>
