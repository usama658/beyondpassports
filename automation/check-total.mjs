import { chromium } from 'playwright';
const b = await chromium.launch();
const page = await (await b.newContext({ viewport: { width: 1280, height: 1000 } })).newPage();
page.setDefaultTimeout(30000);
await page.goto('http://localhost/ukvisa/apply/?dest=egypt', { waitUntil: 'load' });
await page.waitForTimeout(2500);
// select Standard tier
const std = page.locator('input[value="29"]').first();
if (await std.count()) await std.check().catch(()=>{});
await page.waitForTimeout(1500);
// read the calculation/total field
const calc = await page.locator('.forminator-calculation, [data-element="calculation-1"], input[name="calculation-1"]').first();
let total = '';
try { total = await calc.inputValue().catch(()=> calc.textContent()); } catch {}
const body = (await page.textContent('body')).replace(/\s+/g,' ');
const m = body.match(/Total payable[^£]*£\s*([0-9.]+)/i) || body.match(/£\s*([0-9.]+)/);
console.log('calc field:', total);
console.log('total on page:', m ? m[1] : 'n/a');
console.log('warnings on page:', /Undefined array key/.test(body) ? 'YES' : 'none');
await page.screenshot({ path: 'total-check.png' });
await b.close();
