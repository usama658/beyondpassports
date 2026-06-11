import { chromium } from 'playwright';
import { writeFileSync } from 'node:fs';

// tiny test upload file
const testFile = 'test-passport.txt';
writeFileSync(testFile, 'test passport scan');

const headed = process.env.HEADED === '1';
const b = await chromium.launch({ headless: !headed, slowMo: headed ? 300 : 0 });
const page = await (await b.newContext({ viewport: { width: 1280, height: 1000 } })).newPage();
page.setDefaultTimeout(30000);
const shot = async n => { await page.screenshot({ path: `e2e-${n}.png` }); console.log('shot e2e-' + n); };

await page.goto('http://localhost/ukvisa/apply/?dest=egypt', { waitUntil: 'load' });
await page.waitForTimeout(2500);
await shot('1-start');

async function clickNext() {
  const btn = page.locator('.forminator-button-next, button:has-text("Next")').first();
  if (await btn.count()) { await btn.click(); await page.waitForTimeout(1500); return true; }
  return false;
}

try {
  // STEP 1: destination + tier
  // destination select (native or forminator)
  const sel = page.locator('select[name="select-1"]');
  if (await sel.count()) { await sel.selectOption('egypt').catch(()=>{}); }
  // tier radio (Standard)
  const std = page.locator('label:has-text("Standard"), input[value="29"]').first();
  if (await std.count()) await std.click().catch(()=>{});
  await page.waitForTimeout(500);
  await shot('2-step1');
  await clickNext();

  // STEP 2: details
  await page.fill('input[name="name-1-first-name"], input[name="name-1"]', 'Test').catch(()=>{});
  await page.fill('input[name="name-1-last-name"]', 'User').catch(()=>{});
  await page.fill('input[name="email-1"]', 'test@example.com').catch(()=>{});
  await page.fill('input[name="text-1"]', '123456789').catch(()=>{});
  await shot('3-step2');
  await clickNext();

  // STEP 3: upload
  const up = page.locator('input[type=file]').first();
  if (await up.count()) await up.setInputFiles(testFile).catch(()=>{});
  await page.waitForTimeout(1500);
  await shot('4-step3');
  await clickNext();

  // STEP 4: review + total + stripe
  await page.waitForTimeout(1500);
  const bodyTxt = (await page.textContent('body')).replace(/\s+/g,' ');
  const totalMatch = bodyTxt.match(/Total[^£]*£\s*([0-9.]+)/i);
  console.log('TOTAL on review:', totalMatch ? totalMatch[1] : 'not found');
  await shot('5-step4-review');

  // Stripe card iframe
  const frames = page.frames();
  console.log('frames:', frames.length, frames.map(f=>f.url().slice(0,40)));
  let filled = false;
  for (const f of frames) {
    const num = f.locator('input[name="cardnumber"], input[autocomplete="cc-number"]');
    if (await num.count()) {
      await num.fill('4242424242424242');
      await f.locator('input[name="exp-date"], input[autocomplete="cc-exp"]').fill('12/34').catch(()=>{});
      await f.locator('input[name="cvc"], input[autocomplete="cc-csc"]').fill('123').catch(()=>{});
      await f.locator('input[name="postal"], input[autocomplete="postal-code"]').fill('SW1A1AA').catch(()=>{});
      filled = true; break;
    }
  }
  console.log('stripe card filled:', filled);
  await shot('6-card');

  // submit
  const submit = page.locator('button:has-text("Submit"), .forminator-button-submit').first();
  if (await submit.count()) { await submit.click(); await page.waitForTimeout(6000); }
  const after = (await page.textContent('body')).replace(/\s+/g,' ');
  console.log('SUCCESS msg present:', /thank you|received/i.test(after));
  console.log('ERROR present:', /error|invalid|declined/i.test(after) ? after.match(/[^.]*(error|invalid|declined)[^.]*/i)?.[0] : 'none');
  await shot('7-result');
} catch (e) {
  console.log('FLOW ERROR:', e.message.split('\n')[0]);
  await shot('error');
}
await b.close();
