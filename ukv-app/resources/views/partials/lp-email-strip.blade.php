{{--
    Landing-page email fallback strip. For the WhatsApp-first LPs: a lightweight, self-contained
    email capture so a visitor who won't use WhatsApp can still leave a lead. Posts in the background
    to appointment.enquiry (which accepts email); shows an inline thank-you on success. Dark-friendly
    translucent card that sits on any LP background. Include once, near the end of a LP.
--}}
<section class="lpes" aria-label="Prefer email">
  <div class="lpes-card">
    <p class="lpes-h">Prefer email?</p>
    <p class="lpes-sub">Leave your address and a <x-reg-verify>UK &amp; Europe registered</x-reg-verify> specialist will reach out. No payment now.</p>
    <form class="lpes-form" onsubmit="return lpesGo(this)" data-capture="{{ route('appointment.enquiry') }}" novalidate>
      @csrf
      <label for="lpes-email" class="mrz">Your email</label>
      <input id="lpes-email" name="e" type="email" required placeholder="name@email.com" autocomplete="email">
      <button type="submit">Email me</button>
    </form>
    <div style="margin-top:14px">@include('partials.disclaimer-strip', ['wrap' => false, 'variant' => 'dark'])</div>
    <p class="lpes-ok" hidden>Thanks — we'll be in touch shortly.</p>
  </div>
</section>
<script>
function lpesGo(f){
  var e=(f.e.value||'').trim(); if(!e){return false;}
  try{
    var url=f.getAttribute('data-capture'), tok=f.querySelector('input[name=_token]');
    if(url&&tok){ fetch(url,{method:'POST',keepalive:true,headers:{'Content-Type':'application/json','X-CSRF-TOKEN':tok.value,'Accept':'application/json'},
      body:JSON.stringify({email:e,source:location.pathname})}).catch(function(){}); }
  }catch(err){}
  f.style.display='none';
  var ok=f.parentNode.querySelector('.lpes-ok'); if(ok){ok.hidden=false;}
  return false;
}
</script>
@once
<style>
  .lpes{padding:36px 20px;display:flex;justify-content:center}
  .lpes-card{width:100%;max-width:560px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.16);
    border-radius:18px;padding:26px 26px 24px;text-align:center;font-family:"Outfit",system-ui,sans-serif;
    backdrop-filter:blur(2px)}
  .lpes-h{font-weight:800;font-size:20px;letter-spacing:-.01em;margin:0 0 6px;color:#fff}
  .lpes-sub{font-size:14px;line-height:1.5;color:rgba(255,255,255,.82);margin:0 0 16px}
  .lpes-form{display:flex;gap:10px;flex-wrap:wrap;justify-content:center}
  .lpes-form input{flex:1 1 240px;min-width:0;padding:13px 14px;border:1px solid rgba(255,255,255,.28);border-radius:11px;
    background:rgba(255,255,255,.95);color:#16222E;font:inherit;font-size:15px}
  .lpes-form input:focus{outline:none;border-color:#25D366;box-shadow:0 0 0 3px rgba(37,211,102,.35)}
  .lpes-form button{flex:0 0 auto;background:#25D366;color:#fff;border:0;border-radius:11px;font:800 15px "Outfit",system-ui;
    padding:13px 22px;cursor:pointer}
  .lpes-form button:hover{background:#1da851}
  .lpes-ok{color:#dff7e6;font-weight:700;font-size:15px;margin:6px 0 0}
  .mrz{position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0)}
</style>
@endonce
