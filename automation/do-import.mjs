import { openAdmin, BASE } from './lib.mjs';

const ZIP = 'C:\\Users\\mumya\\Downloads\\travisa-immigration-visa-consulting-elementor-2023-11-27-05-08-14-utc.zip';
const { browser, page } = await openAdmin();
await page.goto(`${BASE}/wp-admin/tools.php?page=template-kit-import`, { waitUntil: 'domcontentloaded' });
await page.waitForTimeout(3000);

// set the zip on the (possibly hidden) file input
await page.setInputFiles('#upload-template-kit-zip-file', ZIP);
console.log('zip set, waiting for analysis...');
await page.waitForTimeout(12000); // allow upload + manifest analysis

const buttons = await page.$$eval('button, a.button, input[type=submit], [role=button]',
  els => els.map(e => (e.textContent || e.value || '').trim()).filter(Boolean).slice(0, 40));
console.log('BUTTONS AFTER UPLOAD:', JSON.stringify(buttons));
const body = (await page.textContent('body')).replace(/\s+/g, ' ');
console.log('HAS template names?', /Home|Visa|Pricing|FAQ/.test(body));
console.log('BODY SNIPPET:', body.slice(0, 800));
await page.screenshot({ path: 'importer-step2.png', fullPage: true });
console.log('screenshot: automation/importer-step2.png');
await browser.close();
