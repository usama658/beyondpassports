{{-- Cookie consent banner (UK PECR / GDPR). Non-essential third-party scripts
     (currently Trustpilot) load ONLY after the visitor accepts. Choice stored in
     the `ukv_consent` cookie for 180 days. No JS = no banner + no non-essential
     scripts load, so the default state is compliant.

     To gate another non-essential script later, add it inside loadAcceptedScripts(). --}}
@once
<div id="ck-consent" class="ck-consent" role="dialog" aria-live="polite" aria-label="Cookie choices" hidden>
  <div class="ck-inner">
    <p class="ck-text">
      We use a few cookies to run this site and, with your permission, third-party cookies
      (Trustpilot reviews and Google Analytics) to show reviews and understand how the site is used.
      See our <a href="{{ url('/legal') }}#cookies">cookie &amp; privacy notice</a>.
    </p>
    <div class="ck-actions">
      <button type="button" class="ck-btn ck-reject" id="ck-reject">Reject non-essential</button>
      <button type="button" class="ck-btn ck-accept" id="ck-accept">Accept all</button>
    </div>
  </div>
</div>
<style>
  .ck-consent{position:fixed;left:16px;right:16px;bottom:16px;z-index:9999;background:#fff;
    border:1px solid var(--paper-edge,#dde3ec);border-radius:14px;box-shadow:0 24px 60px -24px rgba(20,30,45,.5);
    padding:16px 18px}
  .ck-consent .ck-inner{display:flex;flex-wrap:wrap;gap:12px 18px;align-items:center;justify-content:space-between;max-width:1100px;margin:0 auto}
  .ck-consent .ck-text{margin:0;font:500 13.5px/1.5 var(--body,sans-serif);color:var(--ink,#16222E);max-width:64ch}
  .ck-consent .ck-text a{color:var(--cta,#155E7A);font-weight:700}
  .ck-consent .ck-actions{display:flex;gap:10px;flex:none}
  .ck-consent .ck-btn{font:800 14px var(--display,sans-serif);border-radius:10px;padding:11px 18px;cursor:pointer;border:1px solid var(--paper-edge,#dde3ec)}
  .ck-consent .ck-reject{background:#fff;color:var(--ink,#16222E)}
  .ck-consent .ck-reject:hover{border-color:var(--soft,#A9CCDA)}
  .ck-consent .ck-accept{background:var(--cta,#155E7A);color:#fff;border-color:var(--cta,#155E7A)}
  .ck-consent .ck-accept:hover{filter:brightness(1.06)}
  @media(max-width:560px){.ck-consent .ck-actions{width:100%}.ck-consent .ck-btn{flex:1}}
</style>
<script>
(function () {
  var NAME = 'ukv_consent';
  function get(n){ return document.cookie.split('; ').reduce(function(a,c){var p=c.split('=');return p[0]===n?decodeURIComponent(p[1]):a;},''); }
  function set(v){ var d=new Date(); d.setTime(d.getTime()+180*864e5); document.cookie=NAME+'='+v+'; expires='+d.toUTCString()+'; path=/; SameSite=Lax'; }
  var loaded=false;
  function loadAcceptedScripts(){
    if(loaded) return; loaded=true;
    // Trustpilot TrustBox bootstrap (non-essential — reviews)
    var s=document.createElement('script');
    s.async=true; s.src='https://widget.trustpilot.com/bootstrap/v5/tp.widget.bootstrap.min.js';
    document.head.appendChild(s);
@if (config('ukv.gtm_id'))
    // Google Tag Manager (non-essential — analytics/marketing). Consent-gated.
    (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','{{ config('ukv.gtm_id') }}');
@endif
@if (config('ukv.ga4_id'))
    // Google Analytics 4 (non-essential — analytics). Consent-gated.
    (function(){var g=document.createElement('script');g.async=true;g.src='https://www.googletagmanager.com/gtag/js?id={{ config('ukv.ga4_id') }}';document.head.appendChild(g);})();
    window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','{{ config('ukv.ga4_id') }}');
@endif
@if (config('ukv.clarity_id'))
    // Microsoft Clarity (non-essential — analytics). Consent-gated.
    (function(c,l,a,r,i,t,y){c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};t=l.createElement(r);t.async=1;t.src='https://www.clarity.ms/tag/'+i;y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);})(window,document,'clarity','script','{{ config('ukv.clarity_id') }}');
@endif
@if (config('ukv.meta_pixel_id'))
    // Meta Pixel is already loaded in <head> with consent revoked (partials.meta-pixel).
    // Granting consent now releases the queued PageView + any later events. (UK PECR)
    if(window.fbq){ fbq('consent','grant'); }
@endif
  }
  var banner=document.getElementById('ck-consent');
  var choice=get(NAME);
  if(choice==='accepted'){ loadAcceptedScripts(); }
  else if(choice!=='rejected'){ if(banner) banner.hidden=false; }
  function done(v){ set(v); if(banner) banner.hidden=true; if(v==='accepted') loadAcceptedScripts(); }
  var a=document.getElementById('ck-accept'), r=document.getElementById('ck-reject');
  if(a) a.addEventListener('click', function(){ done('accepted'); });
  if(r) r.addEventListener('click', function(){ done('rejected'); });
})();
</script>
@endonce
