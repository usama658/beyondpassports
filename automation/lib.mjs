import { chromium } from 'playwright';
import { existsSync } from 'node:fs';

export const BASE = 'http://localhost/ukvisa';
const USER = 'admin', PASS = 'ukvisa-admin-2026';
const AUTH = 'auth.json';

export async function openAdmin() {
  const headed = process.env.HEADED === '1';
  const browser = await chromium.launch({ headless: !headed, slowMo: headed ? 400 : 0 });
  const ctx = await browser.newContext({
    viewport: { width: 1400, height: 1000 },
    storageState: existsSync(AUTH) ? AUTH : undefined,
  });
  const page = await ctx.newPage();
  page.setDefaultTimeout(120000);
  page.setDefaultNavigationTimeout(120000);

  // verify session; if not logged in, log in + persist
  await page.goto(`${BASE}/wp-admin/`, { waitUntil: 'domcontentloaded' });
  if (/wp-login\.php/.test(page.url())) {
    await page.fill('#user_login', USER);
    await page.fill('#user_pass', PASS);
    await Promise.all([page.waitForNavigation({ waitUntil: 'domcontentloaded' }), page.click('#wp-submit')]);
    await ctx.storageState({ path: AUTH });
  }
  return { browser, ctx, page };
}
