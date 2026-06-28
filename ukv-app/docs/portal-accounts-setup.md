# Portal accounts — setup tracker (do this before first client)

Goal: have an operator account ready on every Schengen appointment portal so a paid order can be
booked same-day. Reuse one account per operator for all applicants (see
[schengen-appointment-portals.md](schengen-appointment-portals.md) for the why + the country→operator map).

> I (the assistant) can't create these accounts or enter credentials — account signup, passwords and
> CAPTCHAs are yours to do. This sheet makes it a 30-minute job. Store every password in a password
> manager (1Password/Bitwarden), never in this repo or chat.

## Step 0 — shared inbox + 2FA phone
- [ ] Ops mailbox live: `appointments@beyondpassports.co.uk` (receives all confirmations).
- [ ] A business mobile for SMS 2FA (some portals require it).
- [ ] Password manager vault created, shared with whoever books.

## Step 1 — register the ~7 operator accounts
For each: open the signup URL, register with the ops mailbox, set a strong password (save to vault),
add phone, turn on 2FA if offered, then tick done.

| Operator | Covers | Sign-up URL | Account email | 2FA | Done |
|---|---|---|---|---|---|
| **VFS Global** | most countries (Austria, Bulgaria, Croatia, Czechia, Denmark, Estonia, Finland, Hungary, Iceland, Italy, Latvia, Liechtenstein, Lithuania, Malta, Netherlands, Norway, Portugal, Slovenia, Sweden) | https://visa.vfsglobal.com/gbr/en/ (Register) | hello@… | ☐ | ☐ |
| **TLScontact** | Germany, France, Belgium, Switzerland | https://visas-de.tlscontact.com/ (and per-country) | hello@… | ☐ | ✅ |
| **BLS International** | Spain | https://uk.blsspainglobal.com | hello@… | ☐ | ☐ |
| **GVCW** | Greece | https://uk-gr.gvcworld.eu/en | hello@… | ☐ | ☐ |
| **e-Konsulat** | Poland | https://secure.e-konsulat.gov.pl/ | hello@… | ☐ | n/a — per-application |
| **eViza** | Romania | https://eviza.mae.ro/ | hello@… | ☐ | n/a — per-application |
| **ezov.mzv.sk** | Slovakia | https://ezov.mzv.sk | hello@… | ☐ | n/a — per-application |
| Luxembourg | (no portal) | email `Londres.consulat@mae.etat.lu` to book | — | — | ☐ |

(Account emails can all be the one ops inbox `hello@`, or per-operator **forwarders** that land in `hello@`.)

### Optional — per-operator forwarders (all land in hello@, no extra mailbox/login)
cPanel → Email → **Forwarders → Add Forwarder**: address `vfs`, forward to `hello@beyondpassports.co.uk`.
Repeat for `tls bls gvcw pl ro sk`. Or via SSH (per address):

```
uapi Email add_forwarder domain=beyondpassports.co.uk email=vfs fwdopt=fwd fwdemail=hello@beyondpassports.co.uk
```

A forwarder receives the portal's verification mail (delivered to hello@) — the portal login itself is
the address + the portal password, so no separate mailbox is needed.

## Step 2 — request agent / business accounts (the scale unlock)
A normal account books one applicant at a time. An agent/B2B account lets one login manage many
applicants — essential for volume. Email each operator's B2B team to request it. Template:

> Subject: Business / agent account request — Beyond Passports (UK visa facilitation)
>
> Hello,
>
> Beyond Passports is a UK-registered visa-facilitation business preparing and submitting Schengen
> short-stay applications for our clients. We expect regular volume and would like a business / agent
> account to manage multiple applicants under one login.
>
> Could you tell us: how to open a corporate/agent account, any volume or contract requirements, and
> whether priority/bulk appointment booking is available to partners?
>
> Company: Beyond Passports Ltd · Contact: [name] · [phone] · appointments@beyondpassports.co.uk
>
> Thank you.

- [ ] VFS Global B2B request sent
- [ ] TLScontact group/agent request sent
- [ ] BLS / GVCW partner enquiry sent (if available)

## Step 3 — record + verify
- [ ] All logins saved in the password manager, keyed by operator.
- [ ] Test-login each portal once (no booking) to confirm access + that 2FA works.
- [ ] Note each operator's slot-release rhythm (e.g. Croatia ~15th, Portugal Thu) in
      [schengen-appointment-portals.md](schengen-appointment-portals.md).

## Before booking for a real client (reminder)
- [ ] Signed letter of authority / consent to act + handle their data (GDPR).
- [ ] Applicant details ready (name, passport no., travel dates).
- [ ] Record the operator booking reference on the order (Admin → Appointments).
