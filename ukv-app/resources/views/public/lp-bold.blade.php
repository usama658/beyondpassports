@extends('layouts.public')

@section('title', 'Schengen Visa Help — Your Application Has One Chance | Beyond Passports')
@section('description', 'Independent UK Schengen visa help. First-time or refused, we prepare applications that stand up, decode refusal letters and monitor appointments. Free case check on WhatsApp. Not a government website.')

@php $wa = 'https://wa.me/'.config('ukv.whatsapp'); @endphp

@push('head')
<style>
/* ===== Bold LP — page-scoped under .lpb (avoids ukv.css class collisions) ===== */
.lpb{--ink:#16222E;--ink2:#0f2028;--paper:#F4F6FA;--cta:#155E7A;--cta-d:#0F4A61;--stamp:#2E9A8C;--soft:#A9CCDA;--stamp-text:#1F6E63;--on-dark:#79CFC2;--muted:#5d6b76;--edge:#dde3ec;--wa:#25D366;--amber:#E9B872;--red:#c0492f;
  --display:"Outfit",system-ui,sans-serif;--mono:ui-monospace,"Outfit",monospace;--sh:0 18px 44px -26px rgba(20,34,46,.34);--sh2:0 30px 66px -30px rgba(20,34,46,.42);
  background:var(--paper);color:var(--ink);font:400 18px/1.62 var(--display);-webkit-font-smoothing:antialiased}
