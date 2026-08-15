{{-- Desktop-only appointment-board lead modal (V1: split layout, dark country rail,
     phone-first "Secure now", channel row below).

     Gated by config('ukv.slots.desktop_form') — OFF by default (staging flag). When on,
     desktop pointers (pointer:fine, >=900px) get this modal on board-tile click instead
     of the wa.me tap-through; mobile behaviour is untouched.

     Submits phone + country to the existing lp-bold.lead endpoint (email to the team
     inbox + log, UTM attached by the utm-capture helper when present). "Secure on Chat"
     reuses the tile's original wa.me link; "Book Consultation" opens Calendly. --}}
@if (config('ukv.slots.desktop_form'))
<div class="bdf" id="bdf" role="dialog" aria-modal="true" aria-labelledby="bdf-country" hidden>
  <div class="bdf-box">
    <div class="bdf-rail">
      <img class="bdf-flag" id="bdf-flag" src="" alt="" width="44" height="31">
      <h3 id="bdf-country">Country</h3>
      <p class="bdf-st" id="bdf-status">Slots open</p>
      <div class="bdf-stats">
        <span><b>{{ \App\Support\SiteStats::approval() }}%</b>approval rate</span>
        <span><b>30 min</b>reply time</span>
        <span><b>&pound;0</b>until confirmed</span>
      </div>
    </div>
    <div class="bdf-main">
      <button type="button" class="bdf-x" data-bdf-close aria-label="Close">&times;</button>
      <p class="bdf-lead">Drop your number and a named consultant secures the live slot with you personally.</p>
      <form id="bdf-form" autocomplete="off">
        <label class="bdf-fl" for="bdf-phone">Your phone number <span class="bdf-req">*</span></label>
        @include('partials.phone-country', ['id' => 'bdf-phone', 'name' => 'phone', 'required' => true, 'placeholder' => '7911 123456'])
        <p class="bdf-hint">Required. We only use this to confirm your slot. Never shared.</p>
        <button type="submit" class="bdf-secure">Secure now</button>
      </form>
      <div class="bdf-ordiv">or secure it your way</div>
      <div class="bdf-chrow">
        <a class="bdf-ch bdf-ch-wa" id="bdf-wa" href="#">@include('partials.wa-glyph')Secure on Chat</a>
        <a class="bdf-ch bdf-ch-cal" href="https://calendly.com/beyondpassports/30min" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 3h-1V1h-2v2H8V1H6v2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2zm0 16H5V9h14v10zM5 7V5h14v2H5z"/></svg>Book Consultation</a>
      </div>
      <p class="bdf-note"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 1 3 5v6c0 5.5 3.8 10.7 9 12 5.2-1.3 9-6.5 9-12V5l-9-4z"/></svg>Booking is confirmed live with the centre before anything is paid.</p>
      {{-- thank-you state (swapped in after submit) --}}
      <div class="bdf-thx" hidden>
        <div class="bdf-tick"><svg viewBox="0 0 24 24"><path d="m5 13 4 4L19 7"/></svg></div>
        <h4>Request received</h4>
        <p>Your slot request has landed in our inbox. <b>A named consultant replies within 30 minutes</b>, 7 days a week. Want to move faster?</p>
        <div class="bdf-thx-btns">
          <a class="bdf-ch bdf-ch-cal" href="https://calendly.com/beyondpassports/30min" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 3h-1V1h-2v2H8V1H6v2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2zm0 16H5V9h14v10zM5 7V5h14v2H5z"/></svg>Book a free 30-min consultation</a>
          <a class="bdf-ch bdf-ch-wa" id="bdf-wa2" href="#">@include('partials.wa-glyph')Chat with us on WhatsApp now</a>
        </div>
        <p class="bdf-thx-sub">Or just wait for our message. Either way, you are in the queue.</p>
      </div>
    </div>
  </div>
</div>
<style>
.bdf{position:fixed;inset:0;z-index:150;display:flex;align-items:center;justify-content:center;padding:16px;background:rgba(10,16,24,.62);backdrop-filter:blur(3px)}
.bdf[hidden]{display:none}
.bdf-box{display:flex;width:min(660px,94vw);border-radius:22px;overflow:hidden;background:#fff;box-shadow:0 50px 110px -35px rgba(0,0,0,.6);animation:bdf-in .2s ease}
@keyframes bdf-in{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:none}}
.bdf-rail{flex:0 0 220px;background:linear-gradient(160deg,#132c34,#1F6E63);color:#fff;padding:26px 22px;display:flex;flex-direction:column;position:relative;overflow:hidden}
.bdf-rail::after{content:'';position:absolute;right:-50px;bottom:-70px;width:200px;height:200px;border-radius:50%;background:radial-gradient(circle,rgba(143,227,201,.16),transparent 65%)}
.bdf-flag{width:44px;height:31px;border-radius:4px;box-shadow:0 0 0 1px rgba(255,255,255,.3);margin-bottom:12px;object-fit:cover;position:relative;z-index:1}
.bdf-rail h3{font:800 21px "Outfit",system-ui,sans-serif;margin:0 0 4px;letter-spacing:-.02em;position:relative;z-index:1}
.bdf-st{font:700 11px "Outfit",system-ui,sans-serif;letter-spacing:.1em;text-transform:uppercase;color:#8fe3c9;margin:0 0 20px;position:relative;z-index:1}
.bdf-stats{margin-top:auto;display:flex;flex-direction:column;gap:12px;position:relative;z-index:1}
.bdf-stats span{font:600 12px "Outfit",system-ui,sans-serif;color:rgba(255,255,255,.85)}
.bdf-stats b{display:block;font-size:17px;color:#8fe3c9}
.bdf-main{flex:1;padding:24px 24px 22px;background:#fff;position:relative}
.bdf-x{position:absolute;right:14px;top:14px;background:#eef2f6;color:#5d6b76;border:0;width:32px;height:32px;border-radius:50%;font-size:18px;line-height:1;cursor:pointer}
.bdf-lead{font-size:13px;color:#5d6b76;line-height:1.55;margin:0 0 16px;max-width:36ch}
.bdf-fl{font-size:12.5px;font-weight:800;color:#16222E;margin:0 0 8px;display:block}
.bdf-req{color:#c0492f;font-weight:900}
.bdf-hint{font-size:11.5px;color:#5d6b76;margin:7px 2px 0}
.bdf-secure{display:flex;align-items:center;justify-content:center;gap:9px;width:100%;background:#25D366;color:#fff;border:0;border-radius:14px;font:800 16px "Outfit",system-ui,sans-serif;padding:15px 22px;cursor:pointer;box-shadow:0 14px 30px -12px rgba(37,211,102,.7);transition:filter .15s,transform .12s;margin-top:14px}
.bdf-secure:hover{filter:brightness(.95);transform:translateY(-1px)}
.bdf-secure[disabled]{opacity:.6;cursor:wait}
.bdf-ordiv{display:flex;align-items:center;gap:12px;margin:15px 0;font:800 10.5px "Outfit",system-ui,sans-serif;letter-spacing:.14em;text-transform:uppercase;color:#5d6b76}
.bdf-ordiv::before,.bdf-ordiv::after{content:'';flex:1;height:1.5px;background:#dde3ec}
.bdf-chrow{display:flex;gap:9px}
.bdf-ch{flex:1;display:flex;align-items:center;justify-content:center;gap:8px;border-radius:12px;font:800 13px "Outfit",system-ui,sans-serif;padding:12px 10px;text-decoration:none;cursor:pointer;transition:transform .12s,filter .15s}
.bdf-ch:hover{transform:translateY(-1px);filter:brightness(.96)}
.bdf-ch svg{width:16px;height:16px;flex:none}
.bdf-ch-wa{background:rgba(37,211,102,.12);color:#128c46;border:1.5px solid rgba(37,211,102,.4)}
.bdf-ch-wa svg{fill:#128c46}
.bdf-ch-cal{background:rgba(21,94,122,.08);color:#155E7A;border:1.5px solid rgba(21,94,122,.3)}
.bdf-ch-cal svg{fill:#155E7A}
.bdf-note{display:flex;align-items:center;justify-content:center;gap:7px;font-size:11.5px;color:#5d6b76;margin:11px 0 0;text-align:center}
.bdf-note svg{width:13px;height:13px;fill:#1F6E63;flex:none}
.bdf-thx{text-align:center;padding:8px 0 0}
.bdf-thx[hidden]{display:none}
.bdf-tick{width:62px;height:62px;border-radius:50%;background:rgba(46,154,140,.14);border:2px solid #2E9A8C;display:grid;place-items:center;margin:0 auto 14px;animation:bdf-pop .3s ease}
@keyframes bdf-pop{from{transform:scale(.6);opacity:0}to{transform:scale(1);opacity:1}}
.bdf-tick svg{width:30px;height:30px;fill:none;stroke:#1F6E63;stroke-width:3;stroke-linecap:round;stroke-linejoin:round}
.bdf-thx h4{font:800 20px "Outfit",system-ui,sans-serif;margin:0 0 8px;color:#16222E}
.bdf-thx p{font-size:14px;color:#5d6b76;line-height:1.6;margin:0 0 18px}
.bdf-thx p b{color:#16222E}
.bdf-thx-btns{display:flex;flex-direction:column;gap:10px}
.bdf-thx-btns .bdf-ch{padding:13px 16px}
.bdf-thx-sub{font-size:12px;color:#5d6b76;margin:13px 0 0}
@media(max-width:600px){.bdf-box{flex-direction:column}.bdf-rail{flex:none}}
</style>
<script>
(function(){
  // Desktop only: fine pointer + real viewport. Mobile keeps the wa.me tap-through.
  var mq = window.matchMedia('(pointer: fine)');
  function isDesktop(){ return mq.matches && window.innerWidth >= 900; }
  var wrap = document.getElementById('bdf');
  if (!wrap) return;
  var box = wrap.querySelector('.bdf-box'),
      form = document.getElementById('bdf-form'),
      thx = wrap.querySelector('.bdf-thx'),
      wa1 = document.getElementById('bdf-wa'),
      wa2 = document.getElementById('bdf-wa2'),
      ordiv = wrap.querySelector('.bdf-ordiv'),
      chrow = wrap.querySelector('.bdf-chrow'),
      note = wrap.querySelector('.bdf-note'),
      leadP = wrap.querySelector('.bdf-lead');
  var ISO = {"austria":"at","belgium":"be","bulgaria":"bg","croatia":"hr","czechia":"cz","denmark":"dk","estonia":"ee","finland":"fi","france":"fr","germany":"de","greece":"gr","hungary":"hu","iceland":"is","italy":"it","latvia":"lv","liechtenstein":"li","lithuania":"lt","luxembourg":"lu","malta":"mt","netherlands":"nl","norway":"no","poland":"pl","portugal":"pt","romania":"ro","slovakia":"sk","slovenia":"si","spain":"es","sweden":"se","switzerland":"ch"};
  var country = '';
  function openM(card){
    var name = card.getAttribute('data-slotname') || '';
    country = name.charAt(0).toUpperCase() + name.slice(1);
    var stp = card.querySelector('.ngstp');
    document.getElementById('bdf-country').textContent = country;
    document.getElementById('bdf-status').textContent = stp ? stp.textContent : 'Slots open';
    var iso = ISO[name] || '';
    var flag = document.getElementById('bdf-flag');
    if (iso) { flag.src = 'https://flagcdn.com/' + iso + '.svg'; flag.hidden = false; } else { flag.hidden = true; }
    var href = card.getAttribute('href') || '#';
    wa1.href = href; wa2.href = href;
    // reset to form state
    form.hidden = false; ordiv.hidden = false; chrow.hidden = false; note.hidden = false; leadP.hidden = false; thx.hidden = true;
    wrap.hidden = false;
    document.body.style.overflow = 'hidden';
  }
  function closeM(){ wrap.hidden = true; document.body.style.overflow = ''; }
  wrap.addEventListener('click', function(e){ if (e.target === wrap) closeM(); });
  wrap.querySelector('[data-bdf-close]').addEventListener('click', closeM);
  document.addEventListener('keydown', function(e){ if (e.key === 'Escape' && !wrap.hidden) closeM(); });
  // Intercept board tile clicks on desktop only.
  document.querySelectorAll('.ngcard').forEach(function(card){
    card.addEventListener('click', function(e){
      if (!isDesktop()) return;               // mobile: normal wa.me behaviour
      e.preventDefault();
      openM(card);
    });
  });
  // Submit -> existing lead endpoint (email + log). UTM attached when the capture helper is present.
  form.addEventListener('submit', function(e){
    e.preventDefault();
    var input = document.getElementById('bdf-phone');
    var phone = (input.value || '').trim();
    if (!phone) { input.focus(); return; }
    var dial = form.querySelector('[data-pc-dial]');
    if (phone.charAt(0) !== '+' && dial) { phone = dial.value + ' ' + phone.replace(/[^\d]/g,'').replace(/^0+/,''); }
    var btn = form.querySelector('.bdf-secure'); btn.disabled = true;
    var payload = { phone: phone, dest: country, utm: (window.bpUtm ? window.bpUtm() : null) };
    fetch(@json(route('lp-bold.lead')), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': @json(csrf_token()), 'Accept': 'application/json' },
      body: JSON.stringify(payload)
    }).catch(function(){}).finally(function(){
      btn.disabled = false;
      form.hidden = true; ordiv.hidden = true; chrow.hidden = true; note.hidden = true; leadP.hidden = true;
      thx.hidden = false;
    });
  });
})();
</script>
@endif
