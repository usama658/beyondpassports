import { openAdmin, BASE } from './lib.mjs';
const ZIP = 'C:\\Users\\mumya\\Downloads\\travisa-immigration-visa-consulting-elementor-2023-11-27-05-08-14-utc.zip';
const { browser, page } = await openAdmin();
page.setDefaultTimeout(45000);
await page.goto(`${BASE}/wp-admin/tools.php?page=template-kit-import`, { waitUntil: 'domcontentloaded' });
await page.waitForTimeout(3000);
await page.setInputFiles('#upload-template-kit-zip-file', ZIP);
await page.waitForTimeout(15000);

// "Import Template" are <a> links styled as buttons
const imp = page.locator('a:has-text("Import Template"), button:has-text("Import Template")');
console.log('import controls:', await imp.count());
await imp.first().click();
await page.waitForTimeout(5000);

const dlgButtons = await page.$$eval('.tk-modal button, [role=dialog] button, .modal button, button',
  els => els.map(e => (e.textContent||'').trim()).filter(Boolean).slice(0,40));
console.log('DIALOG BUTTONS:', JSON.stringify(dlgButtons));
await page.screenshot({ path: 'import-dialog.png', fullPage: true });
console.log('screenshot: automation/import-dialog.png');
await browser.close();
