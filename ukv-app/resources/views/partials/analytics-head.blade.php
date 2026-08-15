{{-- Static analytics install (GTM + Google tag/GA4 + Clarity) in <head>.

     Rendered ONLY in ungated mode (config('ukv.cookie_banner') === false), so Google's
     tag verifier / Tag Assistant find the canonical snippets at page load — the JS-injected
     versions in partials.cookie-consent are not detectable by Google's static scan.

     Sets window.__bpTagsLoaded so cookie-consent skips re-injecting the same tags on pages
     that include this partial (no double page_view). Pages without this partial still get the
     JS-loaded fallback from cookie-consent.

     PERF: tag loading is DEFERRED until first user interaction (pointer/scroll/key/touch)
     or a 3.5s idle fallback, whichever comes first. Keeps ~200 KiB of third-party JS off
     the critical path (mobile LCP/TBT). Tracking still fires for every real visitor; the
     canonical inline snippets remain in the HTML source for static scanners.

     When the cookie banner is switched back on (UKV_COOKIE_BANNER=true) this renders nothing;
     consent-gated loading in cookie-consent takes over (PECR-compliant). --}}
@if (! config('ukv.cookie_banner', false))
<script>window.__bpTagsLoaded=true;</script>
<script>
(function(){
  var loaded=false;
  function loadTags(){
    if(loaded)return;loaded=true;
    @if (config('ukv.gtm_id'))
    /* Google Tag Manager */
    (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','{{ config('ukv.gtm_id') }}');
    @endif
    @if (config('ukv.ga4_id'))
    /* Google tag (gtag.js) */
    (function(){var s=document.createElement('script');s.async=true;s.src='https://www.googletagmanager.com/gtag/js?id={{ config('ukv.ga4_id') }}';document.head.appendChild(s);})();
    window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}window.gtag=window.gtag||gtag;gtag('js',new Date());gtag('config','{{ config('ukv.ga4_id') }}');
    @endif
    @if (config('ukv.clarity_id'))
    /* Microsoft Clarity */
    (function(c,l,a,r,i,t,y){c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};t=l.createElement(r);t.async=1;t.src='https://www.clarity.ms/tag/'+i;y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);})(window,document,'clarity','script','{{ config('ukv.clarity_id') }}');
    @endif
    @if (config('ukv.meta_pixel_id'))
    if(window.fbq){fbq('consent','grant');}
    @endif
    ['pointerdown','scroll','keydown','touchstart'].forEach(function(e){window.removeEventListener(e,loadTags,{passive:true});});
  }
  ['pointerdown','scroll','keydown','touchstart'].forEach(function(e){window.addEventListener(e,loadTags,{passive:true,once:true});});
  setTimeout(loadTags,3500);
})();
</script>
@endif
