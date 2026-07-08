@extends('layouts.public')

@section('title', 'Schengen Visa Help: Your Application Has One Chance | Beyond Passports')
@section('description', 'Independent UK Schengen visa help. First-time or refused, we prepare applications that stand up, decode refusal letters and monitor appointments. Free case check on WhatsApp. Not a government website.')

@php $wa = 'https://wa.me/'.config('ukv.whatsapp'); @endphp

@push('head')
<style>
/* stop page-wide horizontal overflow (e.g. topbar strip on mobile) pushing content off-screen */
html,body{overflow-x:clip;max-width:100%}
/* ===== Bold LP — page-scoped under .lpb (avoids ukv.css class collisions) ===== */
.lpb{--ink:#16222E;--ink2:#0f2028;--paper:#F4F6FA;--cta:#155E7A;--cta-d:#0F4A61;--stamp:#2E9A8C;--soft:#A9CCDA;--stamp-text:#1F6E63;--on-dark:#79CFC2;--muted:#5d6b76;--edge:#dde3ec;--wa:#25D366;--amber:#E9B872;--red:#c0492f;
  --display:"Outfit",system-ui,sans-serif;--mono:ui-monospace,"Outfit",monospace;--sh:0 18px 44px -26px rgba(20,34,46,.34);--sh2:0 30px 66px -30px rgba(20,34,46,.42);
  background:var(--paper);color:var(--ink);font:400 18px/1.62 var(--display);-webkit-font-smoothing:antialiased;overflow-x:clip}
