import { openAdmin, BASE } from './lib.mjs';
const ZIP = 'C:\\Users\\mumya\\Downloads\\travisa-immigration-visa-consulting-elementor-2023-11-27-05-08-14-utc.zip';
const MISSING = ['Home', 'Metfrom Registration', 'Visa', 'About', 'Pricing', 'Blog', 'Metform Contact', 'Header', '404 Page'];

const { browser, page } = await openAdmin();
page.setDefaultTimeout(45000);
await page.goto(`${BASE}/wp-admin/tools.php?page=template-kit-import`, { waitUntil: 'domcontentloaded' });
await page.waitForTimeout(3000);
await page.setInputFiles('#upload-template-kit-zip-file', ZIP);
await page.waitForTimeout(16000);

for (const title of MISSING) {
  try {
    // clear any leftover overlay
    await page.keyboard.press('Escape').catch(() => {});
    await page.mouse.wheel(0, -5000).catch(() => {});
    const heading = page.getByText(title, { exact: true }).first();
    await heading.scrollIntoViewIfNeeded({ timeout: 20000 });
    const card = heading.locator('xpath=ancestor::*[.//a[contains(normalize-space(.),"Import Template")] or .//button[contains(normalize-space(.),"Import Template")]][1]');
    const link = card.locator('a:has-text("Import Template"), button:has-text("Import Template")').first();
    await link.click({ timeout: 20000 });
    await page.waitForTimeout(1500);
    try { await page.locator('text=/Importing/i').first().waitFor({ state: 'detached', timeout: 45000 }); } catch {}
    await page.waitForTimeout(2000);
    const close = page.locator('button:has-text("Close dialog"), [aria-label="Close"]');
    if (await close.count()) { try { await close.first().click({ timeout: 3000 }); } catch {} }
    console.log(`OK: ${title}`);
  } catch (e) {
    console.log(`FAIL: ${title} :: ${e.message.split('\n')[0]}`);
  }
}
await page.screenshot({ path: 'import-missing-done.png', fullPage: true });
console.log('DONE');
await browser.close();
