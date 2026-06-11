import { openAdmin, BASE } from './lib.mjs';
const ZIP = 'C:\\Users\\mumya\\Downloads\\travisa-immigration-visa-consulting-elementor-2023-11-27-05-08-14-utc.zip';
const { page } = await openAdmin();           // HEADED=1 -> visible window; do NOT close
page.setDefaultTimeout(60000);
await page.goto(`${BASE}/wp-admin/tools.php?page=template-kit-import`, { waitUntil: 'domcontentloaded' });
await page.waitForTimeout(3000);
await page.setInputFiles('#upload-template-kit-zip-file', ZIP);
await page.waitForTimeout(16000);
console.log('BROWSER OPEN — kit analyzed. Import "Home" (and anything else), then tell me. Window stays open.');
await page.waitForTimeout(60 * 60 * 1000);     // keep window alive ~1h
