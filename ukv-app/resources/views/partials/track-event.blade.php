{{-- Fire a conversion event to Meta Pixel (fbq) AND GA4 (gtag), once, on page load.
     Analytics libs load ASYNC only after cookie consent (see cookie-consent partial),
     so we poll briefly for them and fire when ready. If consent was refused the libs
     never appear and nothing fires — PECR-safe by construction.

     Params:
       $teEvent   Meta standard event name   (e.g. 'Lead', 'Purchase'); null to skip Meta
       $teGa      GA4 event name             (e.g. 'generate_lead', 'purchase'); null to skip GA4
       $teParams  shared params object        (e.g. ['value'=>49.0,'currency'=>'GBP']) --}}
@php
  $teEvent  = $teEvent  ?? null;
  $teGa     = $teGa     ?? null;
  $teParams = $teParams ?? [];
@endphp
@if ($teEvent || $teGa)
<script>
(function(){
  var fired=false;
  function fire(){
    if(fired) return; fired=true;
    @if($teEvent)
      if(window.fbq){ fbq('track', @json($teEvent)@if(!empty($teParams)), @json((object)$teParams)@endif); }
    @endif
    @if($teGa)
      if(window.gtag){ gtag('event', @json($teGa)@if(!empty($teParams)), @json((object)$teParams)@endif); }
    @endif
  }
  if(window.fbq || window.gtag){ fire(); return; }
  var n=0, iv=setInterval(function(){
    if(window.fbq || window.gtag){ clearInterval(iv); fire(); }
    else if(++n > 40){ clearInterval(iv); }   // ~6s; no consent => no libs => no fire
  }, 150);
})();
</script>
@endif
