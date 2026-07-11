{{-- Trustpilot Automatic Feedback (invitejs). Loads the integration + registers the site
     so review invitations can be sent. Renders only when the key is configured.
     This is for COLLECTING reviews, not displaying them (that is partials/trustpilot). --}}
@if (config('ukv.trustpilot.enabled') && config('ukv.trustpilot.invite_js_key'))
<script>
(function(w,d,s,r,n){w.TrustpilotObject=n;w[n]=w[n]||function(){(w[n].q=w[n].q||[]).push(arguments)};
    a=d.createElement(s);a.async=1;a.src=r;a.type='text/java'+s;f=d.getElementsByTagName(s)[0];
    f.parentNode.insertBefore(a,f)})(window,document,'script','https://invitejs.trustpilot.com/tp.min.js','tp');
tp('register','{{ config('ukv.trustpilot.invite_js_key') }}');
</script>
@endif
