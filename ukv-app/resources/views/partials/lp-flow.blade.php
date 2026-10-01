{{-- Shared LP modal flow (Flow B). Single source of truth. Spec: docs/superpowers/specs/2026-10-01-shared-lp-form-flow-design.md --}}
<style>
.mov{position:fixed;inset:0;background:rgba(9,17,32,.62);backdrop-filter:blur(3px);z-index:1000;display:none;align-items:flex-start;justify-content:center;padding:24px;overflow-y:auto;overscroll-behavior:contain}
.mov.open{display:flex}
.mmodal{background:#fff;border-radius:22px;width:100%;max-width:520px;margin:auto;box-shadow:0 50px 120px -30px rgba(0,0,0,.6);position:relative}
.mhead{display:flex;align-items:center;gap:8px;padding:20px 24px 0}
.mhsp{flex:1}
.mhint{font:600 12px var(--sans);color:var(--muted)}
@media(max-width:520px){.mhint{display:none}}
.mbody{padding:14px 24px 26px}
.mx{width:34px;height:34px;border-radius:9px;border:1px solid var(--border);background:#fff;cursor:pointer;font:700 16px var(--sans);color:var(--muted);display:flex;align-items:center;justify-content:center}
.mpill{display:inline-flex;align-items:center;gap:7px;font:800 11px var(--sans);letter-spacing:.1em;text-transform:uppercase;color:var(--gold);background:rgba(197,150,58,.12);padding:6px 12px;border-radius:999px}
.mdest{display:inline-flex;align-items:center;gap:6px;font:700 12px var(--sans);color:var(--navy);background:#eef4f6;border:1px solid var(--border);padding:5px 11px;border-radius:999px}
.mbars{display:flex;gap:6px;margin:14px 0 18px}.mbar{height:5px;flex:1;border-radius:3px;background:var(--border)}.mbar.on{background:var(--gold)}
h3.mq{font-family:var(--serif);font-weight:400;font-size:24px;line-height:1.18;color:var(--navy);margin-bottom:16px}
.mgrid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
@media(max-width:640px){.mov{padding:0}.mmodal{max-width:none;width:100%;border-radius:0;min-height:100vh;max-height:none;margin:0}}
.mtile{display:flex;align-items:flex-start;gap:12px;background:#fff;border:1.5px solid var(--border);border-radius:13px;padding:14px 13px;cursor:pointer;text-align:left;width:100%;transition:all .16s}
.mtile:hover{border-color:var(--gold);background:#fffdf8}
.mtile .mdot{width:18px;height:18px;border-radius:50%;border:2px solid #cbd5e1;flex:none;margin-top:2px;display:flex;align-items:center;justify-content:center}
.mtile .mdotf{width:9px;height:9px;border-radius:50%;background:var(--gold);transform:scale(0);transition:.16s}.mtile .mic{color:var(--navy-mid);display:flex}
.mtile:hover .mic,.mtile.sel .mic{color:var(--gold)}.mtile.sel{border-color:var(--gold);background:#fffdf8}.mtile.sel .mdot{border-color:var(--gold)}.mtile.sel .mdotf{transform:scale(1)}
.mstep{display:none}.mstep.on{display:block}
.mfld{width:100%;background:#fff;border:1.5px solid var(--border);border-radius:12px;padding:14px 16px;color:var(--navy);font:500 15px var(--sans);margin-bottom:12px;outline:none;box-shadow:0 8px 20px -8px rgba(11,21,40,.24);transition:border-color .16s,box-shadow .16s}
.mfld:focus{border-color:var(--gold);box-shadow:0 10px 24px -10px rgba(197,150,58,.4),0 0 0 3px rgba(197,150,58,.14)}
.mtrust{display:flex;align-items:center;justify-content:center;gap:6px;font:600 11.5px var(--sans);color:#16a34a;margin-top:13px}
.mtrust svg{width:14px;height:14px;flex:none}
.mrl{font:600 12.5px var(--sans);color:var(--muted);margin:18px 0 8px}
.merr{color:#c0392b;font:600 12px var(--sans);margin:-6px 0 12px;display:none}
.mwabtn{display:flex;align-items:center;justify-content:center;gap:9px;background:#C5963A;color:#3d2b08;height:52px;border:0;border-radius:13px;width:100%;font:800 15px var(--sans);cursor:pointer;text-decoration:none}
.mwabtn svg{fill:#3d2b08}
.mreassure{font:500 12px var(--sans);color:var(--muted);text-align:center;margin-top:14px}
.mback{display:none;align-items:center;justify-content:center;width:34px;height:34px;border-radius:10px;border:1.5px solid var(--border);background:#fff;color:var(--muted);cursor:pointer;flex:none;box-shadow:0 6px 14px -8px rgba(11,21,40,.3)}.mback svg{width:16px;height:16px}.mback:hover{color:var(--navy);border-color:var(--gold)}.mback.on{display:inline-flex}
.mtoprow{display:flex;align-items:center;gap:8px}
.mpc{position:relative;display:flex;border:1.5px solid var(--border);border-radius:12px;background:#fff;margin-bottom:12px;box-shadow:0 8px 20px -8px rgba(11,21,40,.24);transition:border-color .16s,box-shadow .16s}
.mpc:focus-within{border-color:var(--gold);box-shadow:0 10px 24px -10px rgba(197,150,58,.4),0 0 0 3px rgba(197,150,58,.14)}
.mpc:focus-within{border-color:var(--gold);box-shadow:0 0 0 3px rgba(197,150,58,.14)}
.mpcbtn{display:flex;align-items:center;gap:6px;padding:0 12px;background:#f4f7f9;border:0;border-right:1.5px solid var(--border);border-radius:12px 0 0 12px;cursor:pointer;font:700 14px var(--sans);color:var(--navy)}
.mpcin{flex:1;border:0!important;padding:14px 15px;font:500 15px var(--sans);border-radius:0 12px 12px 0;background:transparent;color:var(--navy);outline:none}
.msnt{display:none;text-align:center;padding:8px 0}.msnt.on{display:block}
.mseal{width:56px;height:56px;border-radius:50%;margin:0 auto 14px;background:rgba(197,150,58,.14);border:1.5px solid rgba(197,150,58,.5);display:flex;align-items:center;justify-content:center}
</style><style>
.mstep .d{display:block;font:500 12px var(--sans);color:var(--muted);margin-top:3px}
.msub{font:500 13px var(--sans);color:var(--muted);margin:-8px 0 16px}
.mbanner{display:none;font:600 12.5px var(--sans);color:var(--navy);background:rgba(197,150,58,.1);border:1px solid rgba(197,150,58,.28);border-radius:10px;padding:9px 12px;margin:-6px 0 16px}
.mbanner.on{display:block}
.mtile.wide{grid-column:1 / -1}
.mov .flag{width:26px;height:19px;border-radius:3px;object-fit:cover;flex:none;box-shadow:0 0 0 1px rgba(0,0,0,.08)}
.mov .globe{font-size:20px;flex:none}
.mov .mres li img.flag,.mov .msearch img.flag{width:22px;height:16px}
.dform .hflag{width:26px;height:19px;border-radius:3px;object-fit:cover;flex:none;box-shadow:0 0 0 1px rgba(0,0,0,.08)}
.dform .tile .ic{display:none}
.dform .mres li img.flag,.dform .msearch img.flag{width:22px;height:16px;border-radius:3px;object-fit:cover;flex:none;box-shadow:0 0 0 1px rgba(0,0,0,.08)}
.mtile.ctile,.mtile.ptile{align-items:center}
#mcgrid .mtile{min-width:0}
.mov .pg .mtile{align-items:center;position:relative;overflow:hidden}
.mov .mtile .s1ic{width:36px;height:36px;border-radius:10px;background:rgba(197,150,58,.12);color:var(--gold);display:flex;align-items:center;justify-content:center;flex:none;transition:.16s}
.mov .mtile .s1ic svg{width:20px;height:20px}
.mov .mtile.sel .s1ic{background:var(--gold);color:#fff}
.mov .mtile .s1b{flex:1;min-width:0}
.mov .mtile .s1b b{display:block;font:700 13.5px var(--sans);color:var(--navy)}
.mov .mtile .s1s{display:block;font:500 11.5px var(--sans);color:var(--muted);margin-top:2px}
.mov .mtile .s1n{position:absolute;top:-6px;right:8px;font:400 44px var(--serif);color:rgba(11,21,40,.05);line-height:1;pointer-events:none}
.mov .mtile.sel .s1n{color:rgba(197,150,58,.18)}
.ptile{flex-direction:column!important;align-items:flex-start!important;gap:2px;position:relative}
.ptile .num{font-family:var(--serif);font-size:30px;color:var(--navy);line-height:1}
.ptile .psub{font:600 12.5px var(--sans);color:var(--navy)}
.ptile .then{font:500 11.5px var(--sans);color:var(--muted);margin-top:1px}
.ptile.sel{border-color:var(--gold);background:#fffdf8;box-shadow:0 0 0 3px rgba(197,150,58,.14)}
.mov .ptile{overflow:hidden}
.mov .ptile .pic{width:32px;height:32px;border-radius:9px;background:rgba(197,150,58,.12);color:var(--gold);display:flex;align-items:center;justify-content:center;margin-bottom:8px;transition:.16s}
.mov .ptile .pic svg{width:18px;height:18px}
.mov .ptile.sel .pic{background:var(--gold);color:#fff}
.mov .ptile .pick{position:absolute;top:12px;right:12px;width:20px;height:20px;border-radius:50%;border:1.5px solid var(--border);display:flex;align-items:center;justify-content:center;transition:.16s}
.mov .ptile.sel .pick{background:var(--gold);border-color:var(--gold)}
.mov .ptile .pick svg{width:12px;height:12px;color:#fff;opacity:0;transition:.16s}
.mov .ptile.sel .pick svg{opacity:1}
.frow{display:flex;align-items:center;gap:12px;border:1.5px solid var(--border);border-radius:14px;padding:14px;margin-bottom:11px;background:#fff;box-shadow:0 8px 20px -8px rgba(11,21,40,.24);transition:all .18s}
.frow:hover{border-color:var(--gold);background:#fffdf8;transform:translateY(-4px);box-shadow:0 24px 42px -16px rgba(11,21,40,.42)}
.frow .fic{width:34px;height:34px;border-radius:10px;background:rgba(197,150,58,.12);color:var(--gold);display:flex;align-items:center;justify-content:center;flex:none}
.frow .fic svg{width:19px;height:19px}
.frow .ftx{flex:1;min-width:0}
.frow .ft{font:700 13.5px var(--sans);color:var(--navy)}.frow .fd{font:500 11.5px var(--sans);color:var(--muted);margin-top:2px}
.seg{display:flex;border:1.5px solid var(--border);border-radius:10px;overflow:hidden;flex:none}
.seg button{border:0;background:#fff;padding:8px 14px;font:700 13px var(--sans);color:var(--muted);cursor:pointer}
.seg button.on{background:var(--gold);color:#0b1528}
.msearch{display:flex;align-items:center;gap:10px;border:1.5px solid var(--gold);border-radius:13px;padding:0 13px;height:50px;box-shadow:0 0 0 3px rgba(197,150,58,.14)}
.msearch input{border:0;outline:0;flex:1;font:600 14px var(--sans);color:var(--navy);background:transparent}
.msearch .bk{border:0;background:none;color:var(--muted);font:700 15px var(--sans);cursor:pointer}
.mres{list-style:none;margin:10px 0 0;padding:0;max-height:220px;overflow:auto;display:grid;grid-template-columns:1fr 1fr;gap:8px}
.mres li{display:flex;align-items:center;gap:9px;padding:10px;border:1.5px solid var(--border);border-radius:11px;cursor:pointer;font:600 13px var(--sans);color:var(--navy)}
.mres li:hover{border-color:var(--gold);background:#fffdf8}
.mov .mtile.ctile .cn{flex:1;min-width:0;font:700 13.5px var(--sans);color:var(--navy)}
.mov .mtile.ctile .cst{display:inline-flex;align-items:center;gap:5px;font:700 9.5px var(--sans);flex:none}
.mov .mtile.ctile .cdot{width:6px;height:6px;border-radius:50%;flex:none}
.mov .mtile.ctile .cst.open{color:#16a34a}.mov .mtile.ctile .cst.open .cdot{background:#22c55e}
.mov .mtile.ctile .cst.lim{color:#b45309}.mov .mtile.ctile .cst.lim .cdot{background:#f59e0b}
.mov .mtile.ctile .csrch{margin-left:auto;color:#cbd5e1;flex:none}
.mov .msearch .euchip{display:flex;align-items:center;gap:7px;background:rgba(197,150,58,.1);border-radius:9px;padding:5px 9px 5px 6px;flex:none}
.mov .msearch .euchip img.flag{width:24px;height:17px}
.mov .msearch .euchip span{font:800 10px var(--sans);letter-spacing:.05em;text-transform:uppercase;color:var(--gold)}
.mov .mres li{box-shadow:0 8px 20px -8px rgba(11,21,40,.24);transition:.16s}
.mov .mres li:hover{border-color:var(--gold);background:#fffdf8}
.mov .mres li .cn2{flex:1;min-width:0;font:700 13px var(--sans);color:var(--navy)}
.mov .mres li .cst2{display:inline-flex;align-items:center;gap:5px;font:700 9px var(--sans);flex:none}
.mov .mres li .cdot2{width:6px;height:6px;border-radius:50%;flex:none}
.mov .mres li .cst2.open{color:#16a34a}.mov .mres li .cst2.open .cdot2{background:#22c55e}
.mov .mres li .cst2.lim{color:#b45309}.mov .mres li .cst2.lim .cdot2{background:#f59e0b}
.mov .mres li .cst2.chk{color:#94a3b8}.mov .mres li .cst2.chk .cdot2{background:#cbd5e1}
.mcallbtn{display:flex;align-items:center;justify-content:center;gap:9px;height:52px;border-radius:13px;width:100%;font:800 15px var(--sans);cursor:pointer;text-decoration:none;margin-top:10px}
.mwabtn,.mcallbtn,.waopen{white-space:nowrap}
.mhide{display:none}
.mov .mtile{box-shadow:0 8px 20px -8px rgba(11,21,40,.24);transition:all .18s}
.mov .mtile:hover{box-shadow:0 24px 42px -16px rgba(11,21,40,.42);transform:translateY(-4px)}
.mov .mtile.sel{box-shadow:0 12px 26px -10px rgba(197,150,58,.5)}
.mov .msntgrid{display:grid;grid-template-columns:1.1fr .9fr;gap:0}
.mov .msntL{padding:4px 18px 4px 2px}
.mov .msntL .mseal{margin:0 0 14px}
.mov .msntL h3{text-align:left;margin-bottom:8px}
.mov .msntL .mb{font:500 13px var(--sans);color:#475569;line-height:1.6;margin:0 0 6px}
.mov .msntR{background:radial-gradient(300px 200px at 100% 0,rgba(37,211,102,.12),transparent 60%),#f6f9fb;border-left:1px solid var(--border);border-radius:16px;padding:22px 18px;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center}
.mov .msntsum{background:#f6f9fb;border:1px solid var(--border);border-radius:12px;padding:2px 14px;margin-top:14px;box-shadow:0 8px 20px -8px rgba(11,21,40,.24)}
.mov .msntsum .row{display:flex;justify-content:space-between;gap:10px;padding:8px 0;border-bottom:1px solid var(--border);font:500 12.5px var(--sans)}
.mov .msntsum .row:last-child{border-bottom:0}
.mov .msntsum .k{color:var(--muted)}.mov .msntsum .v{color:var(--navy);font-weight:700;text-align:right}
.mov .mring{width:96px;height:96px;position:relative;margin-bottom:16px}
.mov .mring>svg{transform:rotate(-90deg)}
.mov .mring circle{fill:none;stroke-width:8}
.mov .mring .bg{stroke:var(--border)}
.mov .mring .fg{stroke:var(--gold);stroke-linecap:round;stroke-dasharray:283;stroke-dashoffset:283;animation:sntFill 3s linear forwards}
.mov .mring .wac{position:absolute;inset:0;display:flex;align-items:center;justify-content:center}
.mov .mring .disc{width:50px;height:50px;border-radius:50%;background:var(--gold);display:flex;align-items:center;justify-content:center}
.mov .mpk{font:800 11px var(--sans);letter-spacing:.12em;text-transform:uppercase;color:var(--muted);margin-bottom:5px}
.mov .mcnt{font:500 12.5px var(--sans);color:#475569;margin-bottom:16px}
.mov .mcnt b{color:var(--navy)}
@media(max-width:600px){.mov .msntgrid{grid-template-columns:1fr}.mov .msntR{border-left:0;border-top:1px solid var(--border);margin-top:14px}.mov .msntL{padding-right:2px}}
.v-badge{display:inline-flex;align-items:center;gap:7px;font:800 10.5px var(--sans);letter-spacing:.06em;text-transform:uppercase;padding:6px 12px;border-radius:999px;margin-bottom:12px}
.v-badge svg{width:14px;height:14px;flex:none}
.v-badge.g{background:rgba(22,163,74,.1);border:1px solid rgba(22,163,74,.28);color:#15803d}
.v-badge.a{background:rgba(180,83,9,.1);border:1px solid rgba(180,83,9,.3);color:#b45309}
.v-badge.r{background:rgba(220,38,38,.09);border:1px solid rgba(220,38,38,.28);color:#b91c1c}
.v-badge.s{background:#eef2f6;border:1px solid var(--border);color:#475569}
.v-lean{font:600 11px var(--sans);color:#94a3b8;margin-top:10px;display:flex;align-items:center;gap:6px}
.v-sub{font:500 13.5px var(--sans);color:#475569;line-height:1.55;margin:0 0 18px}
.v-sub b{color:var(--navy);font-weight:700}
.u-strip{display:flex;align-items:center;gap:10px;background:#fff7ed;border:1px solid #fed7aa;border-left:4px solid #b45309;border-radius:11px;padding:11px 13px;margin:2px 0 16px;font:700 13px var(--sans);color:#b45309}
.u-strip svg{width:18px;height:18px;stroke:#b45309;fill:none;flex:none}
.v-flabel{font:700 12.5px var(--sans);color:var(--navy);margin-bottom:8px}
.v-verdict{display:flex;gap:9px;align-items:flex-start;font:600 12px var(--sans);color:#475569;line-height:1.5;margin:16px 0 14px;padding:0 2px}
.v-verdict svg{width:16px;height:16px;flex:none;margin-top:1px}
.v-verdict b{color:var(--navy);font-weight:800}
.v-snap{background:#f6f9fb;border:1px solid var(--border);border-radius:14px;padding:15px 16px;box-shadow:0 8px 20px -12px rgba(11,21,40,.28)}
.v-snaphd{font:800 10.5px var(--sans);letter-spacing:.06em;text-transform:uppercase;color:var(--muted);margin-bottom:11px;display:flex;align-items:center;justify-content:space-between}
.v-snaphd small{font:600 10px var(--sans);letter-spacing:0;text-transform:none;color:#94a3b8}
.v-row{display:flex;align-items:center;gap:10px;padding:8px 0;border-top:1px solid var(--border)}
.v-row:first-of-type{border-top:0}
.wa-or{display:flex;align-items:center;gap:12px;margin:16px 0 12px;color:#9aa7b5}
.wa-or::before,.wa-or::after{content:"";height:1px;flex:1;background:var(--border)}
.wa-or span{font:800 10.5px var(--sans);letter-spacing:.1em;text-transform:uppercase}
.wa-alt{display:flex;align-items:center;justify-content:center;gap:8px;font:800 14px var(--sans);color:var(--gold);text-decoration:none}
.wa-alt svg{width:18px;height:18px;fill:var(--gold)}
.tscroll{scrollbar-width:none;-ms-overflow-style:none}
.tscroll::-webkit-scrollbar{display:none;width:0;height:0}
.tdots{display:flex;gap:8px;justify-content:center;margin:16px auto 0}
.tdots button{width:8px;height:8px;border-radius:999px;border:0;background:#cbd5e1;cursor:pointer;padding:0;transition:width .2s,background .2s}
.tdots button.on{width:24px;background:var(--gold)}
.v-row .kv{display:flex;align-items:center;gap:10px;flex:1;min-width:0}
.v-row .k{font:600 12px var(--sans);color:var(--muted);width:84px;flex:none}
.v-row .v{font:700 12.5px var(--sans);color:var(--navy);flex:1;min-width:0}
.v-chip{display:inline-flex;align-items:center;gap:5px;font:700 10px var(--sans);border-radius:999px;padding:4px 9px;flex:none}
.v-chip svg{width:11px;height:11px;flex:none}
.v-chip.ok{background:rgba(22,163,74,.1);border:1px solid rgba(22,163,74,.24);color:#15803d}
.v-chip.warn{background:rgba(245,158,11,.12);border:1px solid rgba(245,158,11,.3);color:#b45309}
.v-chip.bad{background:rgba(220,38,38,.09);border:1px solid rgba(220,38,38,.26);color:#b91c1c}
.v-chip.neutral{background:#f1f5f9;border:1px solid var(--border);color:#475569}
.v-fine{font:500 11px var(--sans);color:#94a3b8;text-align:center;margin-top:11px;line-height:1.45}
.mpl-badge{display:inline-flex;align-items:center;gap:6px;font:800 10px var(--sans);letter-spacing:.06em;text-transform:uppercase;padding:5px 11px;border-radius:999px;margin-bottom:11px;background:rgba(197,150,58,.12);border:1px solid rgba(197,150,58,.3);color:#8a6a1f}
.mpl-badge.teal{background:rgba(22,163,74,.1);border-color:rgba(22,163,74,.28);color:#15803d}
.mpl-badge.slate{background:#eef2f6;border-color:var(--border);color:#475569}
.mpl-tag{font:600 13px var(--sans);color:var(--muted);margin:4px 0 16px}
.mpl-price{display:flex;align-items:flex-end;gap:10px;padding:15px 16px;background:linear-gradient(120deg,#fbf6ec,#fff);border:1px solid rgba(197,150,58,.3);border-radius:14px;margin-bottom:8px}
.mpl-price .now{font-family:var(--serif);font-size:33px;color:var(--navy);line-height:.95}
.mpl-price .now small{font-size:15px;color:var(--muted)}
.mpl-price .then{font:600 12px var(--sans);color:var(--muted);padding-bottom:4px;line-height:1.4}
.mpl-price .then b{color:var(--navy);font-weight:800}
.mpl-split{font:600 11.5px var(--sans);color:#94a3b8;margin:0 2px 15px;display:flex;align-items:center;gap:6px}
.mpl-inc{list-style:none;margin:0 0 15px;padding:0}
.mpl-inc li{display:flex;align-items:flex-start;gap:9px;font:600 13px var(--sans);color:var(--navy);padding:6px 0}
.mpl-inc li svg{width:17px;height:17px;flex:none;color:#15803d;margin-top:1px}
.mpl-inc li span small{display:block;font:500 11.5px var(--sans);color:var(--muted);margin-top:1px}
.mpl-after{display:flex;gap:10px;align-items:flex-start;background:#fff;border:1px solid var(--border);border-radius:13px;padding:12px 13px;margin-bottom:16px;box-shadow:0 16px 34px -16px rgba(11,21,40,.4)}
.mpl-after b{display:block;font:800 12px var(--sans);color:var(--navy)}
.mpl-after span{display:block;font:600 11.5px var(--sans);color:var(--muted);margin-top:2px;line-height:1.45}
.mpl-fine{font:500 11px var(--sans);color:#94a3b8;text-align:center;margin-top:11px;line-height:1.45}
</style>
<div class=mov id=mov>
 <div class=mmodal>
  <div class=mhead><button class=mback id=mback onclick="mBack()" aria-label=Back><svg viewBox="0 0 24 24" fill=none stroke=currentColor stroke-width=2.4 stroke-linecap=round stroke-linejoin=round><path d="M15 18l-6-6 6-6"/></svg></button><span class=mpill id=mpill>Step 1</span><span class=mdest id=mdest></span><span class=mhsp></span><span class=mhint>60-sec check</span><button class=mx onclick="mClose()" aria-label=Close>✕</button></div>
  <div class=mbody>
   <div id=mtdiff style="display:none;margin-bottom:2px"></div>
   <div class=mbars id=mbars></div>
   <div class=mbanner id=mbanner></div>
   <div id=murg style="margin:0 0 4px;display:none"></div>

   <div class=mstep data-k=plan>
    <span class=mpl-badge id=mplbadge></span>
    <h3 class=mq id=mpltitle style="margin-bottom:2px"></h3>
    <p class=mpl-tag id=mpltag></p>
    <div class=mpl-price><span class=now id=mplnow></span><span class=then id=mplthen></span></div>
    <p class=mpl-split id=mplsplit></p>
    <ul class=mpl-inc id=mplinc></ul>
    <div class=mpl-after id=mplafter></div>
    <button class=mwabtn style="background:#C5963A;color:#3d2b08" onclick="mNext()"><svg width=18 height=18 viewBox="0 0 24 24" fill=none stroke="#3d2b08" stroke-width=2.4 stroke-linecap=round stroke-linejoin=round><path d="M5 12h14M13 6l6 6-6 6"/></svg> Confirm and continue</button>
    <p class=mpl-fine id=mplfine></p>
   </div>

   <div class=mstep data-k=preference><h3 class=mq>How would you like to start?</h3><p class=msub>Your call. Nothing is charged here.</p><div class=mgrid>
    <button class="mtile ptile" data-pref=p40 onclick="mPref('p40')"><span class=pick><svg viewBox="0 0 24 24" fill=none stroke=currentColor stroke-width=3 stroke-linecap=round stroke-linejoin=round><path d="M20 6L9 17l-5-5"/></svg></span><span class=pic><svg viewBox="0 0 24 24" fill=currentColor><path fill-rule=evenodd d="M9.315 7.584C12.195 3.883 16.695 1.5 21.75 1.5a.75.75 0 0 1 .75.75c0 5.056-2.383 9.555-6.084 12.436A6.75 6.75 0 0 1 9.75 22.5a.75.75 0 0 1-.75-.75v-4.131A15.838 15.838 0 0 1 6.382 15H2.25a.75.75 0 0 1-.75-.75 6.75 6.75 0 0 1 7.815-6.666ZM15 6.75a2.25 2.25 0 1 0 0 4.5 2.25 2.25 0 0 0 0-4.5Z" clip-rule=evenodd/><path d="M5.26 17.242a.75.75 0 1 0-.897-1.203 5.243 5.243 0 0 0-2.05 5.022.75.75 0 0 0 .625.627 5.243 5.243 0 0 0 5.022-2.051.75.75 0 1 0-1.202-.897 3.744 3.744 0 0 1-3.008 1.51c0-1.23.592-2.323 1.51-3.008Z"/></svg></span><span class=num>&pound;49</span><span class=psub>Just to start</span><span class=then>Pay &pound;109 later</span></button>
    <button class="mtile ptile" data-pref=p85 onclick="mPref('p85')"><span class=pick><svg viewBox="0 0 24 24" fill=none stroke=currentColor stroke-width=3 stroke-linecap=round stroke-linejoin=round><path d="M20 6L9 17l-5-5"/></svg></span><span class=pic><svg viewBox="0 0 24 24" fill=currentColor><path fill-rule=evenodd d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z" clip-rule=evenodd/></svg></span><span class=num>&pound;89</span><span class=psub>To book Prime slot</span><span class=then>Pay &pound;209 later</span></button>
    <button class="mtile ptile" data-pref=p140 onclick="mPref('p140')"><span class=pick><svg viewBox="0 0 24 24" fill=none stroke=currentColor stroke-width=3 stroke-linecap=round stroke-linejoin=round><path d="M20 6L9 17l-5-5"/></svg></span><span class=pic><svg viewBox="0 0 24 24" fill=currentColor><path fill-rule=evenodd d="M8.603 3.799A4.49 4.49 0 0 1 12 2.25c1.357 0 2.573.6 3.397 1.549a4.49 4.49 0 0 1 3.498 1.307 4.491 4.491 0 0 1 1.307 3.497A4.49 4.49 0 0 1 21.75 12a4.49 4.49 0 0 1-1.549 3.397 4.491 4.491 0 0 1-1.307 3.497 4.491 4.491 0 0 1-3.497 1.307A4.49 4.49 0 0 1 12 21.75a4.49 4.49 0 0 1-3.397-1.549 4.49 4.49 0 0 1-3.498-1.306 4.491 4.491 0 0 1-1.307-3.498A4.49 4.49 0 0 1 2.25 12c0-1.357.6-2.573 1.549-3.397a4.49 4.49 0 0 1 1.307-3.497 4.49 4.49 0 0 1 3.497-1.307Zm7.007 6.387a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule=evenodd/></svg></span><span class=num>&pound;158</span><span class=psub>Pay in full</span><span class=then>Nothing more to pay</span></button>
    <button class="mtile ptile" data-pref=notsure onclick="mPref('notsure')"><span class=pick><svg viewBox="0 0 24 24" fill=none stroke=currentColor stroke-width=3 stroke-linecap=round stroke-linejoin=round><path d="M20 6L9 17l-5-5"/></svg></span><span class=pic><svg viewBox="0 0 24 24" fill=currentColor><path fill-rule=evenodd d="M4.848 2.771A49.144 49.144 0 0 1 12 2.25c2.43 0 4.817.178 7.152.52 1.978.292 3.348 2.024 3.348 3.97v6.02c0 1.946-1.37 3.678-3.348 3.97a48.901 48.901 0 0 1-3.476.383.39.39 0 0 0-.297.17l-2.755 4.133a.75.75 0 0 1-1.248 0l-2.755-4.133a.39.39 0 0 0-.297-.17 48.9 48.9 0 0 1-3.476-.384c-1.978-.29-3.348-2.024-3.348-3.97V6.741c0-1.946 1.37-3.678 3.348-3.97Z" clip-rule=evenodd/></svg></span><span class=num style="font-size:22px">Not sure</span><span class=psub>Talk it through first</span></button>
   </div></div>

   <div class=mstep data-k=refreason><h3 class=mq>What did they refuse you on?</h3><p class=msub>Pick what it came down to. Not sure is fine.</p><div class="mgrid pg">
    <button class=mtile onclick="mRr('Travel plan')"><span class=s1ic><svg viewBox="0 0 24 24" fill=currentColor><path fill-rule=evenodd d="M8.161 2.58a1.875 1.875 0 0 1 1.678 0l4.993 2.498c.106.052.23.052.336 0l3.869-1.935A1.875 1.875 0 0 1 21.75 4.82v12.485c0 .71-.401 1.36-1.037 1.677l-4.875 2.437a1.875 1.875 0 0 1-1.676 0l-4.994-2.497a.375.375 0 0 0-.336 0l-3.868 1.935A1.875 1.875 0 0 1 2.25 19.18V6.695c0-.71.401-1.36 1.036-1.677l4.875-2.437ZM9 6a.75.75 0 0 1 .75.75V15a.75.75 0 0 1-1.5 0V6.75A.75.75 0 0 1 9 6Zm6.75 3a.75.75 0 0 0-1.5 0v8.25a.75.75 0 0 0 1.5 0V9Z" clip-rule=evenodd/></svg></span><span class=s1b><b>Travel plan</b><span class=s1s>Purpose, hotel or itinerary</span></span><span class=s1n>1</span></button>
    <button class=mtile onclick="mRr('Funds')"><span class=s1ic><svg viewBox="0 0 24 24" fill=currentColor><path d="M12 7.5a2.25 2.25 0 1 0 0 4.5 2.25 2.25 0 0 0 0-4.5Z"/><path fill-rule=evenodd d="M1.5 4.875C1.5 3.839 2.34 3 3.375 3h17.25c1.035 0 1.875.84 1.875 1.875v9.75c0 1.036-.84 1.875-1.875 1.875H3.375A1.875 1.875 0 0 1 1.5 14.625v-9.75ZM8.25 9.75a3.75 3.75 0 1 1 7.5 0 3.75 3.75 0 0 1-7.5 0ZM18.75 9a.75.75 0 0 0-.75.75v.008c0 .414.336.75.75.75h.008a.75.75 0 0 0 .75-.75V9.75a.75.75 0 0 0-.75-.75h-.008ZM4.5 9.75A.75.75 0 0 1 5.25 9h.008a.75.75 0 0 1 .75.75v.008a.75.75 0 0 1-.75.75H5.25a.75.75 0 0 1-.75-.75V9.75Z" clip-rule=evenodd/><path d="M2.25 18a.75.75 0 0 0 0 1.5c5.4 0 10.63.722 15.6 2.075 1.19.324 2.4-.558 2.4-1.82V18.75a.75.75 0 0 0-.75-.75H2.25Z"/></svg></span><span class=s1b><b>Funds</b><span class=s1s>Money not accepted</span></span><span class=s1n>2</span></button>
    <button class=mtile onclick="mRr('Return')"><span class=s1ic><svg viewBox="0 0 24 24" fill=currentColor><path fill-rule=evenodd d="M9.53 2.47a.75.75 0 0 1 0 1.06L4.81 8.25H15a6.75 6.75 0 0 1 0 13.5h-3a.75.75 0 0 1 0-1.5h3a5.25 5.25 0 1 0 0-10.5H4.81l4.72 4.72a.75.75 0 1 1-1.06 1.06l-6-6a.75.75 0 0 1 0-1.06l6-6a.75.75 0 0 1 1.06 0Z" clip-rule=evenodd/></svg></span><span class=s1b><b>Return</b><span class=s1s>Doubted I'd come back</span></span><span class=s1n>3</span></button>
    <button class=mtile onclick="mRr('Insurance')"><span class=s1ic><svg viewBox="0 0 24 24" fill=currentColor><path fill-rule=evenodd d="M12.516 2.17a.75.75 0 0 0-1.032 0 11.209 11.209 0 0 1-7.877 3.08.75.75 0 0 0-.722.515A12.74 12.74 0 0 0 2.25 9.75c0 5.942 4.064 10.933 9.563 12.348a.749.749 0 0 0 .374 0c5.499-1.415 9.563-6.406 9.563-12.348 0-1.39-.223-2.73-.635-3.985a.75.75 0 0 0-.722-.516l-.143.001c-2.996 0-5.717-1.17-7.734-3.08Zm3.094 8.016a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule=evenodd/></svg></span><span class=s1b><b>Insurance</b><span class=s1s>Cover wrong or missing</span></span><span class=s1n>4</span></button>
    <button class=mtile onclick="mRr('Documents')"><span class=s1ic><svg viewBox="0 0 24 24" fill=currentColor><path fill-rule=evenodd d="M5.625 1.5c-1.036 0-1.875.84-1.875 1.875v17.25c0 1.035.84 1.875 1.875 1.875h12.75c1.035 0 1.875-.84 1.875-1.875V12.75A3.75 3.75 0 0 0 16.5 9h-1.875a1.875 1.875 0 0 1-1.875-1.875V5.25A3.75 3.75 0 0 0 9 1.5H5.625ZM7.5 15a.75.75 0 0 1 .75-.75h7.5a.75.75 0 0 1 0 1.5h-7.5A.75.75 0 0 1 7.5 15Zm.75 2.25a.75.75 0 0 0 0 1.5H12a.75.75 0 0 0 0-1.5H8.25Z" clip-rule=evenodd/><path d="M12.971 1.816A5.23 5.23 0 0 1 14.25 5.25v1.875c0 .207.168.375.375.375H16.5a5.23 5.23 0 0 1 3.434 1.279 9.768 9.768 0 0 0-6.963-6.963Z"/></svg></span><span class=s1b><b>Documents</b><span class=s1s>Papers not believed</span></span><span class=s1n>5</span></button>
    <button class=mtile onclick="mRr('Overstay')"><span class=s1ic><svg viewBox="0 0 24 24" fill=currentColor><path fill-rule=evenodd d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003ZM12 8.25a.75.75 0 0 1 .75.75v3.75a.75.75 0 0 1-1.5 0V9a.75.75 0 0 1 .75-.75Zm0 8.25a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule=evenodd/></svg></span><span class=s1b><b>Overstay</b><span class=s1s>Previous visa overstayed</span></span><span class=s1n>6</span></button>
    <button class="mtile wide" onclick="mRr('Not sure')"><span class=s1ic><svg viewBox="0 0 24 24" fill=currentColor><path fill-rule=evenodd d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm8.706-1.442c1.146-.573 2.437.463 2.126 1.706l-.709 2.836.042-.02a.75.75 0 0 1 .671 1.34l-.041.022c-1.147.573-2.438-.463-2.127-1.706l.71-2.836-.042.02a.75.75 0 1 1-.671-1.34l.041-.022ZM12 9a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule=evenodd/></svg></span><span class=s1b><b>Not sure</b><span class=s1s>Send us the letter, we'll read the reason</span></span><span class=s1n>7</span></button>
   </div></div>

   <div class=mstep data-k=purpose><h3 class=mq>Why are you travelling?</h3><p class=msub>This tells us exactly what your case needs.</p><div class="mgrid pg">
    <button class=mtile onclick="mPk('purpose','Holiday')"><span class=s1ic><svg viewBox="0 0 24 24" fill=currentColor><path fill-rule=evenodd d="M12 2.25a.75.75 0 0 1 .75.75v2.25a.75.75 0 0 1-1.5 0V3a.75.75 0 0 1 .75-.75ZM7.5 12a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM18.894 6.166a.75.75 0 0 0-1.06-1.06l-1.591 1.59a.75.75 0 1 0 1.06 1.061l1.591-1.59ZM21.75 12a.75.75 0 0 1-.75.75h-2.25a.75.75 0 0 1 0-1.5H21a.75.75 0 0 1 .75.75ZM17.834 18.894a.75.75 0 0 0 1.06-1.06l-1.59-1.591a.75.75 0 1 0-1.061 1.06l1.59 1.591ZM12 18a.75.75 0 0 1 .75.75V21a.75.75 0 0 1-1.5 0v-2.25A.75.75 0 0 1 12 18ZM7.758 17.303a.75.75 0 0 0-1.061-1.06l-1.591 1.59a.75.75 0 0 0 1.06 1.061l1.592-1.59ZM6 12a.75.75 0 0 1-.75.75H3a.75.75 0 0 1 0-1.5h2.25A.75.75 0 0 1 6 12ZM6.697 7.757a.75.75 0 0 0 1.06-1.06l-1.59-1.591a.75.75 0 0 0-1.061 1.06l1.59 1.591Z" clip-rule=evenodd/></svg></span><span class=s1b><b>Holiday</b><span class=s1s>Tourism or a short break</span></span><span class=s1n>1</span></button>
    <button class=mtile onclick="mPk('purpose','Family/friends')"><span class=s1ic><svg viewBox="0 0 24 24" fill=currentColor><path d="M4.5 6.375a4.125 4.125 0 1 1 8.25 0 4.125 4.125 0 0 1-8.25 0ZM14.25 8.625a3.375 3.375 0 1 1 6.75 0 3.375 3.375 0 0 1-6.75 0ZM1.5 19.125a7.125 7.125 0 0 1 14.25 0v.003l-.001.119a.75.75 0 0 1-.363.63 13.067 13.067 0 0 1-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 0 1-.364-.63l-.001-.122ZM17.25 19.128l-.001.144a2.25 2.25 0 0 1-.233.96 10.088 10.088 0 0 0 5.06-1.01.75.75 0 0 0 .42-.643 4.875 4.875 0 0 0-6.957-4.611 8.586 8.586 0 0 1 1.71 5.157v.003Z"/></svg></span><span class=s1b><b>Family / friends</b><span class=s1s>Visiting someone abroad</span></span><span class=s1n>2</span></button>
    <button class=mtile onclick="mPk('purpose','Business')"><span class=s1ic><svg viewBox="0 0 24 24" fill=currentColor><path fill-rule=evenodd d="M7.5 5.25a3 3 0 0 1 3-3h3a3 3 0 0 1 3 3v.205c.933.085 1.857.197 2.774.334 1.454.218 2.476 1.483 2.476 2.917v3.033c0 1.211-.734 2.352-1.936 2.752A24.726 24.726 0 0 1 12 15.75c-2.73 0-5.357-.442-7.814-1.259-1.202-.4-1.936-1.541-1.936-2.752V8.706c0-1.434 1.022-2.7 2.476-2.917A48.814 48.814 0 0 1 7.5 5.455V5.25Zm7.5 0v.09a49.488 49.488 0 0 0-6 0v-.09a1.5 1.5 0 0 1 1.5-1.5h3a1.5 1.5 0 0 1 1.5 1.5Zm-3 8.25a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule=evenodd/><path d="M3 18.4v-2.796a4.3 4.3 0 0 0 .713.31A26.226 26.226 0 0 0 12 17.25c2.892 0 5.68-.468 8.287-1.335.252-.084.49-.19.713-.311V18.4c0 1.452-1.047 2.728-2.523 2.923-2.12.282-4.282.427-6.477.427a49.19 49.19 0 0 1-6.477-.427C4.047 21.128 3 19.852 3 18.4Z"/></svg></span><span class=s1b><b>Business</b><span class=s1s>Work trip or meetings</span></span><span class=s1n>3</span></button>
    <button class=mtile onclick="mPk('purpose','Study')"><span class=s1ic><svg viewBox="0 0 24 24" fill=currentColor><path d="M11.7 2.805a.75.75 0 0 1 .6 0A60.65 60.65 0 0 1 22.83 8.72a.75.75 0 0 1-.231 1.337 49.949 49.949 0 0 0-9.902 3.912l-.003.002c-.114.06-.227.119-.34.18a.75.75 0 0 1-.707 0A50.88 50.88 0 0 0 7.5 12.173v-.224c0-.131.067-.248.172-.311a54.615 54.615 0 0 1 4.653-2.52.75.75 0 0 0-.65-1.352 56.123 56.123 0 0 0-4.78 2.589 1.858 1.858 0 0 0-.859 1.228 49.803 49.803 0 0 0-4.634-1.527.75.75 0 0 1-.231-1.337A60.653 60.653 0 0 1 11.7 2.805Z"/><path d="M13.06 15.473a48.45 48.45 0 0 1 7.666-3.282c.134 1.414.22 2.843.255 4.284a.75.75 0 0 1-.46.711 47.87 47.87 0 0 0-8.105 4.342.75.75 0 0 1-.832 0 47.87 47.87 0 0 0-8.104-4.342.75.75 0 0 1-.461-.71c.035-1.442.121-2.87.255-4.286.921.304 1.83.634 2.727.99v1.27a1.5 1.5 0 0 0-.14 2.508c-.09.38-.222.753-.397 1.11.452.213.901.434 1.346.66a6.727 6.727 0 0 0 .551-1.607 1.5 1.5 0 0 0 .14-2.67v-.645a48.549 48.549 0 0 1 3.44 1.667 2.25 2.25 0 0 0 2.12 0Z"/><path d="M4.462 19.462c.42-.419.753-.89 1-1.395.453.214.902.435 1.347.662a6.742 6.742 0 0 1-1.286 1.794.75.75 0 0 1-1.06-1.06Z"/></svg></span><span class=s1b><b>Study</b><span class=s1s>Course or exchange</span></span><span class=s1n>4</span></button>
    <button class="mtile wide" onclick="mPk('purpose','Long-stay')"><span class=s1ic><svg viewBox="0 0 24 24" fill=currentColor><path fill-rule=evenodd d="M4.755 10.059a7.5 7.5 0 0 1 12.548-3.364l1.903 1.903h-3.183a.75.75 0 1 0 0 1.5h4.992a.75.75 0 0 0 .75-.75V4.356a.75.75 0 0 0-1.5 0v3.18l-1.9-1.9A9 9 0 0 0 3.306 9.67a.75.75 0 1 0 1.45.388Zm15.408 3.352a.75.75 0 0 0-.919.53 7.5 7.5 0 0 1-12.548 3.364l-1.902-1.903h3.183a.75.75 0 0 0 0-1.5H2.984a.75.75 0 0 0-.75.75v4.992a.75.75 0 0 0 1.5 0v-3.18l1.9 1.9a9 9 0 0 0 15.059-4.035.75.75 0 0 0-.53-.918Z" clip-rule=evenodd/></svg></span><span class=s1b><b>Long-stay</b><span class=s1s>Frequent traveller or multi-entry</span></span><span class=s1n>5</span></button>
   </div></div>

   <div class=mstep data-k=country><h3 class=mq id=mcq>Which country are you applying for?</h3><p class=msub>Pick a popular one, or search all Schengen countries.</p>
    <div class=mgrid id=mcgrid>
     <button class="mtile ctile" onclick="mPickC('Spain','es')"><img class=flag src="https://flagcdn.com/es.svg" alt=""><span class=cn>Spain</span><span class="cst lim"><span class=cdot></span>Filling</span></button>
     <button class="mtile ctile" onclick="mPickC('France','fr')"><img class=flag src="https://flagcdn.com/fr.svg" alt=""><span class=cn>France</span><span class="cst open"><span class=cdot></span>Open</span></button>
     <button class="mtile ctile" onclick="mPickC('Italy','it')"><img class=flag src="https://flagcdn.com/it.svg" alt=""><span class=cn>Italy</span><span class="cst lim"><span class=cdot></span>Limited</span></button>
     <button class="mtile ctile" onclick="mPickC('Germany','de')"><img class=flag src="https://flagcdn.com/de.svg" alt=""><span class=cn>Germany</span><span class="cst open"><span class=cdot></span>Open</span></button>
     <button class="mtile ctile" onclick="mPickC('Greece','gr')"><img class=flag src="https://flagcdn.com/gr.svg" alt=""><span class=cn>Greece</span><span class="cst open"><span class=cdot></span>Open</span></button>
     <button class="mtile ctile" onclick="mCsearch()"><img class=flag src="https://flagcdn.com/eu.svg" alt=""><span class=cn>Other Schengen</span><svg class=csrch width=17 height=17 viewBox="0 0 24 24" fill=none stroke=currentColor stroke-width=2.2 stroke-linecap=round stroke-linejoin=round><circle cx=11 cy=11 r=7/><path d="M21 21l-4.3-4.3"/></svg></button>
    </div>
    <div id=mcwrap class=mhide><div class=msearch><span class=euchip><img class=flag src="https://flagcdn.com/eu.svg" alt=""><span>Schengen</span></span><input id=mcq2 placeholder="Search countries" oninput="mCrender(this.value)" autocomplete=off><button class=bk onclick="mCclose()">✕</button></div><ul class=mres id=mclist></ul></div>
   </div>

   <div class=mstep data-k=flex><h3 class=mq>How much time is left on your UK e-visa?</h3><p class=msub>Ideally your UK leave stays valid about 3 months after you return.</p><div class="mgrid pg">
    <button class=mtile onclick="mPk('ukvisa','Valid past my trip')"><span class=s1ic><svg viewBox="0 0 24 24" fill=currentColor><path fill-rule=evenodd d="M8.603 3.799A4.49 4.49 0 0 1 12 2.25c1.357 0 2.573.6 3.397 1.549a4.49 4.49 0 0 1 3.498 1.307 4.491 4.491 0 0 1 1.307 3.497A4.49 4.49 0 0 1 21.75 12a4.49 4.49 0 0 1-1.549 3.397 4.491 4.491 0 0 1-1.307 3.497 4.491 4.491 0 0 1-3.497 1.307A4.49 4.49 0 0 1 12 21.75a4.49 4.49 0 0 1-3.397-1.549 4.49 4.49 0 0 1-3.498-1.306 4.491 4.491 0 0 1-1.307-3.498A4.49 4.49 0 0 1 2.25 12c0-1.357.6-2.573 1.549-3.397a4.49 4.49 0 0 1 1.307-3.497 4.49 4.49 0 0 1 3.497-1.307Zm7.007 6.387a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule=evenodd/></svg></span><span class=s1b><b>Valid past my trip</b><span class=s1s>3+ months after I return</span></span><span class=s1n>1</span></button>
    <button class=mtile onclick="mPk('ukvisa','Expires near my trip')"><span class=s1ic><svg viewBox="0 0 24 24" fill=currentColor><path fill-rule=evenodd d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003ZM12 8.25a.75.75 0 0 1 .75.75v3.75a.75.75 0 0 1-1.5 0V9a.75.75 0 0 1 .75-.75Zm0 8.25a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule=evenodd/></svg></span><span class=s1b><b>Expires near my trip</b><span class=s1s>Around it or soon after</span></span><span class=s1n>2</span></button>
    <button class=mtile onclick="mPk('ukvisa','ILR or settled')"><span class=s1ic><svg viewBox="0 0 24 24" fill=currentColor><path d="M11.47 3.84a.75.75 0 0 1 1.06 0l8.69 8.69a.75.75 0 1 0 1.06-1.06l-8.689-8.69a2.25 2.25 0 0 0-3.182 0l-8.69 8.69a.75.75 0 0 0 1.061 1.06l8.69-8.69Z"/><path d="m12 5.432 8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 0 1-.75-.75v-4.5a.75.75 0 0 0-.75-.75h-3a.75.75 0 0 0-.75.75V21a.75.75 0 0 1-.75.75H5.625a1.875 1.875 0 0 1-1.875-1.875v-6.198a2.29 2.29 0 0 0 .091-.086L12 5.432Z"/></svg></span><span class=s1b><b>ILR or settled</b><span class=s1s>No expiry date</span></span><span class=s1n>3</span></button>
    <button class=mtile onclick="mPk('ukvisa','Extension pending')"><span class=s1ic><svg viewBox="0 0 24 24" fill=currentColor><path fill-rule=evenodd d="M4.755 10.059a7.5 7.5 0 0 1 12.548-3.364l1.903 1.903h-3.183a.75.75 0 1 0 0 1.5h4.992a.75.75 0 0 0 .75-.75V4.356a.75.75 0 0 0-1.5 0v3.18l-1.9-1.9A9 9 0 0 0 3.306 9.67a.75.75 0 1 0 1.45.388Zm15.408 3.352a.75.75 0 0 0-.919.53 7.5 7.5 0 0 1-12.548 3.364l-1.902-1.903h3.183a.75.75 0 0 0 0-1.5H2.984a.75.75 0 0 0-.75.75v4.992a.75.75 0 0 0 1.5 0v-3.18l1.9 1.9a9 9 0 0 0 15.059-4.035.75.75 0 0 0-.53-.918Z" clip-rule=evenodd/></svg></span><span class=s1b><b>Extension pending</b><span class=s1s>Applied, awaiting decision</span></span><span class=s1n>4</span></button>
    <button class="mtile wide" onclick="mPk('ukvisa','Not sure')"><span class=s1ic><svg viewBox="0 0 24 24" fill=currentColor><path fill-rule=evenodd d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm11.378-3.917c-.89-.777-2.366-.777-3.255 0a.75.75 0 0 1-.988-1.129c1.454-1.272 3.776-1.272 5.23 0 1.513 1.324 1.513 3.518 0 4.842a3.75 3.75 0 0 1-.837.552c-.676.328-1.028.774-1.028 1.152v.75a.75.75 0 0 1-1.5 0v-.75c0-1.279 1.06-2.107 1.875-2.502.182-.088.351-.199.503-.331.83-.727.83-1.857 0-2.584ZM12 18a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule=evenodd/></svg></span><span class=s1b><b>Not sure</b><span class=s1s>We'll check it with you</span></span><span class=s1n>5</span></button>
   </div>
   </div>

   <div class=mstep data-k=tim><h3 class=mq>When are you travelling?</h3><p class=msub>Even a rough idea helps.</p><div class="mgrid pg">
    <button class=mtile onclick="mPk('tim','Within 2 weeks')"><span class=s1ic><svg viewBox="0 0 24 24" fill=currentColor><path fill-rule=evenodd d="M14.615 1.595a.75.75 0 0 1 .359.852L12.982 9.75h7.268a.75.75 0 0 1 .548 1.262l-10.5 11.25a.75.75 0 0 1-1.272-.71l1.992-7.302H3.75a.75.75 0 0 1-.548-1.262l10.5-11.25a.75.75 0 0 1 .913-.143Z" clip-rule=evenodd/></svg></span><span class=s1b><b>Within 2 weeks</b><span class=s1s>Urgent</span></span><span class=s1n>1</span></button>
    <button class=mtile onclick="mPk('tim','In 2 to 4 weeks')"><span class=s1ic><svg viewBox="0 0 24 24" fill=currentColor><path fill-rule=evenodd d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25ZM12.75 6a.75.75 0 0 0-1.5 0v6c0 .414.336.75.75.75h4.5a.75.75 0 0 0 0-1.5h-3.75V6Z" clip-rule=evenodd/></svg></span><span class=s1b><b>In 2 to 4 weeks</b><span class=s1s>Soon</span></span><span class=s1n>2</span></button>
    <button class=mtile onclick="mPk('tim','In 1 to 3 months')"><span class=s1ic><svg viewBox="0 0 24 24" fill=currentColor><path fill-rule=evenodd d="M6.75 2.25A.75.75 0 0 1 7.5 3v1.5h9V3A.75.75 0 0 1 18 3v1.5h.75a3 3 0 0 1 3 3v11.25a3 3 0 0 1-3 3H5.25a3 3 0 0 1-3-3V7.5a3 3 0 0 1 3-3H6V3a.75.75 0 0 1 .75-.75Zm13.5 9a1.5 1.5 0 0 0-1.5-1.5H5.25a1.5 1.5 0 0 0-1.5 1.5v7.5a1.5 1.5 0 0 0 1.5 1.5h13.5a1.5 1.5 0 0 0 1.5-1.5v-7.5Z" clip-rule=evenodd/></svg></span><span class=s1b><b>In 1 to 3 months</b><span class=s1s>Planning</span></span><span class=s1n>3</span></button>
    <button class=mtile onclick="mPk('tim','Just planning ahead')"><span class=s1ic><svg viewBox="0 0 24 24" fill=currentColor><path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/><path fill-rule=evenodd d="M1.323 11.447C2.811 6.976 7.028 3.75 12.001 3.75c4.97 0 9.185 3.223 10.675 7.69.12.362.12.752 0 1.113-1.487 4.471-5.705 7.697-10.677 7.697-4.97 0-9.186-3.223-10.675-7.69a1.762 1.762 0 0 1 0-1.113ZM17.25 12a5.25 5.25 0 1 1-10.5 0 5.25 5.25 0 0 1 10.5 0Z" clip-rule=evenodd/></svg></span><span class=s1b><b>Just planning ahead</b><span class=s1s>No fixed date</span></span><span class=s1n>4</span></button>
   </div></div>

   <div class=mstep data-k=details><span class="v-badge g" id=mdetbadge></span><h3 class=mq id=mdetq style="margin-bottom:8px">Good news. You look eligible.</h3><p class=v-sub id=mdetsub></p><div class=v-flabel>Where should we reach you?</div>
     <input class=mfld id=mfname placeholder="Your first name" autocomplete=given-name>
     <div class=merr id=mename>Please enter your name</div>
     <div class=mpc id=mpc><button type=button class=mpcbtn id=mpcbtn><img id=mpcflag src="https://beyondpassports.co.uk/assets/flags/gb.svg" width=20 height=15 alt=""><span id=mpcdc>+44</span><span style="color:var(--muted);font-size:10px">▾</span></button><input class=mpcin id=mfphone type=tel placeholder="7911 123456" inputmode=tel><div id=mpcpop style="position:absolute;z-index:5;top:calc(100% + 6px);left:0;width:280px;max-width:90vw;background:#fff;border:1px solid var(--border);border-radius:12px;box-shadow:0 30px 60px -24px rgba(20,30,45,.4);padding:8px" hidden></div></div>
     <div class=merr id=mephone>Enter a valid number</div>
     <button class=mwabtn id=mdetbtn style="background:#C5963A;color:#3d2b08" onclick=mNext()><svg width=18 height=18 viewBox="0 0 24 24" fill=none stroke="#3d2b08" stroke-width=2.4 stroke-linecap=round stroke-linejoin=round><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg> Check my file</button>
<div class=wa-or><span>or</span></div>
<a class=wa-alt href="https://wa.me/447848494680?text=Hi%20Beyond%20Passports%2C%20I%20would%20like%20help%20with%20my%20Schengen%20visa." target=_blank rel=noopener><svg viewBox="0 0 24 24"><path d="M12 2a10 10 0 0 0-8.6 15l-1.3 4.7 4.8-1.3A10 10 0 1 0 12 2zm5.5 14.2c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .1-1.7-.1-.4-.1-.9-.3-1.6-.6-2.8-1.2-4.6-4-4.7-4.2-.1-.2-1.1-1.5-1.1-2.8s.7-2 .9-2.2c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.8 2c.1.1.1.3 0 .5l-.4.5-.3.3c-.2.2-.3.4-.1.7.2.3.9 1.4 1.9 2.3 1.3 1.1 2.3 1.5 2.6 1.6.3.1.5.1.7-.1l.9-1c.2-.2.4-.2.6-.1l1.9.9c.3.2.5.2.5.4.1.1.1.8-.2 1.4z"/></svg>Talk to an Expert</a>
     <div class=v-verdict id=mdetverdict></div>
     <div class=v-snap id=mdetsum></div>
     <p class=v-fine>Provisional only. Eligibility to apply is not a visa decision. Your details stay private.</p>
   </div>

   <div class=mstep data-k=closeWa><div class=msntgrid>
    <div class=msntL>
     <div class=mseal><svg width=27 height=27 viewBox="0 0 24 24" fill=none stroke="#C5963A" stroke-width=2.4 stroke-linecap=round stroke-linejoin=round><path d="M20 6L9 17l-5-5"/></svg></div>
     <h3 class=mq id=msnthead>You're all set.</h3>
     <p class=mb>A UK-based specialist reads your answers and replies, usually within 30 minutes.</p>
     <div class=msntsum id=msntsum></div>
    </div>
    <div class=msntR>
     <div class=mring><svg width=96 height=96 viewBox="0 0 104 104"><circle class=bg cx=52 cy=52 r=45></circle><circle class=fg cx=52 cy=52 r=45></circle></svg><span class=wac><span class=disc><svg width=26 height=26 viewBox="0 0 24 24" fill="#fff"><path d="M12 2a10 10 0 0 0-8.6 15l-1.3 4.7 4.8-1.3A10 10 0 1 0 12 2zm5.5 14.2c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .1-1.7-.1-.4-.1-.9-.3-1.6-.6-2.8-1.2-4.6-4-4.7-4.2-.1-.2-1.1-1.5-1.1-2.8s.7-2 .9-2.2c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.8 2c.1.1.1.3 0 .5l-.4.5-.3.3c-.2.2-.3.4-.1.7.2.3.9 1.4 1.9 2.3 1.3 1.1 2.3 1.5 2.6 1.6.3.1.5.1.7-.1l.9-1c.2-.2.4-.2.6-.1l1.9.9c.3.2.5.2.5.4.1.1.1.8-.2 1.4z"/></svg></span></span></div>
     <p class=mpk>Opening WhatsApp</p>
     <p class=mcnt id=msntcnt>The chat opens in <b>3</b> seconds, prefilled with your details.</p>
     <a class=mwabtn id=msntwa href="#" target=_blank rel=noopener style="margin:0"><svg width=20 height=20 viewBox="0 0 24 24" fill="#fff"><path d="M12 2a10 10 0 0 0-8.6 15l-1.3 4.7 4.8-1.3A10 10 0 1 0 12 2zm5.5 14.2c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .1-1.7-.1-.4-.1-.9-.3-1.6-.6-2.8-1.2-4.6-4-4.7-4.2-.1-.2-1.1-1.5-1.1-2.8s.7-2 .9-2.2c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.8 2c.1.1.1.3 0 .5l-.4.5-.3.3c-.2.2-.3.4-.1.7.2.3.9 1.4 1.9 2.3 1.3 1.1 2.3 1.5 2.6 1.6.3.1.5.1.7-.1l.9-1c.2-.2.4-.2.6-.1l1.9.9c.3.2.5.2.5.4.1.1.1.8-.2 1.4z"/></svg> Open now</a>
    </div>
   </div></div>

   <div class=mstep data-k=closeCall><div class=msntgrid>
    <div class=msntL>
     <div class=mseal><svg width=27 height=27 viewBox="0 0 24 24" fill=none stroke="#C5963A" stroke-width=2.4 stroke-linecap=round stroke-linejoin=round><path d="M20 6L9 17l-5-5"/></svg></div>
     <h3 class=mq>Let's talk it through.</h3>
     <p class=mb>Book a free 30-minute call, your choice of channel. A UK specialist walks you through it, no pressure.</p>
     <div class=msntsum id=mcallsum></div>
    </div>
    <div class=msntR>
     <p class=mpk>Your choice</p>
     <p class=mcnt>Pick whichever suits you.</p>
     <a class=mcallbtn href="https://calendly.com/beyondpassports/30min" target=_blank rel=noopener style="background:var(--navy);color:#fff;margin:0">&#128197; Calendly</a>
     <a class=mcallbtn id=mcallwa href="#" target=_blank rel=noopener style="background:#C5963A;color:#3d2b08"><svg width=19 height=19 viewBox="0 0 24 24" fill="#3d2b08"><path d="M12 2a10 10 0 0 0-8.6 15l-1.3 4.7 4.8-1.3A10 10 0 1 0 12 2zm5.5 14.2c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .1-1.7-.1-.4-.1-.9-.3-1.6-.6-2.8-1.2-4.6-4-4.7-4.2-.1-.2-1.1-1.5-1.1-2.8s.7-2 .9-2.2c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.8 2c.1.1.1.3 0 .5l-.4.5-.3.3c-.2.2-.3.4-.1.7.2.3.9 1.4 1.9 2.3 1.3 1.1 2.3 1.5 2.6 1.6.3.1.5.1.7-.1l.9-1c.2-.2.4-.2.6-.1l1.9.9c.3.2.5.2.5.4.1.1.1.8-.2 1.4z"/></svg> WhatsApp</a>
    </div>
   </div></div>
  </div>
 </div>
</div><script>
(function(){
var WA='447848494680',ST={},SEQ=[],CUR=0,CFG=null;
var FD=(window.BP_FLOW_DEST&&window.BP_FLOW_DEST.dest)?window.BP_FLOW_DEST:null;
var PREF={p40:'£49 upfront, appointment booking only',p85:'£49 to start, then £249 for the full service',p140:'£49 to start, then £109 for the full service',notsure:'Not sure yet'};
var RLOOP={'Travel plan':'which part of my trip looked unconvincing?','Funds':'was it the money, or how I showed it?','Return':'how do I prove I will come back?','Insurance':'what cover do they actually require?','Documents':'which document did they distrust?','Overstay':'can a past overstay be explained away?','Not sure':'what actually went wrong? I can send the refusal letter.'};
var INT={
 eligibility:{seq:['country','purpose','tim','details','close'],wa:'I would like to check my eligibility for a Schengen visa.',cq:'Which country are you applying for?',head:'Tell us where to reach you.'},
 slots:{seq:['country','purpose','flex','tim','details','close'],wa:'which appointments are actually open for me right now?',cq:'Which country do you need a slot for?',head:'Tell us where to reach you.'},
 availability:{seq:['purpose','flex','tim','details','close'],wa:'are there any slots open for me right now?',cq:'Which country do you need a slot for?',head:'Tell us where to reach you.'},
 ready:{seq:['country','purpose','details','close'],wa:'I am ready to start - what is my first step?',cq:'Which country are you applying for?',head:'Tell us where to reach you.'},
 priority:{seq:['country','purpose','flex','tim','details','close'],wa:'my trip is close - is it still doable, and what has to happen now?',cq:'Which country are you applying for?',head:'Tell us where to reach you.'},
 timeline:{seq:['country','purpose','tim','details','close'],wa:'do my dates still leave enough time? When must I start?',cq:'Which country are you applying for?',head:'Tell us where to reach you.'},
 refused:{seq:['country','refreason','details','close'],wa:'I was refused before and need help.',cq:'Which country refused you?',head:'Where do we send your review?'},
 refusalpay:{seq:['plan','country','refreason','details','close'],plan:'refusal',wa:'I would like Refusal Recovery (£238 service fee, paid in full).',cq:'Which country refused you?',head:'Where do we send your review?'},
 price40:{seq:['plan','country','details','close'],plan:'start',wa:'I would like Slot Hunt (£49, you lock it).',preselect:'p40',cq:'Which country are you applying for?',head:'Where do we send your first step?'},
 price85:{seq:['plan','country','details','close'],plan:'prime',wa:'I would like Priority Concierge (£298, £49 to start).',preselect:'p85',cq:'Which country are you applying for?',head:'Where do we send your first step?'},
 price140:{seq:['plan','country','details','close'],plan:'full',wa:'I would like Full Service (£158, £49 to start).',preselect:'p140',cq:'Which country are you applying for?',head:'Where do we send your first step?'},
 find:{seq:['country','purpose','flex','tim','details','close'],wa:'which appointments are actually open for me right now?',cq:'Which Schengen country do you need?',head:'Tell us where to reach you.'}
};
var ISOM={Spain:'es',France:'fr',Italy:'it',Germany:'de',Greece:'gr'};
function $(id){return document.getElementById(id);}
function bars(){var b=$('mbars');b.innerHTML='';SEQ.forEach(function(_,i){var d=document.createElement('div');d.className='mbar'+(i<=CUR?' on':'');b.appendChild(d);});}
function realKey(k){if(k==='close')return (ST.pref==='notsure')?'closeCall':'closeWa';return k;}
function show(i){CUR=i;var raw=SEQ[i],k=realKey(raw);document.querySelectorAll('#mov .mstep').forEach(function(s){s.classList.toggle('on',s.dataset.k===k);});
 $('mback').classList.toggle('on',i>0&&raw!=='close');
 $('mpill').textContent=(raw==='close')?'All set':('Step '+(i+1)+' of '+(SEQ.length-1));
 var chip=$('mdest');if(ST.dest){chip.innerHTML=(ST.iso?'<img src="https://flagcdn.com/'+ST.iso+'.svg" alt="" style="width:18px;height:13px;border-radius:2px;box-shadow:0 0 0 1px rgba(0,0,0,.1)">':'🌍')+' '+ST.dest;chip.style.display='inline-flex';}else chip.style.display='none';
 if(raw==='plan'){mPlanFill();}
 if(raw==='details'){mDetailsFill();}
 if(raw==='close'){doClose();try{window.dataLayer=window.dataLayer||[];window.dataLayer.push({'event':'quiz_completed','page_key':(document.body&&document.body.dataset.page)||'unknown','page_path':location.pathname,'page_title':document.title,'quiz_flow':'modal'});}catch(e){}}
 var md=$('mtdiff');if(md){if(ST.dest&&ST.iso&&raw!=='country'&&raw!=='close'&&window.diffHTML){md.innerHTML=window.diffHTML(ST.iso);md.style.display='inline-flex';}else{md.style.display='none';md.innerHTML='';}}
 var mu=$('murg');if(mu){if(ST.dest&&ST.iso&&raw!=='country'&&raw!=='close'&&window.urgHTML){mu.innerHTML=window.urgHTML(ST.dest,ST.iso);mu.style.display='';}else{mu.style.display='none';mu.innerHTML='';}}
 bars();}
window.mOpen=function(intent,dest,iso){
 if(!INT[intent]){dest=intent;iso=ISOM[intent]||'';intent='availability';}
 CFG=INT[intent];ST={intent:intent,pref:null,plan:null,purpose:null,refreason:null,dest:null,iso:null,centre:null,dates:null,altc:null,tim:null};
 SEQ=CFG.seq.slice();
 if(!dest&&FD){dest=FD.dest;iso=FD.iso;}
 if(dest){ST.dest=dest;ST.iso=iso||'';var ci=SEQ.indexOf('country');if(ci>-1)SEQ.splice(ci,1);}
 if(CFG.preselect)ST.pref=CFG.preselect;
 if(CFG.plan)ST.plan=CFG.plan;
 $('mcq').textContent=CFG.cq||'Which country are you applying for?';
 var bn=$('mbanner');if(CFG.banner){bn.textContent=CFG.banner;bn.classList.add('on');}else bn.classList.remove('on');
 document.querySelectorAll('#mov .ptile').forEach(function(t){t.classList.toggle('sel',t.dataset.pref===ST.pref);});
 document.querySelectorAll('#mov .mtile').forEach(function(t){if(!t.classList.contains('ptile'))t.classList.remove('sel');});
 mCclose();$('mfname').value='';$('mfphone').value='';$('mename').style.display='none';$('mephone').style.display='none';
 show(0);$('mov').classList.add('open');document.body.style.overflow='hidden';};
window.mOpenAny=function(){mOpen('eligibility');};
window.mOpenSearch=function(){mOpen('find');setTimeout(function(){if(window.mCsearch)mCsearch();},40);};
window.mClose=function(){$('mov').classList.remove('open');document.body.style.overflow='';};
$('mov').addEventListener('click',function(e){if(e.target===this)mClose();});
document.addEventListener('keydown',function(e){if(e.key==='Escape')mClose();});
function next(){if(CUR<SEQ.length-1)show(CUR+1);}
window.mBack=function(){if(CUR>0)show(CUR-1);};
window.mPk=function(k,v){ST[k]=v;setTimeout(next,150);};
window.mRr=function(v){ST.refreason=v;setTimeout(next,150);};
window.mPref=function(v){ST.pref=v;document.querySelectorAll('#mov .ptile').forEach(function(t){t.classList.toggle('sel',t.dataset.pref===v);});setTimeout(next,180);};
window.mPickC=function(name,iso){ST.dest=name;ST.iso=iso;mCclose();setTimeout(next,150);};
window.mFlex=function(el,k,v){var g=el.parentNode;g.querySelectorAll('button').forEach(function(b){b.classList.remove('on');});el.classList.add('on');ST[k]=v;};
window.mCsearch=function(){$('mcgrid').classList.add('mhide');$('mcwrap').classList.remove('mhide');mCrender('');$('mcq2').focus();};
window.mCclose=function(){var w=$('mcwrap');if(w)w.classList.add('mhide');var g=$('mcgrid');if(g)g.classList.remove('mhide');var q=$('mcq2');if(q)q.value='';};
var SC=[["Austria","at"],["Belgium","be"],["Bulgaria","bg"],["Croatia","hr"],["Czechia","cz"],["Denmark","dk"],["Estonia","ee"],["Finland","fi"],["France","fr"],["Germany","de"],["Greece","gr"],["Hungary","hu"],["Iceland","is"],["Italy","it"],["Latvia","lv"],["Liechtenstein","li"],["Lithuania","lt"],["Luxembourg","lu"],["Malta","mt"],["Netherlands","nl"],["Norway","no"],["Poland","pl"],["Portugal","pt"],["Romania","ro"],["Slovakia","sk"],["Slovenia","si"],["Spain","es"],["Sweden","se"],["Switzerland","ch"]];
var MSLOT={fr:['open','Open'],de:['open','Open'],gr:['open','Open'],es:['lim','Filling'],it:['lim','Limited'],at:['open','Open'],be:['lim','Limited'],pt:['open','Open'],nl:['open','Open']};
window.mCrender=function(q){q=(q||'').toLowerCase();var ul=$('mclist');ul.innerHTML='';var m=SC.filter(function(c){return !q||c[0].toLowerCase().indexOf(q)>-1;});if(!m.length){ul.innerHTML='<li style="grid-column:1/-1;color:var(--muted);padding:8px">No match. Try another spelling.</li>';return;}m.forEach(function(c){var s=MSLOT[c[1]]||['chk','Check'];var li=document.createElement('li');li.innerHTML='<img class=flag src="https://flagcdn.com/'+c[1]+'.svg" alt=""><span class=cn2>'+c[0]+'</span><span class="cst2 '+s[0]+'"><span class=cdot2></span>'+s[1]+'</span>';li.onclick=function(){ST.dest=c[0];ST.iso=c[1];next();mCclose();};ul.appendChild(li);});};
window.mNext=function(){var nm=$('mfname');if(document.querySelector('#mov .mstep.on').dataset.k==='details'){var ok=true;if(!nm.value.trim()){nm.style.borderColor='#dc2626';$('mename').style.display='block';ok=false;}else{nm.style.borderColor='';$('mename').style.display='none';}var ps=(window.mPcStatus&&window.mPcStatus())||'bad';if(ps!=='ok'){$('mpc').style.borderColor='#dc2626';$('mephone').style.display='block';ok=false;}else{$('mpc').style.borderColor='';$('mephone').style.display='none';}if(!ok)return;}next();};
function msg(){var nm=$('mfname').value.trim(),ph=(window.mPcFull&&window.mPcFull())||'';var intro;if(ST.intent==='refused')intro='I was refused before and need help - '+(RLOOP[ST.refreason]||'what went wrong?');else if(ST.intent==='refusalpay')intro='I would like Refusal Recovery (£238 service fee, paid in full) - '+(RLOOP[ST.refreason]||'my refusal.');else intro=(CFG&&CFG.wa)||'I would like help with my Schengen visa.';var l=['Hi Beyond Passports, '+intro,''];
 if(ST.pref&&ST.pref!=='notsure')l.push('Preference: '+PREF[ST.pref]);
 if(ST.purpose)l.push('Travelling for: '+ST.purpose);
 if(ST.refreason)l.push('Refused on: '+ST.refreason);
 if(ST.dest)l.push('Destination: '+ST.dest);
 if(ST.ukvisa){l.push('UK visa/BRP: '+ST.ukvisa);if(ST.ukvisa==='Extension pending')l.push('Note: confirm UK status before booking travel');}
 if(ST.tim)l.push('Travelling: '+ST.tim);
 if(nm)l.push('Name: '+nm);if(ph)l.push('Number: '+ph);
 return l.join('\n');}
function mFillSum(id){var el=$(id);if(!el)return;var r=[];
 if(ST.pref&&ST.pref!=='notsure')r.push(['How to start',PREF[ST.pref]]);
 if(ST.purpose)r.push(['Travelling for',ST.purpose]);
 if(ST.refreason)r.push(['Refused on',ST.refreason]);
 if(ST.dest)r.push(['Destination',ST.dest]);
 if(ST.tim)r.push(['Travelling',ST.tim]);
 var h='';r.forEach(function(x){h+='<div class=row><span class=k>'+x[0]+'</span><span class=v>'+x[1]+'</span></div>';});el.innerHTML=h;}
var REFBASE={'Funds':3,'Travel plan':3,'Insurance':3,'Return':2,'Documents':2,'Overstay':1,'Not sure':0};
var REFMOD={straightforward:1,standard:0,strict:-1,high:-2};
var REFLEAN={it:'Italy leans on document and funds proof.',nl:'Netherlands leans on ties and purpose.',be:'Belgium applies high scrutiny across the board.',ch:'Switzerland is exact on funds and insurance.',fr:'France leans on a coherent, evidenced itinerary.',de:'Germany leans on ties and financial pattern.'};
var REFBIC={
 check:'<svg viewBox="0 0 24 24" fill=currentColor><path fill-rule=evenodd d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule=evenodd/></svg>',
 bolt:'<svg viewBox="0 0 24 24" fill=currentColor><path d="M13 2 4 14h6l-1 8 9-12h-6l1-8z"/></svg>',
 tri:'<svg viewBox="0 0 24 24" fill=currentColor><path fill-rule=evenodd d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003ZM12 8.25a.75.75 0 0 1 .75.75v3.75a.75.75 0 0 1-1.5 0V9a.75.75 0 0 1 .75-.75Zm0 8.25a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule=evenodd/></svg>',
 srch:'<svg viewBox="0 0 24 24" fill=none stroke=currentColor stroke-width=2.2 stroke-linecap=round stroke-linejoin=round><circle cx=11 cy=11 r="7"></circle><path d="M21 21l-4.3-4.3"/></svg>'
};
var REFCOPY={
 green:{badge:['g','Recoverable'],icon:'check',head:'Good news. This looks recoverable.',sub:'This type of refusal is usually about how things were shown, not whether you qualify. Add your number and a consultant confirms the fix.',verd:'<b>Looks recoverable.</b> A consultant confirms the exact fix in a quick review.',chip:['ok','Workable'],vcol:'#15803d'},
 amber:{badge:['a','Fixable with work'],icon:'bolt',head:'Your case needs a proper look.',sub:'This can often be turned around, but it needs stronger evidence. Add your number and we will tell you straight.',verd:'<b>Fixable in many cases, not all.</b> A consultant reviews what can be strengthened.',chip:['warn','Needs work'],vcol:'#b45309'},
 red:{badge:['r','High risk'],icon:'tri',head:'This one is harder. We will be honest.',sub:'Refusals on this ground are difficult to reverse. Add your number and we tell you if it is worth trying, no sales pitch.',verd:'<b>Higher risk.</b> We only proceed if there is a real path, and give you a straight answer first.',chip:['bad','High risk'],vcol:'#b91c1c'},
 assess:{badge:['s','Assessment needed'],icon:'srch',head:'Send us the letter and we will tell you.',sub:'We read your refusal letter and give you a straight read before anything else. Add your number and share it in chat.',verd:'<b>We assess first.</b> Then tell you honestly what can be done.',chip:['neutral','Read letter first'],vcol:'#64748b'}
};
function refTier(reason,iso){var base=REFBASE[reason];if(base===undefined||base===0)return 'assess';var c=window.CTRY&&window.CTRY[(iso||'').toLowerCase()];var mod=REFMOD[(c?c[1]:'standard')]||0;var s=Math.max(1,Math.min(3,base+mod));return s===3?'green':(s===2?'amber':'red');}
function refVIcon(col){return '<svg viewBox="0 0 24 24" fill=none stroke="'+col+'" stroke-width=2 stroke-linecap=round stroke-linejoin=round><circle cx=12 cy=12 r="9"></circle><path d="M12 8v4l3 2"/></svg>';}
function mDetailsFill(){
 var purpose=ST.purpose||'',ref=ST.refreason||'',dest=ST.dest||'',iso=ST.iso||'',tim=ST.tim||'';
 var badge=$('mdetbadge'),head=$('mdetq'),sub=$('mdetsub'),verd=$('mdetverdict'),snap=$('mdetsum');
 if(ref){
  var t=refTier(ref,iso),c=REFCOPY[t];
  if(badge){badge.style.display='none';}
  if(head)head.textContent=c.head;
  if(sub)sub.innerHTML=c.sub;
  if(verd)verd.innerHTML=refVIcon(c.vcol)+'<span>'+c.verd+'</span>';
  var rr=[['Refused on',ref,c.chip]];if(dest)rr.push(['Destination',dest,window.vSlot(iso)]);
  if(snap){snap.innerHTML=window.vSnap(rr,'Your refusal snapshot');
   var lean=REFLEAN[(iso||'').toLowerCase()];
   if((t==='amber'||t==='red')&&lean)snap.insertAdjacentHTML('beforeend','<div class=v-lean>'+PLINFO+lean+'</div>');}
  return;
 }
 var amber=/within 2 weeks/i.test(tim)||/complex/i.test(ST.intent||'');
 if(badge){badge.style.display='none';}
 if(head)head.textContent=amber?'Your case needs a human look.':'Good news. You look eligible.';
 if(sub)sub.innerHTML=amber
   ?'That is exactly the kind of case we handle. Add your number and we will tell you straight, no sales pitch.'
   :'You can apply for <b>'+(dest||'the Schengen area')+'</b> from the UK. Add your number and your consultant confirms today.';
 if(verd)verd.innerHTML=window.vVerdictInner(amber);
 var rows=[];
 if(purpose)rows.push(['Purpose',purpose,['ok','Eligible category']]);
 if(dest)rows.push(['Destination',dest,window.vSlot(iso)]);
 if(tim)rows.push(['Timeline',tim,window.vTime(tim)]);
 if(snap)snap.innerHTML=window.vSnap(rows);}
var PLAN={
 start:{badge:['teal','Just the appointment'],title:'Slot Hunt',tag:'Best if you are confident with your own paperwork.',now:'&pound;49<small> one-off</small>',then:'pay upfront<br><b>nothing more to us</b>',split:'Slot booking only. Government visa fees are separate.',inc:[['Live slot hunting across all centres','We watch every centre daily'],['We spot the slot the moment it opens','You get the confirmation'],['Full refund if no slot is found','No slot, no fee']],aftic:'bolt',aftt:'What &pound;49 gets you',aftx:'We hunt the slot and help you secure it. Your documents stay with you.',fine:'Slot booking only. Refunded if we cannot get a slot. Government fees are separate.'},
 prime:{badge:['','Priority'],title:'Priority Concierge',tag:'Everything in Full Service, done faster.',now:'&pound;49<small> today</small>',then:'then <b>&pound;249</b><br>for the full service',split:'&pound;298 service fee, split. Government visa fees are separate.',inc:[['Senior consultant handles it personally','Your case with our best'],['Documents ready within 48 hours','Fast turnaround'],['Priority slot hunting, morning and evening','Premium and standard tiers watched'],['Schengen insurance included (&euro;30,000)','Cover sorted for you'],['Priority refile if refused','We rebuild and resubmit']],aftic:'bolt',aftt:'Why Priority',aftx:'Senior consultant, 48-hour documents and premium slot hunting for a faster, calmer application.',fine:'&pound;298 service fee. Premium slot availability is not guaranteed. Government fees are separate.'},
 full:{badge:['teal','Most chosen'],title:'Full Service',tag:'Everything done for you, start to submission.',now:'&pound;49<small> today</small>',then:'then <b>&pound;109</b><br>for the full service',split:'&pound;158 service fee, split. Government visa fees are separate.',inc:[['Named consultant assigned to your case','One person, start to finish'],['Full application, every document checked','We build and check the file'],['Employer and cover letter written for you','Structured the way consulates expect'],['Submission support to appointment day','Right up to the appointment']],aftic:'check',aftt:'What &pound;49 unlocks today',aftx:'A named consultant opens your file and replies within 30 minutes.',fine:'&pound;158 service fee. Refunded if you don\'t get a slot. Government fees are separate.'},
 refusal:{badge:['','For a previous refusal'],title:'Refusal Recovery',tag:'We read the letter, find the cause, rebuild and reapply.',now:'&pound;238<small> in full</small>',then:'one payment<br><b>no second payment</b>',split:'&pound;238 service fee, one payment. Government visa fees are separate.',inc:[['We read your refusal letter','Find the exact reason you were refused'],['Rebuild the weak points','Funds, ties, travel plan, documents'],['Prepare and submit the reapplication','With a stronger case this time']],aftic:'shield',aftt:'Honest note',aftx:'A refusal does not decide the next one, but we cannot promise an approval. We fix what we can control.',fine:'&pound;238 service fee, refundable if we cannot help. No guaranteed outcome.'}
};
var PLCHK='<svg viewBox="0 0 24 24" fill=none stroke=currentColor stroke-width=2.6 stroke-linecap=round stroke-linejoin=round><path d="M20 6L9 17l-5-5"/></svg>';
var PLINFO='<svg viewBox="0 0 24 24" fill=currentColor style="width:13px;height:13px;color:#C5963A;flex:none"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm1 15h-2v-6h2v6Zm0-8h-2V7h2v2Z"/></svg>';
var PLIC={
 bolt:'<svg viewBox="0 0 24 24" fill=none stroke=currentColor stroke-width=2 stroke-linecap=round stroke-linejoin=round style="width:19px;height:19px;color:#C5963A;flex:none;margin-top:1px"><path d="M13 2 4 14h6l-1 8 9-12h-6l1-8z"/></svg>',
 check:'<svg viewBox="0 0 24 24" fill=none stroke=currentColor stroke-width=2 stroke-linecap=round stroke-linejoin=round style="width:19px;height:19px;color:#C5963A;flex:none;margin-top:1px"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>',
 shield:'<svg viewBox="0 0 24 24" fill=none stroke=currentColor stroke-width=2 stroke-linecap=round stroke-linejoin=round style="width:19px;height:19px;color:#C5963A;flex:none;margin-top:1px"><path d="M12 2 2 7v6c0 5 4 8 10 10 6-2 10-5 10-10V7L12 2Z"/></svg>'
};
function mPlanFill(){var p=PLAN[ST.plan]||PLAN.start;
 var b=$('mplbadge');b.className='mpl-badge '+(p.badge[0]||'');b.textContent=p.badge[1];
 $('mpltitle').textContent=p.title;$('mpltag').textContent=p.tag;
 $('mplnow').innerHTML=p.now;$('mplthen').innerHTML=p.then;
 $('mplsplit').innerHTML=PLINFO+p.split;
 var inc='';p.inc.forEach(function(x){inc+='<li>'+PLCHK+'<span>'+x[0]+'<small>'+x[1]+'</small></span></li>';});$('mplinc').innerHTML=inc;
 $('mplafter').innerHTML=PLIC[p.aftic]+'<span><b>'+p.aftt+'</b><span>'+p.aftx+'</span></span>';
 $('mplfine').innerHTML=p.fine;}
function doClose(){if(window.postLead){window.postLead($('mfname').value.trim(),(window.mPcFull&&window.mPcFull())||'',ST.dest||'',ST.intent||'case');}var text=encodeURIComponent(msg()),web=(window.bpWaUrl||String)('https://wa.me/'+WA+'?text='+text),app='whatsapp://send?phone='+WA+'&text='+text,mob=/Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
 if(ST.pref==='notsure'){mFillSum('mcallsum');var cw=$('mcallwa');if(cw)cw.href=web;return;}
 var nm=$('mfname').value.trim();$('msnthead').textContent=nm?("You're all set, "+nm+"."):"You're all set.";$('msntwa').href=web;mFillSum('msntsum');
 var c=3,el=document.querySelector('#msntcnt b'),w=false;function go(){if(w)return;w=true;if(mob){try{location.href=app;}catch(e){}setTimeout(function(){location.href=web;},1500);}else{window.open(web,'_blank');}}
 var t=setInterval(function(){c--;if(el)el.textContent=c<0?0:c;if(c<=0){clearInterval(t);go();}},1000);setTimeout(go,3300);}
var FB='https://beyondpassports.co.uk/assets/flags/';
var C=[["United Kingdom","GB","+44",[10,10]],["Ireland","IE","+353",[7,9]],["France","FR","+33",[9,9]],["Germany","DE","+49",[10,11]],["Spain","ES","+34",[9,9]],["Italy","IT","+39",[9,10]],["Greece","GR","+30",[10,10]],["Netherlands","NL","+31",[9,9]],["Portugal","PT","+351",[9,9]],["Poland","PL","+48",[9,9]],["India","IN","+91",[10,10]],["Pakistan","PK","+92",[10,10]],["Nigeria","NG","+234",[8,10]],["United States","US","+1",[10,10]],["UAE","AE","+971",[9,9]]];
var pc=$('mpc'),btn=$('mpcbtn'),pop=$('mpcpop'),flag=$('mpcflag'),dc=$('mpcdc'),inp=$('mfphone'),cur=C[0];
pop.innerHTML='<input id=mpcs placeholder="Search" style="width:100%;padding:9px 11px;border:1.5px solid var(--border);border-radius:9px;font:600 13px var(--sans);margin-bottom:6px"><ul id=mpcl style="list-style:none;max-height:200px;overflow:auto;margin:0;padding:0"></ul>';
var s=pop.querySelector('#mpcs'),ul=pop.querySelector('#mpcl');
function fl(c){return '<img src="'+FB+c[1].toLowerCase()+'.svg" width=20 height=15 alt="" style="border-radius:2px">';}
function paint(c){cur=c;flag.src=FB+c[1].toLowerCase()+'.svg';dc.textContent=c[2];}
function rnd(q){q=(q||'').toLowerCase();ul.innerHTML='';C.filter(function(c){return !q||c[0].toLowerCase().indexOf(q)>-1||c[2].indexOf(q)>-1;}).forEach(function(c){var li=document.createElement('li');li.style.cssText='display:flex;gap:9px;align-items:center;padding:8px 9px;border-radius:8px;cursor:pointer;font:600 13.5px var(--sans);color:var(--navy)';li.innerHTML=fl(c)+'<span style="flex:1">'+c[0]+'</span><span style="color:var(--muted)">'+c[2]+'</span>';li.onmousedown=function(e){e.preventDefault();paint(c);pop.hidden=true;inp.focus();};ul.appendChild(li);});}
btn.onclick=function(e){e.stopPropagation();pop.hidden=!pop.hidden;if(!pop.hidden){rnd('');setTimeout(function(){s.focus();},20);}};
s.addEventListener('input',function(){rnd(this.value);});document.addEventListener('click',function(e){if(!pc.contains(e.target))pop.hidden=true;});paint(cur);
window.mPcStatus=function(){var r=(inp.value||'').trim();if(!r)return 'req';var d=r.replace(/[^0-9]/g,'').replace(/^0+/,''),rg=cur[3];return d.length>=rg[0]&&d.length<=rg[1]?'ok':'bad';};
window.mPcFull=function(){var v=(inp.value||'').trim();if(!v)return '';return v.charAt(0)==='+'?v:cur[2]+' '+v.replace(/[^0-9]/g,'').replace(/^0+/,'');};
})();
</script>
<script>
(function(){
 var CT={es:['filling','standard'],fr:['open','straightforward'],it:['limited','strict'],de:['open','standard'],gr:['open','straightforward'],nl:['filling','strict'],pt:['open','straightforward'],be:['limited','high'],at:['check','standard'],ch:['waitlist','strict']};
 window.CTRY=window.CTRY||CT;
 var S={
  open:['linear-gradient(100deg,rgba(197,150,58,.1),rgba(197,150,58,.03))','rgba(197,150,58,.28)','#C5963A','#5c410f','#8a621f','<svg viewBox="0 0 24 24" fill=none stroke=#fff stroke-width=2.6 stroke-linecap=round stroke-linejoin=round style="width:10px;height:10px"><path d="M20 6L9 17l-5-5"/></svg>',': good availability now','Best slots go early. Start while dates are open.'],
  filling:['linear-gradient(100deg,rgba(245,158,11,.12),rgba(245,158,11,.04))','rgba(245,158,11,.28)','#B45309','#7c3d06','#b45309','<svg viewBox="0 0 24 24" fill=none stroke=#fff stroke-width=2.2 stroke-linecap=round stroke-linejoin=round style="width:10px;height:10px"><path d="M6 3h12M6 21h12M7 3c0 5 10 5 10 0M7 21c0-5 10-5 10 0"/></svg>',': appointments filling fast','If your trip is in the next 8 weeks, do not wait.'],
  limited:['linear-gradient(100deg,rgba(245,158,11,.12),rgba(245,158,11,.04))','rgba(245,158,11,.28)','#B45309','#7c3d06','#b45309','<svg viewBox="0 0 24 24" fill=#fff style="width:10px;height:10px"><path d="M13 2 4 14h6l-1 8 9-12h-6l1-8z"/></svg>',': limited slots left','If your trip is in the next 8 weeks, do not wait.'],
  check:['#f6f9fb','#E2E8F0','#64748b','#0B1528','#5b6b7d','<svg viewBox="0 0 24 24" fill=none stroke=#fff stroke-width=2.4 stroke-linecap=round stroke-linejoin=round style="width:10px;height:10px"><circle cx=11 cy=11 r=7/><path d="M21 21l-4.3-4.3"/></svg>',': live availability check','Tell us your dates and we confirm fast.'],
  waitlist:['rgba(11,21,40,.04)','rgba(11,21,40,.12)','#0B1528','#0B1528','#5b6b7d','<svg viewBox="0 0 24 24" fill=none stroke=#fff stroke-width=2.2 stroke-linecap=round stroke-linejoin=round style="width:10px;height:10px"><path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v4h4"/></svg>',': currently by waitlist','We alert you the moment a slot opens.']
 };
 var D={
  straightforward:['rgba(22,163,74,.1)','rgba(22,163,74,.25)','#166534','<svg viewBox="0 0 24 24" fill=currentColor style="width:13px;height:13px"><path d="M7 10v10H3V10h4zm3.5 0 3-6.5c.7-1.5 3-1 3 .8V8h3.3c1.3 0 2.2 1.2 1.9 2.4l-1.4 6.2c-.2 1-1.1 1.6-2.1 1.6H10.5V10z"/></svg>','Usually smooth when documents are in order'],
  standard:['#f1f5f9','#E2E8F0','#5b6b7d','<svg viewBox="0 0 24 24" fill=currentColor style="width:13px;height:13px"><path fill-rule=evenodd d="M5.625 1.5c-1.036 0-1.875.84-1.875 1.875v17.25c0 1.035.84 1.875 1.875 1.875h12.75c1.035 0 1.875-.84 1.875-1.875V12.75A3.75 3.75 0 0 0 16.5 9h-1.875a1.875 1.875 0 0 1-1.875-1.875V5.25A3.75 3.75 0 0 0 9 1.5H5.625ZM7.5 15a.75.75 0 0 1 .75-.75h7.5a.75.75 0 0 1 0 1.5h-7.5A.75.75 0 0 1 7.5 15Zm.75 2.25a.75.75 0 0 0 0 1.5H12a.75.75 0 0 0 0-1.5H8.25Z" clip-rule=evenodd/><path d="M12.971 1.816A5.23 5.23 0 0 1 14.25 5.25v1.875c0 .207.168.375.375.375H16.5a5.23 5.23 0 0 1 3.434 1.279 9.768 9.768 0 0 0-6.963-6.963Z"/></svg>','Standard checks. Details matter'],
  strict:['rgba(180,83,9,.1)','rgba(180,83,9,.28)','#b45309','<svg viewBox="0 0 24 24" fill=currentColor style="width:13px;height:13px"><path fill-rule=evenodd d="M12.516 2.17a.75.75 0 0 0-1.032 0 11.209 11.209 0 0 1-7.877 3.08.75.75 0 0 0-.722.515A12.74 12.74 0 0 0 2.25 9.75c0 5.942 4.064 10.933 9.563 12.348a.749.749 0 0 0 .374 0c5.499-1.415 9.563-6.406 9.563-12.348 0-1.39-.223-2.73-.635-3.985a.75.75 0 0 0-.722-.516l-.143.001c-2.996 0-5.717-1.17-7.734-3.08Zm3.094 8.016a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule=evenodd/></svg>','Strict on funds and documents. Common refusal points'],
  high:['rgba(220,38,38,.09)','rgba(220,38,38,.28)','#b91c1c','<svg viewBox="0 0 24 24" fill=currentColor style="width:13px;height:13px"><path fill-rule=evenodd d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003ZM12 8.25a.75.75 0 0 1 .75.75v3.75a.75.75 0 0 1-1.5 0V9a.75.75 0 0 1 .75-.75Zm0 8.25a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule=evenodd/></svg>','Higher refusal risk. Expert prep advised']
 };
 window.urgHTML=window.urgHTML||function(name,iso){
  iso=(iso||'').toLowerCase();var c=CT[iso]||['check','standard'];var s=S[c[0]]||S.check;
  return '<div style="display:flex;gap:11px;align-items:center;border-radius:13px;padding:11px 13px;background:'+s[0]+';border:1px solid '+s[1]+'">'
   +'<span style="position:relative;flex:none"><img src="https://flagcdn.com/'+iso+'.svg" alt="" style="width:36px;height:36px;border-radius:10px;object-fit:cover;box-shadow:0 6px 14px -6px rgba(11,21,40,.5),0 0 0 1px rgba(0,0,0,.08)"><span style="position:absolute;bottom:-4px;right:-4px;width:18px;height:18px;border-radius:50%;border:2px solid #fff;display:flex;align-items:center;justify-content:center;background:'+s[2]+'">'+s[5]+'</span></span>'
   +'<span><b style="display:block;font:800 12px Inter,sans-serif;color:'+s[3]+'">'+name+s[6]+'</b><span style="display:block;font:600 11px Inter,sans-serif;color:'+s[4]+';margin-top:2px;line-height:1.4">'+s[7]+'</span></span></div>';
 };
 window.diffHTML=window.diffHTML||function(iso){
  iso=(iso||'').toLowerCase();var c=CT[iso]||['check','standard'];var d=D[c[1]]||D.standard;
  return '<span style="display:inline-flex;align-items:center;gap:6px;font:700 10.5px Inter,sans-serif;border-radius:999px;padding:5px 10px;background:'+d[0]+';border:1px solid '+d[1]+';color:'+d[2]+'">'+d[3]+d[4]+'</span>';
 };
 var VIC={
  ok:'<svg viewBox="0 0 24 24" fill=none stroke=currentColor stroke-width=3 stroke-linecap=round stroke-linejoin=round><path d="M20 6L9 17l-5-5"/></svg>',
  warn:'<svg viewBox="0 0 24 24" fill=currentColor><path d="M13 2 4 14h6l-1 8 9-12h-6l1-8z"/></svg>',
  bad:'<svg viewBox="0 0 24 24" fill=currentColor><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm-1 5h2v6h-2V7Zm0 8h2v2h-2v-2Z"/></svg>',
  neutral:'<svg viewBox="0 0 24 24" fill=none stroke=currentColor stroke-width=2.2 stroke-linecap=round stroke-linejoin=round><circle cx=11 cy=11 r="7"></circle><path d="M21 21l-4.3-4.3"/></svg>'
 };
 window.vChip=window.vChip||function(c){return c&&c[1]?'<span class="v-chip '+c[0]+'">'+(VIC[c[0]]||'')+c[1]+'</span>':'';};
 window.vSlot=window.vSlot||function(iso){var c=CT[(iso||'').toLowerCase()];var s=c?c[0]:'check';
  if(s==='open')return['ok','Appointments open'];
  if(s==='filling'||s==='limited')return['warn','Book soon'];
  if(s==='waitlist')return['neutral','Waitlist'];
  return['neutral','Live check'];};
 window.vTime=window.vTime||function(t){t=t||'';
  if(/within 2 weeks/i.test(t))return['bad','Very tight'];
  if(/2 to 4 weeks/i.test(t))return['warn','Tight, act now'];
  if(/1 to 3 months/i.test(t))return['ok','Good runway'];
  if(/planning/i.test(t))return['ok','Plenty of time'];
  return['neutral',''];};
 window.vSnap=window.vSnap||function(rows,title){var h='<div class=v-snaphd>'+(title||'Your eligibility snapshot')+' <small>provisional</small></div>';
  rows.forEach(function(r){if(!r[1])return;h+='<div class=v-row><span class=kv><span class=k>'+r[0]+'</span><span class=v>'+r[1]+'</span></span>'+window.vChip(r[2])+'</div>';});
  return h;};
 window.vVerdictInner=window.vVerdictInner||function(amber){return amber
   ? '<svg viewBox="0 0 24 24" fill=none stroke="#b45309" stroke-width=2 stroke-linecap=round stroke-linejoin=round><circle cx=12 cy=12 r="9"><path d="M12 8v4l3 2"/></svg><span><b>We can still help.</b> A consultant reviews complex and urgent cases first, subject to a quick review.</span>'
   : '<svg viewBox="0 0 24 24" fill=none stroke="#15803d" stroke-width=2 stroke-linecap=round stroke-linejoin=round><circle cx=12 cy=12 r="9"><path d="M12 8v4l3 2"/></svg><span><b>Looks eligible.</b> Final check takes 2 minutes with your consultant, subject to a quick review.</span>';};
})();
</script>
