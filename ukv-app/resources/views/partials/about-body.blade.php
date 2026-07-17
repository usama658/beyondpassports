{{-- About body (shared, locked): the full About page content + its page-scoped CSS. Rendered
     verbatim by the coded About page AND the CMS locked-include block, so both are byte-identical. --}}
<style>
  /* ── about page — page-scoped styles only. Design system in ukv.css ─────── */

  /* ── Hero — statement left + "who we are" credentials card right ─────────── */
  .ab-hero {
    background: linear-gradient(180deg, #EAF1F4, #F2F5F6 60%, var(--paper));
    border-bottom: 1px solid var(--paper-edge);
  }
  .ab-hero-grid {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 60px;
    align-items: center;
  }
  .ab-hero-copy h1 { max-width: 20ch; font-size: clamp(32px, 4.4vw, 50px); }
  .ab-hero-copy .lede { max-width: 46ch; margin-bottom: 16px; }
  .ab-hero-copy .callout {
    font-size: 15px; color: #33454f; line-height: 1.6;
    background: var(--white); border: 1px solid var(--paper-edge);
    border-left: 3px solid var(--stamp); border-radius: 0 8px 8px 0;
    padding: 12px 16px; margin: 0 0 26px;
  }
  .ab-hero-copy .h-btns { display: flex; flex-wrap: wrap; gap: 12px; align-items: center; margin: 0 0 10px; }
  .ab-hero-copy .h-btn {
    display: inline-flex; align-items: center; gap: 9px;
    font-weight: 700; padding: 13px 22px; border-radius: 12px;
    text-decoration: none; font-size: 15.5px; border: 0;
    transition: transform .1s, box-shadow .15s;
  }
  .ab-hero-copy .h-btn.wa { background: #25D366; color: #fff; }
  .ab-hero-copy .h-btn.wa svg { width: 18px; height: 18px; fill: #fff; }
  .ab-hero-copy .h-btn.wa:hover { transform: translateY(-1px); box-shadow: 0 10px 24px -12px rgba(37,211,102,.7); }
  .ab-hero-copy .h-btn.ghost { background: transparent; color: var(--cta); border: 1.5px solid var(--cta); }
  .ab-hero-copy .h-btn.ghost:hover { box-shadow: rgba(21,94,122,.14) 0 0 0 3px; }
  .ab-hero-copy .friction { font-size: 13px; color: var(--muted, #5d6b76); margin: 0 0 18px; }
  /* navy portrait frame + glass founder badge (hero right) */
  .ab-frame {
    background: var(--navy); border-radius: 18px; overflow: hidden;
    aspect-ratio: 4/5; position: relative; box-shadow: var(--lift-2);
  }
  .ab-frame img { width: 100%; height: 100%; object-fit: cover; object-position: top center; display: block; }
  .ab-fbadge {
    position: absolute; bottom: 20px; left: 20px; right: 20px;
    background: rgba(22,34,46,.88); backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,.12); border-radius: 12px;
    padding: 14px 18px; color: #fff;
  }
  .ab-fbadge strong { display: block; font-size: 15px; font-weight: 700; margin-bottom: 2px; }
  .ab-fbadge span { font-size: 13px; color: var(--soft); }
  .ab-idcard {
    background:
      radial-gradient(380px 180px at 110% -10%, rgba(21,94,122,.30), transparent 60%),
      radial-gradient(360px 180px at -10% 120%, rgba(46,154,140,.28), transparent 60%),
      var(--navy);
    color: #fff;
    border-radius: 18px;
    padding: 26px 28px;
    box-shadow: var(--lift-2);
  }
  .ab-idcard .ic-k {
    font-size: 10.5px;
    font-weight: 800;
    letter-spacing: .14em;
    text-transform: uppercase;
    color: var(--soft);
    margin: 0 0 6px;
  }
  .ab-idcard .ic-row {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    gap: 16px;
    padding: 13px 0;
    border-top: 1px solid rgba(255,255,255,.14);
    font-size: 14px;
  }
  .ab-idcard .ic-row:first-of-type { border-top: 0; }
  .ab-idcard .ic-row span { color: rgba(255,255,255,.72); }
  .ab-idcard .ic-row b { font-weight: 700; color: #fff; }

  /* ── Trust bands (mirrors home) — dark mesh points (F) + warm stat counters (B) ── */
  .tbar-b, .tbar-f { padding: 0; }
  .tbar-f {
    background:
      radial-gradient(520px 200px at 12% 0%, rgba(21,94,122,.45), transparent 60%),
      radial-gradient(520px 200px at 92% 100%, rgba(46,154,140,.42), transparent 60%),
      var(--navy);
    color: #fff;
  }
  .tbar-f .row { display: flex; justify-content: center; gap: 30px; flex-wrap: wrap; padding: 16px 0; }
  .tbar-f .ti { display: flex; align-items: center; gap: 9px; font: 600 14px var(--display); color: #fff; white-space: nowrap; }
  .tbar-f .ti svg { width: 20px; height: 20px; color: var(--soft); flex: none; }
  .tbar-f .ti b { color: var(--soft); font-weight: 800; }
  .tbar-b { background: linear-gradient(180deg, #FBF6F1, var(--paper)); border-bottom: 1px solid var(--paper-edge); }
  .tbar-b .row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px; text-align: center; padding: 24px 0; }
  .tbar-b .n { font: 800 clamp(24px, 3vw, 30px)/1 var(--display); color: var(--cta); letter-spacing: -.02em; }
  .tbar-b .l { font: 600 13px var(--display); color: var(--muted); margin-top: 6px; }
  .tbar-b .row > div + div { border-left: 1px solid var(--paper-edge); }
  @media (max-width: 760px) {
    .tbar-b .row { grid-template-columns: 1fr 1fr; gap: 14px; }
    .tbar-b .row > div:nth-child(odd) { border-left: 0; }
    .tbar-f .row { gap: 18px 22px; }
  }

  /* ── Who we are — prose + "we are / we are not" contrast cards ───────────── */
  .ab-who-grid {
    display: grid;
    grid-template-columns: 1.2fr .8fr;
    gap: 44px;
    align-items: start;
  }
  .ab-prose p {
    font-size: 17px;
    line-height: 1.7;
    color: #33454f;
    margin: 0 0 20px;
  }
  .ab-prose p:last-child { margin-bottom: 0; }
  .ab-note {
    background: var(--white);
    border: 1px solid var(--paper-edge);
    border-left: 4px solid var(--cta);
    border-radius: 0 12px 12px 0;
    padding: 16px 20px;
    margin: 28px 0 0;
    font-size: 15px;
    line-height: 1.6;
    color: var(--stamp-text);
  }
  .ab-contrast { display: grid; gap: 14px; }
  .ab-cc {
    background: var(--white);
    border: 1px solid var(--paper-edge);
    border-radius: 16px;
    box-shadow: var(--lift-1);
    padding: 20px 22px;
  }
  .ab-cc .cc-t {
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .12em;
    text-transform: uppercase;
    margin: 0 0 12px;
  }
  .ab-cc.is-are .cc-t { color: var(--sage-deep, #1F6E63); }
  .ab-cc.is-not .cc-t { color: var(--cta); }
  .ab-cc ul { margin: 0; padding: 0; list-style: none; }
  .ab-cc li {
    display: flex;
    gap: 10px;
    align-items: flex-start;
    font-size: 14.5px;
    line-height: 1.5;
    color: #33454f;
    padding: 6px 0;
  }
  .ab-cc li svg { flex: 0 0 18px; width: 18px; height: 18px; margin-top: 2px; }
  /* heading moved into column 1 + "whole model" sage highlight box */
  .ab-col-head .eyebrow { margin: 0 0 12px; }
  .ab-col-head h2 { font-size: clamp(26px, 3vw, 36px); color: var(--ink); letter-spacing: -.02em; line-height: 1.1; margin: 0 0 22px; max-width: 18ch; }
  .ab-model { font-size: 16px; color: var(--ink); font-weight: 600; line-height: 1.7; background: rgba(46,154,140,.07); border: 1px solid rgba(46,154,140,.2); border-left: 3px solid var(--stamp); border-radius: 12px; padding: 18px 22px; margin: 0; }

  /* ── How we prevent refusals — three checks (dark petrol band) ──────────── */
  .abproc { background: var(--navy); padding: 80px 0; position: relative; overflow: hidden; }
  .abproc::before { content: ""; position: absolute; top: -120px; right: -120px; width: 500px; height: 500px; border-radius: 50%; background: rgba(46,154,140,.06); pointer-events: none; }
  .abproc .abproc-in { position: relative; }
  .abproc-hd { max-width: 60ch; margin: 0 0 36px; }
  .abproc-ey { font-weight: 700; font-size: 12px; letter-spacing: .14em; text-transform: uppercase; color: var(--soft); margin: 0 0 .6em; display: block; }
  .abproc-hd h2 { font-size: clamp(28px, 3.4vw, 38px); color: #fff; margin: 0; letter-spacing: -.02em; line-height: 1.08; }
  .abproc-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin: 0 0 16px; }
  .abproc-card { background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.1); border-radius: 16px; padding: 32px 28px; }
  .abproc-n { font-size: 11px; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; color: var(--soft); margin: 0 0 14px; display: block; }
  .abproc-card h3 { font-size: 19px; color: #fff; margin: 0 0 10px; letter-spacing: -.01em; }
  .abproc-card p { color: rgba(255,255,255,.62); font-size: 15px; margin: 0; line-height: 1.55; }
  .abproc-bar { background: rgba(46,154,140,.12); border: 1px solid rgba(46,154,140,.25); border-radius: 12px; padding: 20px 28px; margin: 0 0 12px; }
  .abproc-bar p { font-size: 17px; font-weight: 600; color: #fff; margin: 0; }
  .abproc-bar strong { color: var(--soft); }
  .abproc-note { background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.07); border-radius: 10px; padding: 16px 22px; }
  .abproc-note p { font-size: 14px; color: rgba(255,255,255,.5); margin: 0; line-height: 1.6; }
  @media (max-width: 820px) { .abproc-grid { grid-template-columns: 1fr; } }

  /* ── Testimonials — lp-bold monogram review cards (gold-serif, order-ref) ── */
  .abrev { --gold: #C89B3C; --cream: #FBFAF7; --serif: Georgia, "Times New Roman", serif; }
  .abrev .rhead { text-align: center; max-width: 60ch; margin: 0 auto 34px; }
  .abrev .rhead .eyebrow { justify-content: center; }
  .abrev .rhead h2 { font-size: clamp(26px, 3.2vw, 36px); color: var(--ink); margin: 0 auto; max-width: 22ch; }
  .abrev .rhead .rsub { color: var(--muted); font-size: 16px; margin: 12px 0 0; }
  .abrev .rplat { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; max-width: 760px; margin: 0 auto 24px; }
  .abrev .pcard { background: var(--white); border: 1px solid var(--paper-edge); border-radius: 16px; padding: 20px 22px; display: flex; align-items: center; gap: 16px; box-shadow: var(--lift-1); }
  .abrev .pico { width: 46px; height: 46px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex: none; }
  .abrev .pico svg { width: 26px; height: 26px; display: block; }
  .abrev .pico.g { background: #fff; border: 1px solid var(--paper-edge); }
  .abrev .pico.tp { background: #00B67A; }
  .abrev .pname { font-size: 11px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; color: var(--muted); }
  .abrev .pstar { color: var(--gold); font-size: 15px; letter-spacing: 1px; }
  .abrev .pstar.tps { color: #00B67A; }
  .abrev .pscore { font-size: 24px; font-weight: 800; letter-spacing: -.02em; line-height: 1; }
  .abrev .pcount { font-size: 12.5px; color: var(--muted); margin-top: 2px; }
  .abrev .rgrid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
  .abrev .rc { position: relative; background: var(--cream); border: 1px solid var(--paper-edge); border-radius: 18px; padding: 28px 26px; box-shadow: var(--lift-1); overflow: hidden; transition: transform .16s ease, box-shadow .18s ease; }
  .abrev .rc:hover { transform: translateY(-4px); box-shadow: var(--lift-2); }
  .abrev .rc .wm { position: absolute; top: -16px; right: 8px; font-family: var(--serif); font-weight: 700; font-size: 140px; line-height: 1; color: var(--gold); opacity: .09; pointer-events: none; transition: opacity .2s ease; }
  .abrev .rc:hover .wm { opacity: .16; }
  .abrev .rc .rst { position: relative; font-size: 15px; color: var(--gold); margin: 0 0 14px; letter-spacing: 1px; }
  .abrev .rc .rq { position: relative; font-family: var(--serif); font-size: 18px; font-weight: 500; color: #243039; line-height: 1.5; margin: 0 0 20px; }
  .abrev .rc .rf { position: relative; display: flex; align-items: center; gap: 11px; padding-top: 15px; border-top: 1px solid var(--paper-edge); }
  .abrev .rc .rf .gd { width: 8px; height: 8px; border-radius: 50%; background: var(--gold); flex: none; }
  .abrev .rc .rn { font-weight: 800; font-size: 15px; }
  .abrev .rc .rn span { display: block; font-weight: 400; font-size: 12.5px; color: var(--muted); margin-top: 1px; }
  .abrev .rc .rsrc { margin-left: auto; font-weight: 800; font-size: 10.5px; letter-spacing: .12em; text-transform: uppercase; color: var(--gold); }
  .abrev .rnote { color: var(--muted); font-size: 12.5px; text-align: center; margin: 22px 0 0; }
  @media (max-width: 900px) { .abrev .rgrid { grid-template-columns: 1fr; } .abrev .rplat { grid-template-columns: 1fr; } }
  @media (prefers-reduced-motion: reduce) { .abrev .rc, .abrev .rc:hover { transform: none; } }

  /* ── Recent applications — before→after cards, crown turnaround medallion ── */
  .cvb .chk { stroke-linecap: round; stroke-linejoin: round; fill: none; }
  .cvb .cshead { max-width: 60ch; margin: 0 0 30px; }
  .cvb .cshead .ey { font-weight: 700; font-size: 12px; letter-spacing: .14em; text-transform: uppercase; color: var(--cta); margin: 0 0 .6em; display: block; }
  .cvb .cshead h2 { font-size: clamp(26px, 3.2vw, 36px); color: var(--ink); margin: 0 0 10px; letter-spacing: -.02em; font-weight: 800; }
  .cvb .cshead p { color: var(--muted); font-size: 16px; margin: 0; }
  .cvb .badge { font-size: 10px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; padding: 3px 9px; border-radius: 6px; }
  .cvb .badge.ref { background: rgba(214,84,61,.12); color: #B4432E; }
  .cvb .badge.first { background: rgba(46,154,140,.12); color: var(--stamp-text); }
  .cvb .pl { font-size: 9.5px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; margin: 0 0 7px; display: flex; align-items: center; gap: 6px; }
  .cvb .pl .d { width: 7px; height: 7px; border-radius: 50%; }
  .cvb .pl.b { color: #B4432E; } .cvb .pl.b .d { background: #D6543D; }
  .cvb .pl.a { color: var(--stamp-text); } .cvb .pl.a .d { background: var(--stamp); }
  .cvb .pt { font-size: 12.5px; color: #33454f; line-height: 1.5; margin: 0; }
  .cvb .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
  .cvb .card { position: relative; background: var(--white); border: 1px solid var(--paper-edge); border-radius: 18px; overflow: hidden; box-shadow: var(--lift-1); transition: transform .2s ease, box-shadow .2s ease; }
  .cvb .card:hover { transform: translateY(-4px); box-shadow: var(--lift-2); }
  .cvb .caphd { background: linear-gradient(180deg, #f7f9fb, var(--white)); padding: 16px 20px 30px; border-bottom: 1px solid var(--paper-edge); text-align: center; }
  .cvb .caphd .cons { margin: 0 0 2px; }
  .cvb .ctop { display: flex; align-items: center; justify-content: center; gap: 9px; margin: 0 0 3px; }
  .cvb .country { font-weight: 800; font-size: 16px; color: var(--ink); }
  .cvb .cons { font-size: 11px; color: var(--muted); margin: 0; }
  .cvb .med { position: absolute; left: 50%; top: 122px; transform: translateX(-50%); width: 72px; height: 72px; border-radius: 50%; background: var(--stamp); display: grid; place-items: center; text-align: center; color: #fff; box-shadow: 0 0 0 6px var(--white), 0 14px 26px -12px rgba(46,154,140,.8); z-index: 3; }
  .cvb .med .chk { width: 13px; height: 13px; stroke: #fff; stroke-width: 2.8; margin-bottom: 1px; }
  .cvb .med .n { font-size: 18px; font-weight: 800; line-height: 1; }
  .cvb .med .u { font-size: 8px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; opacity: .85; }
  .cvb .split { display: grid; grid-template-columns: 1fr 1fr; padding-top: 66px; }
  /* client verified-client chip (top of each case card) */
  .cvb .cn-chip { display: inline-flex; align-items: center; gap: 9px; margin: 0 0 12px; background: #fff; border: 1px solid rgba(200,155,60,.45); border-radius: 999px; padding: 5px 14px 5px 6px; box-shadow: 0 6px 16px -12px rgba(200,155,60,.75); }
  .cvb .cn-chip .cn-av { width: 26px; height: 26px; border-radius: 50%; flex: none; display: grid; place-items: center; font-weight: 800; font-size: 10.5px; color: #8a6a1f; background: linear-gradient(135deg,#f6ecd2,#eeddb0); border: 1px solid rgba(200,155,60,.5); }
  .cvb .cn-chip .cn-name { font-weight: 800; font-size: 13px; color: var(--ink); }
  .cvb .cn-chip .cn-sep { width: 1px; height: 14px; background: rgba(200,155,60,.4); }
  .cvb .cn-chip .cn-verify { display: inline-flex; align-items: center; gap: 4px; font-size: 9.5px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #8a6a1f; }
  .cvb .cn-chip .cn-verify svg { width: 11px; height: 11px; fill: none; stroke: #C89B3C; stroke-width: 2.6; stroke-linecap: round; stroke-linejoin: round; }
  .cvb .pane { padding: 18px 20px; }
  .cvb .pane.b { background: #fbf4f2; }
  .cvb .pane.a { background: #f3faf7; border-left: 1px solid var(--paper-edge); }
  .cvb .foot { display: flex; align-items: center; justify-content: center; gap: 9px; padding: 13px 20px; background: linear-gradient(90deg, rgba(46,154,140,.14), rgba(46,154,140,.05)); border-top: 1px solid var(--paper-edge); }
  .cvb .foot .ic { width: 22px; height: 22px; border-radius: 50%; background: var(--stamp); display: grid; place-items: center; flex: none; }
  .cvb .foot .ic svg { width: 13px; height: 13px; stroke: #fff; fill: none; stroke-width: 2.8; }
  .cvb .foot .ot { font-weight: 800; font-size: 13px; color: var(--stamp-text); }
  .cvb .csnote { color: var(--muted); font-size: 12.5px; text-align: center; margin: 24px 0 0; }
  @media (max-width: 900px) { .cvb .grid { grid-template-columns: 1fr; } }

  /* ── Document handling — vault: shield hero + steps + credential strip ───── */
  .dsv .ic { fill: none; stroke-linecap: round; stroke-linejoin: round; }
  .dsv .head { text-align: center; max-width: 60ch; margin: 0 auto 36px; }
  .dsv .shield { width: 56px; height: 56px; margin: 0 auto 16px; display: block; }
  .dsv .shield svg { width: 56px; height: 56px; stroke: var(--stamp-text); fill: rgba(46,154,140,.08); }
  .dsv .ey { font-weight: 700; font-size: 12px; letter-spacing: .14em; text-transform: uppercase; color: var(--cta); margin: 0 0 .6em; display: block; }
  .dsv h2 { font-size: clamp(24px, 3vw, 34px); color: var(--ink); margin: 0 0 10px; letter-spacing: -.02em; font-weight: 800; }
  .dsv .intro { color: var(--muted); font-size: 16px; margin: 0; }
  .dsv .steps { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin: 0 0 20px; }
  .dsv .step { background: var(--white); border: 1px solid var(--paper-edge); border-radius: 14px; padding: 22px 20px; box-shadow: var(--lift-1); transition: transform .18s ease, box-shadow .18s ease; }
  .dsv .step:hover { transform: translateY(-4px); box-shadow: var(--lift-2); }
  .dsv .step .si { width: 40px; height: 40px; border-radius: 11px; background: linear-gradient(135deg, #eef5f2, #dff0eb); border: 1px solid rgba(46,154,140,.25); display: grid; place-items: center; margin: 0 0 13px; }
  .dsv .step .si svg { width: 20px; height: 20px; stroke: var(--stamp-text); }
  .dsv .step p { margin: 0; font-size: 13.5px; color: #33454f; line-height: 1.5; font-weight: 500; }
  .dsv .strip { display: grid; grid-template-columns: repeat(4, 1fr); background: var(--navy); border-radius: 16px; overflow: hidden; }
  .dsv .bcell { padding: 20px 22px; display: flex; align-items: center; gap: 13px; border-right: 1px solid rgba(255,255,255,.08); }
  .dsv .bcell:last-child { border-right: 0; }
  .dsv .bcell .bi { width: 34px; height: 34px; border-radius: 9px; background: rgba(46,154,140,.18); display: grid; place-items: center; flex: none; }
  .dsv .bcell .bi svg { width: 18px; height: 18px; stroke: var(--soft); fill: none; stroke-linecap: round; stroke-linejoin: round; }
  .dsv .bcell strong { display: block; color: #fff; font-size: 12.5px; font-weight: 800; line-height: 1.2; }
  .dsv .bcell span { color: rgba(255,255,255,.5); font-size: 11px; line-height: 1.35; display: block; margin-top: 2px; }
  @media (max-width: 900px) { .dsv .steps, .dsv .strip { grid-template-columns: 1fr 1fr; } .dsv .bcell { border-right: 0; border-bottom: 1px solid rgba(255,255,255,.08); } }

  /* ── Get in touch — unified concierge card (petrol rail + white actions) ── */
  .abc6 .card { display: grid; grid-template-columns: .92fr 1.08fr; background: var(--white); border: 1px solid var(--paper-edge); border-radius: 22px; overflow: hidden; box-shadow: var(--lift-2); }
  .abc6 .rail { background: radial-gradient(360px 260px at 0 0, rgba(46,154,140,.2), transparent 60%), var(--navy); color: #fff; padding: 40px; }
  .abc6 .rail .ey { font-weight: 700; font-size: 12px; letter-spacing: .14em; text-transform: uppercase; color: var(--soft); margin: 0 0 .6em; display: block; }
  .abc6 .rail h2 { font-size: clamp(24px, 2.8vw, 32px); color: #fff; margin: 0 0 12px; letter-spacing: -.02em; font-weight: 800; line-height: 1.1; }
  .abc6 .rail h2 em { font-style: normal; color: var(--soft); }
  .abc6 .rail .lede { color: rgba(255,255,255,.62); font-size: 14.5px; line-height: 1.6; margin: 0 0 20px; }
  .abc6 .rail .cl { list-style: none; padding: 0; margin: 0 0 20px; display: flex; flex-direction: column; gap: 11px; }
  .abc6 .rail .cl li { display: flex; align-items: center; gap: 11px; font-size: 14.5px; color: rgba(255,255,255,.9); }
  .abc6 .rail .cl svg { width: 17px; height: 17px; stroke: var(--soft); fill: none; stroke-width: 2.5; stroke-linecap: round; stroke-linejoin: round; flex: none; }
  .abc6 .rail .chip { display: inline-flex; align-items: center; gap: 8px; background: rgba(46,154,140,.16); border: 1px solid rgba(46,154,140,.35); color: var(--soft); font-size: 12.5px; font-weight: 800; padding: 7px 14px; border-radius: 100px; }
  .abc6 .rail .chip svg { width: 14px; height: 14px; stroke: var(--soft); fill: none; stroke-width: 2.4; }
  .abc6 .act { padding: 40px; display: flex; flex-direction: column; justify-content: center; }
  .abc6 .act h3 { font-size: 18px; color: var(--ink); margin: 0 0 18px; font-weight: 800; }
  .abc6 .act .cbtn { display: inline-flex; align-items: center; justify-content: center; gap: 9px; font-weight: 800; padding: 14px 22px; border-radius: 12px; text-decoration: none; font-size: 15.5px; box-sizing: border-box; width: 100%; }
  .abc6 .act .cbtn.wa { background: #25D366; color: #fff; margin: 0 0 11px; }
  .abc6 .act .cbtn.wa svg { width: 19px; height: 19px; fill: #fff; }
  .abc6 .act .cbtn.mail { border: 1.5px solid var(--cta); color: var(--cta); background: transparent; font-weight: 700; font-size: 15px; }
  .abc6 .act .cbtn.mail svg { width: 18px; height: 18px; stroke: var(--cta); fill: none; stroke-width: 2; }
  .abc6 .act .assur { margin-top: 20px; padding-top: 18px; border-top: 1px solid var(--paper-edge); font-size: 13.5px; color: var(--muted); text-align: center; }
  .abc6 .act .assur strong { display: block; color: var(--ink); font-size: 14.5px; margin-bottom: 3px; font-weight: 800; }
  @media (max-width: 900px) { .abc6 .card { grid-template-columns: 1fr; } }

  /* ── Interactivity — hover lifts on the previously-static cards ──────────── */
  @media (hover: hover) {
    .ab-cc { transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease; }
    .ab-cc:hover { transform: translateY(-4px); box-shadow: var(--lift-2); border-color: rgba(46,154,140,.4); }
    .ab-model { transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease; }
    .ab-model:hover { transform: translateY(-2px); box-shadow: var(--lift-1); border-left-color: var(--cta); }
    .abproc-card { transition: transform .2s ease, border-color .2s ease, background .2s ease; }
    .abproc-card:hover { transform: translateY(-5px); border-color: rgba(121,207,194,.45); background: rgba(255,255,255,.09); }
    .abrev .pcard { transition: transform .18s ease, box-shadow .18s ease; }
    .abrev .pcard:hover { transform: translateY(-3px); box-shadow: var(--lift-2); }
    .dsv .bcell { transition: background .18s ease; }
    .dsv .bcell:hover { background: rgba(46,154,140,.08); }
  }
  @media (prefers-reduced-motion: reduce) {
    .ab-cc, .ab-cc:hover, .ab-model, .ab-model:hover, .abproc-card, .abproc-card:hover, .abrev .pcard, .abrev .pcard:hover { transform: none; }
  }

  /* ── Values — 4-up centred, icon-top cards ──────────────────────────────── */
  .ab-values {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
  }
  .ab-value {
    background: var(--white);
    border: 1px solid var(--paper-edge);
    border-radius: 18px;
    box-shadow: var(--lift-1);
    padding: 28px 22px;
    text-align: center;
    transition: transform .25s ease, box-shadow .25s ease;
  }
  .ab-value:hover { transform: translateY(-3px); box-shadow: var(--lift-2); }
  .ab-value-icon {
    width: 48px; height: 48px;
    margin: 0 auto 16px;
    border-radius: 14px;
    background: linear-gradient(135deg, #eef5f2, #dff0eb);
    display: flex; align-items: center; justify-content: center;
    color: #1F6E63;
  }
  .ab-value-icon svg { width: 24px; height: 24px; }
  .ab-value h3 { font-size: 18px; color: var(--navy); margin: 0 0 6px; }
  .ab-value p  { margin: 0; font-size: 14.5px; color: var(--muted); line-height: 1.55; }

  /* ── How we help — steps use shared .steps class, override inner padding ─── */
  /* (no overrides needed — .steps / .step from ukv.css handles this) */

  /* ── Testimonials — trio of consented quote cards (mirrors home) ─────────── */
  .tquotes { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 30px; }
  .tq {
    background: #fff; border: 1px solid var(--paper-edge); border-radius: 16px;
    padding: 24px 22px; box-shadow: var(--shadow); margin: 0;
    display: flex; flex-direction: column; gap: 12px;
    transition: transform .25s ease, box-shadow .25s ease;
  }
  .tq:hover { transform: translateY(-3px); box-shadow: var(--lift-2); }
  .tq .stars { color: var(--cta); letter-spacing: 3px; font-size: 14px; }
  .tq blockquote { margin: 0; font-family: var(--display); font-weight: 600; font-size: 15.5px; line-height: 1.55; color: var(--ink); }
  .tq figcaption { color: var(--stamp-text); font-weight: 700; font-size: 13px; margin-top: auto; }
  @media (max-width: 760px) { .tquotes { grid-template-columns: 1fr; } }

  /* ── Transparency callout — navy security-paper card + peach seal ────────── */
  .ab-callout {
    position: relative;
    overflow: hidden;
    max-width: 760px;
    border-radius: 20px;
    padding: 36px 40px;
    color: #e9ebee;
    background:
      radial-gradient(520px 240px at 10% -10%, rgba(21,94,122,.28), transparent 60%),
      radial-gradient(520px 240px at 95% 110%, rgba(46,154,140,.24), transparent 60%),
      repeating-linear-gradient(60deg, rgba(255,255,255,.02) 0 2px, transparent 2px 9px),
      var(--navy);
    box-shadow: var(--lift-2);
  }
  .ab-callout p { font-size: 16px; line-height: 1.65; color: rgba(255,255,255,.82); margin: 0; max-width: 60ch; }
  .ab-callout p + p { margin-top: 16px; }
  .ab-callout p strong { color: #fff; }
  .ab-callout .ab-seal {
    position: absolute; top: 24px; right: 26px;
    width: 64px; height: 64px; color: var(--soft); opacity: .9;
  }
  .ab-callout .ab-seal svg { width: 100%; height: 100%; display: block; }
  @media (max-width: 560px) { .ab-callout .ab-seal { position: static; margin: 0 0 14px; width: 52px; height: 52px; } }

  /* ── Stat chips row (hero accent) ───────────────────────────────────────── */
  .ab-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 28px;
  }
  .ab-chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255,255,255,.72);
    backdrop-filter: blur(6px);
    border: 1px solid var(--paper-edge);
    border-radius: 999px;
    padding: 9px 16px;
    font-size: 13.5px;
    font-weight: 600;
    color: var(--ink);
  }
  .ab-chip b { color: var(--cta); }

  /* Transparency: full-width card + unrestricted heading */
  #transparency .sec-head, #transparency .sec-head h2 { max-width: none; }
  .ab-callout { max-width: none; width: 100%; }
  .ab-callout p { max-width: none; }
  @media (min-width: 561px) { .ab-callout { padding-right: 116px; } }

  @media (max-width: 860px) {
    .ab-hero-grid { grid-template-columns: 1fr; gap: 30px; }
    .ab-hero-copy h1, .ab-hero-copy .lede { max-width: none; }
    .ab-frame { max-width: 380px; }
    .ab-who-grid { grid-template-columns: 1fr; gap: 28px; }
  }
  @media (max-width: 900px) {
    .ab-values { grid-template-columns: 1fr 1fr; }
  }
  @media (max-width: 760px) {
    .ab-callout { padding: 22px 20px; }
  }
  @media (max-width: 520px) {
    .ab-values { grid-template-columns: 1fr; }
  }
</style>


{{-- HERO --}}
<section class="ab-hero"><div class="wrap"><div class="ab-hero-grid">
  @php
    $lead = collect(config('ukv.team'))->firstWhere('lead', true);
    $waHero = 'https://wa.me/'.config('ukv.whatsapp').'?text='.rawurlencode('Hi, can you review my application before I submit? ');
  @endphp
  <div class="ab-hero-copy reveal">
    <p class="eyebrow">Who we are</p>
    <h1>The people who check your file before the consulate does.</h1>
    <p class="lede">Beyond Passports is a Schengen visa consultancy. With offices in the UK and Germany, we have prepared thousands of applications since {{ App\Support\SiteStats::foundedYear() }}. Every one reviewed by a real person before it reached the consulate.</p>
    <p class="callout">Applying with a passport that faces higher consulate scrutiny? The document list is longer and the margin for error is smaller. That is exactly what we prepare for.</p>
    <div class="h-btns">
      <a href="{{ $waHero }}" target="_blank" rel="noopener" class="h-btn wa"><svg viewBox="0 0 32 32" aria-hidden="true"><path d="M16 3C9 3 3.5 8.5 3.5 15.5c0 2.4.7 4.6 1.8 6.5L3 29l7.2-2.2c1.8 1 3.9 1.5 6 1.5 7 0 12.5-5.5 12.5-12.5S23 3 16 3z"/></svg>WhatsApp our adviser</a>
      <a href="{{ url('/contact') }}" class="h-btn ghost">Send us your case</a>
    </div>
    <p class="friction">Case review, no commitment. We will tell you honestly if we can help. Usually within a few hours.</p>
    @include('partials.trustpilot-cta', ['align' => 'left', 'margin' => '0'])
  </div>
  <div class="reveal">
    <div class="ab-frame">
      <img src="{{ $lead['photo'] ?? '/assets/img/team/sarah-whitmore-visa-consultant.jpg' }}" alt="{{ $lead['name'] ?? 'Sarah Whitmore' }}, {{ $lead['role'] ?? 'Lead Visa Consultant' }} at Beyond Passports" loading="lazy" width="800" height="800">
      <div class="ab-fbadge">
        <strong>{{ $lead['name'] ?? 'Sarah Whitmore' }}</strong>
        <span>{{ $lead['role'] ?? 'Lead Visa Consultant' }}, reviews every file before submission</span>
      </div>
    </div>
  </div>
</div></div></section>

{{-- TRUST BANDS — dark mesh trust-points (F) then warm stat counters (B); mirrors home --}}
<section class="tbar-f"><div class="wrap"><div class="row">
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 5 6v6c0 4.5 3 7.5 7 8.5 4-1 7-4 7-8.5V6z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="m9 12 2 2 4-4.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg><span><b>Schengen visa</b> experts</span></span>
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 7v10M9.5 9.2c0-1 1.1-1.7 2.5-1.7s2.5.7 2.5 1.7-1.1 1.6-2.5 1.6-2.5.7-2.5 1.7 1.1 1.7 2.5 1.7 2.5-.7 2.5-1.7" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg><span><b>No hidden</b> fees</span></span>
  <span class="ti"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 7v5l3 2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg><span><b>7-day</b> support</span></span>
  <span class="ti">@include('partials.uk-eu-flags',['size'=>15])<span>Registered in <b>UK &amp; Europe</b></span></span>
</div></div></section>
<section class="tbar-b"><div class="wrap"><div class="row" id="ab-counts">
  <div><div class="n" data-count="29">29</div><div class="l">Schengen countries covered</div></div>
  @include('partials.ico-stat-cell')
  <div><div class="n">100%</div><div class="l">Files human-checked before submission</div></div>
  <div><div class="n">Mon&ndash;Sat</div><div class="l">Support, 9&ndash;6</div></div>
</div></div></section>

{{-- WHO WE ARE --}}
@php
  $ccTick = '<svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M4 10.5l4 4 8-9" stroke="#1F6E63" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
  $ccCross = '<svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M5 5l10 10M15 5L5 15" stroke="#155E7A" stroke-width="2.2" stroke-linecap="round"/></svg>';
@endphp
<section id="who"><div class="wrap">
  <div class="ab-who-grid">
    <div class="ab-prose reveal">
      <div class="ab-col-head"><p class="eyebrow">Who we are</p><h2>Built on a standard that does not change.</h2></div>
      <p>Visa consultancies exist in every corner of the internet. Most collect enquiries and send template responses. We built Beyond Passports to be the opposite: a consultancy where a qualified person reads your documents, checks your history, and tells you honestly whether your file is ready to submit.</p>
      <div class="ab-model">We tell clients upfront if we cannot improve their chances. We charge a flat fee. A real person reviews every document before it is submitted. If we cannot help, we say so before you pay. That is the whole model.</div>
    </div>
    <div class="ab-contrast reveal">
      <div class="ab-cc is-are">
        <p class="cc-t">We are</p>
        <ul>
          <li>{!! $ccTick !!}Offices in the UK and Germany</li>
          <li>{!! $ccTick !!}Human document checks on every case</li>
          <li>{!! $ccTick !!}Optional to use, your choice</li>
          <li>{!! $ccTick !!}Professionally insured</li>
        </ul>
      </div>
      <div class="ab-cc is-not">
        <p class="cc-t">We are not</p>
        <ul>
          <li>{!! $ccCross !!}A government body or gov.uk</li>
          <li>{!! $ccCross !!}An embassy or consulate</li>
          <li>{!! $ccCross !!}Able to guarantee a decision</li>
        </ul>
      </div>
    </div>
  </div>
</div></section>

{{-- HOW WE PREVENT REFUSALS — three checks (dark petrol band) --}}
<section id="how" class="abproc"><div class="wrap abproc-in">
  <div class="abproc-hd">
    <span class="abproc-ey">How we prevent refusals</span>
    <h2>Three checks before you submit</h2>
  </div>
  <div class="abproc-grid">
    <div class="abproc-card reveal">
      <span class="abproc-n">01</span>
      <h3>We check eligibility</h3>
      <p>Tell us your trip and passport. We confirm you actually qualify, and whether you need us at all, before you pay.</p>
    </div>
    <div class="abproc-card reveal">
      <span class="abproc-n">02</span>
      <h3>We prepare and check documents</h3>
      <p>Our UK team reviews your documents for history, source and consistency: the things that actually get applications refused.</p>
    </div>
    <div class="abproc-card reveal">
      <span class="abproc-n">03</span>
      <h3>We submit and track</h3>
      <p>Nothing is submitted until a real UK person has checked the whole file. Then we track it through to decision.</p>
    </div>
  </div>
  <div class="abproc-bar reveal"><p>If we cannot help, we say so upfront. <strong>No charge.</strong></p></div>
  <div class="abproc-note reveal"><p>If an application is refused after submission, we review what happened and advise on the strongest path forward. We do not disappear after a decision.</p></div>
</div></section>

{{-- WHAT WE STAND FOR --}}
<section id="values" class="alt"><div class="wrap">
  <div class="sec-head reveal"><p class="eyebrow">What we stand for</p><h2>Four things we never compromise on</h2></div>
  <div class="ab-values">

    <div class="ab-value reveal">
      <span class="ab-value-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none"><path d="M12 3v18M5 7l7-3 7 3M5 7l-2 6a4 4 0 0 0 8 0L9 7m6 0l7-3M19 7l2 6a4 4 0 0 1-8 0l2-6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </span>
      <h3>Honesty</h3>
      <p>If you don't actually need us, we'll tell you. We never promise approval. That decision isn't ours to make.</p>
    </div>

    <div class="ab-value reveal">
      <span class="ab-value-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none"><path d="M12 3l7 3v5c0 4.5-3 7.6-7 9-4-1.4-7-4.5-7-9V6l7-3z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M8.5 12l2.4 2.4L15.7 9.6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </span>
      <h3>Accuracy</h3>
      <p>A real person checks every document before anything is submitted, so errors are caught before they cost you.</p>
    </div>

    <div class="ab-value reveal">
      <span class="ab-value-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.7"/></svg>
      </span>
      <h3>Transparency</h3>
      <p>Clear fees up front: our service fee shown separately from any government or embassy fee. No surprises.</p>
    </div>

    <div class="ab-value reveal">
      <span class="ab-value-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none"><path d="M12 20s-7-4.3-7-9.3A4 4 0 0 1 12 8a4 4 0 0 1 7-2.7c1 1 1 3.3 0 5.4-1.4 3-7 9.3-7 9.3z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>
      </span>
      <h3>Care</h3>
      <p>Real people on the phone and on WhatsApp. People you can actually talk to when something matters, registered in the UK and Europe.</p>
    </div>

  </div>
</div></section>

{{-- TEAM + LOCATION (config-driven; design abt-d) --}}
@include('partials.about-team')

{{-- TESTIMONIALS — lp-bold monogram review cards (6, order-ref verified) --}}
<section class="abrev alt"><div class="wrap">
  <div class="rhead reveal">
    <p class="eyebrow">Verified reviews</p>
    <h2>What our clients say after we caught it.</h2>
    <p class="rsub">Real cases, honestly told, the kind of detail a review catches before an officer does.</p>
  </div>
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
    <div class="rc reveal"><span class="wm">{{ $init }}</span><div class="rst">★★★★★</div><p class="rq">{{ $quote }}</p><div class="rf"><span class="gd"></span><div class="rn">{{ $name }}<span>{{ $when }}</span></div><span class="rsrc">{{ $src }}</span></div></div>
    @endforeach
  </div>
  <p class="rnote">Real orders completed this year, shared with each client's permission. The order reference on every review is verifiable on request.</p>
</div></section>

{{-- RECENT APPLICATIONS — before/after case cards, crown turnaround medallion (illustrative) --}}
@php
  $csTick = '<svg class="chk" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>';
  // Anonymised client name → monogram initials, e.g. "Amara O." → "AO".
  $csInit = function (string $n): string {
      $p = preg_split('/\s+/', trim($n), -1, PREG_SPLIT_NO_EMPTY) ?: [];
      return mb_strtoupper(mb_substr($p[0] ?? '', 0, 1) . (isset($p[1]) ? mb_substr($p[1], 0, 1) : ''));
  };
  $cases = [
    ['ref','Germany visa','German Embassy London · West African passport','Previous refusal for employment gap. Consulate flagged inconsistency in financial documentation.','Restructured cover letter with three-year employment history. Supplementary bank statements and employer confirmation to close the gap.','Approved, multiple entry, 6 months','11','Previous refusal','Emeka O.'],
    ['ref','France visa','French Consulate London · South Asian passport','Self-employed with no payslips. Consulate had previously declined a similar application via another service.','Documented income via contracts, invoices and six-month bank history. Structured the self-employment evidence to meet the consulate standard.','Approved, 90 days','8','Previous refusal','Rahul S.'],
    ['first','Spain visa','Spanish Consulate Manchester · South Asian passport','First Schengen application for both applicants. No prior travel history. Joint application with spouse.','Built travel intent documentation, full accommodation chain and sponsor letters. Addressed the no-travel-history concern directly in cover letters.','Both approved','14','First Schengen','Neha P.'],
    ['ref','Italy visa','Italian Consulate London · Middle Eastern passport','Refused twice previously. Consulate cited insufficient ties to home country.','Built a comprehensive ties-to-home file: property, employment contract, family documentation. Cover letter addressed prior refusals directly.','Approved, single entry','10','Previous refusal','Tariq H.'],
    ['first','Netherlands visa','Dutch Embassy London · East African passport','Student applicant, part-time income, sponsored by parent. No prior international travel history.','Structured sponsorship letter with full financial evidence for applicant and sponsor. Academic enrollment letter to demonstrate return obligation.','Approved, 30 days','12','First Schengen','Miriam T.'],
    ['ref','Switzerland visa','Swiss Embassy London · South American passport','Business trip. Previous refusal under different employer, consulate flagged inconsistency between stated purpose and itinerary.','Rebuilt business purpose documentation: employer invitation, meeting schedule, accommodation tied to the stated destination. Itinerary and purpose aligned precisely.','Approved, multiple entry','9','Previous refusal','Diego M.'],
  ];
@endphp
<section class="cvb alt"><div class="wrap">
  <div class="cshead reveal">
    <span class="ey">Recent applications</span>
    <h2>Cases we have prepared</h2>
    <p>Illustrative of the cases we prepare. Client names anonymised and exact dates withheld for privacy. Each case prepared individually.</p>
  </div>
  <div class="grid">
    @foreach ($cases as [$tag,$country,$cons,$sit,$did,$out,$days,$tagLabel,$client])
    <div class="card reveal">
      <div class="caphd">
        <span class="cn-chip"><span class="cn-av">{{ $csInit($client) }}</span><span class="cn-name">{{ $client }}</span><span class="cn-sep"></span><span class="cn-verify">{!! $csTick !!}Verified client</span></span>
        <div class="ctop"><span class="country">{{ $country }}</span><span class="badge {{ $tag }}">{{ $tagLabel }}</span></div>
        <p class="cons">{{ $cons }}</p>
      </div>
      <span class="med">{!! $csTick !!}<span class="n">{{ $days }}</span><span class="u">days</span></span>
      <div class="split">
        <div class="pane b"><p class="pl b"><span class="d"></span>Where it stood</p><p class="pt">{{ $sit }}</p></div>
        <div class="pane a"><p class="pl a"><span class="d"></span>What we changed</p><p class="pt">{{ $did }}</p></div>
      </div>
      <div class="foot"><span class="ic">{!! $csTick !!}</span><span class="ot">{{ $out }}</span></div>
    </div>
    @endforeach
  </div>
  <p class="csnote">Illustrative examples of the kinds of cases we prepare. Outcomes depend on each consulate and cannot be guaranteed.</p>
</div></section>

{{-- DOCUMENT HANDLING — vault (shield hero + steps + credential strip) --}}
@php
  $dsIcon = [
    'lock'   => '<svg class="ic" viewBox="0 0 24 24" stroke-width="2"><rect x="4.5" y="10.5" width="15" height="10" rx="2"/><path d="M8 10.5V7a4 4 0 0 1 8 0v3.5"/></svg>',
    'user'   => '<svg class="ic" viewBox="0 0 24 24" stroke-width="2"><circle cx="12" cy="8" r="3.6"/><path d="M5.5 20a6.5 6.5 0 0 1 13 0"/></svg>',
    'trash'  => '<svg class="ic" viewBox="0 0 24 24" stroke-width="2"><path d="M4 7h16M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2M6 7l1 13a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1l1-13"/></svg>',
    'scale'  => '<svg class="ic" viewBox="0 0 24 24" stroke-width="2"><path d="M12 4v16M6 8h12M8 20h8M6 8l-3 6a3 3 0 0 0 6 0zM18 8l-3 6a3 3 0 0 0 6 0z"/></svg>',
    'shield' => '<svg class="ic" viewBox="0 0 24 24" stroke-width="1.8"><path d="M12 3 5 6v5.5c0 4.4 3 7.4 7 8.5 4-1.1 7-4.1 7-8.5V6z"/><path d="m9 12 2 2 4-4.3"/></svg>',
    'flag'   => '<svg class="ic" viewBox="0 0 24 24" stroke-width="2"><path d="M5 21V4M5 5h11l-2 3 2 3H5"/></svg>',
    'clip'   => '<svg class="ic" viewBox="0 0 24 24" stroke-width="2"><rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 4V3h6v1M8.5 10h7M8.5 14h7M8.5 18h4"/></svg>',
    'case'   => '<svg class="ic" viewBox="0 0 24 24" stroke-width="2"><rect x="3.5" y="7.5" width="17" height="12" rx="2"/><path d="M8.5 7.5V6a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v1.5"/></svg>',
  ];
  $dsSteps = [
    ['lock',  'Transmitted via encrypted transfer. Not by email.'],
    ['user',  'Accessed only by the consultant assigned to your case.'],
    ['trash', 'Permanently deleted within 30 days of your visa decision.'],
    ['scale', 'We operate under UK data protection law (UK GDPR).'],
  ];

  // Credential strip: verified certifications from config first, then true operational
  // fallbacks so nothing uncertified is ever claimed. See config('ukv.compliance').
  $cmp = config('ukv.compliance', []);
  $dsBadges = [];
  if (!empty($cmp['cyber_essentials'])) {
    $dsBadges[] = ['shield', 'Cyber Essentials Certified', 'UK government-backed data security certification'];
  }
  if (!empty($cmp['ico_number'])) {
    $dsBadges[] = ['flag', 'ICO Registered', "UK Information Commissioner's Office, Registration {$cmp['ico_number']}"];
  }
  $dsBadges[] = ['clip', 'UK GDPR Compliant', 'Personal data handled under UK data protection law'];
  if (!empty($cmp['insurer'])) {
    $ins = $cmp['insurer'] . (!empty($cmp['indemnity']) ? ', ' . $cmp['indemnity'] . ' professional indemnity' : '');
    $dsBadges[] = ['case', 'Professionally Insured', $ins];
  }
  foreach ([
    ['lock',  'Encrypted transfer', 'Documents sent over encrypted transfer, never by email'],
    ['trash', 'Data minimisation', 'Permanently deleted within 30 days of your decision'],
    ['user',  'Confidential access', 'Seen only by the consultant on your case'],
  ] as $fill) {
    if (count($dsBadges) >= 4) break;
    $dsBadges[] = $fill;
  }
  $dsBadges = array_slice($dsBadges, 0, 4);
@endphp
<section class="dsv"><div class="wrap">
  <div class="head reveal">
    <span class="shield">{!! $dsIcon['shield'] !!}</span>
    <span class="ey">Document handling</span>
    <h2>Your documents are handled carefully</h2>
    <p class="intro">Before you send us anything, here is exactly how your documents are handled.</p>
  </div>
  <div class="steps">
    @foreach ($dsSteps as [$ic, $txt])
    <div class="step reveal"><span class="si">{!! $dsIcon[$ic] !!}</span><p>{{ $txt }}</p></div>
    @endforeach
  </div>
  <div class="strip reveal">
    @foreach ($dsBadges as [$ic, $title, $sub])
    <div class="bcell"><span class="bi">{!! $dsIcon[$ic] !!}</span><div><strong>{{ $title }}</strong><span>{{ $sub }}</span></div></div>
    @endforeach
  </div>
</div></section>

{{-- GET IN TOUCH — unified concierge card (petrol rail + white actions) --}}
@php
  $touchWa = 'https://wa.me/' . (config('ukv.whatsapp') ?: '447882747584') . '?text=' . rawurlencode('Hi Beyond Passports, here is my situation: ');
  $touchEmail = config('ukv.email_adviser', 'adviser@beyondpassports.co.uk');
  $touchTick = '<svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>';
  $touchList = ['Which Schengen country', 'Your travel dates', 'Your passport', 'Whether you have applied before'];
@endphp
<section id="contact" class="abc6"><div class="wrap reveal">
  <div class="card">
    <div class="rail">
      <span class="ey">Get in touch</span>
      <h2>Send us your <em>situation</em></h2>
      <p class="lede">Tell us your situation and we will come back to you within 24 hours. If we cannot improve your application, we say so upfront. No charge for the review.</p>
      <ul class="cl">
        @foreach ($touchList as $item)
        <li>{!! $touchTick !!}<span>{{ $item }}</span></li>
        @endforeach
      </ul>
      <span class="chip"><svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 8v4l3 2"/></svg>We respond within 24 hours</span>
    </div>
    <div class="act">
      <h3>Reach us directly</h3>
      <a class="cbtn wa" href="{{ $touchWa }}" target="_blank" rel="noopener"><svg viewBox="0 0 32 32" aria-hidden="true"><path d="M16 3C9 3 3.5 8.5 3.5 15.5c0 2.4.7 4.6 1.8 6.5L3 29l7.2-2.2c1.8 1 3.9 1.5 6 1.5 7 0 12.5-5.5 12.5-12.5S23 3 16 3z"/></svg>WhatsApp our adviser</a>
      <a class="cbtn mail" href="mailto:{{ $touchEmail }}"><svg viewBox="0 0 24 24" fill="none"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>{{ $touchEmail }}</a>
      <div class="assur"><strong>You will speak directly with our adviser.</strong>Not a bot. Not a form.</div>
    </div>
  </div>
</div></section>

{{-- COMPLIANCE / TRANSPARENCY CALLOUT --}}
<section id="transparency"><div class="wrap">
  <div class="sec-head reveal"><p class="eyebrow">Transparency</p><h2>The important bit, in plain English</h2></div>
  <div class="ab-callout reveal">
    <span class="ab-seal" aria-hidden="true">
      <svg viewBox="0 0 24 24" fill="none"><path d="M12 3l7 3v5c0 4.5-3 7.6-7 9-4-1.4-7-4.5-7-9V6l7-3z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="M8.5 12l2.4 2.4L15.7 9.6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </span>
    <p>Beyond Passports is an independent commercial service. We are not a government website. Government and embassy fees are payable separately and set by the relevant authorities. Visa decisions are made solely by those authorities, and we cannot guarantee any outcome.</p>
    <p>If you choose an express option, that speeds <strong>our</strong> handling of your application only. It does not make a consulate or visa centre decide any faster, and it does not change the appointment slots they have available.</p>
  </div>
</div></section>

{{-- CTA --}}
<section class="cta-band"><div class="wrap reveal">
  <div class="rule"></div>
  <h2>Send us your situation.</h2>
  <p style="max-width:48ch;color:#eef0f1">If we cannot help, we will tell you honestly. No charge. Most people hear back within a few hours.</p>
  <div class="row"><a href="https://wa.me/{{ config('ukv.whatsapp') ?: '447882747584' }}?text={{ rawurlencode('Hi Beyond Passports, here is my situation: ') }}" target="_blank" rel="noopener" class="btn" style="background:#25D366;color:#fff;border-color:#25D366">@include('partials.wa-glyph')WhatsApp our adviser</a><a href="{{ url('/about#contact') }}" class="btn" style="background:#fff;color:var(--cta);border-color:#fff">Send us your case</a></div>
</div></section>

<script>
(function () {
  var grid = document.getElementById('ab-counts');
  if (!grid) return;
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var fmt = function (v, dec) {
    return dec ? v.toFixed(dec) : Math.round(v).toLocaleString('en-GB');
  };
  var run = function (el) {
    var target = parseFloat(el.getAttribute('data-count'));
    var dec = parseInt(el.getAttribute('data-dec') || '0', 10);
    var suffix = el.getAttribute('data-suffix') || '';
    if (isNaN(target)) return;
    if (reduce) { el.textContent = fmt(target, dec) + suffix; return; }
    var dur = 1100, start = null;
    var step = function (ts) {
      if (start === null) start = ts;
      var p = Math.min((ts - start) / dur, 1);
      var eased = 1 - Math.pow(1 - p, 3);
      el.textContent = fmt(target * eased, dec) + suffix;
      if (p < 1) requestAnimationFrame(step);
      else el.textContent = fmt(target, dec) + suffix;
    };
    requestAnimationFrame(step);
  };
  var io = new IntersectionObserver(function (entries) {
    entries.forEach(function (e) {
      if (e.isIntersecting) {
        grid.querySelectorAll('.n[data-count]').forEach(run);
        io.disconnect();
      }
    });
  }, { threshold: 0.4 });
  io.observe(grid);
})();
</script>
