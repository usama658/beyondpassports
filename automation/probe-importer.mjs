import { openAdmin, BASE } from './lib.mjs';

const { browser, page } = await openAdmin();
await page.goto(`${BASE}/wp-admin/tools.php?page=template-kit-import`, { waitUntil: 'domcontentloaded' });
await page.waitForTimeout(3000);

// dump file inputs + buttons so we can see the wizard controls
const inputs = await page.$$eval('input[type=file]', els => els.map(e => ({ name: e.name, id: e.id, accept: e.accept })));
const buttons = await page.$$eval('button, a.button, input[type=submit], [role=button]',
  els => els.map(e => (e.textContent || e.value || '').trim()).filter(Boolean).slice(0, 30));
console.log('FILE INPUTS:', JSON.stringify(inputs));
console.log('BUTTONS:', JSON.stringify(buttons));
console.log('BODY SNIPPET:', (await page.textContent('body')).replace(/\s+/g, ' ').slice(0, 600));

await page.screenshot({ path: 'importer-step1.png', fullPage: true });
console.log('screenshot: automation/importer-step1.png');
await browser.close();
