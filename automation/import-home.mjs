import { openAdmin, BASE } from './lib.mjs';
const ZIP = 'C:\\Users\\mumya\\Downloads\\travisa-immigration-visa-consulting-elementor-2023-11-27-05-08-14-utc.zip';
const { browser, page } = await openAdmin();
page.setDefaultTimeout(45000);
await page.goto(`${BASE}/wp-admin/tools.php?page=template-kit-import`, { waitUntil: 'domcontentloaded' });
await page.waitForTimeout(3000);
await page.setInputFiles('#upload-template-kit-zip-file', ZIP);
await page.waitForTimeout(16000);

// Click the Import link whose card heading is EXACTLY "Home" (kit grid, not the admin menu)
const res = await page.evaluate(() => {
  const links = [...document.querySelectorAll('a,button')].filter(e => /Import Template/i.test(e.textContent || ''));
  for (const el of links) {
    let n = el;
    for (let i = 0; i < 6 && n; i++) {
      n = n.parentElement;
      const h = n && n.querySelector('h1,h2,h3,h4,h5,.title,[class*=title]');
      if (h && h.textContent.trim() === 'Home') { el.scrollIntoView(); el.click(); return 'clicked'; }
    }
  }
  return 'notfound';
});
console.log('Home click:', res);
await page.waitForTimeout(2000);
try { await page.locator('text=/Importing/i').first().waitFor({ state: 'detached', timeout: 45000 }); } catch {}
await page.waitForTimeout(3000);
await browser.close();
console.log('done');
