{{-- Phone field with country dial-code selector (variant A: attached combo).
     Self-contained (own pc- classes + @once CSS/JS). Keeps the real input's id/name so
     existing form JS and the backend keep working; on submit the selected dial code is
     prepended into the value (e.g. "+44 7911 123456"). Also writes a hidden
     {name}_dialcode field. Flags use emoji (real flags on iOS/Android/Mac; 2-letter code
     on Windows desktop, which has no flag glyphs).

     Params: name (default 'phone'), id (default = name), required (bool), placeholder,
     value (old input), class (extra classes merged onto the input), default (ISO2, e.g. 'GB'). --}}
@php
  $pcName  = $name ?? 'phone';
  $pcId    = $id ?? $pcName;
  $pcReq   = $required ?? false;
  $pcPh    = $placeholder ?? '7XXX XXXXXX';
  $pcVal   = $value ?? '';
  $pcClass = $class ?? '';
  $pcDef   = strtoupper($default ?? 'GB');
@endphp
<div class="pc-combo" data-pc data-pc-default="{{ $pcDef }}">
  <button type="button" class="pc-btn" data-pc-toggle aria-haspopup="listbox" aria-expanded="false" aria-label="Select country dialling code">
    <span class="pc-fl" data-pc-fl>🇬🇧</span><span class="pc-dc" data-pc-dc>+44</span><span class="pc-car" aria-hidden="true">▾</span>
  </button>
  <input type="tel" id="{{ $pcId }}" name="{{ $pcName }}" class="pc-input {{ $pcClass }}"
         value="{{ $pcVal }}" placeholder="{{ $pcPh }}" inputmode="tel" autocomplete="tel"
         @if($pcReq) required aria-required="true" @endif>
  <input type="hidden" name="{{ $pcName }}_dialcode" value="+44" data-pc-dial>
  <div class="pc-pop" data-pc-pop role="listbox" aria-label="Country" hidden></div>
