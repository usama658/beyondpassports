{{-- Meta Pixel — loaded in <head> so Meta can DETECT it, but started with consent
     REVOKED: no cookies set and no data sent until the visitor accepts cookies, when
     cookie-consent's loadAcceptedScripts() calls fbq('consent','grant') (UK PECR).
     The <noscript> img is intentionally omitted — it can't be consent-gated and would
     fire on first load without consent. Config: ukv.meta_pixel_id (blank = no pixel). --}}
@if (config('ukv.meta_pixel_id'))
<script>
  !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
  fbq('consent', 'revoke');               // hold all cookies/data until consent is granted
  fbq('init', '{{ config('ukv.meta_pixel_id') }}');
  fbq('track', 'PageView');               // queued; only sent after fbq('consent','grant')
</script>
@endif
