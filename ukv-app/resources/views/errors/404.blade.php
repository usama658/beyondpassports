<!doctype html>
<html lang="en-GB">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Page not found (404) — Beyond Passports</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  :root{
    --navy:#16222E; --paper:#FBF6F1; --edge:#e7ddd1; --cta:#155E7A; --ink:#16222E;
    --muted:#6c7075; --sage:#1F6E63; --soft:#A9CCDA;
    --j:"Plus Jakarta Sans",system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;
  }
  *{box-sizing:border-box}
  html,body{height:100%}
  body{
    margin:0;font-family:var(--j);color:#e8eaec;
    background:
      radial-gradient(680px 320px at 12% -8%, rgba(21,94,122,.30), transparent 60%),
      radial-gradient(620px 300px at 92% 108%, rgba(46,154,140,.26), transparent 60%),
      /* faint guilloché / security-paper weave on the navy cover */
      repeating-linear-gradient(60deg, rgba(255,255,255,.018) 0 2px, transparent 2px 9px),
      repeating-linear-gradient(-60deg, rgba(255,255,255,.018) 0 2px, transparent 2px 9px),
      var(--navy);
    display:flex;align-items:center;justify-content:center;
    min-height:100dvh;padding:32px 20px;
  }
  .visa-page{
    position:relative;width:100%;max-width:620px;background:var(--paper);
    border:1px solid var(--edge);border-radius:18px;
    box-shadow:0 40px 90px -40px rgba(0,0,0,.6);
    padding:40px 40px 30px;overflow:hidden;
  }
  /* security tint lines inside the passport page */
  .visa-page::before{
    content:"";position:absolute;inset:0;pointer-events:none;opacity:.5;
    background:repeating-linear-gradient(135deg, rgba(63,114,89,.05) 0 1px, transparent 1px 14px);
  }
  .vp-head{display:flex;justify-content:space-between;align-items:center;
    font-weight:800;font-size:11px;letter-spacing:.18em;text-transform:uppercase;color:var(--sage)}
  .vp-head .pp{color:var(--cta)}
  .vp-body{position:relative;z-index:2;padding:30px 0 6px;max-width:34ch}
  .vp-body h1{font-size:clamp(28px,5vw,40px);line-height:1.04;letter-spacing:-.03em;color:var(--ink);margin:0 0 14px;font-weight:700}
  .vp-body p{color:var(--muted);font-size:16px;line-height:1.6;margin:0 0 24px;max-width:42ch}
  /* the signature: an immigration "NO ENTRY · 404" rubber stamp pressed onto the page */
  .stamp{
    position:absolute;top:34px;right:26px;width:150px;height:150px;color:var(--cta);
    transform:rotate(-9deg);transform-origin:center;
    animation:stamp-press .42s cubic-bezier(.2,1.3,.45,1) .25s both;
    opacity:.92;
  }
  .stamp svg{width:100%;height:100%;display:block;filter:drop-shadow(0 1px 0 rgba(21,94,122,.25))}
  @keyframes stamp-press{
    0%{opacity:0;transform:rotate(-9deg) scale(2)}
    60%{opacity:.92;transform:rotate(-9deg) scale(.92)}
    100%{opacity:.92;transform:rotate(-9deg) scale(1)}
  }
  /* route tabs — boarding-pass style */
  .routes{display:flex;flex-wrap:wrap;gap:10px;position:relative;z-index:2}
  .routes a{
    display:inline-flex;align-items:center;gap:8px;text-decoration:none;
    font-weight:700;font-size:13.5px;border-radius:10px;padding:11px 16px;
    transition:transform .12s ease, box-shadow .15s ease, background .15s ease;
  }
  .routes a.primary{background:var(--cta);color:#fff;box-shadow:0 12px 24px -14px rgba(21,94,122,.8)}
  .routes a.ghost{background:#fff;color:var(--ink);border:1px solid var(--edge)}
  .routes a:hover{transform:translateY(-2px)}
  .routes a:focus-visible{outline:2px solid var(--cta);outline-offset:3px}
  /* MRZ strip — the machine-readable line at the foot of a passport page */
  .mrz{margin-top:30px;border-top:1px dashed var(--edge);padding-top:14px;position:relative;z-index:2;
    font-family:ui-monospace,"SF Mono",Menlo,Consolas,monospace;font-size:11px;letter-spacing:.18em;color:#9a8f82;
    white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .vp-foot{margin-top:14px;position:relative;z-index:2;font-size:12px;color:var(--muted)}
  .vp-foot a{color:var(--sage);font-weight:700}
  @media (max-width:560px){
    .visa-page{padding:30px 24px 24px}
    .stamp{position:static;margin:0 0 8px;width:120px;height:120px}
    .vp-body{padding-top:18px}
    .vp-body h1,.vp-body p{max-width:none}
  }
  @media (prefers-reduced-motion:reduce){.stamp{animation:none}}
</style>
</head>
<body>
  <main class="visa-page" role="main">
    <div class="vp-head">
      <span class="pp">Beyond&nbsp;Passports</span>
      <span>Entry record · page 404</span>
    </div>

    {{-- Signature: circular immigration stamp --}}
    <div class="stamp" aria-hidden="true">
      <svg viewBox="0 0 200 200" role="img">
        <defs><path id="arc-top" d="M100,100 m-74,0 a74,74 0 1,1 148,0" /><path id="arc-bot" d="M100,100 m74,0 a74,74 0 1,1 -148,0" /></defs>
        <circle cx="100" cy="100" r="92" fill="none" stroke="currentColor" stroke-width="3"/>
        <circle cx="100" cy="100" r="78" fill="none" stroke="currentColor" stroke-width="1.5"/>
        <text font-family="Plus Jakarta Sans, sans-serif" font-weight="800" font-size="17" letter-spacing="6" fill="currentColor">
          <textPath href="#arc-top" startOffset="50%" text-anchor="middle">NO ENTRY</textPath>
        </text>
        <text font-family="Plus Jakarta Sans, sans-serif" font-weight="700" font-size="12" letter-spacing="4" fill="currentColor">
          <textPath href="#arc-bot" startOffset="50%" text-anchor="middle">BEYOND PASSPORTS</textPath>
        </text>
        <text x="100" y="116" text-anchor="middle" font-family="Plus Jakarta Sans, sans-serif" font-weight="800" font-size="54" letter-spacing="-2" fill="currentColor">404</text>
        <text x="42" y="106" text-anchor="middle" font-size="16" fill="currentColor">✦</text>
        <text x="158" y="106" text-anchor="middle" font-size="16" fill="currentColor">✦</text>
      </svg>
    </div>

    <div class="vp-body">
      <h1>This page didn't make it through.</h1>
      <p>The link you followed isn't in our system — it may have moved, or the address has a typo. Let's get you back on route.</p>
      <div class="routes">
        <a class="primary" href="{{ url('/') }}">Back to home</a>
        <a class="ghost" href="{{ url('/destinations') }}">Browse destinations</a>
        <a class="ghost" href="{{ url('/track') }}">Track an application</a>
        <a class="ghost" href="{{ url('/contact') }}">Talk to our team</a>
      </div>
    </div>

    <div class="mrz" aria-hidden="true">P&lt;GBRBEYOND&lt;&lt;PASSPORTS&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;404&lt;&lt;ROUTE&lt;NOT&lt;FOUND&lt;&lt;&lt;&lt;&lt;</div>
    <p class="vp-foot">Beyond Passports is an independent service — not a government website. Lost? <a href="{{ url('/contact') }}">We're happy to help</a>.</p>
  </main>
</body>
</html>
