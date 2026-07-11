{{-- Custom Trustpilot block. If a real rating is configured (config ukv.trustpilot.rating),
     shows a Trustpilot-style stars + score + count widget; otherwise a "Review us" CTA.
     Honest: rating/count are typed by hand from the real Trustpilot profile (manual sync),
     never invented. Params: $theme ('light'|'dark'), $align, $margin. --}}
@php
    $tp = config('ukv.trustpilot');
    $tpcDomain = $tp['domain'] ?? 'beyondpassports.co.uk';
    $tpcUrl = $tp['profile_url'] ?: 'https://uk.trustpilot.com/review/'.$tpcDomain;
    $tpcEval = 'https://www.trustpilot.com/evaluate/'.$tpcDomain;
    $tpcDark = ($theme ?? 'light') === 'dark';
    $tpcAlign = $align ?? 'left';
    $tpcMargin = $margin ?? '14px 0 4px';
    $rating = (float) ($tp['rating'] ?? 0);
    $count  = $tp['reviews_count'] ?? null;
    $hasRating = $rating > 0;
    $pct = $hasRating ? min(100, round($rating / 5 * 100, 1)) : 0;
    $word = $rating >= 4.3 ? 'Excellent' : ($rating >= 3.5 ? 'Great' : ($rating >= 2.5 ? 'Average' : ($rating >= 1.5 ? 'Poor' : 'Bad')));
    $starSvg = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l2.9 6.6 7.1.6-5.4 4.7 1.6 7L12 17.8 5.8 21.5l1.6-7L2 9.8l7.1-.6z"/></svg>';
@endphp
@if ($tp['enabled'] ?? false)
<div style="text-align:{{ $tpcAlign }};margin:{{ $tpcMargin }}">
@if ($hasRating)
  <a class="tpr {{ $tpcDark ? 'tpr--dark' : '' }}" href="{{ $tpcUrl }}" target="_blank" rel="noopener"
     aria-label="Rated {{ $rating }} out of 5 on Trustpilot from {{ $count }} reviews">
    <span class="tpr-stars">
      <span class="tpr-row tpr-bg">{!! str_repeat('<i class="tpr-box">'.$starSvg.'</i>', 5) !!}</span>
      <span class="tpr-row tpr-fg" style="width:{{ $pct }}%">{!! str_repeat('<i class="tpr-box">'.$starSvg.'</i>', 5) !!}</span>
    </span>
    <span class="tpr-meta"><b>TrustScore {{ rtrim(rtrim(number_format($rating,1),'0'),'.') }}</b></span>
    <span class="tpr-logo">{!! $starSvg !!} Trustpilot</span>
  </a>
@else
  <a class="tpc {{ $tpcDark ? 'tpc--dark' : '' }}" href="{{ $tpcEval }}" target="_blank" rel="noopener" aria-label="Review us on Trustpilot">
    <span class="tpc-star">{!! $starSvg !!}</span>
    <span class="tpc-tx">Review us on <b>Trustpilot</b></span>
  </a>
@endif
</div>
@endif
@once
<style>
  /* CTA (no rating yet) */
  .tpc{display:inline-flex;align-items:center;gap:9px;text-decoration:none;border-radius:10px;
    padding:9px 14px;font:700 14px var(--display);color:var(--ink);background:#fff;border:1px solid var(--paper-edge);transition:.15s}
  .tpc:hover{border-color:#00b67a;transform:translateY(-1px);box-shadow:0 12px 26px -16px rgba(0,182,122,.6)}
  .tpc-star{width:24px;height:24px;border-radius:5px;background:#00b67a;display:grid;place-items:center;flex:none}
  .tpc-star svg{width:16px;height:16px;fill:#fff}
  .tpc b{font-weight:800}
  .tpc--dark{background:rgba(255,255,255,.08);border-color:rgba(255,255,255,.2);color:#fff}
  .tpc--dark:hover{background:rgba(255,255,255,.14);border-color:#00b67a}
  .ft-main .cols a.tpc{display:inline-flex;width:auto;padding:9px 14px;color:#fff}
  .ft-main .cols a.tpc:hover{color:#fff}

  /* Rating widget (Trustpilot-style) */
  .tpr{display:inline-flex;flex-direction:row;align-items:center;flex-wrap:wrap;gap:8px;text-decoration:none;color:var(--ink)}
  .tpr-word{font:800 14px var(--display);letter-spacing:.01em}
  .tpr-stars{position:relative;display:inline-block;line-height:0}
  .tpr-row{display:inline-flex;gap:3px;white-space:nowrap}
  .tpr-fg{position:absolute;top:0;left:0;overflow:hidden}
  .tpr-box{width:23px;height:23px;border-radius:4px;display:inline-grid;place-items:center;flex:none}
  .tpr-bg .tpr-box{background:#dcdce6}
  .tpr-fg .tpr-box{background:#00b67a}
  .tpr-box svg{width:15px;height:15px;fill:#fff}
  .tpr-meta{font-size:13px;color:var(--ink-soft)}
  .tpr-meta b{color:var(--ink);font-weight:700}
  .tpr-sep{opacity:.5;margin:0 2px}
  .tpr-logo{display:inline-flex;align-items:center;gap:5px;font:800 13px var(--display);color:var(--ink)}
  .tpr-logo svg{width:16px;height:16px;fill:#00b67a}
  .tpr--dark .tpr-word, .tpr--dark .tpr-meta b, .tpr--dark .tpr-logo{color:#fff}
  .tpr--dark .tpr-meta{color:#c7cfd6}
  .ft-main .cols a.tpr{display:inline-flex;width:auto;padding:0;color:#fff}
</style>
@endonce