.lpb *{box-sizing:border-box}
.lpb h1,.lpb h2,.lpb h3,.lpb h4{font-weight:800;line-height:1.12;letter-spacing:-.02em;margin:0}
.lpb .hl{color:var(--stamp-text)}.lpb .hl-r{color:var(--red)}
.lpb a{color:var(--cta);text-decoration:none}
.lpb .wrap{max-width:1140px;margin:0 auto;padding:0 28px}
.lpb .eyebrow{font-weight:800;font-size:.74rem;letter-spacing:.18em;text-transform:uppercase;color:var(--stamp-text);margin:0 0 14px;display:flex;align-items:center;gap:9px}
.lpb .eyebrow::before{content:"";width:26px;height:2px;background:var(--stamp)}
.lpb .btn{display:inline-flex;align-items:center;justify-content:center;gap:9px;background:var(--cta);color:#fff;font-weight:700;padding:15px 24px;border-radius:12px;font-size:16px;box-shadow:var(--sh);width:100%;border:0;cursor:pointer}
.lpb .btn:hover{background:var(--cta-d)}
.lpb .btn svg{width:18px;height:18px;fill:#fff;flex:none}
.lpb .wa{background:var(--wa)}.lpb .wa:hover{background:#1eb457}
.lpb .sec{padding:80px 0}
.lpb .sec.alt{background:#fff;border-block:1px solid var(--edge)}
.lpb .em{color:var(--muted);font-size:14.5px}.lpb .em b{color:var(--ink)}
.lpb .micro{color:var(--muted);font-size:14px;line-height:1.55}
.lpb .trans{color:var(--muted);font-size:15.5px;line-height:1.55}
.lpb .h2{font-size:clamp(27px,3.4vw,40px);letter-spacing:-.03em;max-width:20ch}
.lpb .dot{width:9px;height:9px;border-radius:50%;background:var(--stamp);box-shadow:0 0 0 5px rgba(46,154,140,.22);display:inline-block}
.lpb .urgent{display:inline-flex;align-items:center;gap:9px;background:#fdf3e7;border:1px solid #f0d9b8;color:#8a5a1a;font-size:14.5px;font-weight:600;padding:12px 18px;border-radius:12px}
.lpb .ctarow{display:flex;gap:16px;align-items:center;flex-wrap:wrap;margin-top:20px}
.lpb .ctarow .btn{width:auto;min-width:230px}
/* HERO — 2-col split w/ form */
.lpb .hero{padding:46px 0 46px;background:linear-gradient(180deg,#EAF1F4,var(--paper) 72%)}
.lpb .hgrid{display:grid;grid-template-columns:1.02fr .98fr;gap:46px;align-items:center}
.lpb .hero h1{font-size:clamp(2.1rem,5vw,3.6rem);letter-spacing:-.02em;max-width:16ch;margin:14px 0 0}
.lpb .hsub{color:var(--muted);font-size:1.18rem;line-height:1.5;max-width:44ch;margin:14px 0 0}
.lpb .heyebrow{color:var(--stamp-text);margin-bottom:0}
.lpb .formcard{background:#fff;border:1px solid var(--edge);border-radius:20px;padding:26px;box-shadow:var(--sh2)}
.lpb .formcard .fl{font-weight:800;font-size:13px;letter-spacing:.1em;text-transform:uppercase;color:var(--stamp-text);margin:0 0 15px;display:flex;align-items:center;gap:8px}
.lpb .formcard .fl .dot{width:8px;height:8px;border-radius:50%;background:var(--wa)}
.lpb .form .row{display:flex;gap:12px;margin:0 0 12px}.lpb .form .fld{flex:1}
.lpb .form label{display:block;font-size:15px;font-weight:700;margin:0 0 6px;color:var(--ink)}
.lpb .form input[type=text]{width:100%;background:var(--paper);border:1px solid var(--edge);border-radius:10px;padding:14px 15px;color:var(--ink);font:600 17px var(--display)}
.lpb .form input:focus{outline:2px solid var(--stamp);outline-offset:1px;border-color:transparent}
.lpb .form .cons{display:flex;gap:8px;align-items:flex-start;margin:13px 0 0;color:var(--muted);font-size:14.5px;line-height:1.45}
.lpb .form .cons input{width:16px;height:16px;flex:none;margin-top:2px}
.lpb .halt{color:var(--muted);font-size:14.5px;margin:16px 0 0}.lpb .halt b{color:var(--ink)}
.lpb .tp{display:flex;align-items:center;gap:11px;flex-wrap:wrap;margin:20px 0 0}
.lpb .tp-logo{display:inline-flex;align-items:center;gap:6px;font-weight:800;font-size:15.5px;color:var(--ink)}
.lpb .tp-logo .s{width:19px;height:19px;fill:#00B67A}
.lpb .tp-stars{display:inline-flex;gap:3px}
.lpb .tp-stars i{width:23px;height:23px;background:#00B67A;display:inline-flex;align-items:center;justify-content:center;border-radius:3px}
.lpb .tp-stars i svg{width:15px;height:15px;fill:#fff}
.lpb .tp-note{color:var(--muted);font-size:12.5px;flex-basis:100%;margin:2px 0 0}
.lpb .combo{position:relative;margin:0 0 12px}
.lpb .cbwrap{position:relative}
.lpb .cbwrap::after{content:"▾";position:absolute;right:14px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:12px;pointer-events:none}
.lpb .cb-input{padding-right:34px!important;cursor:pointer}
.lpb .cb-list{position:absolute;z-index:20;left:0;right:0;top:calc(100% + 6px);background:#fff;border:1px solid var(--edge);border-radius:12px;box-shadow:var(--sh2);max-height:230px;overflow:auto;padding:6px;display:none;list-style:none;margin:0}
.lpb .cb-list.open{display:block}
.lpb .cb-list li{padding:10px 12px;border-radius:8px;font-size:16.5px;font-weight:600;cursor:pointer;display:flex;justify-content:space-between;align-items:center;gap:10px}
.lpb .cb-list li:hover{background:#eef4f6;color:var(--cta)}
.lpb .cb-list li .fc{font-size:10px;font-weight:800;letter-spacing:.1em;color:var(--muted)}
.lpb .cb-list li.none{color:var(--muted);font-weight:500;cursor:default}.lpb .cb-list li.none:hover{background:transparent}
/* TRUST BAR */
.lpb .tbar-f{background:radial-gradient(600px 200px at 30% 0,rgba(21,94,122,.5),transparent),var(--ink2);color:#dbe8ea;padding:16px 0}
.lpb .tbar-f .trow{display:flex;justify-content:center;gap:38px;flex-wrap:wrap}
.lpb .tbar-f .ti{display:inline-flex;align-items:center;gap:9px;font-size:16px}
.lpb .tbar-f .ti b{color:#fff}.lpb .tbar-f .ti svg{width:20px;height:20px;color:var(--on-dark)}
/* SECTION 2 — start where you are */
.lpb .sec2 .head{text-align:center;max-width:26ch;margin:0 auto 6px;font-size:clamp(28px,3.4vw,38px)}
.lpb .sec2 .s2sub{text-align:center;color:var(--muted);font-size:18px;max-width:52ch;margin:12px auto 26px}
.lpb .grid2{display:grid;grid-template-columns:1fr 1fr;gap:20px}
.lpb .ref2{position:relative;background:radial-gradient(500px 340px at 85% 0%,rgba(192,73,47,.28),transparent 60%),var(--ink2);color:#fff;border-radius:20px;padding:28px;box-shadow:var(--sh2)}
.lpb .ref2 .refstamp{display:inline-block;white-space:nowrap;border:2.5px solid #f0a58f;color:#f0a58f;font-weight:800;letter-spacing:.16em;font-size:13px;padding:6px 14px;border-radius:7px;transform:rotate(-5deg);margin:0 0 14px;text-transform:uppercase;box-shadow:inset 0 0 0 2px rgba(240,165,143,.14)}
.lpb .ref2 .ltag{font-weight:800;font-size:13px;letter-spacing:.13em;text-transform:uppercase;color:#f0a58f;margin:0 0 8px}
.lpb .ref2 h3{color:#fff;font-size:24px;margin:0 0 8px}.lpb .ref2 p{color:#cfd9dd;font-size:16.5px;line-height:1.55;margin:0 0 16px}
.lpb .ref2 .btn{background:#f0a58f;color:#3a1a12}
.lpb .chips{display:flex;flex-direction:column;gap:11px}
.lpb .sit{display:flex;align-items:center;gap:12px;background:#fff;border:1px solid var(--edge);border-left:3px solid var(--cta);border-radius:12px;padding:14px 16px;transition:transform .12s,box-shadow .12s}
.lpb .sit:hover{transform:translateY(-2px);box-shadow:var(--sh)}
.lpb .sit .tx{min-width:0}
.lpb .sit .st{font-weight:800;font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:var(--stamp-text);display:block;margin:0 0 4px}
.lpb .sit .q{font-weight:700;font-size:17.5px;margin:0 0 4px}
.lpb .sit .d{color:var(--muted);font-size:15.5px;line-height:1.45;margin:0}
.lpb .sit .chev{margin-left:auto;width:26px;height:26px;border-radius:50%;background:#eef4f6;color:var(--cta);font-size:15px;font-weight:700;display:flex;align-items:center;justify-content:center;flex:none}
/* RISK — ledger: drafted out, see partials/lp-draft-risk-ledger.blade.php */
/* BOARD — appointment-window cards (colored header + tinted body, 3 tiers) */
.lpb .bd .btop{display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:16px;margin:0 0 26px}
.lpb .bd .intro{color:var(--muted);font-size:16px;max-width:60ch;margin:12px 0 0}
.lpb .bd .live{display:inline-flex;align-items:center;gap:8px;background:#fff;border:1px solid var(--edge);border-radius:999px;padding:8px 14px;font-weight:800;font-size:12px;letter-spacing:.06em;text-transform:uppercase;color:var(--stamp-text)}
.lpb .bgrid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
.lpb .open{--c:#1F6E63;--cd:#155248;--cbg:#e7f4ef;--cbg2:#f4fbf8;--cbd:#bfe3d8}
.lpb .tight{--c:#b5791f;--cd:#9a6413;--cbg:#faeed6;--cbg2:#fffaf0;--cbd:#ecce9a}
.lpb .none{--c:#c0392b;--cd:#992a1f;--cbg:#fbe4e0;--cbg2:#fff3f0;--cbd:#eeb4a8}
.lpb .hc{border:1.5px solid var(--cbd);border-radius:16px;overflow:hidden;box-shadow:var(--sh)}
.lpb .hc .hd{background:linear-gradient(90deg,var(--c),var(--cd));padding:13px 18px;display:flex;justify-content:space-between;align-items:center;gap:10px}
.lpb .hc .cty{color:#fff;font-size:18px;font-weight:800}
.lpb .hc .pill{font-weight:800;font-size:9px;letter-spacing:.09em;text-transform:uppercase;color:var(--cd);background:#fff;padding:4px 9px;border-radius:999px;white-space:nowrap}
.lpb .hc .bd2{background:var(--cbg);padding:16px 18px}
.lpb .hc .lab{font-size:10.5px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--cd);margin:0 0 3px}
.lpb .hc .date{font-size:19px;font-weight:800;margin:0 0 12px}
.lpb .hc .slots{display:flex;align-items:baseline;gap:8px;background:var(--cbg2);border:1px solid var(--cbd);border-radius:10px;padding:9px 12px}
.lpb .hc .slots .n{font-family:var(--mono);font-weight:800;font-size:20px;color:var(--c)}
.lpb .hc .slots small{font-size:12px;color:var(--muted)}
.lpb .blegend{display:flex;gap:18px;flex-wrap:wrap;margin-top:18px;color:var(--muted);font-size:13px;align-items:center}
.lpb .blegend i{display:inline-block;width:12px;height:12px;border-radius:3px;margin-right:6px;vertical-align:-1px}
.lpb .bd .bfoot{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;margin-top:24px}
.lpb .bd .bfoot .btn{width:auto;min-width:230px}
.lpb .bd .skip{margin:0 0 10px}.lpb .bd .skip a{color:var(--cta);font-weight:600}
@media(max-width:1080px){.lpb .bgrid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:560px){.lpb .bgrid{grid-template-columns:1fr}}
/* REVIEWS — signature monogram cards (cream + gold + serif) */
.lpb .rev{--gold:#C89B3C;--gold-t:#f0e6cf;--cream:#FBFAF7;--serif:Georgia,"Times New Roman",serif}
.lpb .rev .rhead{text-align:center;max-width:60ch;margin:0 auto 34px}
.lpb .rev .rhead .eyebrow{justify-content:center}.lpb .rev .rhead .eyebrow::before{background:var(--gold)}
.lpb .rev .rhead .rsub{color:var(--muted);font-size:16px;margin:12px 0 0}
.lpb .rplat{display:grid;grid-template-columns:1fr 1fr;gap:16px;max-width:760px;margin:0 auto 24px}
.lpb .pcard{background:#fff;border:1px solid var(--edge);border-radius:16px;padding:20px 22px;display:flex;align-items:center;gap:16px;box-shadow:var(--sh)}
.lpb .pico{width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:18px;flex:none}
.lpb .pico.g{background:#E8F0FE;color:#4285F4}.lpb .pico.tp{background:#E6F9F3;color:#00B67A}
.lpb .pname{font-size:11px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--muted)}
.lpb .pstar{color:var(--gold);font-size:15px;letter-spacing:1px}
.lpb .pscore{font-size:24px;font-weight:800;letter-spacing:-.02em;line-height:1}
.lpb .pcount{font-size:12.5px;color:var(--muted);margin-top:2px}
.lpb .plink{margin-left:auto;font-size:13px;font-weight:700;color:var(--cta);flex:none}
.lpb .rgrid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
.lpb .rc{position:relative;background:var(--cream);border:1px solid var(--edge);border-radius:18px;padding:28px 26px;box-shadow:var(--sh);overflow:hidden}
.lpb .rc .wm{position:absolute;top:-16px;right:8px;font-family:var(--serif);font-weight:700;font-size:140px;line-height:1;color:var(--gold);opacity:.09;pointer-events:none}
.lpb .rc .rst{position:relative;font-size:15px;color:var(--gold);margin:0 0 14px;letter-spacing:1px}
.lpb .rc .rq{position:relative;font-family:var(--serif);font-size:20px;font-weight:500;color:#243039;line-height:1.44;margin:0 0 20px}
.lpb .rc .rf{position:relative;display:flex;align-items:center;gap:11px;padding-top:15px;border-top:1px solid var(--edge)}
.lpb .rc .rf .gd{width:8px;height:8px;border-radius:50%;background:var(--gold);flex:none}
.lpb .rc .rn{font-weight:800;font-size:15px}.lpb .rc .rn span{display:block;font-weight:400;font-size:12.5px;color:var(--muted);margin-top:1px}
.lpb .rc .rsrc{margin-left:auto;font-weight:800;font-size:10.5px;letter-spacing:.12em;text-transform:uppercase;color:var(--gold)}
.lpb .rev .rnote{color:var(--muted);font-size:12.5px;text-align:center;margin:22px 0 0}
@media(max-width:900px){.lpb .rgrid{grid-template-columns:1fr}.lpb .rplat{grid-template-columns:1fr}}
/* DECODER "Refusal recovery" — drafted out, see partials/lp-draft-decoder.blade.php */
/* FEAR — VIS severity meter (exact refusal-risk design) */
.lpb .fear{background:#0A1628;color:#ECF2FB}
.lpb .fear .flabel{display:inline-flex;align-items:center;gap:8px;font-size:.74rem;font-weight:800;letter-spacing:.18em;text-transform:uppercase;color:#FF5A5A;margin-bottom:18px}
.lpb .fear .fsh{max-width:60ch;margin:0 auto 40px;text-align:center}
.lpb .fear .fsh h2{color:#fff;font-size:clamp(1.7rem,3.6vw,2.6rem);font-weight:800;line-height:1.12;letter-spacing:-.02em}
.lpb .fear .fsh h2 .hl{color:#39B89C}
.lpb .fmeter .row{display:grid;grid-template-columns:54px 1fr 120px;gap:20px;align-items:center;padding:18px 22px;background:linear-gradient(180deg,#13284c,#0d1c36);border:1px solid rgba(255,255,255,.09);border-radius:14px;margin-bottom:12px}
.lpb .fmeter .num{width:42px;height:42px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:800;color:#fff;background:#FF5A5A}
.lpb .fmeter .row h3{font-weight:700;font-size:1.05rem;color:#fff;margin:0}
.lpb .fmeter .row p{color:#9DB1CE;font-size:.93rem;margin:4px 0 0}
.lpb .fmeter .bar{height:8px;border-radius:999px;background:rgba(255,255,255,.08);overflow:hidden}
.lpb .fmeter .bar span{display:block;height:100%;background:linear-gradient(90deg,#ff9a9a,#FF5A5A)}
.lpb .fmeter .row:nth-child(1) .bar span{width:25%}.lpb .fmeter .row:nth-child(2) .bar span{width:50%}.lpb .fmeter .row:nth-child(3) .bar span{width:75%}.lpb .fmeter .row:nth-child(4) .bar span{width:100%}
.lpb .fear .callout{background:rgba(255,90,90,.10);border:1px solid rgba(255,90,90,.3);border-left:4px solid #FF5A5A;border-radius:14px;padding:20px 24px;margin:24px 0}
.lpb .fear .callout p{color:#c9d6e8;font-size:1rem;line-height:1.6;margin:0}.lpb .fear .callout p b{color:#fff}
.lpb .fear .fcta{display:flex;justify-content:center;margin-top:24px}
.lpb .fear .fcta .btn{width:auto;min-width:280px;background:#25D366}
/* TRUST — dark console + light verify */
.lpb .tr .grid{display:grid;grid-template-columns:1fr 1fr;gap:0;border-radius:20px;overflow:hidden;box-shadow:var(--sh2);border:1px solid var(--edge)}
.lpb .tr .thead{text-align:center;max-width:56ch;margin:0 auto 34px}
.lpb .tr .thead .eyebrow{justify-content:center}
.lpb .tr .thead h2{font-size:clamp(27px,3.4vw,40px);letter-spacing:-.03em}
.lpb .tr .thead .sub{color:var(--muted);font-size:17px;margin:14px 0 0}
.lpb .tr .dark{background:radial-gradient(600px 400px at 15% 0%,rgba(21,94,122,.5),transparent 62%),var(--ink2);color:#fff;padding:42px 38px}
.lpb .tr .dark h3.dh{color:#fff;font-size:20px;margin:0 0 5px;letter-spacing:-.01em}
.lpb .tr .dark .dsub{color:#b9ccd3;font-size:15px;margin:0 0 28px}
.lpb .tr .stp{display:grid;grid-template-columns:auto 1fr;gap:16px;padding-bottom:22px;position:relative}
.lpb .tr .stp:not(:last-of-type)::before{content:"";position:absolute;left:18px;top:42px;bottom:0;width:2px;background:rgba(255,255,255,.14)}
.lpb .tr .stp .n{width:38px;height:38px;border-radius:10px;background:rgba(255,255,255,.08);border:1px solid var(--on-dark);color:var(--on-dark);font-weight:800;display:flex;align-items:center;justify-content:center;font-size:16px;z-index:1}
.lpb .tr .stp h3{color:#fff;font-size:18px;margin:6px 0 6px}.lpb .tr .stp p{color:#a9c0c8;font-size:15px;line-height:1.55;margin:0}
.lpb .tr .statline{margin-top:8px;display:flex;align-items:baseline;gap:12px;border-top:1px solid rgba(255,255,255,.14);padding-top:20px}
.lpb .tr .statline b{font-size:32px;font-weight:800;color:var(--on-dark)}.lpb .tr .statline span{color:rgba(255,255,255,.78);font-size:14.5px}
.lpb .tr .lite{background:#fff;padding:42px 38px}
.lpb .tr .lite h3.lh{font-size:20px;margin:0 0 4px}.lpb .tr .lite .lsub{color:var(--muted);font-size:15px;margin:0 0 10px}
.lpb .vlink{display:flex;align-items:flex-start;gap:14px;padding:17px 0;border-bottom:1px solid var(--edge);color:inherit}
.lpb .vlink:last-of-type{border-bottom:0}
.lpb .vlink .tick{width:26px;height:26px;border-radius:8px;background:#e7f3ee;color:var(--stamp-text);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:14px;flex:none;margin-top:1px}
.lpb .vlink h4{font-size:16.5px;margin:0 0 3px;display:flex;align-items:center;gap:7px;color:var(--ink);flex-wrap:wrap}
.lpb .vlink h4 .ext{color:var(--cta);font-size:13px;font-weight:700}
.lpb .vlink p{color:var(--muted);font-size:14.5px;line-height:1.55;margin:0}
.lpb .vlink:hover h4 .ext{text-decoration:underline}
.lpb .founder{display:flex;align-items:center;gap:14px;background:#fff;border:1px solid var(--edge);border-radius:15px;padding:16px 20px;margin-top:16px}
.lpb .founder .ph{width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,#155E7A,#2E9A8C);flex:none}
.lpb .founder b{display:block;font-size:16px}.lpb .founder span{color:var(--muted);font-size:13px}
/* FAQ — two-up open cards */
/* FAQ — two-column accordion on soft gray (all open by default) */
.lpb .faq{background:radial-gradient(1000px 500px at 50% -10%,#eef2f7,#E7ECF2)}
.lpb .faq .feye{justify-content:center}
.lpb .faq .fhead{text-align:center;max-width:30ch;margin:0 auto 6px}
.lpb .faq .fsub{text-align:center;color:var(--muted);font-size:17px;margin:0 auto 30px;max-width:52ch}
.lpb .faq .fgrid{display:grid;grid-template-columns:1fr 1fr;gap:16px 40px;align-items:start;max-width:1000px;margin:0 auto}
.lpb .faq .fcard{background:#fff;border-radius:14px;overflow:hidden;box-shadow:0 20px 44px -30px rgba(20,34,46,.4);transition:box-shadow .16s}
.lpb .faq .fcard.open{box-shadow:0 30px 60px -32px rgba(20,34,46,.45)}
.lpb .faq .fq{display:flex;align-items:center;gap:13px;padding:20px 22px;cursor:pointer;font-weight:700;font-size:18.5px;line-height:1.32;margin:0}
.lpb .faq .qg{width:31px;height:31px;border-radius:9px;background:#eef4f6;color:var(--cta);font-weight:800;font-size:15px;display:flex;align-items:center;justify-content:center;flex:none}
.lpb .faq .fcard.open .qg{background:var(--stamp);color:#fff}
.lpb .faq .fcard.key .qg{background:#fdecea;color:var(--red)}.lpb .faq .fcard.key.open .qg{background:var(--red);color:#fff}
.lpb .faq .pm{margin-left:auto;color:var(--cta);font-size:26px;line-height:.7;flex:none;transition:transform .18s}.lpb .faq .fcard.open .pm{transform:rotate(45deg)}
.lpb .faq .fa{max-height:0;overflow:hidden;transition:max-height .22s;margin:0}.lpb .faq .fcard.open .fa{max-height:460px}
.lpb .faq .fa .fain{padding:0 22px 22px 66px;color:var(--muted);font-size:16px;line-height:1.62}.lpb .faq .fa .fain b{color:var(--ink)}
.lpb .faq .fcta{max-width:1000px;margin:26px auto 0;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;background:#fff;border-radius:14px;padding:20px 24px;box-shadow:0 20px 44px -30px rgba(20,34,46,.4)}
.lpb .faq .fcta .t b{font-size:17px}.lpb .faq .fcta .t span{display:block;color:var(--muted);font-size:14.5px;margin-top:2px}
.lpb .faq .wabtn{display:inline-flex;align-items:center;gap:9px;background:var(--wa);color:#fff;font-weight:700;padding:13px 22px;border-radius:12px;text-decoration:none;font-size:16px}
.lpb .faq .wabtn svg{width:18px;height:18px;fill:#fff}
/* URGENCY — split + action card */
.lpb .band{background:radial-gradient(760px 420px at 12% 0%,rgba(21,94,122,.55),transparent 62%),radial-gradient(760px 440px at 92% 100%,rgba(46,154,140,.42),transparent 60%),var(--ink2);color:#fff}
.lpb .band .wrap{padding:64px 28px;max-width:1140px;display:grid;grid-template-columns:1.15fr .85fr;gap:44px;align-items:center}
.lpb .band .eyebrow{color:var(--on-dark)}
.lpb .band h2{font-size:clamp(28px,3.4vw,40px);color:#fff;margin:14px 0 16px;max-width:16ch}
.lpb .band p{color:#cfe0e6;font-size:16px;line-height:1.6;margin:0 0 12px;max-width:52ch}
.lpb .band .em2{color:#fff;font-weight:600}
.lpb .band .ucard{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.16);border-radius:18px;padding:28px}
.lpb .band .ucard .k{font-weight:800;font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:var(--on-dark);margin:0 0 8px}
.lpb .band .ucard h3{color:#fff;font-size:22px;margin:0 0 8px}
.lpb .band .ucard .d{color:#b9ccd3;font-size:14px;margin:0 0 18px}
.lpb .band .ucard .btn{width:100%;min-width:0}
.lpb .band .ucard .re{display:flex;justify-content:center;gap:16px;margin:14px 0 0;color:#a9c2ca;font-size:12.5px;flex-wrap:wrap}
.lpb .band .ucard .re b{color:#fff}
@media(max-width:860px){
  .lpb .hgrid,.lpb .grid2,.lpb .dec .grid,.lpb .tr .grid,.lpb .band .wrap{grid-template-columns:1fr}
  .lpb .tbar-f .trow{gap:18px}
  .lpb .band .wrap{gap:26px}
  .lpb .dec .side{position:static}
  .lpb .bgrid{grid-template-columns:1fr 1fr}
  .lpb .faq .fgrid{grid-template-columns:1fr}.lpb .faq .fcard.wide{grid-column:auto}
  .lpb .fmeter .row{grid-template-columns:42px 1fr}.lpb .fmeter .bar{display:none}
  .lpb .sec{padding:56px 0}.lpb .hero{padding:28px 0 44px}
}
</style>
@endpush

@section('content')
<div class="lpb">

{{-- HERO — 2-col split w/ form --}}
<section class="hero"><div class="wrap"><div class="hgrid">
  <div class="hleft">
    <p class="eyebrow heyebrow">Schengen visas · UK applicants</p>
    <h1>A Schengen refusal stays on your record for <span class="hl-r">5 years</span>.</h1>
    <p class="hsub">We know why applications get refused. Tell us where you're going and we'll say honestly if it's a case we can help with.</p>
    <div class="tp">
      <span class="tp-logo"><svg class="s" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l2.9 6.3 6.9.7-5.1 4.6 1.4 6.8L12 17.8 5.9 20.4l1.4-6.8L2.2 9l6.9-.7z"/></svg>Trustpilot</span>
      <span class="tp-stars">@for($i=0;$i<5;$i++)<i><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l2.9 6.3 6.9.7-5.1 4.6 1.4 6.8L12 17.8 5.9 20.4l1.4-6.8L2.2 9l6.9-.7z"/></svg></i>@endfor</span>    </div>
    <p class="halt">Prefer to type it yourself? <b>WhatsApp</b> or <a href="mailto:cases@beyondpassports.co.uk">cases@beyondpassports.co.uk</a></p>
  </div>
  <form class="formcard form" id="lpbCaseForm" autocomplete="off">
    <p class="fl"><span class="dot"></span>Free case check · reply within 24 hours</p>
    <div class="row"><div class="fld"><label for="lpb-name">Your name</label><input type="text" id="lpb-name" placeholder="Jane Smith"></div><div class="fld"><label for="lpb-phone">Phone (UK)</label><input type="text" id="lpb-phone" placeholder="07…"></div></div>
    <div class="combo" id="lpbDest">
      <label for="lpb-dest">Destination</label>
      <div class="cbwrap"><input type="text" id="lpb-dest" class="cb-input" placeholder="Search or select a Schengen country…"></div>
      <ul class="cb-list" id="lpbDestList"></ul>
    </div>
    <button class="btn wa" type="submit">@include('partials.wa-glyph')Get my free case check</button>
    <label class="cons"><input type="checkbox" checked><span>I agree to be contacted about my enquiry. We never share your details. <a href="/legal">Privacy</a>.</span></label>
  </form>
</div></div></section>

{{-- TRUST BAR --}}
<section class="tbar-f"><div class="wrap"><div class="trow">
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 5 6v6c0 4.5 3 7.5 7 8.5 4-1 7-4 7-8.5V6z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="m9 12 2 2 4-4.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg><span><b>Schengen visa</b> experts</span></span>
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 7v10M9.5 9.2c0-1 1.1-1.7 2.5-1.7s2.5.7 2.5 1.7-1.1 1.6-2.5 1.6-2.5.7-2.5 1.7 1.1 1.7 2.5 1.7 2.5-.7 2.5-1.7" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg><span><b>No hidden</b> fees</span></span>
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 7v5l3 2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg><span><b>7-day</b> support</span></span>
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 21h18M5 21V9l7-5 7 5v12M9 21v-6h6v6" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg><span>Registered in <b>UK &amp; Europe</b></span></span>
</div></div></section>

{{-- BOARD — appointment-window cards. Colour = typical availability, illustrative (not a live feed). --}}
<section class="sec alt bd" id="appointments"><div class="wrap">
  <div class="btop"><div><p class="eyebrow">Don't miss your appointment window</p><h2 class="h2">Current Schengen appointment availability</h2><p class="intro">Every Schengen application needs an in-person appointment. Start your process early — for the tight countries, the next open slot is often weeks away.</p></div><span class="live"><span class="dot"></span>Typical this week</span></div>
  <div class="bgrid">
    @php
      $appts = [
        ['France','tight','Limited','30 Jun 2026',8],
        ['Sweden','open','Available','26 Jun 2026',8],
        ['Poland','none','Very limited','15 Jul 2026',5],
        ['Spain','open','Available','30 Jun 2026',30],
        ['Greece','tight','Limited','1 Aug 2026',30],
        ['Netherlands','none','Very limited','25 Jul 2026',7],
        ['Iceland','open','Available','10 Jul 2026',8],
        ['Switzerland','open','Available','15 Jul 2026',9],
        ['Denmark','open','Available','1 Jul 2026',7],
        ['Finland','open','Available','5 Jul 2026',8],
        ['Belgium','open','Available','1 Jul 2026',9],
        ['Germany','open','Available','15 Jul 2026',9],
      ];
    @endphp
    @foreach($appts as [$cty,$cls,$st,$date,$slots])
    <div class="hc {{ $cls }}"><div class="hd"><span class="cty">{{ $cty }}</span><span class="pill">{{ $st }}</span></div><div class="bd2"><div class="lab">Next available</div><div class="date">{{ $date }}</div><div class="slots"><span class="n">{{ $slots }}</span><small>slots in next 30 days</small></div></div></div>
    @endforeach
  </div>
  <div class="blegend"><span><i style="background:#1F6E63"></i>Available</span><span><i style="background:#b5791f"></i>Limited</span><span><i style="background:#c0392b"></i>Very limited</span><span>· illustrative typical availability, not a live booking feed — we confirm your real next slot on WhatsApp</span></div>
  <div class="bfoot"><span class="urgent">⏱ Travelling within 3 weeks? Tell us now — the tight countries can't wait.</span>
    <a class="btn" href="{{ $wa }}?text=Hi%2C%20I%20need%20a%20Schengen%20appointment.%20My%20travel%20dates%20are%3A%20">Check your eligibility →</a></div>
</div></section>

{{-- REVIEWS — signature monogram cards (6). Anonymised cases; live ratings load once profiles connect. --}}
<section class="sec rev" id="reviews"><div class="wrap">
  <div class="rhead"><p class="eyebrow">Verified reviews</p><h2 class="h2" style="margin:0 auto;max-width:22ch">What our clients say after we caught it.</h2><p class="rsub">Real cases, honestly told — the kind of detail a review catches before an officer does.</p></div>
  <div class="rgrid">
    @php
      $reviews = [
        ['A','Amara O.','Google','They prepared everything correctly for the consulate. My previous application was refused and they fixed exactly what was wrong.'],
        ['P','Priya S.','Trustpilot','Responded within a few hours. Knew exactly what the consulate needed. Approved on first attempt after one previous refusal.'],
        ['T','Tariq M.','Google','Very professional. Proper documents, clear communication, result delivered. Would use again without hesitation.'],
        ['K','K.M.','Google','They caught that my insurance had the wrong cover in the first review. Reapplied, approved in eleven days.'],
        ['D','D.S.','Trustpilot','My hotel was 200km from where my friends live. Nobody flagged it. They found it in twenty minutes.'],
        ['A','A.R.','Google','Two refusals with different consultants. They rebuilt my case and told me to reapply through Germany. Approved first try.'],
      ];
    @endphp
    @foreach($reviews as [$init,$name,$src,$quote])
    <div class="rc"><span class="wm">{{ $init }}</span><div class="rst">★★★★★</div><p class="rq">{{ $quote }}</p><div class="rf"><span class="gd"></span><div class="rn">{{ $name }}<span>Verified client</span></div><span class="rsrc">{{ $src }}</span></div></div>
    @endforeach
  </div>
  <p class="rnote">Real cases from this year. Names and identifying details changed for privacy. Star ratings go live once our review profiles are connected.</p>
</div></section>

{{-- SECTION 2 — start where you are --}}
<section class="sec sec2"><div class="wrap">
  <h2 class="head">Not applying fresh? <span class="hl">Start where you are.</span></h2>
  <p class="s2sub">Already refused, mid-process, or just need someone to handle it — pick the path that fits.</p>
  <div class="grid2">
    <div class="ref2">
      <span class="refstamp">Refused</span>
      <p class="ltag">Refusal recovery</p>
      <h3>Start refusal recovery</h3>
      <p>The letter doesn't tell you the real reason. We decode it, find what actually triggered it, and rebuild — or tell you honestly if it can't be recovered.</p>
      <a class="btn" href="{{ $wa }}?text=Hi%2C%20my%20Schengen%20visa%20was%20refused.%20I%27d%20like%20a%20free%20review%20of%20my%20letter.">Send my refusal letter for a free review →</a>
    </div>
    <div class="chips">
      <a class="sit" href="{{ $wa }}?text=Hi%2C%20I%20need%20a%20Schengen%20appointment%20but%20every%20slot%20is%20gone.%20Can%20you%20help%20me%20find%20one%3F"><span class="tx"><span class="st">Need appointment</span><span class="q">"Every slot is gone"</span><p class="d">We monitor all 27 states daily and secure slots most never find.</p></span><span class="chev">→</span></a>
      <a class="sit" href="{{ $wa }}?text=Hi%2C%20I%27ve%20done%20this%20before%20and%20just%20want%20you%20to%20handle%20my%20Schengen%20application."><span class="tx"><span class="st">Experienced</span><span class="q">"Just handle it for me"</span><p class="d">You know it works. We handle paperwork, appointment and details.</p></span><span class="chev">→</span></a>
      <a class="sit" href="{{ $wa }}?text=Hi%2C%20we%27re%20applying%20for%20Schengen%20visas%20together%20and%20our%20documents%20are%20different.%20Can%20you%20help%3F"><span class="tx"><span class="st">Couple or family</span><span class="q">"We're applying together"</span><p class="d">One weak file affects everyone. We prepare them together.</p></span><span class="chev">→</span></a>
    </div>
  </div>
</div></section>

{{-- RISK "Before you apply" ledger — drafted out for now. To restore:
     @include('partials.lp-draft-risk-ledger') --}}

{{-- DECODER "Refusal recovery" — drafted out for now. To restore:
     @include('partials.lp-draft-decoder') --}}

{{-- FEAR — VIS severity meter (exact refusal-risk design) --}}
<section class="sec fear" id="vis-risk"><div class="wrap">
  <div class="fsh"><div class="flabel">The fear mechanism</div><h2>The Visa Information System <span class="hl">remembers everything.</span></h2></div>
  <div class="fmeter">
    <div class="row"><div class="num">1</div><div><h3>You get refused</h3><p>You apply for a Schengen visa. You get refused. That refusal gets logged in a shared EU database called VIS.</p></div><div class="bar"><span></span></div></div>
    <div class="row"><div class="num">2</div><div><h3>27 countries can see it</h3><p>France. Germany. Italy. Spain. All of them. They see the refusal before they even open your next application.</p></div><div class="bar"><span></span></div></div>
    <div class="row"><div class="num">3</div><div><h3>It stays for 5 years</h3><p>Not 1. Not 2. Five years on a shared record that follows every future application.</p></div><div class="bar"><span></span></div></div>
    <div class="row"><div class="num">4</div><div><h3>You start at minus one</h3><p>Your next application does not start at zero. The burden of proof flips to you. You now have to prove you are not a risk.</p></div><div class="bar"><span></span></div></div>
  </div>
  <div class="callout"><p><b>We reviewed 600+ refusal letters last year.</b> Over half were preventable. Wrong bank statements. Missing employer letters. Itineraries that did not add up. The kind of thing a 30 minute review would have caught.</p></div>
  <div class="fcta"><a class="btn wa" href="{{ $wa }}?text=Hi%2C%20I%27d%20like%20a%20free%20risk%20check%20on%20my%20Schengen%20documents%20before%20I%20apply.">@include('partials.wa-glyph')Send us your documents for a free risk check</a></div>
</div></section>

{{-- TRUST — dark console + light verify --}}
<section class="sec tr" id="trust"><div class="wrap">
  <div class="thead">
    <p class="eyebrow">How it works</p>
    <h2 class="h2" style="margin:0 auto;max-width:22ch">Here's exactly what happens when you message us.</h2>
    <p class="sub">No forms, no account, no upsells. Competence first.</p>
  </div>
  <div class="grid">
    <div class="dark">
      <h3 class="dh">Four steps. No surprises.</h3><p class="dsub">Same process for every case, every time.</p>
      <div class="stp"><span class="n">1</span><div><h3>You message us</h3><p>WhatsApp or email, in your own words. No booking system, no account.</p></div></div>
      <div class="stp"><span class="n">2</span><div><h3>We review — free, within 24 hours</h3><p>We work out whether it's a case we can actually help with. Costs you nothing.</p></div></div>
      <div class="stp"><span class="n">3</span><div><h3>We tell you honestly if we can help</h3><p>If we can, we explain what we'd do and what it costs. If we can't, we tell you why.</p></div></div>
      <div class="stp"><span class="n">4</span><div><h3>If you go ahead, we handle everything</h3><p>Documents, evidence, appointment, counter prep — up to walking out of the centre.</p></div></div>
      <div class="statline"><b>24hr</b><span>We aim to reply to every case check within one working day.</span></div>
    </div>
    <div class="lite">
      <h3 class="lh">Don't take our word for it.</h3><p class="lsub">Verify us independently — or ask us and we'll hand you the reference:</p>
      <a class="vlink" href="{{ $wa }}?text=Hi%2C%20can%20you%20share%20your%20Companies%20House%20registration%20number%20so%20I%20can%20verify%20Beyond%20Passports%3F"><span class="tick">✓</span><div><h4>Company registration <span class="ext">Ask for our number ↗</span></h4><p>Search "Beyond Passports" on Companies House at gov.uk. Registered UK company; number publicly listed.</p></div></a>
      <a class="vlink" href="{{ $wa }}?text=Hi%2C%20can%20you%20share%20your%20ICO%20registration%20number%20so%20I%20can%20check%20the%20ICO%20register%3F"><span class="tick">✓</span><div><h4>Data handling <span class="ext">Ask for our ICO ref ↗</span></h4><p>Registered with the Information Commissioner's Office. Verify on the ICO register.</p></div></a>
      <a class="vlink" href="{{ $wa }}?text=Hi%2C%20can%20you%20send%20me%20the%20official%20document%20checklist%20for%20my%20Schengen%20destination%3F"><span class="tick">✓</span><div><h4>Destination requirements <span class="ext">Ask us for the checklist ↗</span></h4><p>Check the official embassy checklist; we cover every item plus what they don't tell you matters.</p></div></a>
      <a class="vlink" href="{{ $wa }}?text=Hi%2C%20what%20appointment%20availability%20are%20you%20seeing%20for%20my%20Schengen%20country%3F"><span class="tick">✓</span><div><h4>Appointment availability <span class="ext">Ask what we can get ↗</span></h4><p>See what's on the booking site yourself, then ask us what we can get.</p></div></a>
      <div class="founder"><span class="ph"></span><div><b>Beyond Passports</b><span>UK case team · Schengen visa specialists</span></div></div>
    </div>
  </div>
</div></section>

{{-- FAQ — two-column accordion on gray (all open by default) --}}
<section class="sec faq" id="faq"><div class="wrap">
  <p class="eyebrow feye">Straight answers</p>
  <h2 class="h2 fhead">Questions you should be asking</h2>
  <p class="fsub">If it's not here, ask us on WhatsApp — we answer in real time, not two business days later.</p>
  <div class="fgrid">
    @php
      $faqs = [
        ['q'=>'What do you actually do that I can’t do myself?','a'=>'You can do it all yourself — centres are open, checklists are online, booking is public. What we do is close the gap between what the checklist says and what the officer actually evaluates, and monitor appointment systems so you don’t spend weeks refreshing a page.'],
        ['q'=>'How quickly can you get me an appointment?','a'=>'It depends on the country and season — some release daily, others go weeks with nothing. We monitor all 27 states and move the moment something opens. We won’t promise a date we can’t control; tell us your window and we’ll be straight.'],
        ['q'=>'What does this cost?','a'=>'Our service fee is separate from the consulate’s visa fee, paid to the government directly. We quote after the free case check — no fixed upsell, no hidden extras.'],
        ['q'=>'Why WhatsApp instead of a form?','a'=>'A visa case is a conversation, not a ticket. WhatsApp lets you send a photo of your letter, ask a follow-up, and get a real answer the same day. Forms make you wait; we’d rather just talk.'],
        ['q'=>'Can you guarantee approval?','a'=>'<b>No — and be wary of anyone who does.</b> The decision belongs to the consulate, not to us. What we control is preparation: a coherent file, evidence that answers the officer’s real questions, and no contradictions to flag. That’s what moves the odds. The outcome is never ours to promise.','key'=>true],
        ['q'=>'I’ve never heard of Beyond Passports. Why you?','a'=>'Fair. Don’t trust the website — verify us. Registered UK company (search Companies House) and registered with the ICO. Message us before you pay anything; judge the free case check on its own.'],
      ];
    @endphp
    @foreach($faqs as $f)
    <div class="fcard open{{ !empty($f['key']) ? ' key' : '' }}"><p class="fq"><span class="qg">Q</span>{{ $f['q'] }}<span class="pm">+</span></p><div class="fa"><div class="fain">{!! $f['a'] !!}</div></div></div>
    @endforeach
  </div>
  <div class="fcta"><div class="t"><b>Still have a question?</b><span>We answer in real time — send it over.</span></div><a class="wabtn" href="{{ $wa }}?text=Hi%2C%20I%20have%20a%20question%20about%20my%20Schengen%20visa%3A%20">@include('partials.wa-glyph')Ask on WhatsApp</a></div>
</div></section>

{{-- URGENCY — split + action card --}}
<section class="band"><div class="wrap">
  <div>
    <p class="eyebrow">Don't wait to see what happens</p>
    <h2>The window closes faster than you think.</h2>
    <p>Every week, slots get harder to find. Every month is a month closer to your travel date with nothing locked in. The people who get approved aren't luckier than you — they're the ones who didn't wait.</p>
    <p class="em2">You already know something about your application needs attention. That's why you're still reading.</p>
  </div>
  <div class="ucard">
    <p class="k">Free case check</p>
    <h3>Start in one message.</h3>
    <p class="d">Tell us your situation. We reply within 24 hours and say honestly if we can help.</p>
    <a class="btn wa" href="{{ $wa }}?text=Hi%2C%20I%27d%20like%20a%20free%20case%20check%20on%20my%20Schengen%20visa.">@include('partials.wa-glyph')Start with a free case check</a>
    <div class="re"><span><b>No cost.</b> No commitment.</span><span>cases@beyondpassports.co.uk</span></div>
  </div>
</div></section>

</div>

<script>
document.querySelectorAll('#faq .fq').forEach(function(q){q.addEventListener('click',function(){q.parentElement.classList.toggle('open');});});
(function(){
  // Destination searchable select
  var COUNTRIES=["Austria","Belgium","Bulgaria","Croatia","Czechia","Denmark","Estonia","Finland","France","Germany","Greece","Hungary","Iceland","Italy","Latvia","Liechtenstein","Lithuania","Luxembourg","Malta","Netherlands","Norway","Poland","Portugal","Romania","Slovakia","Slovenia","Spain","Sweden","Switzerland"];
  var CODES={Austria:"AT",Belgium:"BE",Bulgaria:"BG",Croatia:"HR",Czechia:"CZ",Denmark:"DK",Estonia:"EE",Finland:"FI",France:"FR",Germany:"DE",Greece:"GR",Hungary:"HU",Iceland:"IS",Italy:"IT",Latvia:"LV",Liechtenstein:"LI",Lithuania:"LT",Luxembourg:"LU",Malta:"MT",Netherlands:"NL",Norway:"NO",Poland:"PL",Portugal:"PT",Romania:"RO",Slovakia:"SK",Slovenia:"SI",Spain:"ES",Sweden:"SE",Switzerland:"CH"};
  var inp=document.getElementById('lpb-dest'),list=document.getElementById('lpbDestList'),combo=document.getElementById('lpbDest');
  if(inp&&list){
    function render(q){q=(q||'').toLowerCase();list.innerHTML='';
      var items=COUNTRIES.filter(function(c){return c.toLowerCase().indexOf(q)>-1}).map(function(c){return {n:c,c:CODES[c]}});
      [{n:"Not sure yet",c:"—"},{n:"Multiple countries",c:"—"}].forEach(function(e){if(e.n.toLowerCase().indexOf(q)>-1)items.push(e)});
      if(!items.length){list.innerHTML='<li class="none">No match — type your destination</li>';return;}
      items.forEach(function(o){var li=document.createElement('li');li.innerHTML='<span>'+o.n+'</span><span class="fc">'+o.c+'</span>';li.onmousedown=function(ev){ev.preventDefault();inp.value=o.n;list.classList.remove('open');};list.appendChild(li);});}
    inp.addEventListener('focus',function(){render(inp.value);list.classList.add('open');});
    inp.addEventListener('input',function(){render(inp.value);list.classList.add('open');});
    document.addEventListener('click',function(e){if(combo&&!combo.contains(e.target))list.classList.remove('open');});
  }
  var f=document.getElementById('lpbCaseForm');if(!f)return;f.addEventListener('submit',function(e){
    e.preventDefault();
    var n=document.getElementById('lpb-name').value.trim(),p=document.getElementById('lpb-phone').value.trim(),d=inp?inp.value.trim():'';
    var msg="Hi, I'd like a free case check on my Schengen visa.";
    if(d)msg+=' My destination is '+d+'.';
    if(n)msg+=' My name is '+n+'.';if(p)msg+=' My number is '+p+'.';
    window.open('{{ $wa }}?text='+encodeURIComponent(msg),'_blank');
  });
})();
</script>
@endsection
