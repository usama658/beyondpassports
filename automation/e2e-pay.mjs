import { chromium } from 'playwright';
import { writeFileSync } from 'node:fs';
writeFileSync('test-doc.txt', 'test passport scan');

const b = await chromium.launch({ headless: true });
const page = await (await b.newContext({ viewport: { width: 1280, height: 1100 } })).newPage();
page.setDefaultTimeout(30000);
const log = [];
const shot = async n => { await page.screenshot({ path: `pay-${n}.png`, fullPage: true }); };

try {
  await page.goto('http://localhost/ukvisa/apply/?dest=egypt', { waitUntil: 'load' });
  await page.waitForTimeout(3000);

  // tier Standard — Forminator hides the radio; click the visible label
  await page.locator('label').filter({ hasText: 'Standard' }).first().click({ force: true }).catch(()=>{});
  await page.waitForTimeout(500);
  // also force the underlying radio + change event for the calculation JS
  await page.evaluate(() => {
    const r = document.querySelector('input[type=radio][value="29"]');
    if (r) { r.checked = true; r.dispatchEvent(new Event('change', { bubbles: true })); r.dispatchEvent(new Event('click', { bubbles: true })); }
  });
  await page.waitForTimeout(1500);

  // applicant fields (single-page form)
  await page.fill('input[name="name-1-first-name"]', 'Test').catch(()=>{});
  await page.fill('input[name="name-1-last-name"]', 'User').catch(()=>{});
  await page.fill('input[name="email-1"]', 'e2e@example.com').catch(()=>{});
  await page.fill('input[name="text-1"]', '987654321').catch(()=>{});
  // upload
  const up = page.locator('input[type=file]').first();
  if (await up.count()) await up.setInputFiles('test-doc.txt').catch(()=>{});
  await page.waitForTimeout(1500);

  // read total
  const bodyTxt = (await page.textContent('body')).replace(/\s+/g,' ');
  const tm = bodyTxt.match(/Total payable[\s\S]{0,40}?£\s*([0-9]+(?:\.[0-9]+)?)/i);
  log.push('TOTAL: ' + (tm ? tm[1] : 'not found'));
  await shot('1-filled');

  // Stripe combined Card Element (legacy): one iframe, fields cardnumber/exp-date/cvc/postal
  let filled = false;
  const sf = page.frameLocator('.forminator-stripe-element iframe, iframe[name^="__privateStripeFrame"], iframe[title*="Secure"]').first();
  try {
    await sf.locator('input[name="cardnumber"]').fill('4242424242424242', { timeout: 10000 });
    await sf.locator('input[name="exp-date"]').fill('1234');
    await sf.locator('input[name="cvc"]').fill('123');
    const postal = sf.locator('input[name="postal"]');
    if (await postal.count().catch(()=>0)) await postal.fill('SW1A1AA');
    filled = true;
  } catch (e) { log.push('stripe-fill err: ' + e.message.split('\n')[0]); }
  log.push('card filled: ' + filled);
  await shot('2-card');

  // submit / pay
  const submit = page.locator('button:has-text("Pay"), button:has-text("Submit"), .forminator-button-submit, button[type=submit]').first();
  if (await submit.count()) { await submit.click(); }
  await page.waitForTimeout(9000);
  await shot('3-result');
  const after = (await page.textContent('body')).replace(/\s+/g,' ');
  log.push('thank-you present: ' + /thank you|received|application has been/i.test(after));
} catch (e) { log.push('ERROR: ' + e.message.split('\n')[0]); await shot('err'); }
console.log(log.join('\n'));
await b.close();
