{{-- Hero form VARIANT 2 (locked design): 2-step short form, Country -> Name + Phone.
     Reuses the site's real CSS classes (.htoprow/.back/.pill/.q/.grid/.tile/.pc-combo/.wabtn/
     .errmsg/.reassure/.v-flabel) so it sits inside the SAME <div class=dform><div class=card>
     wrapper the existing hero form uses — zero new visual system, same brand tokens.
     Reuses the real lead pipeline: postLead(name,phone,dest,intent) (server beacon + email +
     generate_lead dataLayer/GTM/Ads tracking) and window.bpWaUrl (ref-attribution wrapper),
     both already injected site-wide by LpAssembler::inject(). On a per-country page,
     window.BP_FLOW_DEST={dest,iso} is already set (same shim the modal flow uses) — step 1
     is skipped there and the destination shows locked, matching how the modal already locks.
     Phone field is a fixed +44 (UK-only audience; no dial-code picker) — intentional scope
     cut, see memory [[hero-form-variants]] for why. --}}
@php
  $bpWa = config('ukv.whatsapp') ?: '447882747584';
@endphp
<div id=hf2-step1>
  <div class=htoprow><span class=pill>Step 1 of 2</span></div>
  <h2 class=q>Where are you going?</h2>
  <div class=grid style="grid-template-columns:repeat(2,1fr)">
    <button type=button class=tile onclick="hf2pick('Spain','es')"><img class=hflag src="https://flagcdn.com/es.svg" alt=""><span class=cn>Spain</span></button>
    <button type=button class=tile onclick="hf2pick('France','fr')"><img class=hflag src="https://flagcdn.com/fr.svg" alt=""><span class=cn>France</span></button>
    <button type=button class=tile onclick="hf2pick('Italy','it')"><img class=hflag src="https://flagcdn.com/it.svg" alt=""><span class=cn>Italy</span></button>
    <button type=button class=tile onclick="hf2pick('Germany','de')"><img class=hflag src="https://flagcdn.com/de.svg" alt=""><span class=cn>Germany</span></button>
    <button type=button class=tile onclick="hf2pick('Greece','gr')"><img class=hflag src="https://flagcdn.com/gr.svg" alt=""><span class=cn>Greece</span></button>
    <button type=button class=tile onclick="hf2pick('Other Schengen','eu')"><img class=hflag src="https://flagcdn.com/eu.svg" alt=""><span class=cn>Other Schengen</span></button>
  </div>
</div>

<div id=hf2-step2 style="display:none">
  <div class=htoprow><button type=button class=back id=hf2back aria-label=Back onclick="hf2back()"><svg viewBox="0 0 24 24" fill=none stroke=currentColor stroke-width=2.4 stroke-linecap=round stroke-linejoin=round><path d="M15 18l-6-6 6-6"/></svg></button><span class=pill>Step 2 of 2</span></div>
  <h2 class=q>Where do we send it<span id=hf2destq></span>?</h2>
  <div class="v-flabel" style="margin-top:2px">Full name</div>
  <input type=text id=hf2name class=hf2-input placeholder="Your name" autocomplete=name>
  <div class=errmsg id=hf2e_name style="display:none">Please enter your name</div>
  <div class="v-flabel">WhatsApp number</div>
  <div class=pc-combo style="pointer-events:none">
    <button type=button class=pc-btn tabindex=-1><img class=pc-fl-img src="https://beyondpassports.co.uk/assets/flags/gb.svg" width=20 height=15 alt=""><span class=pc-dc>+44</span></button>
    <input type=tel id=hf2phone class=pc-input style="pointer-events:auto" placeholder="7911 123456" inputmode=tel autocomplete=tel>
  </div>
  <div class=errmsg id=hf2e_phone style="display:none">Please enter a valid UK number</div>
  <button type=button class=wabtn onclick="hf2send()"><svg width=19 height=19 viewBox="0 0 24 24" fill=none stroke="#3d2b08" stroke-width=2.4 stroke-linecap=round stroke-linejoin=round><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg> <span id=hf2cta>Get me a consultant</span></button>
  <p class=reassure>Nothing is charged. A UK specialist replies within 30 minutes.</p>
