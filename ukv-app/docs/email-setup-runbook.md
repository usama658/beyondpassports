# Email setup runbook (Beyond Passports)

Canonical reference for creating mailboxes, forwarders and reading mail. Reuse this whenever a new
address is needed (operator signups, role addresses, etc.).

> Boundary: the assistant supplies the exact commands/steps; **you run them** — creating mailboxes or
> logging into the host means authenticating with credentials, which the assistant does not do.
> Credentials live in your **password manager**, never in this repo. If a password/token is ever
> pasted in chat, treat it as exposed and **rotate it**.

## The setup
- **Host:** cPanel on the production host (control panel for `beyondpassports.co.uk`).
- **Master inbox (the only real mailbox):** `hello@beyondpassports.co.uk`.
  - Webmail: `https://beyondpassports.co.uk/webmail` (or `:2096`) → log in as the full address + its
    mailbox password.
- **Everything else = a forwarder into `hello@`** — no separate mailbox or login. A forwarder still
  receives verification/confirmation mail (it lands in hello@); any portal login uses the address +
  that portal's own password.

Existing forwarders → `hello@`: `complaints@`, `privacy@`, `support@`, `billing@`.

## Add a forwarder (preferred — alias, no mailbox/login)
**cPanel UI:** Email → **Forwarders → Add Forwarder** → address e.g. `vfs`, forward to
`hello@beyondpassports.co.uk`.

**SSH / cPanel terminal (per address):**
```
uapi Email add_forwarder domain=beyondpassports.co.uk email=vfs fwdopt=fwd fwdemail=hello@beyondpassports.co.uk
```

**cPanel API token (curl), only if you hold the real cPanel API token:**
```
curl -H 'Authorization: cpanel CPANELUSER:APITOKEN' \
 'https://YOURHOST:2083/execute/Email/add_forwarder?domain=beyondpassports.co.uk&email=vfs&fwdopt=fwd&fwdemail=hello@beyondpassports.co.uk'
```

## Add a real mailbox (only when an address must have its own inbox/login)
**cPanel UI:** Email → **Email Accounts → Create** → username + domain + password (save to vault).

**SSH / cPanel terminal:**
```
uapi Email add_pop email=tls domain=beyondpassports.co.uk password='STRONGPASS' quota=0
```

**cPanel API token (curl):**
```
curl -H 'Authorization: cpanel CPANELUSER:APITOKEN' \
 'https://YOURHOST:2083/execute/Email/add_pop?email=tls&domain=beyondpassports.co.uk&password=STRONGPASS&quota=0'
```

## Read / verify mail
- Webmail (above) → open hello@ → click verification links.
- On a phone: same webmail URL (Roundcube) works.

## Rule of thumb
- New role/operator address you only need to *receive* on → **forwarder → hello@**.
- Address that needs its own login/storage → **mailbox**.
- Don't create per-applicant inboxes; reuse hello@ / one operator login per portal
  (see [portal-accounts-setup.md](portal-accounts-setup.md)).
