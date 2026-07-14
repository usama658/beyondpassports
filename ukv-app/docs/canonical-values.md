# Canonical business values

Single human-readable record of real, verified business facts. The live source of
truth is `config/ukv.php` (git-tracked) + prod `.env`; this file mirrors the locked
values so they are easy to find. Update both when a value is confirmed.

| Value | Confirmed | Config key / env | Notes |
|-------|-----------|------------------|-------|
| Companies House number | **17331903** | `ukv.address.company_no` / `UKV_COMPANY_NO` | Renders in site + LP footers and Organization schema |
| Registered company name | Beyond Passports Ltd | `ukv.address.company` / `UKV_COMPANY_NAME` | |
| Public enquiries email | hello@beyondpassports.co.uk | `ukv.email` / `UKV_EMAIL` | |
| Contact/callback lead recipient | **hello@beyondpassports.co.uk** | `ukv.owner_email` / `UKV_OWNER_EMAIL` | Where /contact form emails land; prod .env set to hello@ (was admin@ — nobody watched it) |
| WhatsApp number | **447882747584** | `ukv.whatsapp` / `UKV_WHATSAPP` | Confirmed real (2026-07-12); drives all WhatsApp CTAs + thank-you/appt hand-offs |
| ICO registration number | _not yet registered_ | `ukv.compliance.ico_number` / `UKV_ICO_NUMBER` | Task #215; badge hidden until set |
| Cyber Essentials | _not certified_ | `ukv.compliance.cyber_essentials` / `UKV_CYBER_ESSENTIALS` | Badge hidden until true |
| Professional indemnity insurer | _not set_ | `ukv.compliance.insurer` + `.indemnity` / `UKV_INSURER`, `UKV_INDEMNITY` | Badge hidden until set |
| Registered office address | **Unit 82a James Carter Road, Mildenhall, Bury St. Edmunds, IP28 7DE, United Kingdom** | `ukv.address.line1..postcode` | Renders in footers, /about location card + map, and Organization schema PostalAddress |
| Founded year | see `SiteStats::foundedYear()` | `ukv.stats` | |
| Applications-prepared count | _unverified_ | — | Task #322; "thousands" placeholder in copy until substantiated |

Legend: **bold** = confirmed real; _italic_ = placeholder / pending.
