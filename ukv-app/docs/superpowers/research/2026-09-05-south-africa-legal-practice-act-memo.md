# Research Memo: South Africa — Legal Practice Act 2014 and Immigration-Consultancy Licensing

**Prepared by:** Legal-research-prep assistant (not a lawyer; not legal advice)
**Jurisdiction:** Republic of South Africa
**Date:** 2026-09-05
**Spec relied on:** `ukv-app/docs/superpowers/specs/2026-09-05-south-africa-market-design.md`; `ukv-app/docs/canonical-values.md`

**This memo is a research draft for a qualified South African attorney's review. It is not legal advice. Beyond Passports must not treat any statement below as a compliance conclusion until an attorney has reviewed it.**

---

## 1. Question presented

Does Beyond Passports' South-Africa-facing service (Schengen appointment-booking assistance + document/checklist review, with no representation before any government body, no legal advice on eligibility, no South African entity, and no South African client funds held) fall inside or outside the scope of South Africa's **Legal Practice Act 28 of 2014**'s restrictions on who may render "legal practitioner" services — and is there any **other** South African statutory licensing/registration regime specifically covering immigration-advice or visa-consultancy services that would apply to this business model?

## 2. Business model facts relied on

Per the SA market design spec and canonical values doc, as of 2026-09-05:

