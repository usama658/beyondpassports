import { openAdmin, BASE } from './lib.mjs';
const ZIP = 'C:\\Users\\mumya\\Downloads\\travisa-immigration-visa-consulting-elementor-2023-11-27-05-08-14-utc.zip';

const { browser, page } = await openAdmin();
page.setDefaultTimeout(60000);
await page.goto(`${BASE}/wp-admin/tools.php?page=template-kit-import`, { waitUntil: 'domcontentloaded' });
await page.waitForTimeout(3000);
await page.setInputFiles('#upload-template-kit-zip-file', ZIP);
await page.waitForTimeout(16000);

const sel = 'a:has-text("Import Template"), button:has-text("Import Template")';
const n = await page.locator(sel).count();
console.log('templates to import:', n);

// capture each card's title (sibling text) for logging
for (let i = 0; i < n; i++) {
  const link = page.locator(sel).nth(i);
  let title = '';
  try { title = (await link.locator('xpath=ancestor::*[self::div][1]').innerText()).split('\n')[0].trim(); } catch {}
  try {
    await link.scrollIntoViewIfNeeded();
    await link.click({ timeout: 30000 });
    // wait for the importing toast to appear then finish
    await page.waitForTimeout(1500);
    try { await page.locator('text=/Importing/i').first().waitFor({ state: 'detached', timeout: 45000 }); } catch {}
    await page.waitForTimeout(2500);
    // dismiss any open dialog/toast
    const close = page.locator('button:has-text("Close dialog"), .tk-modal-close, [aria-label="Close"]');
    if (await close.count()) { try { await close.first().click({ timeout: 3000 }); } catch {} }
    console.log(`[${i + 1}/${n}] imported: ${title || '(card ' + (i + 1) + ')'}`);
  } catch (e) {
    console.log(`[${i + 1}/${n}] FAILED: ${title} :: ${e.message.split('\n')[0]}`);
  }
}

await page.screenshot({ path: 'import-done.png', fullPage: true });
console.log('DONE. screenshot: automation/import-done.png');
await browser.close();