</div>
@once
<style>
  .pc-combo{position:relative;display:flex;border:1.5px solid #e2e8ee;border-radius:12px;background:#fff;transition:border-color .15s,box-shadow .15s}
  .pc-combo:focus-within{border-color:var(--cta,#155E7A);box-shadow:0 0 0 3px rgba(21,94,122,.14)}
  .pc-btn{display:flex;align-items:center;gap:6px;padding:0 11px;background:#f4f7f9;border:0;border-right:1.5px solid #e2e8ee;border-radius:12px 0 0 12px;cursor:pointer;font:700 14px var(--display,inherit);color:var(--ink,#16222E);white-space:nowrap}
  .pc-btn .pc-fl{font-size:18px;line-height:1}
  .pc-btn .pc-car{color:var(--muted,#5d6b76);font-size:10px}
  .pc-input{flex:1;min-width:0;border:0!important;padding:13px 14px;font:600 15px var(--display,inherit);border-radius:0 12px 12px 0;background:transparent;color:var(--ink,#16222E);box-shadow:none!important}
  .pc-input:focus{outline:none}
  .pc-pop{position:absolute;z-index:60;top:calc(100% + 6px);left:0;width:290px;max-width:90vw;background:#fff;border:1px solid var(--paper-edge,#dde3ec);border-radius:14px;box-shadow:0 30px 60px -24px rgba(20,30,45,.5);padding:8px}
  .pc-pop[hidden]{display:none}
  .pc-search{width:100%;padding:9px 11px;border:1.5px solid #e2e8ee;border-radius:9px;font:600 13px var(--display,inherit);margin:0 0 6px}
  .pc-search:focus{outline:none;border-color:var(--cta,#155E7A)}
  .pc-list{max-height:220px;overflow:auto;margin:0;padding:0;list-style:none}
  .pc-opt{display:flex;align-items:center;gap:10px;padding:9px 10px;border-radius:9px;cursor:pointer;font:600 14px var(--display,inherit)}
  .pc-opt:hover,.pc-opt.pc-hl{background:#eef4f6}
  .pc-opt .pc-fl{font-size:18px;line-height:1}
  .pc-opt .pc-nm{flex:1;color:var(--ink,#16222E)}
  .pc-opt .pc-dc{color:var(--muted,#5d6b76);font-weight:700;font-size:13px}
  @media(prefers-reduced-motion:reduce){.pc-combo{transition:none}}
</style>
<script>
(function(){
  var C=[
   ["United Kingdom","GB","🇬🇧","+44"],["Ireland","IE","🇮🇪","+353"],["France","FR","🇫🇷","+33"],
   ["Germany","DE","🇩🇪","+49"],["Spain","ES","🇪🇸","+34"],["Italy","IT","🇮🇹","+39"],
   ["Netherlands","NL","🇳🇱","+31"],["Belgium","BE","🇧🇪","+32"],["Portugal","PT","🇵🇹","+351"],
   ["Poland","PL","🇵🇱","+48"],["Greece","GR","🇬🇷","+30"],["Austria","AT","🇦🇹","+43"],
   ["Czechia","CZ","🇨🇿","+420"],["Switzerland","CH","🇨🇭","+41"],["Sweden","SE","🇸🇪","+46"],
   ["Denmark","DK","🇩🇰","+45"],["Norway","NO","🇳🇴","+47"],["United States","US","🇺🇸","+1"],
   ["Canada","CA","🇨🇦","+1"],["India","IN","🇮🇳","+91"],["Pakistan","PK","🇵🇰","+92"],
   ["Bangladesh","BD","🇧🇩","+880"],["Nigeria","NG","🇳🇬","+234"],["Kenya","KE","🇰🇪","+254"],
   ["South Africa","ZA","🇿🇦","+27"],["UAE","AE","🇦🇪","+971"],["Saudi Arabia","SA","🇸🇦","+966"],
   ["Qatar","QA","🇶🇦","+974"],["Australia","AU","🇦🇺","+61"],["New Zealand","NZ","🇳🇿","+64"],
   ["Turkey","TR","🇹🇷","+90"],["China","CN","🇨🇳","+86"]
  ];
  function initOne(root){
    if(root.__pc) return; root.__pc=1;
    var btn=root.querySelector('[data-pc-toggle]'), pop=root.querySelector('[data-pc-pop]'),
        fl=root.querySelector('[data-pc-fl]'), dcEls=root.querySelectorAll('[data-pc-dc]'),
        dial=root.querySelector('[data-pc-dial]'), input=root.querySelector('.pc-input');
    var cur=C[0];
    var def=(root.getAttribute('data-pc-default')||'GB').toUpperCase();
    for(var i=0;i<C.length;i++){ if(C[i][1]===def){ cur=C[i]; break; } }
    pop.innerHTML='<input class="pc-search" placeholder="Search country or code" aria-label="Search country"><ul class="pc-list"></ul>';
    var search=pop.querySelector('.pc-search'), ul=pop.querySelector('.pc-list');
    function paint(c){ cur=c; fl.textContent=c[2]; dcEls.forEach(function(e){e.textContent=c[3];}); if(dial)dial.value=c[3]; }
    function render(q){
      q=(q||'').toLowerCase().trim(); ul.innerHTML='';
      C.filter(function(c){return !q||c[0].toLowerCase().indexOf(q)>-1||c[3].indexOf(q)>-1||c[1].toLowerCase()===q;})
       .forEach(function(c){
        var li=document.createElement('li'); li.className='pc-opt'; li.setAttribute('role','option');
        li.innerHTML='<span class="pc-fl">'+c[2]+'</span><span class="pc-nm">'+c[0]+'</span><span class="pc-dc">'+c[3]+'</span>';
        li.addEventListener('mousedown',function(e){e.preventDefault(); paint(c); close(); input&&input.focus();});
        ul.appendChild(li);
      });
    }
    function open(){ render(''); pop.hidden=false; btn.setAttribute('aria-expanded','true'); setTimeout(function(){search.focus();},20); }
    function close(){ pop.hidden=true; btn.setAttribute('aria-expanded','false'); }
    btn.addEventListener('click',function(e){ e.stopPropagation(); pop.hidden?open():close(); });
    search.addEventListener('input',function(){ render(this.value); });
    search.addEventListener('keydown',function(e){ if(e.key==='Escape'){close(); btn.focus();} });
    document.addEventListener('click',function(e){ if(!root.contains(e.target)) close(); });
    paint(cur);
    // On submit, prepend the dial code into the number so the stored value is full-international.
    var form=input&&input.closest('form');
    if(form){ form.addEventListener('submit',function(){
      var v=(input.value||'').trim();
      if(v && v.charAt(0)!=='+'){ input.value=cur[3]+' '+v.replace(/^0+/,''); }
    }); }
  }
  function initAll(){ document.querySelectorAll('[data-pc]').forEach(initOne); }
  if(document.readyState!=='loading') initAll(); else document.addEventListener('DOMContentLoaded',initAll);
})();
</script>
@endonce
