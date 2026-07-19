{{-- Site-wide floating WhatsApp button. Chat = the universal capture channel. --}}
<div class="wa-float" data-wa-float>
    {{-- "Got a question?" prompt bubble (dark brand pill). Auto-shows after a delay,
         dismissible, stays hidden for the rest of the session. Desktop only. --}}
    <div class="wa-bub" id="waBub" hidden>
        <span class="wa-bub__dot" aria-hidden="true"></span>
        <button type="button" class="wa-bub__x" id="waBubX" aria-label="Dismiss">&times;</button>
        <b>Got a question?</b>
        <span>Talk to a specialist now.</span>
    </div>
    @include('partials.wa-cta', [
        'message' => "Hi Beyond Passports, I'd like some help with my trip.",
        'label' => 'Chat with us',
        'variant' => 'floating',
    ])
</div>
@once
<style>
  .wa-float{position:fixed;right:18px;bottom:18px;z-index:35}
  /* "Got a question?" bubble — dark brand pill above the button. */
  .wa-bub{position:absolute;right:0;bottom:64px;width:236px;background:linear-gradient(120deg,#14262f,#0f1e26);color:#fff;
    border:1px solid #26424d;border-radius:14px;padding:12px 30px 12px 14px;box-shadow:0 22px 44px -22px rgba(0,0,0,.55);
    font-family:"Outfit",system-ui,sans-serif;animation:waBubIn .28s ease}
  .wa-bub b{display:block;font:800 14px "Outfit",system-ui,sans-serif;color:#fff}
  .wa-bub span:not(.wa-bub__dot){display:block;font-size:12.5px;color:#cfe0dd;margin-top:2px}
  .wa-bub__dot{position:absolute;top:12px;right:34px;width:8px;height:8px;border-radius:50%;background:#37d19a;box-shadow:0 0 0 4px rgba(55,209,154,.2)}
  .wa-bub__x{position:absolute;top:6px;right:7px;width:19px;height:19px;border:0;border-radius:50%;background:rgba(255,255,255,.14);color:#cfe0dd;font-size:13px;line-height:1;cursor:pointer;padding:0}
  .wa-bub__x:hover{background:rgba(255,255,255,.26);color:#fff}
  @keyframes waBubIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:none}}
  @media (prefers-reduced-motion:reduce){.wa-bub{animation:none}}
  /* Mobile: collapse the button to a circular FAB (glyph only). Keep the bubble but shrink it
     and pin it to the viewport edge so it fits above the round FAB. */
  @media (max-width:620px){
    .wa-float{right:14px;bottom:14px}
    .wa-float .wa-cta{padding:14px;border-radius:50%}
    .wa-float .wa-cta__label{display:none}
    .wa-bub{position:fixed;right:14px;bottom:80px;left:auto;width:auto;max-width:calc(100vw - 28px);padding:10px 30px 10px 13px}
    .wa-bub b{font-size:13.5px}
    .wa-bub span:not(.wa-bub__dot){font-size:12px}
    .wa-bub__dot{top:11px;right:32px}
  }
</style>
<script>
  (function () {
    var bub = document.getElementById('waBub');
    if (!bub) return;
    var KEY = 'waBubDismissed';
    try { if (sessionStorage.getItem(KEY)) return; } catch (e) {}
    // Show after a short delay so it doesn't nag on arrival (desktop + mobile).
    var t = setTimeout(function () { bub.hidden = false; }, 6000);
    var x = document.getElementById('waBubX');
    if (x) x.addEventListener('click', function () {
      clearTimeout(t); bub.hidden = true;
      try { sessionStorage.setItem(KEY, '1'); } catch (e) {}
    });
  })();
</script>
@endonce