- Beyond Passports Ltd is a UK-registered company (Companies House 17331903), operating from the UK only. **No South African registered entity exists or is planned.**
- The service to South-Africa-based customers is: (a) helping the customer track/book a Schengen visa appointment slot at the relevant VAC (VFS Global), and (b) reviewing the customer's own documents against a checklist **before** the customer submits their own application through the official government/consular channel.
- **No representation** before the South African Department of Home Affairs, any Schengen consulate, or any court/tribunal/board.
- **No legal advice** given on eligibility, grounds for a visa, or immigration status.
- **No South African client funds held** — customers pay Beyond Passports' own UK service fee (GBP) via BP's UK Stripe account; BP does not hold, transfer, or manage any government fee or third-party client money.
- If any of these facts change (e.g., BP starts advising on eligibility, or starts touching a South African client's visa/government fee funds), this memo is stale and must be re-run — it does not extend to those states of fact.

Also noted from prior general-purpose research passes this session (already settled, not re-litigated here): ASATA is a voluntary trade body, not a statutory licensor (confirmed on asata.co.za). FICA's accountable-institutions list does not include travel/visa consultancies, and is moot here regardless since BP holds no SA client funds.

## 3. Applicable law/regulators found

### 3a. Legal Practice Act 28 of 2014 ("LPA")

Primary-source access note (read before relying on the quotes below): The Department of Justice's own PDF of the Act (`justice.gov.za`) was fetched but returned as FlateDecode-compressed binary that could not be decoded into readable text. SAFLII (South Africa's principal free case/legislation database) returned **403 Forbidden**, consistent with prior research attempts this session. `polity.org.za` also returned 403. The gov.za dedicated Acts page for LPA 2014 returned 404. After those four primary/quasi-primary routes failed, the statutory text was reached through **acts.co.za**, a private commercial legislation-republishing site (not the government or an official free legal database like SAFLII). Its chapter/section structure is internally consistent (it correctly cross-references LPA sections 24/30 for admission, matches the LPC's own FAQ language on "advocates, attorneys, fidelity fund advocates" under LPA s34, and its citation of the Immigration Amendment Act 13 of 2011 s23 as the repealing provision for a different Act checks out structurally). This is a **downgraded source** relative to an official government or SAFLII copy — the attorney should verify the exact current wording against a paid legal database (LexisNexis/Juta) or a directly-downloaded gov.za copy before relying on it in a filing or opinion.

Text found (quoted verbatim from acts.co.za):

- **Section 1 (Definitions):** *"legal practitioner"* means "an advocate or attorney admitted and enrolled as such in terms of sections 24 and 30, respectively." The Act does not define a standalone term "legal practice" as a restricted activity separate from the "legal practitioner" role.
- **Section 2 (Application of Act):** "This Act is applicable to all legal practitioners and all candidate legal practitioners."
- **Section 33(1) (Authority to render legal services):** "Subject to any other law no person other than a practising legal practitioner who has been admitted and enrolled as such in terms of this Act may, in expectation of any fee, commission, gain or reward — (a) appear in any court of law or before any board, tribunal or similar institution in which only legal practitioners are entitled to appear; or (b) draw up or execute any instruments or documents relating to or required or intended for use in any action, suit or other proceedings in a court of civil or criminal jurisdiction within the Republic."
- **Section 33(2):** "No person other than a legal practitioner may hold himself or herself out as a legal practitioner or make any representation or use any type or description indicating or implying that he or she is a legal practitioner."
- **Section 33(3):** "No person may in expectation of any fee, commission, gain or reward, directly or indirectly, perform any act or render any service which in terms of any other law may only be done by an advocate, attorney, conveyancer or notary, unless that person is a practising advocate, attorney, conveyancer or notary, as the case may be."
- **Section 33(4):** addresses struck-off/suspended practitioners rendering services without Council consent (not relevant to a party that was never enrolled).

Source: `http://www.acts.co.za/legal-practice-act-2014/1__definitions`, `.../2__application_of_act`, `.../33__authority_to_render____` (all fetched 2026-09-05).

The Legal Practice Council's own site (`lpc.org.za`) was checked for a plainer restatement — its homepage, Code of Conduct landing page, and FAQ page were reached, but none of them contain their own independent definition of "legal practice" or "unauthorised practice" beyond restating that the LPC regulates admitted attorneys and advocates and that the Code of Conduct "applies to all legal practitioners... as defined." The FAQ confirms LPA s34's three practitioner categories (attorney, advocate, fidelity fund advocate) but has no dedicated unauthorised-practice explainer. Source: `https://lpc.org.za/legal-practitioners/code-of-conduct/`, `https://lpc.org.za/contact/faq/` (fetched 2026-09-05).

### 3b. Immigration Act 13 of 2002

- The Act **originally contained a Section 46 titled "Immigration practitioners"**, confirming the South African legislature at one point specifically created a named category for people practising immigration services. This section is now marked, on acts.co.za, as: **"[Section 46 repealed by section 23 of Immigration Amendment Act, 2011 (Act No. 13 of 2011)]."** The original pre-2011 text of section 46 (what it actually required) was **not** retrievable this session — acts.co.za does not display repealed text, and no other source was found that quotes it.
- The current Section 1 definitions of the Immigration Act (fetched at `http://www.acts.co.za/immigration-act-2002/1__definitions_and_interpretation`) do **not** contain any definition of "immigration practitioner" — confirming the term has no live statutory meaning in the current Act.
- The Department of Home Affairs' own site (`https://www.dha.gov.za/index.php/immigration-services`, fetched 2026-09-05) contains no mention of any registration, accreditation, or licensing requirement for immigration consultants, visa agents, or third parties assisting applicants for a fee. It states only that "all Immigration related applications are done by VFS Global." This absence on a government homepage is a data point, not a verified negative.
- The Immigration Regulations (subordinate legislation under the Act) were **not checked** this session and may contain provisions that replaced or superseded the repealed s46 — this is an open gap, not a resolved negative.

### 3c. Trade/practitioner corroboration (non-authoritative, supporting signal only)

- **Xpatweb**, an established South African corporate immigration/visa consultancy, describes itself on its own site as a "multidisciplinary team" including "Attorneys, Immigration Consultants, Chartered Accountants, Master Tax Practitioners, a licensed Financial Services Provider (FSP)..." — i.e., it operates openly as a consultancy that is not exclusively staffed by admitted attorneys, and it does **not** cite any immigration-consulting-specific regulatory registration anywhere on its about page. The only specific regulatory badge shown (FSCA/FSP) relates to an unrelated financial-services line of its business, not immigration consulting itself. Source: `https://www.xpatweb.com/about-us/` (fetched 2026-09-05). This is corroborating market-practice signal, not a legal conclusion.
- No South African immigration-focused law firm client-facing explainer distinguishing "visa consultant" from "regulated legal practitioner" was successfully located this session (attempted: michalsons.com, sechaba-inc.co.za [domain did not resolve], globalmigrate.com [parked domain], intergate-immigration.com [404]). This is a gap in corroborating evidence, not a finding either way.

## 4. Analysis — both readings

**Reading that the LPA does NOT cover BP's SA-facing activity:**
LPA s33's restrictions are specific: (i) appearing before a court, board, or tribunal where only legal practitioners may appear; (ii) drafting instruments for use in court civil/criminal proceedings; (iii) holding oneself out as a "legal practitioner"; (iv) performing acts reserved to attorney/advocate/conveyancer/notary under some other law. Beyond Passports' spec explicitly excludes representation before any government body (so no court/tribunal appearance), and a Schengen visa application submitted by the applicant themselves to a VAC/consulate is not "proceedings in a court of civil or criminal jurisdiction." BP does not hold itself out as a legal practitioner, and gives no legal advice on eligibility or grounds. On the plain text of s33(1)-(2), appointment-booking and checklist-based document review for a non-judicial administrative filing reads as falling outside these restrictions. This reading is corroborated by Xpatweb operating as a non-exclusively-attorney immigration consultancy without displaying a sector-specific license, and by DHA's own site not flagging any registration requirement for third-party visa assistance.

**Reading that the LPA (or another regime) MIGHT reach BP's SA-facing activity, or that the question isn't closed:**
s33(3) is a residual catch-all: if **any other South African law** reserves "advice on" or "preparation of" immigration/visa applications to attorneys, s33(3) pulls that activity back within LPA-restricted territory regardless of how s33(1) reads. No such other law was found in this session's searches, but its non-existence has not been affirmatively verified — the Immigration Regulations were not checked, and absence of a hit in a search is not proof of absence. Separately, the fact that the Immigration Act **originally** contained a named "Immigration practitioners" provision (repealed 2011) shows the legislature has, at least once, treated this exact category of activity as requiring its own regulatory framework; without the original text of that repealed section, it cannot be ruled out that its substance migrated elsewhere (regulations, a different act) rather than being abolished outright. Finally, the line between "checklist completeness review" (arguably pure logistics) and "commenting on the substantive sufficiency or merits of an application" (arguably edging toward legal/immigration advice) is fact-dependent on BP's actual client-facing scripts and messaging, not just the spec's stated intent — this research assumes the spec's boundary holds in practice but cannot verify that from statutory text alone.

## 5. Confirmed vs unconfirmed

**Confirmed (primary statutory text reached and quoted this session, via acts.co.za after DOJ PDF and SAFLII both failed):**
- LPA s1: "legal practitioner" = an admitted/enrolled advocate or attorney only.
- LPA s2: the Act applies to legal practitioners and candidate legal practitioners.
- LPA s33(1)-(4): the specific, named restricted activities are court/tribunal appearance, drafting instruments for court proceedings, holding out as "legal practitioner," and other-law-reserved acts (conveyancer/notary etc.) — no broader catch-all restricting general paid advice-adjacent services outside those categories was found.
- Immigration Act 13 of 2002 s46 ("Immigration practitioners") existed at enactment and was repealed by s23 of the Immigration Amendment Act 13 of 2011.
- "Immigration practitioner" is absent from the current Immigration Act's definitions section.
- DHA's own website contains no mention of a registration/licensing requirement for third-party visa/immigration assistance providers.

**Unconfirmed / inference / unreachable:**
- Whether acts.co.za's reproduction of the LPA is a perfectly faithful, fully up-to-date copy — the government's own PDF could not be decoded and SAFLII returned 403 on this and prior attempts. This is a genuine confidence downgrade, not resolved.
- The original (pre-2011) substantive text of Immigration Act s46 — what it actually required of "immigration practitioners" — was not retrievable; only its existence and repeal are confirmed.
- Whether the repeal of s46 was a straight abolition of that licensing category or whether its substance moved into Immigration Regulations (subordinate legislation) not checked this session.
- Whether any other South African statute triggers LPA s33(3)'s catch-all by reserving immigration/visa advice or preparation to attorneys — not ruled out, only not found.
- The LPC's Code of Conduct PDF's actual text (only the surrounding landing page was reachable, not the document content) — could contain broader "unauthorised practice" language not captured here.
- No SA immigration law firm explainer distinguishing "visa consultant" from "regulated legal practitioner" was located — this corroborating-source gap remains open, it is not evidence either way.
- Whether BP's actual (not just spec-documented) client messaging in South Africa stays inside "checklist review" versus straying into commentary on application merits — a business-practice fact question, not a legal-research question, and outside what statutory text alone can settle.

## 6. What the attorney needs to resolve

1. On the plain text of LPA s33(1)-(2) quoted above, does appointment-booking plus checklist-based document review for a non-judicial DHA/Schengen-consulate filing fall outside "appearing before a court/tribunal" and "drafting instruments for court proceedings" — i.e., does the attorney agree the activity as described is outside s33(1)-(2)'s named restrictions?
2. Is the attorney aware of any other South African statute or regulation that would trigger LPA s33(3)'s catch-all by reserving immigration/visa application advice or preparation to attorneys — something this research could not rule out from the sources checked?
3. Can the attorney access, via a paid legal database (LexisNexis/Juta) or a directly-obtained government copy, the original pre-2011 text of Immigration Act s46 ("Immigration practitioners"), and confirm whether its repeal abolished that licensing category outright or shifted it into the Immigration Regulations (not checked this session)?
4. Given that the LPA text relied on here came from a private republisher (acts.co.za) rather than the government's own copy or SAFLII (both unreachable this session), does the attorney want the exact current wording independently verified before this question is treated as closed?
5. Should the Immigration Regulations, 2014 be checked for any surviving or replacement "immigration practitioner" registration requirement before this open compliance item (flagged in the SA market design spec, section "Compliance") is cleared for real ad spend?

**Files referenced:** `ukv-app/docs/superpowers/specs/2026-09-05-south-africa-market-design.md`, `ukv-app/docs/canonical-values.md`
