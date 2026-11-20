{{-- Hero form VARIANT 3 (locked design): single screen, no steps — Destination + Full name +
     WhatsApp number together. Destination is a compact field; clicking it opens a real
     country-picker overlay (tile grid with real status chips + "Other Schengen" search) and
     picking a country closes the overlay automatically, filling the field.

     Sits inside the SAME <div class=dform><div class=card> wrapper the existing hero form uses
     (between the HERO_FORM_V1 markers), same as [[hero-form-variants]] variant 2 — zero new
     visual system for .tile/.cn/.cst/.cdot/.wabtn/.pc-combo/.pc-btn/.pc-input/.errmsg/.reassure/
     .v-flabel, all already defined globally on every gold page. Only genuinely new pieces
     (.dest-field/.dest-overlay/.dest-sheet/.cta-ghost/.hf3-input) get their own @once style block.

     IMPORTANT: every page already defines its own global hCsearch()/hCclose()/hCrender() for the
     DEFAULT flow's "Other Schengen" search (wired to that flow's STATE/show(2), defined OUTSIDE
     the HERO_FORM_V1 markers near the end of the page script — confirmed via grep, see
     [[hero-form-variants]]). A plain `function hCsearch(){}` here would get silently overwritten
     by that later declaration and crash (it has no null-guard on #hcgrid). All search/pick logic
     here is hf3-prefixed instead to avoid that collision — do not rename to the bare hC* names.

     Reuses the real lead pipeline: postLead(name,phone,dest,intent) and window.bpWaUrl, both
     already injected site-wide by LpAssembler::inject(). On a per-country page it reads
     window.BP_FLOW_DEST={dest,iso} (same shim variant 2 consumes) and locks the destination field
     — deferred to DOMContentLoaded since that shim's own <script> sits later in the DOM (near
     </body>) than this partial (mid-body); reading it synchronously would always see null.
     Phone field is a fixed +44, no dial-code picker — same intentional scope cut as variant 2. --}}
@php
  $bpWa = config('ukv.whatsapp') ?: '447882747584';
@endphp
<div id=hf3-form>
  <h2 class=q>Where are you going?</h2>

  <div class="v-flabel" style="margin-top:2px">Destination</div>
  <button type=button class=dest-field id=hf3destField onclick="hf3destOpen()">
    <img class=dflag id=hf3destFlag src="" alt="">
    <span class=dtxt id=hf3destTxt>Select your destination</span>
    <svg class=dchev id=hf3destChev viewBox="0 0 24 24" fill=none stroke=currentColor stroke-width=2.4 stroke-linecap=round stroke-linejoin=round><path d="M6 9l6 6 6-6"/></svg>
  </button>
  <div class=errmsg id=hf3e_dest style="display:none">Please select your destination</div>

  <div class="v-flabel" style="margin-top:14px">Full name</div>
  <input type=text id=hf3name class=hf3-input placeholder="Your name" autocomplete=name>
  <div class=errmsg id=hf3e_name style="display:none">Please enter your name</div>

  <div class=v-flabel>WhatsApp number</div>
  <div class=pc-combo>
    <button type=button class=pc-btn tabindex=-1><img class=pc-fl-img src="https://beyondpassports.co.uk/assets/flags/gb.svg" width=20 height=15 alt=""><span class=pc-dc>+44</span></button>
    <input type=tel id=hf3phone class=pc-input placeholder="7911 123456" inputmode=tel autocomplete=tel>
  </div>
  <div class=errmsg id=hf3e_phone style="display:none">Please enter a valid UK number</div>

  <button type=button class=wabtn onclick="hf3send()"><svg width=19 height=19 viewBox="0 0 24 24" fill=none stroke="#3d2b08" stroke-width=2.4 stroke-linecap=round stroke-linejoin=round><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg> Get me a consultant</button>
  <button type=button class=cta-ghost id=hf3cbBtn onclick="hf3startCallback(this)">
    <svg viewBox="0 0 24 24" fill=none stroke=currentColor stroke-width=2.1 stroke-linecap=round stroke-linejoin=round><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3.1-8.6A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.6a2 2 0 0 1-.5 2.1L8 9.6a16 16 0 0 0 6 6l1.2-1.2a2 2 0 0 1 2.1-.5c.8.3 1.7.5 2.6.6a2 2 0 0 1 1.7 2z"/></svg>
    <span id=hf3cbLabel>Get a call back in 60 seconds</span>
  </button>
  <p class=reassure>Nothing is charged. A UK specialist replies within 30 minutes.</p>
