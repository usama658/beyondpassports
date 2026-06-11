import { chromium } from 'playwright';
import { writeFileSync } from 'node:fs';
writeFileSync('test-doc.txt', 'test passport scan');

const b = await chromium.launch({ headless: true });
const page = await (await b.newContext({ viewport: { width: 1280, height: 1100 } })).newPage();
page.setDefaultTimeout(30000);
const log = [];
const shot = async n => { try { await page.screenshot({ path: `pay-${n}.png`, fullPage: true }); } catch {} };

try {
  await page.goto('http://localhost/ukvisa/apply/?dest=egypt', { waitUntil: 'load' });
  await page.waitForTimeout(3500);

  // Standard tier (Forminator hides the radio) — click label + force change
  await page.locator('label').filter({ hasText: 'Standard' }).first().click({ force: true }).catch(()=>{});
  await page.evaluate(() => { const r = document.querySelector('input[type=radio][value="29"]'); if (r) { r.checked = true; r.dispatchEvent(new Event('change', { bubbles: true })); } });
  await page.waitForTimeout(1000);

  await page.fill('input[name="name-1-first-name"]', 'Test').catch(()=>{});
  await page.fill('input[name="name-1-last-name"]', 'User').catch(()=>{});
  await page.fill('input[name="email-1"]', 'e2e@example.com').catch(()=>{});
  await page.fill('input[name="text-1"]', '987654321').catch(()=>{});
  const up = page.locator('input[type=file]').first();
  if (await up.count()) await up.setInputFiles('test-doc.txt').catch(()=>{});
  await page.waitForTimeout(2000);
  await shot('1-filled');

  // Stripe combined Card Element — find the iframe with input[name=cardnumber], retry
  let filled = false;
  for (let attempt = 0; attempt < 4 && !filled; attempt++) {
    for (const fr of page.frames()) {
      const id = fr.url() + ' ' + fr.name();
      if (!/stripe/i.test(id)) continue;
      try {
        const num = fr.locator('input[name="cardnumber"]');
        if (await num.count() && await num.isVisible().catch(()=>false)) {
          await num.click({ timeout: 4000 });
          await num.fill('4242424242424242');
          await fr.locator('input[name="exp-date"]').fill('1234').catch(()=>{});
          await fr.locator('input[name="cvc"]').fill('123').catch(()=>{});
          const pc = fr.locator('input[name="postal"]');
          if (await pc.count().catch(()=>0)) await pc.fill('SW1A1AA').catch(()=>{});
          filled = true; break;
        }
      } catch {}
    }
    if (!filled) await page.waitForTimeout(2500);
  }
  log.push('card filled: ' + filled);
  await shot('2-card');

  // 2-page form: NEXT then final SUBMIT
  const next = page.locator('.forminator-button-next, button:has-text("Next")').first();
  if (await next.count()) { await next.click().catch(()=>{}); await page.waitForTimeout(2500); }
  await shot('2b-finish');
  const submit = page.locator('.forminator-button-submit, button:has-text("Submit"), button:has-text("Pay"), button:has-text("Send")').first();
  if (await submit.count()) { await submit.click().catch(()=>{}); log.push('clicked submit'); }
  await page.waitForTimeout(14000); // Stripe charge + redirect
  await shot('3-result');
  const after = (await page.textContent('body')).replace(/\s+/g, ' ');
  log.push('thank-you: ' + /thank you|received|application has been|confirmation/i.test(after));
  log.push('error-msg: ' + (/declined|incomplete|invalid card|error processing/i.test(after) ? 'yes' : 'no'));
} catch (e) { log.push('FATAL: ' + e.message.split('\n')[0]); await shot('err'); }
finally { console.log(log.join('\n')); await b.close(); }
