import { openAdmin, BASE } from './lib.mjs';
const ZIP = 'C:\\Users\\mumya\\Downloads\\travisa-immigration-visa-consulting-elementor-2023-11-27-05-08-14-utc.zip';
const { browser, page } = await openAdmin();
page.setDefaultTimeout(45000);
await page.goto(`${BASE}/wp-admin/tools.php?page=template-kit-import`, { waitUntil: 'domcontentloaded' });
await page.waitForTimeout(3000);
await page.setInputFiles('#upload-template-kit-zip-file', ZIP);
await page.waitForTimeout(16000);

// For each Import link, walk up to the card and grab the title text
const map = await page.$$eval('a, button', els => {
  const out = [];
  for (const el of els) {
    if (!/Import Template/i.test(el.textContent || '')) continue;
    // climb up to a container that also holds a heading/title
    let n = el, title = '';
    for (let i = 0; i < 6 && n; i++) {
      n = n.parentElement;
      if (!n) break;
      const h = n.querySelector('h1,h2,h3,h4,h5,.title,[class*=title]');
      if (h && h.textContent.trim() && !/Import Template/i.test(h.textContent)) { title = h.textContent.trim(); break; }
    }
    out.push(title);
  }
  return out;
});
console.log('CARD TITLES (in DOM order):');
map.forEach((t, i) => console.log(`  ${i}: ${t}`));
await browser.close();