</div>

<div id=hf3-sent style="display:none">
  <div class=seal><svg width=27 height=27 viewBox="0 0 24 24" fill=none stroke="#C5963A" stroke-width=2.4 stroke-linecap=round stroke-linejoin=round><path d="M20 6L9 17l-5-5"/></svg></div>
  <h3>You're all set<span id=hf3sentname></span>.</h3>
  <p class=b>A UK-based specialist reads your details and replies, usually within 30 minutes.</p>
</div>

<div class=dest-overlay id=hf3destOverlay>
  <div class=dest-sheet>
    <div class=dest-sheet-hd>
      <h3>Where are you going?</h3>
      <button type=button class=dest-x onclick="hf3destClose()">&#10005;</button>
    </div>
    <div class=grid id=hf3destGrid>
      <button type=button class=tile onclick="hf3destPick('Spain','es')"><img class=hflag src="https://flagcdn.com/es.svg" alt=""><span><span class=cn>Spain</span><span class="cst lim"><span class=cdot></span>Filling</span></span></button>
      <button type=button class=tile onclick="hf3destPick('France','fr')"><img class=hflag src="https://flagcdn.com/fr.svg" alt=""><span><span class=cn>France</span><span class="cst open"><span class=cdot></span>Open</span></span></button>
      <button type=button class=tile onclick="hf3destPick('Italy','it')"><img class=hflag src="https://flagcdn.com/it.svg" alt=""><span><span class=cn>Italy</span><span class="cst lim"><span class=cdot></span>Limited</span></span></button>
      <button type=button class=tile onclick="hf3destPick('Germany','de')"><img class=hflag src="https://flagcdn.com/de.svg" alt=""><span><span class=cn>Germany</span><span class="cst open"><span class=cdot></span>Open</span></span></button>
      <button type=button class=tile onclick="hf3destPick('Greece','gr')"><img class=hflag src="https://flagcdn.com/gr.svg" alt=""><span><span class=cn>Greece</span><span class="cst open"><span class=cdot></span>Open</span></span></button>
      <button type=button class=tile onclick="hf3Search()"><svg class=csrch viewBox="0 0 24 24" fill=none stroke=currentColor stroke-width=2.2 stroke-linecap=round stroke-linejoin=round><circle cx=11 cy=11 r=7></circle><path d="M21 21l-4.3-4.3"/></svg><span><span class=cn>Other Schengen</span></span></button>
    </div>
    <div id=hf3hcwrap class=mhide style="margin-top:14px">
      <div class=msearch>
        <span class=euchip><img class=flag src="https://flagcdn.com/eu.svg" alt=""><svg width=14 height=14 viewBox="0 0 24 24" fill=none stroke=currentColor stroke-width=2.2 stroke-linecap=round stroke-linejoin=round><circle cx=11 cy=11 r=7></circle><path d="M21 21l-4.3-4.3"/></svg></span>
        <input type=text id=hf3hcq placeholder="Search Schengen countries" oninput="hf3Render(this.value)">
        <button type=button class=bk onclick="hf3Close()">Cancel</button>
      </div>
      <ul class=mres id=hf3hclist></ul>
    </div>
  </div>
</div>