</div>

<div id=hf2-sent style="display:none">
  <div class=seal><svg width=27 height=27 viewBox="0 0 24 24" fill=none stroke="#C5963A" stroke-width=2.4 stroke-linecap=round stroke-linejoin=round><path d="M20 6L9 17l-5-5"/></svg></div>
  <h3>You're all set<span id=hf2sentname></span>.</h3>
  <p class=b>A UK-based specialist reads your details and replies, usually within 30 minutes.</p>
</div>

@once
<style>
.hf2-input{width:100%;border:1.5px solid var(--border);border-radius:12px;padding:14px 15px;font:500 15px var(--sans);color:var(--navy);background:#fff;margin-bottom:4px;box-sizing:border-box}
.hf2-input:focus{outline:none;border-color:var(--gold);box-shadow:0 0 0 3px rgba(197,150,58,.14)}
#hf2-step1 .tile,#hf2-step2{animation:hf2in .18s ease}
@keyframes hf2in{from{opacity:0;transform:translateY(4px)}to{opacity:1;transform:none}}
</style>
@endonce

<script>
(function(){
  var WA={{ json_encode($bpWa) }};
  var state={dest:null,iso:null};
  var locked=false;

  function lockToFlowDest(){
    if (window.BP_FLOW_DEST && window.BP_FLOW_DEST.dest) {
      locked = true;
      state.dest = window.BP_FLOW_DEST.dest;
      state.iso = window.BP_FLOW_DEST.iso || '';
      document.getElementById('hf2-step1').style.display='none';
      document.getElementById('hf2back').style.display='none';
      document.getElementById('hf2destq').textContent=' for your '+state.dest+' visa';
      document.getElementById('hf2-step2').style.display='block';
    }
  }

  window.hf2pick=function(name,iso){
    state.dest=name; state.iso=iso;
    document.getElementById('hf2destq').textContent='';
    document.getElementById('hf2-step1').style.display='none';
    document.getElementById('hf2-step2').style.display='block';
  };
  window.hf2back=function(){
    if(locked)return;
    document.getElementById('hf2-step2').style.display='none';
    document.getElementById('hf2-step1').style.display='block';
  };

  window.hf2send=function(){
    var name=(document.getElementById('hf2name').value||'').trim();
    var phoneRaw=(document.getElementById('hf2phone').value||'').trim();
    var phoneDigits=phoneRaw.replace(/[^0-9]/g,'').replace(/^0+/,'');
    var okName=name.length>=2, okPhone=phoneDigits.length>=9 && phoneDigits.length<=11;
    document.getElementById('hf2e_name').style.display=okName?'none':'block';
    document.getElementById('hf2e_phone').style.display=okPhone?'none':'block';
    if(!okName||!okPhone)return;

    var fullPhone='+44'+phoneDigits;
    try{ if(window.postLead) window.postLead(name, fullPhone, state.dest||'', 'case-short'); }catch(e){}

    var text='Hi Beyond Passports, I would like help with my Schengen visa.'
      +'\nName: '+name
      +(state.dest?'\nDestination: '+state.dest:'')
      +'\nNumber: '+fullPhone;
    var enc=encodeURIComponent(text);
    var web=(window.bpWaUrl||String)('https://wa.me/'+WA+'?text='+enc);
    var app='whatsapp://send?phone='+WA+'&text='+enc;
    var isMobile=/Android|iPhone|iPad|iPod/i.test(navigator.userAgent);

    document.getElementById('hf2-step2').style.display='none';
    document.getElementById('hf2sentname').textContent=name?(', '+name):'';
    document.getElementById('hf2-sent').style.display='block';

    if(isMobile){ try{location.href=app;}catch(e){} setTimeout(function(){location.href=web;},1500); }
    else{ window.open(web,'_blank'); }
  };

  // BP_FLOW_DEST is assigned by a <script> placed near </body> — later in the DOM than this
  // partial (which sits mid-body, inside the hero). Reading it synchronously here would always
  // see null. Defer to DOMContentLoaded so every earlier script (incl. that shim) has run first.
  document.addEventListener('DOMContentLoaded', lockToFlowDest);
})();
</script>
