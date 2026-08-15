{{-- Meta Pixel — fbq stub + queued init/PageView run in <head> so Meta can DETECT it,
     but started with consent REVOKED: no cookies set and no data sent until the visitor
     accepts cookies, when cookie-consent's loadAcceptedScripts() calls
     fbq('consent','grant') (UK PECR).

     PERF: the external fbevents.js download is deferred to first user interaction or a
     3.5s idle fallback (same trigger as analytics-head). All fbq() calls made before the
     script arrives are queued by the stub and replay on load — nothing is lost.

     The <noscript> img is intentionally omitted — it can't be consent-gated and would
     fire on first load without consent. Config: ukv.meta_pixel_id (blank = no pixel). --}}
@if (config('ukv.meta_pixel_id'))
<script>
  !function(f){if(f.fbq)return;var n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];}(window);
  fbq('consent', 'revoke');               // hold all cookies/data until consent is granted
  fbq('init', '{{ config('ukv.meta_pixel_id') }}');
  fbq('track', 'PageView');               // queued; only sent after fbq('consent','grant')
  (function(){
    var done=false;
    function load(){if(done)return;done=true;var t=document.createElement('script');t.async=true;t.src='https://connect.facebook.net/en_US/fbevents.js';document.head.appendChild(t);}
    ['pointerdown','scroll','keydown','touchstart'].forEach(function(e){window.addEventListener(e,load,{passive:true,once:true});});
    setTimeout(load,3500);
  })();
</script>
@endif
