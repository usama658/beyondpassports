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
    <button type=button class=tile onclick="hf2pick('Spain','es')"><img class=hflag src="https://flagcdn.com/w80/es.png" alt=""><span class=cn>Spain</span></button>
    <button type=button class=tile onclick="hf2pick('France','fr')"><img class=hflag src="https://flagcdn.com/w80/fr.png" alt=""><span class=cn>France</span></button>
    <button type=button class=tile onclick="hf2pick('Italy','it')"><img class=hflag src="https://flagcdn.com/w80/it.png" alt=""><span class=cn>Italy</span></button>
    <button type=button class=tile onclick="hf2pick('Germany','de')"><img class=hflag src="https://flagcdn.com/w80/de.png" alt=""><span class=cn>Germany</span></button>
    <button type=button class=tile onclick="hf2pick('Greece','gr')"><img class=hflag src="https://flagcdn.com/w80/gr.png" alt=""><span class=cn>Greece</span></button>
    <button type=button class=tile onclick="hf2hCsearch()"><img class=hflag src="https://flagcdn.com/w80/eu.png" alt=""><span class=cn>Other Schengen</span></button>
  </div>
  <div id=hf2hcwrap class=mhide style="margin-top:10px">
    <div class=msearch><span class=euchip><img class=flag src="https://flagcdn.com/w80/eu.png" alt=""><span>Schengen</span></span><input id=hf2hcq placeholder="Search countries" oninput="hf2hCrender(this.value)" autocomplete=off><button type=button class=bk onclick="hf2hCclose()">Cancel</button></div>
    <ul class=mres id=hf2hclist></ul>
  </div>
</div>

<div id=hf2-step2 style="display:none">
  <div class=htoprow><button type=button class=back id=hf2back aria-label=Back onclick="hf2back()"><svg viewBox="0 0 24 24" fill=none stroke=currentColor stroke-width=2.4 stroke-linecap=round stroke-linejoin=round><path d="M15 18l-6-6 6-6"/></svg></button><span class=pill>Step 2 of 2</span></div>
  <h2 class=q>Where should we contact you<span id=hf2destq></span>?</h2>
  <div class="v-flabel" style="margin-top:2px">Full name</div>
  <input type=text id=hf2name class=hf2-input placeholder="Your name" autocomplete=name>
  <div class=errmsg id=hf2e_name style="display:none">Please enter your name</div>
  <div class="v-flabel">WhatsApp number</div>
  <div class=pc-combo style="pointer-events:none">
    <button type=button class=pc-btn tabindex=-1><img class=pc-fl-img src="https://beyondpassports.co.uk/assets/flags/gb.svg" width=20 height=15 alt=""><span class=pc-dc>+44</span></button>
    <input type=tel id=hf2phone class=pc-input style="pointer-events:auto" placeholder="7911 123456" inputmode=tel autocomplete=tel>
  </div>
  <div class=errmsg id=hf2e_phone style="display:none">Please enter a valid UK number</div>
  <button type=button class=wabtn onclick="hf2send()"><svg width=19 height=19 viewBox="0 0 24 24"><path d="M12 2a10 10 0 0 0-8.6 15l-1.3 4.7 4.8-1.3A10 10 0 1 0 12 2zm5.5 14.2c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .1-1.7-.1-.4-.1-.9-.3-1.6-.6-2.8-1.2-4.6-4-4.7-4.2-.1-.2-1.1-1.5-1.1-2.8s.7-2 .9-2.2c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.8 2c.1.1.1.3 0 .5l-.4.5-.3.3c-.2.2-.3.4-.1.7.2.3.9 1.4 1.9 2.3 1.3 1.1 2.3 1.5 2.6 1.6.3.1.5.1.7-.1l.9-1c.2-.2.4-.2.6-.1l1.9.9c.3.2.5.2.5.4.1.1.1.8-.2 1.4z"/></svg> <span id=hf2cta>Get urgent visa help</span></button>
  <button type=button class=cta-ghost id=hf2cbBtn onclick="hf2startCallback(this)">
    <svg viewBox="0 0 24 24" fill=none stroke=currentColor stroke-width=2.1 stroke-linecap=round stroke-linejoin=round><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3.1-8.6A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.6a2 2 0 0 1-.5 2.1L8 9.6a16 16 0 0 0 6 6l1.2-1.2a2 2 0 0 1 2.1-.5c.8.3 1.7.5 2.6.6a2 2 0 0 1 1.7 2z"/></svg>
    <span id=hf2cbLabel>Get a call back in 60 seconds</span>
  </button>
  <p class=reassure>Nothing is charged. A UK specialist replies within 30 minutes.</p>
