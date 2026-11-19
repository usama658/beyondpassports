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
  <div class=grid id=hf2hcgrid style="grid-template-columns:repeat(2,1fr)">
    <button type=button class=tile onclick="hf2pick('Spain','es')"><img class=hflag src="https://flagcdn.com/es.svg" alt=""><span class=cn>Spain</span></button>
    <button type=button class=tile onclick="hf2pick('France','fr')"><img class=hflag src="https://flagcdn.com/fr.svg" alt=""><span class=cn>France</span></button>
    <button type=button class=tile onclick="hf2pick('Italy','it')"><img class=hflag src="https://flagcdn.com/it.svg" alt=""><span class=cn>Italy</span></button>
    <button type=button class=tile onclick="hf2pick('Germany','de')"><img class=hflag src="https://flagcdn.com/de.svg" alt=""><span class=cn>Germany</span></button>
    <button type=button class=tile onclick="hf2pick('Greece','gr')"><img class=hflag src="https://flagcdn.com/gr.svg" alt=""><span class=cn>Greece</span></button>
    <button type=button class=tile onclick="hf2hCsearch()"><img class=hflag src="https://flagcdn.com/eu.svg" alt=""><span class=cn>Other Schengen</span><svg class=csrch viewBox="0 0 24 24" fill=none stroke=currentColor stroke-width=2.2 stroke-linecap=round stroke-linejoin=round><circle cx=11 cy=11 r=7/><path d="M21 21l-4.3-4.3"/></svg></button>
  </div>
  <div id=hf2hcwrap class=mhide style="margin-top:10px">
    <div class=msearch><span class=euchip><img class=flag src="https://flagcdn.com/eu.svg" alt=""><span>Schengen</span></span><input id=hf2hcq placeholder="Search countries" oninput="hf2hCrender(this.value)" autocomplete=off><button type=button class=bk onclick="hf2hCclose()">&#10005;</button></div>
    <ul class=mres id=hf2hclist></ul>
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
  <button type=button class=cta-ghost id=hf2cbBtn onclick="hf2startCallback(this)">
    <svg viewBox="0 0 24 24" fill=none stroke=currentColor stroke-width=2.1 stroke-linecap=round stroke-linejoin=round><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3.1-8.6A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.6a2 2 0 0 1-.5 2.1L8 9.6a16 16 0 0 0 6 6l1.2-1.2a2 2 0 0 1 2.1-.5c.8.3 1.7.5 2.6.6a2 2 0 0 1 1.7 2z"/></svg>
    <span id=hf2cbLabel>Get a call back in 60 seconds</span>
  </button>
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
.cta-ghost{margin-top:10px;width:100%;background:#fff;color:var(--navy);border:1.5px solid var(--border);border-radius:13px;padding:14px;font:700 14px var(--sans);cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:.15s}
.cta-ghost:hover{border-color:var(--gold);background:#fffdf8}
.cta-ghost svg{width:17px;height:17px;flex:none;color:var(--gold)}
.cb-timer{font:800 13px var(--sans);color:var(--gold);min-width:28px;text-align:left}
</style>
@endonce

<script>
(function(){
  var WA={!! json_encode($bpWa) !!};
  var state={dest:null,iso:null};

  // Same 29-country Schengen list + status slots as the default flow's "Other Schengen"
  // search (HSC/HSLOT in the page's own script) — duplicated here under hf2-prefixed names
  // so this variant doesn't depend on markup the LpAssembler::inject() swap removes from the
  // page (the default #hcwrap/#hcgrid/#hclist search overlay lives INSIDE the HERO_FORM_V1
  // markers, so it's gone from the DOM whenever this variant is active).
  var HF2_HSC=[["Austria","at"],["Belgium","be"],["Bulgaria","bg"],["Croatia","hr"],["Czechia","cz"],["Denmark","dk"],["Estonia","ee"],["Finland","fi"],["France","fr"],["Germany","de"],["Greece","gr"],["Hungary","hu"],["Iceland","is"],["Italy","it"],["Latvia","lv"],["Liechtenstein","li"],["Lithuania","lt"],["Luxembourg","lu"],["Malta","mt"],["Netherlands","nl"],["Norway","no"],["Poland","pl"],["Portugal","pt"],["Romania","ro"],["Slovakia","sk"],["Slovenia","si"],["Spain","es"],["Sweden","se"],["Switzerland","ch"]];
  var HF2_HSLOT={fr:['open','Open'],de:['open','Open'],gr:['open','Open'],es:['lim','Filling'],it:['lim','Limited']};

  window.hf2hCsearch=function(){
    var g=document.getElementById('hf2hcgrid');if(g)g.style.display='none';
    var w=document.getElementById('hf2hcwrap');if(w)w.classList.remove('mhide');
    hf2hCrender('');
    var q=document.getElementById('hf2hcq');if(q)q.focus();
  };
  window.hf2hCclose=function(){
    var w=document.getElementById('hf2hcwrap');if(w)w.classList.add('mhide');
    var g=document.getElementById('hf2hcgrid');if(g)g.style.display='';
    var q=document.getElementById('hf2hcq');if(q)q.value='';
  };
  window.hf2hCrender=function(q){
    q=(q||'').toLowerCase();
    var ul=document.getElementById('hf2hclist');if(!ul)return;
    ul.innerHTML='';
    var m=HF2_HSC.filter(function(c){return !q||c[0].toLowerCase().indexOf(q)>-1;});
    if(!m.length){ul.innerHTML='<li class=nomatch>No match. Try another spelling.</li>';return;}
    m.forEach(function(c){
      var st=HF2_HSLOT[c[1]]||['chk','Check'];
      var li=document.createElement('li');
      li.innerHTML='<img class=flag src="https://flagcdn.com/'+c[1]+'.svg" alt=""><span class=cn2>'+c[0]+'</span><span class="cst2 '+st[0]+'"><span class=cdot2></span>'+st[1]+'</span>';
      li.onclick=function(){ hf2hCclose(); window.hf2pick(c[0],c[1]); };
      ul.appendChild(li);
    });
  };

  // Country pages pre-fill the destination from BP_FLOW_DEST and skip straight to step 2 — but
  // the back button stays visible and working, so a visitor who landed on the wrong country page
  // can still correct it instead of being stuck with a destination they didn't choose.
  function lockToFlowDest(){
    if (window.BP_FLOW_DEST && window.BP_FLOW_DEST.dest) {
      state.dest = window.BP_FLOW_DEST.dest;
      state.iso = window.BP_FLOW_DEST.iso || '';
      document.getElementById('hf2destq').textContent=' for your '+state.dest+' visa';
      document.getElementById('hf2-step1').style.display='none';
      document.getElementById('hf2-step2').style.display='block';
      document.getElementById('hf2back').classList.add('show');
    }
  }

  window.hf2pick=function(name,iso){
    state.dest=name; state.iso=iso;
    document.getElementById('hf2destq').textContent='';
    document.getElementById('hf2-step1').style.display='none';
    document.getElementById('hf2-step2').style.display='block';
    document.getElementById('hf2back').classList.add('show');
  };
  window.hf2back=function(){
    document.getElementById('hf2-step2').style.display='none';
    document.getElementById('hf2-step1').style.display='block';
    document.getElementById('hf2back').classList.remove('show');
  };

  window.hf2startCallback=function(btn){
    if(btn.dataset.running)return;
    btn.dataset.running='1';
    var n=60,label=document.getElementById('hf2cbLabel');
    var tick=setInterval(function(){
      n--;
      label.innerHTML='Calling you back&hellip; <span class=cb-timer>'+n+'s</span>';
      if(n<=0){
        clearInterval(tick);
        label.textContent='Request sent — stay by your phone';
        btn.style.borderColor='var(--green)';
        btn.style.color='var(--green)';
      }
    },1000);
    label.innerHTML='Calling you back&hellip; <span class=cb-timer>60s</span>';
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
