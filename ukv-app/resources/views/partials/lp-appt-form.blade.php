{{-- Hero appointment form (Variant A — boarding-pass card) for the standalone LPs.
     Name + mobile → opens WhatsApp with those details prefilled + an eligibility query.
     Self-contained (bpc-af- prefix); no ukv.css needed. Works without JS: the button is a
     real link to the generic chat, and JS upgrades it to include the typed name/number. --}}
@php $bpcWa = config('ukv.whatsapp') ?: '447882747584'; @endphp
<form class="bpc-af" onsubmit="return bpcAppt(this)" data-capture="{{ route('appointment.enquiry') }}" novalidate>
  @csrf
  <span class="bpc-af-stamp">FREE</span>
  <p class="bpc-af-eyebrow">Book your appointment</p>
  <h2 class="bpc-af-h">Check your eligibility, free</h2>
  <p class="bpc-af-sub">Just your name and number. A UK &amp; Europe registered service spots what could get you refused, then holds the soonest slot before it goes.</p>
  <label class="bpc-af-l" for="bpc-af-name">Your name</label>
  <input class="bpc-af-i" id="bpc-af-name" name="n" type="text" placeholder="e.g. Aisha Khan" autocomplete="name">
  <label class="bpc-af-l" for="bpc-af-phone">Mobile number</label>
  <input class="bpc-af-i" id="bpc-af-phone" name="p" type="tel" placeholder="07…" autocomplete="tel">
  {{-- Email fallback: capture a lead who would rather not use WhatsApp / leave a number. --}}
  <label class="bpc-af-l" for="bpc-af-email">Or email (if you prefer we email you)</label>
  <input class="bpc-af-i" id="bpc-af-email" name="e" type="email" placeholder="name@email.com" autocomplete="email">
  <a class="bpc-af-go" href="https://wa.me/{{ $bpcWa }}?text={{ rawurlencode('Hi Beyond Passports, I would like to check my Schengen visa eligibility.') }}" target="_blank" rel="noopener" data-wa="{{ $bpcWa }}">
    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M.057 24l1.687-6.163a11.867 11.867 0 0 1-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.817 11.817 0 0 1 8.413 3.488 11.824 11.824 0 0 1 3.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 0 1-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884a9.86 9.86 0 0 0 1.51 5.26l-.999 3.648 3.978-1.607zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
    Check eligibility →
  </a>
  <p class="bpc-af-trust"><span class="bpc-af-dot"></span>@include('partials.uk-eu-flags',['size'=>13]) Reply in minutes · No payment now · Registered in <b>UK &amp; Europe</b></p>
</form>
<script>
function bpcAppt(f){
  var n=(f.n.value||'').trim(), p=(f.p.value||'').trim(), e=(f.e?f.e.value:'').trim(), a=f.querySelector('.bpc-af-go'), num=a.getAttribute('data-wa');
  // Belt-and-braces lead capture: POST name/number to the server (email + log) so the lead is
  // recorded even if the traveller never sends the WhatsApp message. Fire-and-forget — never let
  // it block or break the WhatsApp hand-off below.
  try{
    var url=f.getAttribute('data-capture'), tok=f.querySelector('input[name=_token]');
    if(url && tok && (n||p||e)){
      fetch(url,{method:'POST',keepalive:true,headers:{'Content-Type':'application/json','X-CSRF-TOKEN':tok.value,'Accept':'application/json'},
        body:JSON.stringify({name:n,phone:p,email:e,source:location.pathname})}).catch(function(){});
    }
  }catch(e){}
  var msg='Hi Beyond Passports, I would like to check my Schengen visa eligibility.';
  if(n||p){ msg='Hi Beyond Passports, I am '+(n||'(name)')+(p?' ('+p+')':'')+'. I would like to check my Schengen visa eligibility and book my appointment.'; }
  window.open('https://wa.me/'+num+'?text='+encodeURIComponent(msg),'_blank','noopener'); return false;
}
</script>
@once
<style>
  .bpc-af{position:relative;background:#fff;border:1px solid #dde3ec;border-radius:20px;box-shadow:0 40px 80px -40px rgba(30,45,70,.5);
    padding:26px 26px 22px;max-width:460px;font-family:"Outfit",system-ui,sans-serif;color:#16222E;box-sizing:border-box}
  .bpc-af *{box-sizing:border-box}
  .bpc-af-stamp{position:absolute;top:-16px;right:-12px;background:#2E9A8C;color:#fff;font:800 11px system-ui;letter-spacing:.08em;padding:8px 12px;border-radius:10px;transform:rotate(4deg);box-shadow:0 10px 20px -8px rgba(46,154,140,.7)}
  .bpc-af-eyebrow{text-align:center;font:700 12px/1 system-ui;letter-spacing:.12em;text-transform:uppercase;color:#155E7A;margin:0 0 8px}
  .bpc-af-h{text-align:center;font:800 26px/1.05 "Outfit",system-ui;letter-spacing:-.02em;margin:0 0 6px}
  .bpc-af-sub{text-align:center;color:#5d6b76;font-size:14px;line-height:1.45;margin:0 0 18px}
  .bpc-af-l{display:block;font:700 12px system-ui;margin:0 0 5px;color:#16222E}
  .bpc-af-i{width:100%;padding:13px;border:1px solid #dde3ec;border-radius:11px;font:inherit;font-size:15px;background:#fff;color:#16222E;margin:0 0 13px}
  .bpc-af-i:focus{outline:none;border-color:#155E7A;box-shadow:0 0 0 3px rgba(21,94,122,.14)}
  .bpc-af-i::placeholder{color:#9aa6b0}
  .bpc-af-go{display:flex;align-items:center;justify-content:center;gap:9px;background:#25D366;color:#fff;text-decoration:none;
    border-radius:12px;font:800 16px "Outfit",system-ui;padding:14px 22px;cursor:pointer;width:100%}
  .bpc-af-go:hover{background:#1da851}
  .bpc-af-go svg{width:18px;height:18px;fill:#fff;flex:none}
  .bpc-af-trust{display:flex;flex-wrap:wrap;gap:6px;justify-content:center;align-items:center;font-size:12.5px;color:#5d6b76;margin:14px 0 0}
  .bpc-af-trust b{color:#16222E}
  .bpc-af-dot{width:7px;height:7px;border-radius:50%;background:#25D366;display:inline-block}
</style>
@endonce