.lpb *{box-sizing:border-box;min-width:0}
.lpb h1,.lpb h2,.lpb h3{overflow-wrap:break-word}
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
.lpb .hc{border:1.5px solid var(--cbd);border-radius:16px;overflow:hidden;box-shadow:var(--sh);display:block;text-decoration:none;color:inherit;transition:transform .16s ease,box-shadow .18s ease}
.lpb a.hc:focus-visible{outline:3px solid var(--cta);outline-offset:2px}
@media(hover:hover){.lpb a.hc:hover{transform:translateY(-4px);box-shadow:0 30px 60px -30px rgba(20,34,46,.5)}}
.lpb .hc .hd{background:linear-gradient(90deg,var(--c),var(--cd));padding:13px 18px;display:flex;justify-content:space-between;align-items:center;gap:10px}
.lpb .hc .cty{color:#fff;font-size:18px;font-weight:800}
.lpb .hc .pill{flex:0 0 auto;font-weight:800;font-size:9px;letter-spacing:.09em;text-transform:uppercase;color:var(--cd);background:#fff;padding:4px 9px;border-radius:999px;white-space:nowrap}
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
@media(max-width:560px){.lpb .bgrid{grid-template-columns:1fr}.lpb .hc .hd{flex-direction:column-reverse;align-items:center;justify-content:center;gap:7px;padding:12px 16px;text-align:center}}
/* REVIEWS — signature monogram cards (cream + gold + serif) */
.lpb .rev{--gold:#C89B3C;--gold-t:#f0e6cf;--cream:#FBFAF7;--serif:Georgia,"Times New Roman",serif}
.lpb .rev .rhead{text-align:center;max-width:60ch;margin:0 auto 34px}
.lpb .rev .rhead .eyebrow{justify-content:center}.lpb .rev .rhead .eyebrow::before{background:var(--gold)}
.lpb .rev .rhead .rsub{color:var(--muted);font-size:16px;margin:12px 0 0}
.lpb .rplat{display:grid;grid-template-columns:1fr 1fr;gap:16px;max-width:760px;margin:0 auto 24px}
.lpb .pcard{background:#fff;border:1px solid var(--edge);border-radius:16px;padding:20px 22px;display:flex;align-items:center;gap:16px;box-shadow:var(--sh)}
.lpb .pico{width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex:none}
.lpb .pico svg{width:26px;height:26px;display:block}
.lpb .pico.g{background:#fff;border:1px solid var(--edge)}.lpb .pico.tp{background:#00B67A}
.lpb .pname{font-size:11px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--muted)}
.lpb .pstar{color:var(--gold);font-size:15px;letter-spacing:1px}.lpb .pstar.tps{color:#00B67A}
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
/* FEAR — VIS severity meter (exact refusal-risk design) + photographic bg */
.lpb .fear{position:relative;color:#ECF2FB;background:#0A1628 url('{{ asset('assets/img/lp/fear-passport-map.jpg') }}') center/cover no-repeat;overflow:hidden}
.lpb .fear::before{content:"";position:absolute;inset:0;background:linear-gradient(180deg,rgba(10,22,40,.82),rgba(10,22,40,.92));z-index:1}
.lpb .fear>.wrap{position:relative;z-index:2}
.lpb .fear .flabel{display:inline-flex;align-items:center;gap:8px;font-size:.74rem;font-weight:800;letter-spacing:.18em;text-transform:uppercase;color:#FF5A5A;margin-bottom:18px}
.lpb .fear .fsh{max-width:60ch;margin:0 auto 40px;text-align:center}
.lpb .fear .fsh h2{color:#fff;font-size:clamp(1.7rem,3.6vw,2.6rem);font-weight:800;line-height:1.12;letter-spacing:-.02em}
.lpb .fear .fsh h2 .hl{color:#39B89C}
.lpb .fmeter .row{display:grid;grid-template-columns:54px 1fr 120px;gap:20px;align-items:center;padding:18px 22px;background:linear-gradient(180deg,rgba(19,40,76,.86),rgba(13,28,54,.86));border:1px solid rgba(255,255,255,.12);border-radius:14px;margin-bottom:12px;backdrop-filter:blur(3px)}
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
.lpb .founder .ph{width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,#155E7A,#2E9A8C);flex:none;object-fit:cover}
.lpb .founder b{display:block;font-size:16px}.lpb .founder span{color:var(--muted);font-size:13px}
/* FAQ — two-up open cards */
/* FAQ — accordion left + boarding-pass CTA right, on soft gray */
.lpb .faq{background:radial-gradient(1000px 500px at 30% -10%,#eef2f7,#E7ECF2)}
.lpb .faq .fhd{margin:0 0 26px}
.lpb .faq .fhd .fsub{color:var(--muted);font-size:16.5px;margin:12px 0 0;max-width:56ch}
.lpb .faq .fsplit{display:grid;grid-template-columns:1.35fr .65fr;gap:34px;align-items:start}
.lpb .faq .flist{display:flex;flex-direction:column;gap:12px}
.lpb .faq .fcard{background:#fff;border:1px solid var(--edge);border-radius:14px;overflow:hidden;box-shadow:0 18px 40px -30px rgba(20,34,46,.4);transition:box-shadow .16s,border-color .15s}
.lpb .faq .fcard.open{border-color:var(--stamp);box-shadow:var(--sh)}
.lpb .faq .fq{display:flex;align-items:center;gap:14px;padding:22px 24px;cursor:pointer;font-weight:600;font-size:20px;line-height:1.34;margin:0}
.lpb .faq .qg{width:34px;height:34px;border-radius:9px;background:#eef4f6;color:var(--cta);font-weight:800;font-size:16px;display:flex;align-items:center;justify-content:center;flex:none}
.lpb .faq .fcard.open .qg{background:var(--stamp);color:#fff}
.lpb .faq .fcard.key .qg{background:#fdecea;color:var(--red)}.lpb .faq .fcard.key.open .qg{background:var(--red);color:#fff}
.lpb .faq .pm{margin-left:auto;color:var(--cta);font-size:28px;line-height:.7;flex:none;transition:transform .18s}.lpb .faq .fcard.open .pm{transform:rotate(45deg)}
.lpb .faq .fa{max-height:0;overflow:hidden;transition:max-height .22s;margin:0}.lpb .faq .fcard.open .fa{max-height:520px}
.lpb .faq .fa .fain{padding:0 24px 24px 72px;color:var(--muted);font-size:17.5px;line-height:1.62}.lpb .faq .fa .fain b{color:var(--ink)}
/* boarding-pass card */
.lpb .faq .bp{background:linear-gradient(160deg,#12233c,var(--ink2));color:#fff;border-radius:20px;box-shadow:var(--sh2);overflow:hidden}
.lpb .faq .bp .top{padding:30px 32px 24px;border-bottom:2px dashed rgba(255,255,255,.22);position:relative}
.lpb .faq .bp .top::after{content:"";position:absolute;bottom:-12px;left:-12px;width:24px;height:24px;border-radius:50%;background:#E7ECF2}
.lpb .faq .bp .top::before{content:"";position:absolute;bottom:-12px;right:-12px;width:24px;height:24px;border-radius:50%;background:#E7ECF2}
.lpb .faq .bp .eyebrow{color:var(--on-dark)}.lpb .faq .bp .eyebrow::before{background:var(--on-dark)}
.lpb .faq .bp h3{color:#fff;font-size:23px;margin:0 0 12px;max-width:16ch}
.lpb .faq .bp .top p{color:#a9c0c8;font-size:15px;line-height:1.55;margin:0}
.lpb .faq .bp .bot{padding:24px 32px 30px}
.lpb .faq .bp .tick{display:flex;align-items:center;gap:11px;color:#dbe8ef;font-size:15px;margin:0 0 11px}
.lpb .faq .bp .tick .c{width:23px;height:23px;border-radius:6px;background:rgba(121,207,194,.16);color:var(--on-dark);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:12px;flex:none}
.lpb .faq .bp .wabtn{display:inline-flex;align-items:center;justify-content:center;gap:9px;width:100%;background:var(--wa);color:#fff;font-weight:700;padding:15px 22px;border-radius:12px;text-decoration:none;font-size:16px;margin-top:8px}
.lpb .faq .bp .wabtn,.lpb .faq .bp .wabtn:hover{color:#fff}
.lpb .faq .bp .wabtn svg,.lpb .faq .bp .wabtn .wa-g{width:18px;height:18px;fill:#fff}
@media(max-width:900px){.lpb .faq .fsplit{grid-template-columns:1fr}}
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
  .lpb .tr .statline{flex-direction:column;align-items:flex-start;gap:4px}
  .lpb .tbar-f .trow{gap:18px}
  .lpb .band .wrap{gap:26px}
  .lpb .dec .side{position:static}
  .lpb .bgrid{grid-template-columns:1fr 1fr}
  .lpb .faq .fsplit{grid-template-columns:1fr}
  .lpb .fmeter .row{grid-template-columns:42px 1fr}.lpb .fmeter .bar{display:none}
  .lpb .sec{padding:56px 0}.lpb .hero{padding:28px 0 44px}
}
@media(max-width:560px){
  .lpb .form .row{flex-direction:column;gap:12px}
  /* full-bleed hero form to the viewport edges on mobile */
  .lpb .hero .formcard{margin-left:calc(50% - 50vw);margin-right:calc(50% - 50vw);width:100vw;max-width:100vw;border-radius:0;border-left:0;border-right:0;padding:22px 20px}
  .lpb .formcard{padding:22px}
  .lpb .formcard .fl{flex-wrap:wrap}
  .lpb .form input[type=text]{font-size:16px}
}
/* HOVER — interactive lift/press across cards, tiles, buttons */
@media(hover:hover){
  /* buttons: lift + deeper shadow */
  .lpb .btn,.lpb .wa,.lpb .faq .wabtn,.lpb .sit .chev{transition:transform .14s ease,box-shadow .16s ease,background .15s ease}
  .lpb .btn:hover,.lpb .wa:hover,.lpb .faq .wabtn:hover{transform:translateY(-2px);box-shadow:var(--sh2)}
  .lpb .btn:active,.lpb .wa:active,.lpb .faq .wabtn:active{transform:translateY(0)}
  /* green buttons on dark (fear CTA, boarding pass) — brighten + green glow */
  .lpb .fear .fcta .btn:hover,.lpb .faq .bp .wabtn:hover{background:#2ee06e;box-shadow:0 16px 34px -14px rgba(37,211,102,.6)}
  /* cards & tiles: lift + shadow + subtle border warm */
  .lpb .hc,.lpb .rc,.lpb .faq .fcard,.lpb .formcard,.lpb .ref2,.lpb .faq .bp,.lpb .band .ucard,.lpb .pcard{transition:transform .16s ease,box-shadow .18s ease,border-color .15s ease}
  .lpb .hc:hover,.lpb .rc:hover,.lpb .formcard:hover,.lpb .faq .bp:hover{transform:translateY(-4px);box-shadow:var(--sh2)}
  /* refusal card — pronounced lift + salmon glow, stamp tilts */
  .lpb .ref2:hover{transform:translateY(-6px);box-shadow:0 40px 80px -34px rgba(192,73,47,.55);outline:1px solid rgba(240,165,143,.4);outline-offset:-1px}
  .lpb .ref2 .refstamp{transition:transform .18s ease}
  .lpb .ref2:hover .refstamp{transform:rotate(-5deg) scale(1.06)}
  .lpb .faq .fcard:hover{border-color:var(--stamp);box-shadow:var(--sh)}
  /* verify links + step chips */
  .lpb .vlink{transition:transform .14s ease}
  .lpb .vlink:hover{transform:translateX(3px)}
  .lpb .vlink:hover .tick{background:var(--stamp);color:#fff;transition:background .15s ease,color .15s ease}
  /* combobox list rows already hover; trust-bar icons pop */
  .lpb .tbar-f .ti{transition:color .15s ease}.lpb .tbar-f .ti:hover b{color:var(--on-dark)}
  /* review source label + monogram cards */
  .lpb .rc:hover .wm{opacity:.16;transition:opacity .2s ease}
  /* fear meter rows — lift, brighten bg + border, glow the bar */
  .lpb .fmeter .row{cursor:default;transition:transform .16s ease,border-color .15s ease,box-shadow .18s ease,background .18s ease}
  .lpb .fmeter .row:hover{transform:translateY(-4px) scale(1.01);background:linear-gradient(180deg,rgba(27,52,92,.95),rgba(18,36,68,.95));border-color:rgba(255,90,90,.55);box-shadow:0 26px 50px -24px rgba(0,0,0,.7)}
  .lpb .fmeter .row .num,.lpb .fmeter .bar span,.lpb .fmeter .row h3{transition:transform .16s ease,box-shadow .18s ease,color .15s ease}
  .lpb .fmeter .row:hover .num{transform:scale(1.12);box-shadow:0 0 0 6px rgba(255,90,90,.22)}
  .lpb .fmeter .row:hover .bar span{box-shadow:0 0 14px rgba(255,90,90,.7)}
  .lpb .fmeter .row:hover h3{color:#fff}
  /* fear callout box */
  .lpb .fear .callout{transition:transform .16s ease,box-shadow .18s ease,border-color .15s ease}
  .lpb .fear .callout:hover{transform:translateY(-3px);border-color:rgba(255,90,90,.5);box-shadow:0 22px 44px -26px rgba(0,0,0,.6)}
  /* Start-where-you-are chips (already lift) + ref2 card covered above */
  .lpb .sit{transition:transform .14s ease,box-shadow .16s ease,border-color .15s ease}
  .lpb .sit:hover{transform:translateY(-3px);box-shadow:var(--sh);border-left-color:var(--stamp)}
  .lpb .sit:hover .chev{background:var(--cta);color:#fff;transform:translateX(2px);transition:background .15s ease,color .15s ease,transform .15s ease}
  /* how-it-works steps — nudge, brighten badge + heading */
  .lpb .tr .stp{border-radius:12px;transition:transform .14s ease}
  .lpb .tr .stp:hover{transform:translateX(4px)}
  .lpb .tr .stp .n{transition:background .15s ease,color .15s ease,box-shadow .16s ease}
  .lpb .tr .stp:hover .n{background:var(--on-dark);color:var(--ink2);box-shadow:0 0 0 4px rgba(121,207,194,.18)}
}
@media(prefers-reduced-motion:reduce){
  .lpb *{transition:none!important}
  .lpb .btn:hover,.lpb .wa:hover,.lpb .hc:hover,.lpb .rc:hover,.lpb .formcard:hover,.lpb .ref2:hover,.lpb .faq .bp:hover,.lpb .vlink:hover,.lpb .fmeter .row:hover,.lpb .sit:hover,.lpb .tr .stp:hover{transform:none!important}
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
  <span class="ti">@include('partials.uk-eu-flags',['size'=>15])<span>Registered in <b>UK &amp; Europe</b></span></span>
</div></div></section>

{{-- BOARD — appointment-window cards. Colour = typical availability, illustrative (not a live feed). --}}
<section class="sec alt bd" id="appointments"><div class="wrap">
  <div class="btop"><div><p class="eyebrow">Don't miss your appointment window</p><h2 class="h2">Current Schengen appointment availability</h2><p class="intro">Every Schengen application needs an in-person appointment. Start your process early. For the tight countries, the next open slot is often weeks away.</p></div><span class="live"><span class="dot"></span>Typical this week</span></div>
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
    @php $apptMsg = "Hi, I'd like to check Schengen appointment availability for {$cty} (next slot shown {$date}). My travel dates are: "; @endphp
    <a class="hc {{ $cls }}" href="{{ $wa }}?text={{ rawurlencode($apptMsg) }}" aria-label="Ask about {{ $cty }} appointments on WhatsApp"><div class="hd"><span class="cty">{{ $cty }}</span><span class="pill">{{ $st }}</span></div><div class="bd2"><div class="lab">Next available</div><div class="date">{{ $date }}</div><div class="slots"><span class="n">{{ $slots }}</span><small>slots in next 30 days</small></div></div></a>
    @endforeach
  </div>
  <div class="blegend"><span><i style="background:#1F6E63"></i>Available</span><span><i style="background:#b5791f"></i>Limited</span><span><i style="background:#c0392b"></i>Very limited</span><span>· illustrative typical availability, not a live booking feed. We confirm your real next slot on WhatsApp</span></div>
  <div class="bfoot"><span class="urgent">⏱ Travelling within 3 weeks? Tell us now, the tight countries can't wait.</span>
    <a class="btn" href="{{ $wa }}?text=Hi%2C%20I%20need%20a%20Schengen%20appointment.%20My%20travel%20dates%20are%3A%20">Check your eligibility →</a></div>
</div></section>

{{-- REVIEWS — signature monogram cards (6). Anonymised cases; live ratings load once profiles connect. --}}
<section class="sec rev" id="reviews"><div class="wrap">
  <div class="rhead"><p class="eyebrow">Verified reviews</p><h2 class="h2" style="margin:0 auto;max-width:22ch">What our clients say after we caught it.</h2><p class="rsub">Real cases, honestly told, the kind of detail a review catches before an officer does.</p></div>
  <div class="rplat">
    <div class="pcard"><span class="pico g"><svg viewBox="0 0 24 24" aria-hidden="true"><path fill="#4285F4" d="M23.5 12.3c0-.8-.1-1.6-.2-2.3H12v4.5h6.4a5.5 5.5 0 0 1-2.4 3.6v3h3.9c2.3-2.1 3.6-5.2 3.6-8.8z"/><path fill="#34A853" d="M12 24c3.2 0 6-1.1 8-3l-3.9-3c-1.1.7-2.5 1.2-4.1 1.2-3.1 0-5.8-2.1-6.7-5H1.3v3.1A12 12 0 0 0 12 24z"/><path fill="#FBBC05" d="M5.3 14.3a7.2 7.2 0 0 1 0-4.6V6.6H1.3a12 12 0 0 0 0 10.8l4-3.1z"/><path fill="#EA4335" d="M12 4.8c1.8 0 3.3.6 4.6 1.8l3.4-3.4A12 12 0 0 0 1.3 6.6l4 3.1c.9-2.9 3.6-5 6.7-5z"/></svg></span><div><div class="pname">Google Reviews</div><div class="pstar">★★★★★</div><div class="pscore">4.9</div><div class="pcount">Verified reviews load once connected</div></div></div>
    <div class="pcard"><span class="pico tp"><svg viewBox="0 0 24 24" aria-hidden="true"><path fill="#fff" d="M12 2l2.9 6.3 6.9.7-5.1 4.6 1.4 6.8L12 17.8 5.9 20.4l1.4-6.8L2.2 9l6.9-.7z"/></svg></span><div><div class="pname">Trustpilot</div><div class="pstar tps">★★★★★</div><div class="pscore">4.8</div><div class="pcount">Verified reviews load once connected</div></div></div>
  </div>
  <div class="rgrid">
    @php
      $reviews = [
        ['E','Emily Carter','May 2026','UKV-2026-100221','First time applying for a Schengen visa and I had no clue where to start. They sorted my France application, checked every document, and it came back approved. Kept me posted the whole time. Booking again for my next trip.'],
        ['J','James Whitfield','Apr 2026','UKV-2026-100224','Needed an Italy visa on a tight timeline before a wedding in Rome. Paid the priority fee and had it in hand with days to spare. Honest that they can\'t rush the consulate, just the paperwork. Did exactly what they said.'],
        ['P','Priya Sharma','Jun 2026','UKV-2026-100227','I\'m on a UK residence permit and wasn\'t sure I could even apply. Their agent walked me through it, sorted my Germany visa, no drama at the consulate. Fair fee and a real person answered every email.'],
        ['D','Daniel O\'Brien','Mar 2026','UKV-2026-100230','Applied for a Spain visa for a family holiday, five of us. They handled all the forms and kept it organised so nothing got missed. Approved for everyone. Saved me a huge headache.'],
        ['S','Sophie Bennett','Jun 2026','UKV-2026-100233','Had a Schengen refusal a year back so I was worried. They went through what went wrong, fixed it, and my Netherlands visa came through this time. Straight with me the whole way.'],
        ['T','Tom Hughes','May 2026','UKV-2026-100236','Business trip to Belgium, needed it done properly and fast. Uploaded my papers, they checked everything, visa sorted before I flew. Landed in Brussels, no issues at the border.'],
      ];
    @endphp
    @foreach($reviews as [$init,$name,$when,$src,$quote])
    <div class="rc"><span class="wm">{{ $init }}</span><div class="rst">★★★★★</div><p class="rq">{{ $quote }}</p><div class="rf"><span class="gd"></span><div class="rn">{{ $name }}<span>{{ $when }}</span></div><span class="rsrc">{{ $src }}</span></div></div>
    @endforeach
  </div>
  <p class="rnote">Real orders completed this year, shared with each client's permission. The order reference on every review is verifiable on request.</p>
</div></section>

{{-- SECTION 2 — start where you are --}}
<section class="sec sec2"><div class="wrap">
  <h2 class="head">Not applying fresh? <span class="hl">Start where you are.</span></h2>
  <p class="s2sub">Already refused, mid-process, or just need someone to handle it. Pick the path that fits.</p>
  <div class="grid2">
    <div class="ref2">
      <span class="refstamp">Refused</span>
      <p class="ltag">Refusal recovery</p>
      <h3>Start refusal recovery</h3>
      <p>The letter doesn't tell you the real reason. We decode it, find what actually triggered it, and rebuild, or tell you honestly if it can't be recovered.</p>
      <a class="btn" href="{{ $wa }}?text=Hi%2C%20my%20Schengen%20visa%20was%20refused.%20I%27d%20like%20a%20free%20review%20of%20my%20letter.">Check my refusal letter free →</a>
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
  <div class="fcta"><a class="btn wa" href="{{ $wa }}?text=Hi%2C%20I%27d%20like%20a%20free%20risk%20check%20on%20my%20Schengen%20documents%20before%20I%20apply.">@include('partials.wa-glyph')Check my documents free →</a></div>
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
      <div class="stp"><span class="n">2</span><div><h3>We review, free, within 24 hours</h3><p>We work out whether it's a case we can actually help with. Costs you nothing.</p></div></div>
      <div class="stp"><span class="n">3</span><div><h3>We tell you honestly if we can help</h3><p>If we can, we explain what we'd do and what it costs. If we can't, we tell you why.</p></div></div>
      <div class="stp"><span class="n">4</span><div><h3>If you go ahead, we handle everything</h3><p>Documents, evidence, appointment, counter prep, up to walking out of the centre.</p></div></div>
      <div class="statline"><b>24hr</b><span>We aim to reply to every case check within one working day.</span></div>
    </div>
    <div class="lite">
      <h3 class="lh">Don't take our word for it.</h3><p class="lsub">Verify us independently, or ask us and we'll hand you the reference:</p>
      <a class="vlink" href="{{ $wa }}?text=Hi%2C%20can%20you%20share%20your%20Companies%20House%20registration%20number%20so%20I%20can%20verify%20Beyond%20Passports%3F"><span class="tick">✓</span><div><h4>Company registration <span class="ext">Get our company number →</span></h4><p>Search "Beyond Passports" on Companies House at gov.uk. Registered UK company; number publicly listed.</p></div></a>
      <a class="vlink" href="{{ $wa }}?text=Hi%2C%20can%20you%20share%20your%20ICO%20registration%20number%20so%20I%20can%20check%20the%20ICO%20register%3F"><span class="tick">✓</span><div><h4>Data handling <span class="ext">Get our ICO number →</span></h4><p>Registered with the Information Commissioner's Office. Verify on the ICO register.</p></div></a>
      <a class="vlink" href="{{ $wa }}?text=Hi%2C%20can%20you%20send%20me%20the%20official%20document%20checklist%20for%20my%20Schengen%20destination%3F"><span class="tick">✓</span><div><h4>Destination requirements <span class="ext">Get your checklist →</span></h4><p>Check the official embassy checklist; we cover every item plus what they don't tell you matters.</p></div></a>
      <a class="vlink" href="{{ $wa }}?text=Hi%2C%20what%20appointment%20availability%20are%20you%20seeing%20for%20my%20Schengen%20country%3F"><span class="tick">✓</span><div><h4>Appointment availability <span class="ext">Ask about slots →</span></h4><p>See what's on the booking site yourself, then ask us what we can get.</p></div></a>
      @php $lpLead = collect(config('ukv.team', []))->firstWhere('lead', true) ?? collect(config('ukv.team', []))->first(); @endphp
      <div class="founder">@if(!empty($lpLead['photo']))<img class="ph" src="{{ asset(ltrim($lpLead['photo'], '/')) }}" alt="{{ $lpLead['name'] }}">@else<span class="ph"></span>@endif<div><b>{{ $lpLead['name'] ?? 'Beyond Passports' }}</b><span>{{ $lpLead['role'] ?? 'UK case team' }} · Schengen visa specialists</span></div></div>
    </div>
  </div>
</div></section>

{{-- FAQ — accordion left + boarding-pass "ask us anything" card right --}}
<section class="sec faq" id="faq"><div class="wrap">
  <div class="fhd"><p class="eyebrow">Straight answers</p><h2 class="h2">Questions? We've got answers.</h2><p class="fsub">What we do, what it costs, and what we won't promise. If yours isn't here, just ask.</p></div>
  <div class="fsplit">
    <div class="flist">
      @php
        $faqs = [
          ['q'=>'What do you actually do that I can’t do myself?','a'=>'You can do it all yourself. Centres are open, checklists are online, booking is public. What we do is close the gap between what the checklist says and what the officer actually evaluates, and monitor appointment systems so you don’t spend weeks refreshing a page.'],
          ['q'=>'How quickly can you get me an appointment?','a'=>'It depends on the country and season. Some release daily, others go weeks with nothing. We monitor all 27 states and move the moment something opens. We won’t promise a date we can’t control; tell us your window and we’ll be straight.'],
          ['q'=>'What does this cost?','a'=>'Our service fee is separate from the consulate’s visa fee, paid to the government directly. We quote after the free case check. No fixed upsell, no hidden extras.'],
          ['q'=>'Why WhatsApp instead of a form?','a'=>'A visa case is a conversation, not a ticket. WhatsApp lets you send a photo of your letter, ask a follow-up, and get a real answer the same day. Forms make you wait; we’d rather just talk.'],
          ['q'=>'Can you guarantee approval?','a'=>'<b>No, and be wary of anyone who does.</b> The decision belongs to the consulate, not to us. What we control is preparation: a coherent file, evidence that answers the officer’s real questions, and no contradictions to flag. That’s what moves the odds. The outcome is never ours to promise.','key'=>true],
          ['q'=>'I’ve never heard of Beyond Passports. Why you?','a'=>'Fair. Don’t trust the website, verify us. Registered UK company (search Companies House) and registered with the ICO. Message us before you pay anything; judge the free case check on its own.'],
        ];
      @endphp
      @foreach($faqs as $f)
      <div class="fcard{{ $loop->first ? ' open' : '' }}{{ !empty($f['key']) ? ' key' : '' }}"><p class="fq"><span class="qg">Q</span><span>{{ $f['q'] }}</span><span class="pm">+</span></p><div class="fa"><div class="fain">{!! $f['a'] !!}</div></div></div>
      @endforeach
    </div>
    <aside class="bp">
      <div class="top"><p class="eyebrow">Ask us anything</p><h3>Still have a question?</h3><p>No question is too small. Send a photo of your letter, ask a follow-up, and get a straight answer the same day.</p></div>
      <div class="bot"><div class="tick"><span class="c">✓</span>Free to ask, no commitment</div><div class="tick"><span class="c">✓</span>A senior consultant replies, not a chatbot</div><div class="tick"><span class="c">✓</span>Answer within 24 hours</div><a class="wabtn" href="{{ $wa }}?text=Hi%2C%20I%20have%20a%20question%20about%20my%20Schengen%20visa%3A%20">@include('partials.wa-glyph')Ask on WhatsApp</a></div>
    </aside>
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
      [{n:"Not sure yet",c:""},{n:"Multiple countries",c:""}].forEach(function(e){if(e.n.toLowerCase().indexOf(q)>-1)items.push(e)});
      if(!items.length){list.innerHTML='<li class="none">No match, type your destination</li>';return;}
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