</div>

{{-- Thank-you screen, verbatim markup/CSS from the real default flow's own #sent (.snt/.sntgrid/
     .sntL/.sntR/.seal/.ring/.wac/.disc/.pk/.cnt/.waopen all already global on every real page) -
     3s auto-open-WhatsApp countdown ring + manual "Open now" fallback, not our own simpler box. --}}
<div class=snt id=hf2sent><div class=sntgrid>
 <div class=sntL>
  <div class=seal><svg width=27 height=27 viewBox="0 0 24 24" fill=none stroke="#C5963A" stroke-width=2.4 stroke-linecap=round stroke-linejoin=round><path d="M20 6L9 17l-5-5"/></svg></div>
  <h3 id=hf2sntHead>You're all set.</h3>
  <p class=b>A UK-based specialist reads your answers and replies, usually within 30 minutes.</p>
  <div class=sntsum id=hf2sntSummary></div>
 </div>
 <div class=sntR>
  <div class=ring><svg width=96 height=96 viewBox="0 0 104 104"><circle class=bg cx=52 cy=52 r=45></circle><circle class=fg cx=52 cy=52 r=45></circle></svg><span class=wac><span class=disc><svg width=26 height=26 viewBox="0 0 24 24" fill="#fff"><path d="M12 2a10 10 0 0 0-8.6 15l-1.3 4.7 4.8-1.3A10 10 0 1 0 12 2zm5.5 14.2c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .1-1.7-.1-.4-.1-.9-.3-1.6-.6-2.8-1.2-4.6-4-4.7-4.2-.1-.2-1.1-1.5-1.1-2.8s.7-2 .9-2.2c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.8 2c.1.1.1.3 0 .5l-.4.5-.3.3c-.2.2-.3.4-.1.7.2.3.9 1.4 1.9 2.3 1.3 1.1 2.3 1.5 2.6 1.6.3.1.5.1.7-.1l.9-1c.2-.2.4-.2.6-.1l1.9.9c.3.2.5.2.5.4.1.1.1.8-.2 1.4z"/></svg></span></span></div>
  <p class=pk>Opening WhatsApp</p>
  <p class=cnt>The chat opens in <b id=hf2sntCount>3</b> seconds, prefilled with your details.</p>
  <a class=waopen id=hf2sntWa href="#" target=_blank rel=noopener><svg width=18 height=18 viewBox="0 0 24 24" fill="#fff"><path d="M12 2a10 10 0 0 0-8.6 15l-1.3 4.7 4.8-1.3A10 10 0 1 0 12 2zm5.5 14.2c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .1-1.7-.1-.4-.1-.9-.3-1.6-.6-2.8-1.2-4.6-4-4.7-4.2-.1-.2-1.1-1.5-1.1-2.8s.7-2 .9-2.2c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.8 2c.1.1.1.3 0 .5l-.4.5-.3.3c-.2.2-.3.4-.1.7.2.3.9 1.4 1.9 2.3 1.3 1.1 2.3 1.5 2.6 1.6.3.1.5.1.7-.1l.9-1c.2-.2.4-.2.6-.1l1.9.9c.3.2.5.2.5.4.1.1.1.8-.2 1.4z"/></svg> Open now</a>
 </div>
