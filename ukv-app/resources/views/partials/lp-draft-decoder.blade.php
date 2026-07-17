{{--
  DRAFT / reusable — "Refusal recovery" decoder section (redacted letter + reason selector).
  Not currently on the Bold LP. To reuse: @include('partials.lp-draft-decoder')
  inside a `.lpb` wrapper on a page that defines the LP tokens (--ink, --muted,
  --edge, --cta, --red, --sh). $wa must be in scope
  ($wa = 'https://wa.me/'.config('ukv.whatsapp')).
  Self-contained: carries its own CSS and accordion JS.
--}}
@push('head')
<style>
.lpb .dec .top{max-width:60ch;margin:0 0 34px}.lpb .dec .top h2{margin:0 0 12px}
.lpb .dec .grid{display:grid;grid-template-columns:.85fr 1.15fr;gap:44px;align-items:start}
.lpb .dec .side{position:sticky;top:96px}
.lpb .dec .side .btn{margin-top:18px;width:100%}
.lpb .letter{background:#fbfaf7;border:1px solid #e6e2d8;border-radius:12px;padding:26px 26px 30px;box-shadow:var(--sh);position:relative;overflow:hidden;font-size:13px}
.lpb .letter .lh{display:flex;justify-content:space-between;border-bottom:1px solid #e6e2d8;padding-bottom:12px;margin-bottom:14px}
.lpb .letter .lh b{font-size:12px;letter-spacing:.04em;color:#3d4750}.lpb .letter .lh span{color:#9aa0a3;font-size:11px}
.lpb .letter .rl{height:8px;border-radius:3px;background:#e7e3d8;margin:9px 0}
.lpb .letter .rl.s{width:64%}.lpb .letter .rl.m{width:88%}.lpb .letter .rl.l{width:96%}
.lpb .letter .hl{background:rgba(192,73,47,.1);border-left:3px solid var(--red);padding:9px 12px;margin:14px 0;border-radius:4px;color:#5c3026;font-weight:600;font-size:12.5px;line-height:1.45}
.lpb .letter .refstamp{position:absolute;top:20px;right:18px;white-space:nowrap;transform:rotate(8deg);border:2.5px solid var(--red);color:var(--red);font-weight:800;letter-spacing:.16em;font-size:14px;padding:6px 14px;border-radius:7px;opacity:.85;text-transform:uppercase;box-shadow:inset 0 0 0 2px rgba(192,73,47,.12)}
.lpb .letter .lfoot{border-top:1px solid #e6e2d8;margin-top:16px;padding-top:12px;color:#9aa0a3;font-size:11px}
.lpb .lnote{padding:16px;background:#fff;border:1px solid var(--edge);border-radius:12px;color:var(--muted);font-size:14px;line-height:1.55;margin-top:16px}.lpb .lnote b{color:var(--ink)}
.lpb .dec .selh{font-size:18px;margin:0 0 3px}
.lpb .acc{background:#fff;border:1px solid var(--edge);border-radius:12px;margin:0 0 11px;overflow:hidden}
.lpb .acc .h{display:flex;justify-content:space-between;gap:14px;padding:16px 18px;font-weight:700;font-size:14.5px;line-height:1.35;cursor:pointer}
.lpb .acc .h .pm{color:var(--cta);font-size:22px;font-weight:400;flex:none;line-height:.8}
.lpb .acc .b{padding:0 18px 18px;color:var(--muted);font-size:14px;line-height:1.6;display:none}
.lpb .acc.open .b{display:block}
.lpb .acc .b .lab{color:var(--ink);font-weight:700;display:block;margin:11px 0 2px}
@media(max-width:860px){.lpb .dec .grid{grid-template-columns:1fr}.lpb .dec .side{position:static}}
</style>
@endpush

<section class="sec alt dec" id="refusal"><div class="wrap">
  <div class="top"><p class="eyebrow">Refusal recovery</p><h2 class="h2">A refusal is not the end. Your next move decides everything.</h2><p class="micro">A rushed reapplication with the same paperwork gets the same result. A new application to a different country carries the same flag. We take refused cases apart, find what actually went wrong, then rebuild.</p></div>
  <div class="grid">
    <div class="side">
      <div class="letter">
        <div class="refstamp">Refused</div>
        <div class="lh"><b>CONSULATE — VISA SECTION</b><span>Annex VI</span></div>
        <div class="rl l"></div><div class="rl m"></div><div class="rl s"></div>
        <div class="hl">Ground 2: "The justification for the purpose and conditions of the intended stay was not reliable."</div>
        <div class="rl m"></div><div class="rl l"></div><div class="rl s"></div>
        <div class="lfoot">Decisions may be appealed. This mock is illustrative.</div>
      </div>
      <a class="btn" href="{{ $wa }}?text=Hi%2C%20my%20visa%20was%20refused.%20Can%20you%20review%20my%20letter%3F">Send your letter for a review</a>
      <div class="lnote"><b>Honest note:</b> Not every refusal is recoverable. If we don't believe we can materially improve your chances, we'll tell you. We'd rather lose a fee than damage your record.</div>
    </div>
    <div>
      <h3 class="selh">What does your refusal letter actually mean?</h3>
      <p class="micro" style="margin:0 0 16px">Find the reason on your letter. Pick the closest match.</p>
      <div class="acc open"><div class="h">"The justification for the purpose and conditions of the intended stay was not reliable." <span class="pm">–</span></div><div class="b"><span class="lab">What it means:</span>The officer didn't believe the reason for the trip — usually when documents contradict each other (a business conference with a tourist booking and no invitation).<span class="lab">What we'd do:</span>Obtain confirmed event or host documentation, align every booking to the stated purpose, draft a cover letter with the supporting correspondence, then resubmit.</div></div>
      <div class="acc"><div class="h">"Your intention to leave the territory before the visa expires could not be ascertained." <span class="pm">+</span></div><div class="b"><span class="lab">What it means:</span>The most common Schengen refusal — the officer wasn't convinced you'd return home.<span class="lab">What we'd do:</span>Build a documented picture of what pulls you back — work, property, dependents, a return booking that matches your leave — and present your travel history so a clean record counts for you.</div></div>
      <div class="acc"><div class="h">"Reasonable doubt exists as to your intention to leave" plus a second ground. <span class="pm">+</span></div><div class="b"><span class="lab">What it means:</span>Two weaknesses flagged at once. Fixing one and reapplying usually earns the same letter back.<span class="lab">What we'd do:</span>Address every listed ground in one rebuilt application, not just the easiest.</div></div>
      <div class="acc"><div class="h">My refusal reason isn't listed here. <span class="pm">+</span></div><div class="b">Send us a photo of your letter on WhatsApp and we'll read it back in plain English — what it means, whether it's recoverable, what we'd change. That read costs nothing.<br><a href="{{ $wa }}?text=Hi%2C%20my%20refusal%20reason%20isn%27t%20on%20your%20list.%20I%27ll%20send%20a%20photo%20of%20my%20letter." style="font-weight:700;display:inline-block;margin-top:10px">Send your letter →</a></div></div>
    </div>
  </div>
</div></section>

<script>
document.querySelectorAll('#refusal .acc .h').forEach(function(h){h.addEventListener('click',function(){
  var a=h.parentElement,open=a.classList.contains('open');a.classList.toggle('open');
  var pm=h.querySelector('.pm');if(pm)pm.textContent=open?'+':'–';
});});
</script>
