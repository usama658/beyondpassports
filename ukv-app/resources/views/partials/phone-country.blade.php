{{-- Phone field with country dial-code selector (variant A: attached combo).
     Self-contained (own pc- classes + @once CSS/JS). Keeps the real input's id/name so
     existing form JS and the backend keep working; on submit the selected dial code is
     prepended into the value (e.g. "+44 7911 123456"). Also writes a hidden
     {name}_dialcode field. Flags are self-hosted SVGs (/assets/flags/{iso2}.svg,
     lipis/flag-icons, MIT) so they render identically on every OS incl. Windows.

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
    <span class="pc-fl" data-pc-fl><img class="pc-fl-img" src="{{ asset('assets/flags/'.strtolower($pcDef).'.svg') }}" alt="" width="20" height="15"></span><span class="pc-dc" data-pc-dc>+44</span><span class="pc-car" aria-hidden="true">▾</span>
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
  .pc-btn .pc-fl{display:flex;line-height:0}
  .pc-fl-img{width:20px;height:15px;border-radius:2px;display:block;object-fit:cover;box-shadow:0 0 0 1px rgba(0,0,0,.09)}
  .pc-btn .pc-car{color:var(--muted,#5d6b76);font-size:10px}
  .pc-input{flex:1;min-width:0;border:0!important;padding:13px 14px;font:600 15px var(--display,inherit);border-radius:0 12px 12px 0;background:transparent;color:var(--ink,#16222E);box-shadow:none!important}
  .pc-input:focus{outline:none}
  .pc-err{color:#c0492f;font:600 12.5px var(--display,inherit);margin:6px 2px 0}
  .pc-combo.pc-invalid{border-color:#c0492f}
  .pc-combo.pc-invalid:focus-within{box-shadow:0 0 0 3px rgba(192,73,47,.16)}
  .pc-pop{position:absolute;z-index:60;top:calc(100% + 6px);left:0;width:290px;max-width:90vw;background:#fff;border:1px solid var(--paper-edge,#dde3ec);border-radius:14px;box-shadow:0 30px 60px -24px rgba(20,30,45,.5);padding:8px}
  .pc-pop[hidden]{display:none}
  .pc-search{width:100%;padding:9px 11px;border:1.5px solid #e2e8ee;border-radius:9px;font:600 13px var(--display,inherit);margin:0 0 6px}
  .pc-search:focus{outline:none;border-color:var(--cta,#155E7A)}
  .pc-list{max-height:220px;overflow:auto;margin:0;padding:0;list-style:none}
  .pc-opt{display:flex;align-items:center;gap:10px;padding:9px 10px;border-radius:9px;cursor:pointer;font:600 14px var(--display,inherit)}
  .pc-opt:hover,.pc-opt.pc-hl{background:#eef4f6}
  .pc-opt .pc-fl{display:flex;line-height:0}
  .pc-opt .pc-nm{flex:1;color:var(--ink,#16222E)}
  .pc-opt .pc-dc{color:var(--muted,#5d6b76);font-weight:700;font-size:13px}
  @media(prefers-reduced-motion:reduce){.pc-combo{transition:none}}
</style>
<script>
(function(){
  // [name, iso2, emoji, dialcode, [minDigits, maxDigits]] — national significant digits (leading 0 dropped)
  var C=[
   ["United Kingdom","GB","🇬🇧","+44",[10,10]],["Ireland","IE","🇮🇪","+353",[7,9]],["France","FR","🇫🇷","+33",[9,9]],
   ["Germany","DE","🇩🇪","+49",[10,11]],["Spain","ES","🇪🇸","+34",[9,9]],["Italy","IT","🇮🇹","+39",[9,10]],
   ["Netherlands","NL","🇳🇱","+31",[9,9]],["Belgium","BE","🇧🇪","+32",[8,9]],["Portugal","PT","🇵🇹","+351",[9,9]],
   ["Poland","PL","🇵🇱","+48",[9,9]],["Greece","GR","🇬🇷","+30",[10,10]],["Austria","AT","🇦🇹","+43",[9,13]],
   ["Czechia","CZ","🇨🇿","+420",[9,9]],["Switzerland","CH","🇨🇭","+41",[9,9]],["Sweden","SE","🇸🇪","+46",[7,9]],
   ["Denmark","DK","🇩🇰","+45",[8,8]],["Norway","NO","🇳🇴","+47",[8,8]],["United States","US","🇺🇸","+1",[10,10]],
   ["Canada","CA","🇨🇦","+1",[10,10]],["India","IN","🇮🇳","+91",[10,10]],["Pakistan","PK","🇵🇰","+92",[10,10]],
   ["Bangladesh","BD","🇧🇩","+880",[10,10]],["Nigeria","NG","🇳🇬","+234",[8,10]],["Kenya","KE","🇰🇪","+254",[9,9]],
   ["South Africa","ZA","🇿🇦","+27",[9,9]],["UAE","AE","🇦🇪","+971",[9,9]],["Saudi Arabia","SA","🇸🇦","+966",[9,9]],
   ["Qatar","QA","🇶🇦","+974",[8,8]],["Australia","AU","🇦🇺","+61",[9,9]],["New Zealand","NZ","🇳🇿","+64",[8,10]],
   ["Turkey","TR","🇹🇷","+90",[10,10]],["China","CN","🇨🇳","+86",[11,11]]
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
    function flimg(c){ return '<img class="pc-fl-img" src="/assets/flags/'+c[1].toLowerCase()+'.svg" alt="" width="20" height="15">'; }
    function paint(c){ cur=c; fl.innerHTML=flimg(c); dcEls.forEach(function(e){e.textContent=c[3];}); if(dial)dial.value=c[3]; }
    function render(q){
      q=(q||'').toLowerCase().trim(); ul.innerHTML='';
      C.filter(function(c){return !q||c[0].toLowerCase().indexOf(q)>-1||c[3].indexOf(q)>-1||c[1].toLowerCase()===q;})
       .forEach(function(c){
        var li=document.createElement('li'); li.className='pc-opt'; li.setAttribute('role','option');
        li.innerHTML='<span class="pc-fl">'+flimg(c)+'</span><span class="pc-nm">'+c[0]+'</span><span class="pc-dc">'+c[3]+'</span>';
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
    // Inline validation (per-country digit-length). Lightweight: catches empty/letters/wrong-length.
    var err=document.createElement('div'); err.className='pc-err'; err.hidden=true; err.setAttribute('role','alert');
    root.parentNode.insertBefore(err, root.nextSibling);
    function showErr(m){ err.textContent=m; err.hidden=false; root.classList.add('pc-invalid'); input.setAttribute('aria-invalid','true'); }
    function clearErr(){ err.hidden=true; root.classList.remove('pc-invalid'); input.removeAttribute('aria-invalid'); }
    function status(){
      var raw=(input.value||'').trim();
      if(!raw){ return input.hasAttribute('required') ? 'req' : 'ok'; }
      var digits=raw.replace(/[^\d]/g,'').replace(/^0+/,''), rng=cur[4]||[6,15];
      return (digits.length>=rng[0] && digits.length<=rng[1]) ? 'ok' : 'bad';
    }
    input.addEventListener('input', clearErr);
    input.addEventListener('blur', function(){ if(status()==='bad'){ showErr('Enter a valid '+cur[0]+' phone number.'); } });
    // Validate + normalise to full-international on submit. Capture phase + stopPropagation so an
    // invalid number blocks the form's own submit handler (e.g. the LP redirect) too.
    var form=input&&input.closest('form');
    if(form){ form.addEventListener('submit',function(ev){
      var s=status();
      if(s!=='ok'){
        ev.preventDefault(); ev.stopPropagation();
        showErr(s==='req' ? 'Enter your phone number.' : 'Enter a valid '+cur[0]+' phone number.');
        input.focus();
        return;
      }
      clearErr();
      var v=(input.value||'').trim();
      if(v && v.charAt(0)!=='+'){ input.value=cur[3]+' '+v.replace(/[^\d]/g,'').replace(/^0+/,''); }
    }, true); }
  }
  function initAll(){ document.querySelectorAll('[data-pc]').forEach(initOne); }
  if(document.readyState!=='loading') initAll(); else document.addEventListener('DOMContentLoaded',initAll);
})();
</script>
@endonce
