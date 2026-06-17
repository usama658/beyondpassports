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

    // Per-item icon: match the document_key/label against keywords -> a distinct line icon.
    $docIcon = function (array $item): string {
        $k = strtolower(($item['document_key'] ?? '').' '.($item['label'] ?? ''));
        $map = [
            'photo|picture|image' => 'photo',
            'passport' => 'passport',
            'bank|fund|statement|salary|income|money|financ|payment' => 'money',
            'accommod|hotel|booking|stay|address|residence' => 'bed',
            'flight|onward|return|itinerar|ticket|transport|travel date|dates|when' => 'plane',
            'insur|health|vaccin|medical|covid' => 'shield',
            'invit|letter|sponsor|employ|contact|email' => 'letter',
        ];
        $pick = 'doc';
        foreach ($map as $pat => $name) {
            if (preg_match('/'.$pat.'/', $k)) { $pick = $name; break; }
        }
        $p = 'fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"';
        $svgs = [
            'passport' => '<rect x="5" y="2.5" width="14" height="19" rx="2" '.$p.'/><circle cx="12" cy="10" r="3" '.$p.'/><path d="M9 17h6" '.$p.'/>',
            'photo'    => '<rect x="3" y="5" width="18" height="14" rx="2" '.$p.'/><circle cx="8.5" cy="10.5" r="1.8" '.$p.'/><path d="M3 17l5-4 4 3 3-2 6 4" '.$p.'/>',
            'money'    => '<rect x="2" y="6" width="20" height="12" rx="2" '.$p.'/><circle cx="12" cy="12" r="2.5" '.$p.'/>',
            'bed'      => '<path d="M3 18V8m0 5h18m0 5v-6a3 3 0 0 0-3-3H8" '.$p.'/><circle cx="6.5" cy="10.5" r="1.6" '.$p.'/>',
            'plane'    => '<path d="M2 13l20-7-7 20-3-8-8-3z" '.$p.'/>',
            'shield'   => '<path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z" '.$p.'/><path d="M9 12l2 2 4-4" '.$p.'/>',
            'letter'   => '<rect x="3" y="5" width="18" height="14" rx="2" '.$p.'/><path d="M3.5 6.5 12 13l8.5-6.5" '.$p.'/>',
            'doc'      => '<path d="M7 3h7l5 5v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1z" '.$p.'/><path d="M14 3v5h5M9 13h6M9 17h6" '.$p.'/>',
        ];
        return '<svg class="dc-ic" viewBox="0 0 24 24" aria-hidden="true">'.($svgs[$pick] ?? $svgs['doc']).'</svg>';
    };
@endphp

<div class="doc-checklist" role="region" aria-label="{{ $heading }}">
  <style>
    /* doc-checklist partial — self-contained, warm-light palette (ink/terracotta/sage, Plus Jakarta).
       Scoped to .doc-checklist so it is safe to drop on any surface (some use ukv.css vars,
       some are standalone documents like confirmation/track). Uses literal colours, not vars. */
    .doc-checklist{font-family:"Plus Jakarta Sans",system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;color:#22282b}
    .doc-checklist .dc-head{font-family:"Plus Jakarta Sans",system-ui,sans-serif;font-weight:700;font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:#3f7259;margin:0 0 6px}
    .doc-checklist .dc-title{font-size:clamp(22px,2.6vw,28px);font-weight:700;letter-spacing:-.01em;color:#22282b;margin:0 0 6px;line-height:1.2}
    .doc-checklist .dc-intro{font-size:14px;color:#697079;margin:0 0 18px;max-width:60ch}
    .doc-checklist .dc-panel{background:#fff;border:1px solid #e6e8ea;border-radius:18px;padding:22px 28px;box-shadow:0 16px 40px -30px rgba(40,50,70,.5)}
    .doc-checklist .dc-group{margin:0 0 20px}
    .doc-checklist .dc-group:last-child{margin-bottom:0}
    .doc-checklist .dc-group-name{font-family:"Plus Jakarta Sans",system-ui,sans-serif;font-weight:700;font-size:11px;letter-spacing:.1em;text-transform:uppercase;color:#22282b;border-bottom:1px solid #e6e8ea;padding:0 0 7px;margin:0 0 12px}
    .doc-checklist .dc-sub{font-size:12px;font-weight:600;color:#697079;margin:14px 0 8px;letter-spacing:.01em}
    .doc-checklist ul.dc-list{list-style:none;margin:0;padding:0}
    .doc-checklist ul.dc-list li{position:relative;padding:0 0 0 32px;margin:0 0 14px}
    .doc-checklist ul.dc-list li:last-child{margin-bottom:0}
    .doc-checklist .dc-ic{position:absolute;left:0;top:1px;width:20px;height:20px;color:#C75D38}
    .doc-checklist ul.dc-list.is-recommended .dc-ic{color:#3f7259}
    .doc-checklist .dc-label{display:block;font-weight:600;font-size:15px;color:#22282b;line-height:1.4}
    .doc-checklist .dc-note{display:block;font-size:13px;color:#697079;margin-top:2px;line-height:1.5}
    .doc-checklist .dc-empty{font-size:14px;color:#697079;background:#f7fafb;border:1px dashed #e6e8ea;border-left:3px solid #C75D38;border-radius:8px;padding:14px 16px;margin:0}
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

    <div class="dc-panel">
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
                {!! $docIcon($item) !!}
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
                {!! $docIcon($item) !!}
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
    </div>
  @endif
</div>
