{{-- Custom Trustpilot CTA (our own markup, no iframe). Links to the public review page.
     Honest: shows the Trustpilot star + wordmark, not an invented rating.
     Params: $theme ('light'|'dark'), $align ('left'|'center'), $margin (css). --}}
@php
    $tp = config('ukv.trustpilot');
    $tpcDomain = $tp['domain'] ?? 'beyondpassports.co.uk';
    $tpcUrl = $tp['profile_url'] ?: 'https://www.trustpilot.com/evaluate/'.$tpcDomain;
    $tpcDark = ($theme ?? 'light') === 'dark';
    $tpcAlign = $align ?? 'left';
    $tpcMargin = $margin ?? '14px 0 4px';
@endphp
<div style="text-align:{{ $tpcAlign }};margin:{{ $tpcMargin }}">
  <a class="tpc {{ $tpcDark ? 'tpc--dark' : '' }}" href="{{ $tpcUrl }}" target="_blank" rel="noopener" aria-label="Review us on Trustpilot">
    <span class="tpc-star"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l2.9 6.6 7.1.6-5.4 4.7 1.6 7L12 17.8 5.8 21.5l1.6-7L2 9.8l7.1-.6z"/></svg></span>
    <span class="tpc-tx">Review us on <b>Trustpilot</b></span>
  </a>
</div>
@once
<style>
  .tpc{display:inline-flex;align-items:center;gap:9px;text-decoration:none;border-radius:10px;
    padding:9px 14px;font:700 14px var(--display);color:var(--ink);background:#fff;border:1px solid var(--paper-edge);transition:.15s}
  .tpc:hover{border-color:#00b67a;transform:translateY(-1px);box-shadow:0 12px 26px -16px rgba(0,182,122,.6)}
  .tpc-star{width:24px;height:24px;border-radius:5px;background:#00b67a;display:grid;place-items:center;flex:none}
  .tpc-star svg{width:16px;height:16px;fill:#fff}
  .tpc b{font-weight:800}
  .tpc--dark{background:rgba(255,255,255,.08);border-color:rgba(255,255,255,.2);color:#fff}
  .tpc--dark:hover{background:rgba(255,255,255,.14);border-color:#00b67a}
  /* beat the generic ".ft-main .cols a{display:block;padding}" rule in the footer */
  .ft-main .cols a.tpc{display:inline-flex;width:auto;padding:9px 14px;color:#fff}
  .ft-main .cols a.tpc:hover{color:#fff}
</style>
@endonce
