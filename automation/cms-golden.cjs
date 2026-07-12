// automation/cms-golden.cjs — full-page screenshot diff of two URLs (coded vs cms).
// Usage: node cms-golden.cjs <urlA> <urlB> <outDir>
// Prints "MISMATCH pixels: N" (0 = identical). Uses pixelmatch+pngjs if installed,
// else falls back to a byte comparison of the two full-page screenshots.
const { chromium } = require('playwright-core');
const fs = require('fs');
let pixelmatch, PNG;
try { pixelmatch = require('pixelmatch'); pixelmatch = pixelmatch.default || pixelmatch; PNG = require('pngjs').PNG; } catch (e) {}

const [urlA, urlB, outDir] = process.argv.slice(2);
if (!urlA || !urlB || !outDir) { console.error('usage: node cms-golden.cjs <urlA> <urlB> <outDir>'); process.exit(2); }
fs.mkdirSync(outDir, { recursive: true });

(async () => {
  const b = await chromium.launch({ executablePath: chromium.executablePath() });
  const shoot = async (url, file) => {
    const p = await b.newPage({ viewport: { width: 1280, height: 900 }, deviceScaleFactor: 1 });
    // Freeze motion so two loads are deterministic: kill animations/transitions and force
    // any scroll-reveal elements to their final visible state before capturing.
    await p.addInitScript(() => {
      const s = document.createElement('style');
      s.textContent = '*,*::before,*::after{animation:none!important;transition:none!important;scroll-behavior:auto!important}'
        + '.reveal{opacity:1!important;transform:none!important}';
      (document.head || document.documentElement).appendChild(s);
    });
    await p.goto(url, { waitUntil: 'domcontentloaded', timeout: 40000 });
    await p.evaluate(async () => {
      for (let y = 0; y <= document.body.scrollHeight; y += 600) { window.scrollTo(0, y); await new Promise(r => setTimeout(r, 30)); }
      window.scrollTo(0, 0);
      document.querySelectorAll('.reveal').forEach(el => { el.style.opacity = '1'; el.style.transform = 'none'; });
    });
    await p.waitForTimeout(700);
    await p.screenshot({ path: outDir + '/' + file, fullPage: true });
    await p.close();
  };
  await shoot(urlA, 'a.png');
  await shoot(urlB, 'b.png');

  if (pixelmatch) {
    const a = PNG.sync.read(fs.readFileSync(outDir + '/a.png'));
    const c = PNG.sync.read(fs.readFileSync(outDir + '/b.png'));
    const width = Math.min(a.width, c.width), height = Math.min(a.height, c.height);
    const diff = new PNG({ width, height });
    const n = pixelmatch(a.data, c.data, diff.data, width, height, { threshold: 0.1 });
    fs.writeFileSync(outDir + '/diff.png', PNG.sync.write(diff));
    if (a.width !== c.width || a.height !== c.height) console.log('SIZE-DIFF a=' + a.width + 'x' + a.height + ' b=' + c.width + 'x' + c.height);
    console.log('MISMATCH pixels:', n);
  } else {
    const a = fs.readFileSync(outDir + '/a.png'), c = fs.readFileSync(outDir + '/b.png');
    console.log('MISMATCH pixels:', a.equals(c) ? 0 : 'BYTE-DIFF (install pixelmatch+pngjs for exact pixel count)');
  }
  await b.close();
})().catch(e => { console.error('ERR:' + e.message); process.exit(1); });