</div></div>

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
/* A site-wide script injects a .mx.df-close button into every .htoprow (for the DEFAULT flow's
   mobile full-screen-sheet promotion, gated on .step.active[data-n]>=2 - markup this variant
   doesn't use). It still appends into our htoprow since we reuse the real .htoprow class; scope
   it off here only, so the default flow's own close button elsewhere is untouched. */
#hf2-step1 .df-close,#hf2-step2 .df-close{display:none!important}
</style>
@endonce

<script>
(function(){
  var WA={!! json_encode($bpWa) !!};
  var state={dest:null,iso:null};

  // UK mobile normalizer. Field is a fixed +44 with no country picker, but visitors commonly
  // type their number already in international format ("+44 7911 123456", "+447911123456",
  // "+44 (0) 7911 123456") since that's how UK numbers are stored on a phone that travels
  // internationally - a plain digit-count check rejected all of those (only "07911 123456"
  // passed) and, worse, accepted UK landlines (020.../011...) which can never receive WhatsApp.
  // Strip a redundant leading "44" country code first, then any leftover leading zero (from a
  // local "0..." entry or a "(0)" after +44), then require exactly 10 digits starting with 7 -
  // the real shape of every UK mobile's national significant number.
  function hf2NormalizePhone(raw){
    var d=(raw||'').replace(/[^0-9]/g,'');
    if(d.slice(0,2)==='44'&&d.length>10) d=d.slice(2);
    d=d.replace(/^0+/,'');
    return d;
  }
  function hf2PhoneOk(d){ return /^7\d{9}$/.test(d); }

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
      li.innerHTML='<img class=flag src="https://flagcdn.com/w80/'+c[1]+'.png" alt=""><span class=cn2>'+c[0]+'</span><span class="cst2 '+st[0]+'"><span class=cdot2></span>'+st[1]+'</span>';
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

    var name=(document.getElementById('hf2name').value||'').trim();
    var phoneRaw=(document.getElementById('hf2phone').value||'').trim();
    var phoneDigits=hf2NormalizePhone(phoneRaw);
    var okName=name.length>=2, okPhone=hf2PhoneOk(phoneDigits);
    document.getElementById('hf2e_name').style.display=okName?'none':'block';
    document.getElementById('hf2e_phone').style.display=okPhone?'none':'block';
    if(!okName||!okPhone)return;

    btn.dataset.running='1';
    var fullPhone='+44'+phoneDigits;
    try{ if(window.postLead) window.postLead(name, fullPhone, state.dest||'', 'case-callback'); }catch(e){}

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
    var phoneDigits=hf2NormalizePhone(phoneRaw);
    var okName=name.length>=2, okPhone=hf2PhoneOk(phoneDigits);
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
    if(state.dest){ document.getElementById('hf2sntSummary').innerHTML='<div class=row><span class=k>Destination</span><span class=v>'+state.dest+'</span></div>'; }
    document.getElementById('hf2sntWa').href=web;
    document.getElementById('hf2sent').classList.add('active');

    var went=false;
    function go(){
      if(went)return; went=true;
      if(isMobile){ try{location.href=app;}catch(e){} setTimeout(function(){location.href=web;},1500); }
      else{ window.open(web,'_blank'); }
    }
    var c=3,el=document.getElementById('hf2sntCount');
    var t=setInterval(function(){c--;if(el)el.textContent=c<0?0:c;if(c<=0){clearInterval(t);go();}},1000);
    setTimeout(go,3300);
  };

  // BP_FLOW_DEST is assigned by a <script> placed near </body> — later in the DOM than this
  // partial (which sits mid-body, inside the hero). Reading it synchronously here would always
  // see null. Defer to DOMContentLoaded so every earlier script (incl. that shim) has run first.
  document.addEventListener('DOMContentLoaded', lockToFlowDest);
})();
</script>
