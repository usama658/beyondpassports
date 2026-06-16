{{--
    Print-optimised document checklist — the "instant PDF" channel via the browser's
    "Save as PDF" (print-CSS fallback, NO PDF library / NO new Composer dependency).
    Rendered by App\Services\ChecklistPdfService::renderPrintable(). Self-contained
    (inline styles only) so it also renders identically if dompdf is later added against
    this same view.

    Expected: $request (ChecklistRequest), $destination (string), $items (array), $savedLink (string).
--}}
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>Your {{ $destination }} document checklist</title>
<style>
  :root { color-scheme: light; }
  *{box-sizing:border-box}
  body{font-family:"Plus Jakarta Sans",system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;color:#22282b;margin:0;padding:32px;background:#fff;line-height:1.5}
  .sheet{max-width:760px;margin:0 auto}
  .brand{display:flex;align-items:baseline;justify-content:space-between;border-bottom:2px solid #22282b;padding-bottom:12px;margin-bottom:8px}
  .brand .logo{font-size:18px;font-weight:700;color:#22282b;letter-spacing:.01em}
  .brand .url{font-family:"Plus Jakarta Sans",system-ui,-apple-system,sans-serif;font-size:11px;color:#697079}
  .meta{font-size:12px;color:#697079;margin:0 0 20px}
  .saved{font-size:12px;color:#697079;margin:0 0 18px}
  .saved a{color:#5C9A7B;word-break:break-all}
  .compliance{font-size:11px;color:#697079;background:#f7fafb;border:1px solid #e6e8ea;border-left:3px solid #C75D38;border-radius:8px;padding:12px 14px;margin:24px 0 0}
  .print-hint{font-size:12px;color:#22282b;background:#fff7e6;border:1px solid #f0dba8;border-radius:8px;padding:10px 14px;margin:0 0 22px}
  .print-btn{display:inline-block;margin-left:8px;padding:4px 10px;border:1px solid #C75D38;border-radius:6px;background:#C75D38;color:#fff;font-size:12px;cursor:pointer}
  @media print {
    body{padding:0}
    .print-hint{display:none}
    a[href]:after{content:""}
    .doc-checklist .dc-group{page-break-inside:avoid}
  }
</style>
</head>
<body>
  <div class="sheet">
    <div class="brand">
      <span class="logo">Beyond Passports</span>
      <span class="url">{{ rtrim((string) config('ukv.base_url', ''), '/') }}</span>
    </div>
    <p class="meta">{{ $destination }} &middot; Prepared {{ optional($request->created_at)->format('j M Y') ?? now()->format('j M Y') }}</p>

    <p class="print-hint">
      To keep a copy, use your browser's Print &rarr; <strong>Save as PDF</strong>.
      <button class="print-btn" onclick="window.print()" type="button">Print / Save as PDF</button>
    </p>

    @include('partials.doc-checklist', ['items' => $items, 'personalised' => true])

    <p class="saved">Saved online (share or revisit any time): <a href="{{ $savedLink }}">{{ $savedLink }}</a></p>

    <p class="compliance">
      Independent service — not a government website. Our service fee is separate from any government fee.
      Express tiers speed up our handling, not the government's decision. Always confirm the latest requirements
      with the official source linked in each item before you travel.
    </p>
  </div>
</body>
</html>
