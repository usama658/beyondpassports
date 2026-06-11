import { chromium } from 'playwright';

const BASE = 'http://localhost/ukvisa';
const USER = 'admin';
const PASS = 'ukvisa-admin-2026';

const browser = await chromium.launch({ headless: true });
const ctx = await browser.newContext({ viewport: { width: 1400, height: 1000 } });
const page = await ctx.newPage();
page.setDefaultTimeout(90000);
page.setDefaultNavigationTimeout(90000);

// login
await page.goto(`${BASE}/wp-login.php`, { waitUntil: 'domcontentloaded' });
await page.fill('#user_login', USER);
await page.fill('#user_pass', PASS);
await Promise.all([
  page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
  page.click('#wp-submit'),
]);
console.log('after-login URL:', page.url());

// dump admin menu links that look like the importer
await page.goto(`${BASE}/wp-admin/`, { waitUntil: 'domcontentloaded' });
const links = await page.$$eval('#adminmenu a', as =>
  as.map(a => ({ t: a.textContent.trim(), h: a.getAttribute('href') }))
);
const hits = links.filter(l => /kit|import|envato|template|elementor/i.test(l.t + ' ' + l.h));
console.log('MENU HITS:');
for (const h of hits) console.log(`  ${h.t}  ->  ${h.h}`);

await page.screenshot({ path: 'admin-dashboard.png', fullPage: false });
console.log('screenshot: automation/admin-dashboard.png');

await browser.close();