@once
<style>
.hf3-input{width:100%;border:1.5px solid var(--border);border-radius:12px;padding:14px 15px;font:500 15px var(--sans);color:var(--navy);background:#fff;margin-bottom:4px;box-sizing:border-box}
.hf3-input:focus{outline:none;border-color:var(--gold);box-shadow:0 0 0 3px rgba(197,150,58,.14)}
.dest-field{width:100%;display:flex;align-items:center;gap:10px;border:1.5px solid var(--border);border-radius:12px;padding:13px 14px;background:#fff;cursor:pointer;text-align:left;margin-bottom:4px;box-sizing:border-box;transition:border-color .15s,box-shadow .15s}
.dest-field:hover{border-color:var(--gold)}
.dest-field.filled{border-color:var(--gold);background:#fffdf8}
.dest-field .dflag{width:24px;height:18px;border-radius:3px;object-fit:cover;flex:none;display:none}
.dest-field .dtxt{flex:1;min-width:0;font:500 15px var(--sans);color:#94a3b8}
.dest-field.filled .dtxt{color:var(--navy);font-weight:700}
.dest-field .dchev{width:16px;height:16px;color:var(--muted);flex:none}
.dest-field.locked{cursor:default;pointer-events:none}
.dest-overlay{position:fixed;inset:0;background:rgba(11,21,40,.55);display:none;align-items:center;justify-content:center;z-index:100;backdrop-filter:blur(2px);padding:20px}
.dest-overlay.open{display:flex}
.dest-sheet{background:#fff;border-radius:22px;max-width:460px;width:100%;padding:24px 22px 28px;box-shadow:0 40px 90px -30px rgba(0,0,0,.45);max-height:85vh;overflow-y:auto;box-sizing:border-box}
.dest-sheet-hd{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px}
.dest-sheet-hd h3{font-family:var(--serif);font-weight:400;font-size:21px;margin:0;color:var(--navy)}
.dest-x{width:32px;height:32px;border-radius:50%;border:1.5px solid var(--border);background:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--muted)}
.dest-x:hover{color:var(--navy);border-color:var(--gold)}
.tile .csrch{width:18px;height:18px;color:var(--muted);margin-left:auto}
.cta-ghost{margin-top:10px;width:100%;background:#fff;color:var(--navy);border:1.5px solid var(--border);border-radius:13px;padding:14px;font:700 14px var(--sans);cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:.15s}
.cta-ghost:hover{border-color:var(--gold);background:#fffdf8}
.cta-ghost svg{width:17px;height:17px;flex:none;color:var(--gold)}
.cb-timer{font:800 13px var(--sans);color:var(--gold);min-width:28px;text-align:left}
@media(max-width:480px){
  .dest-overlay{padding:12px}
  .dest-sheet{border-radius:16px;padding:20px 16px 24px}
}
</style>
@endonce

<script>
(function(){
  var WA={!! json_encode($bpWa) !!};

  // UK mobile normalizer — see [[hero-form-variants]] memory / hero-form-variant2.blade.php for
  // why: a plain digit-count check rejected numbers typed in international format and accepted
  // landlines. Strip a redundant leading "44", then any leftover leading zero, then require
  // exactly 10 digits starting with 7 (the real shape of a UK mobile's national number).
  function hf3NormalizePhone(raw){
    var d=(raw||'').replace(/[^0-9]/g,'');
    if(d.slice(0,2)==='44'&&d.length>10) d=d.slice(2);
    d=d.replace(/^0+/,'');
    return d;
  }
  function hf3PhoneOk(d){ return /^7\d{9}$/.test(d); }
  var HSC=[['Austria','at'],['Belgium','be'],['Croatia','hr'],['Czechia','cz'],['Denmark','dk'],
  ['Estonia','ee'],['Finland','fi'],['France','fr'],['Germany','de'],['Greece','gr'],['Hungary','hu'],
  ['Iceland','is'],['Italy','it'],['Latvia','lv'],['Liechtenstein','li'],['Lithuania','lt'],
  ['Luxembourg','lu'],['Malta','mt'],['Netherlands','nl'],['Norway','no'],['Poland','pl'],
  ['Portugal','pt'],['Slovakia','sk'],['Slovenia','si'],['Spain','es'],['Sweden','se'],
  ['Switzerland','ch'],['Bulgaria','bg'],['Romania','ro']];
  var state={dest:null,iso:null};
  var locked=false;

  function lockToFlowDest(){
    if (window.BP_FLOW_DEST && window.BP_FLOW_DEST.dest) {
      locked = true;
      state.dest = window.BP_FLOW_DEST.dest;
      state.iso = window.BP_FLOW_DEST.iso || '';
      var field = document.getElementById('hf3destField');
      field.classList.add('filled','locked');
      document.getElementById('hf3destTxt').textContent = state.dest;
      if (state.iso) {
        var flag = document.getElementById('hf3destFlag');
        flag.src = 'https://flagcdn.com/'+state.iso+'.svg';
        flag.style.display = 'block';
      }
      document.getElementById('hf3destChev').style.display = 'none';
    }
  }

  window.hf3destOpen=function(){ if(locked)return; document.getElementById('hf3destOverlay').classList.add('open'); };
  window.hf3destClose=function(){
    document.getElementById('hf3destOverlay').classList.remove('open');
    hf3Close();
  };
  window.hf3destPick=function(name,iso){
    state.dest=name; state.iso=iso;
    var field=document.getElementById('hf3destField');
    document.getElementById('hf3destFlag').src='https://flagcdn.com/'+iso+'.svg';
    document.getElementById('hf3destFlag').style.display='block';
    document.getElementById('hf3destTxt').textContent=name;
    field.classList.add('filled');
    document.getElementById('hf3e_dest').style.display='none';
    hf3destClose();
  };

  window.hf3Search=function(){
    document.getElementById('hf3destGrid').style.display='none';
    document.getElementById('hf3hcwrap').classList.remove('mhide');
    document.getElementById('hf3hcq').value='';
    hf3Render('');
    document.getElementById('hf3hcq').focus();
  };
  window.hf3Close=function(){
    document.getElementById('hf3hcwrap').classList.add('mhide');
    document.getElementById('hf3destGrid').style.display='';
  };
  window.hf3Render=function(q){
    q=(q||'').trim().toLowerCase();
    var list=document.getElementById('hf3hclist');
    var matches=HSC.filter(function(c){return c[0].toLowerCase().indexOf(q)===0;});
    if(!matches.length){ list.innerHTML='<li class=nomatch>No matches</li>'; return; }
    list.innerHTML=matches.map(function(c){
      return '<li onclick="hf3destPick(\''+c[0]+'\',\''+c[1]+'\')"><img class=flag src="https://flagcdn.com/'+c[1]+'.svg" alt=""><span class=cn2>'+c[0]+'</span><span class="cst2 open"><span class=cdot2></span>Open</span></li>';
    }).join('');
  };

  window.hf3startCallback=function(btn){
    if(btn.dataset.running)return;
    btn.dataset.running='1';
    var n=60,label=document.getElementById('hf3cbLabel');
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

  window.hf3send=function(){
    var name=(document.getElementById('hf3name').value||'').trim();
    var phoneRaw=(document.getElementById('hf3phone').value||'').trim();
    var phoneDigits=hf3NormalizePhone(phoneRaw);
    var okDest=!!state.dest, okName=name.length>=2, okPhone=hf3PhoneOk(phoneDigits);
    document.getElementById('hf3e_dest').style.display=okDest?'none':'block';
    document.getElementById('hf3e_name').style.display=okName?'none':'block';
    document.getElementById('hf3e_phone').style.display=okPhone?'none':'block';
    if(!okDest||!okName||!okPhone)return;

    var fullPhone='+44'+phoneDigits;
    try{ if(window.postLead) window.postLead(name, fullPhone, state.dest||'', 'case-single'); }catch(e){}

    var text='Hi Beyond Passports, I would like help with my Schengen visa.'
      +'\nName: '+name
      +(state.dest?'\nDestination: '+state.dest:'')
      +'\nNumber: '+fullPhone;
    var enc=encodeURIComponent(text);
    var web=(window.bpWaUrl||String)('https://wa.me/'+WA+'?text='+enc);
    var app='whatsapp://send?phone='+WA+'&text='+enc;
    var isMobile=/Android|iPhone|iPad|iPod/i.test(navigator.userAgent);

    document.getElementById('hf3-form').style.display='none';
    document.getElementById('hf3sentname').textContent=name?(', '+name):'';
    document.getElementById('hf3-sent').style.display='block';

    if(isMobile){ try{location.href=app;}catch(e){} setTimeout(function(){location.href=web;},1500); }
    else{ window.open(web,'_blank'); }
  };

  document.addEventListener('DOMContentLoaded', lockToFlowDest);
})();
</script>
