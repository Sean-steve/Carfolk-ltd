# Carfolk LTD — Production Activation & Real Data Certification

**Release decision: NO GO until Carfolk supplies verified records and a real production host is accessible.** This is an operational checklist, not a claim of deployment or real customer acceptance.

## 1. Required client-controlled evidence
Record the following privately, not in GitHub commits, issues or CI logs:
- Legal/trading name and authorized representative's written approval.
- Approved domain, DNS control and server owner; dedicated PostgreSQL instance and encrypted offsite backup target.
- Official phone, WhatsApp, business email, street address, operating hours, approved About text.
- Approved Kenyan privacy notice hosted at an HTTPS URL; retention schedule and named data protection/security owner.
- Signed vehicle stock list with stock IDs, descriptions, mileage, prices, condition, locations and permission to use vehicle photos.
- Named dealership administrator(s), real lead response owners and escalation procedure.
- Authorized release window and documented GO decision.

## 2. Server deployment (once supplied)
Use infra/docs/host-provisioning.md, infra/nginx/carfolk.conf.template, and infra/scripts/deploy.sh. Do not deploy to a previously used VPS by assumption. Confirm the correct Carfolk host, TLS and separate database before running scripts. Keep APP_ENV=production, APP_DEBUG=false and CARFOLK_LEADS_ENABLED=false initially. Never enter credentials into chat or commit .env.

Preflight from the checkout:
\`\`\`bash
bash infra/scripts/preflight.sh
\`\`\`
Prepare restricted /srv/carfolk/shared/.env from apps/api/.env.production.example; set unique APP_KEY, private PostgreSQL credentials, actual HTTPS domain and CARFOLK_PUBLIC_URL. Before executing the deploy script, provision backup credentials and prove that a restorable database snapshot exists. Explicit release approval remains mandatory. After deploy, run:
\`\`\`bash
bash infra/scripts/production-smoke.sh https://<approved-carfolk-domain>
cd /srv/carfolk/current/apps/api
php artisan carfolk:launch-audit
\`\`\`
The launch-audit correctly fails until the client data, live policy and backend activation are complete. It prints no customer PII or credentials. Green automated checks still do **not** prove authenticity or full live approval.

## 3. Import verified vehicle stock as drafts only
The import header is provided at docs/examples/carfolk-inventory-template.csv. The sample contains **no invented cars**. Keep the completed file outside the public document root and outside git.

\`\`\`bash
cd /srv/carfolk/current/apps/api
php artisan carfolk:inventory-import /private/carfolk/approved-inventory.csv
# only after operator confirms records against signed stock evidence:
php artisan carfolk:inventory-import /private/carfolk/approved-inventory.csv --apply --attested
\`\`\`

The dry run validates 1–500 rows, all-or-nothing columns, unique references/slugs and known vehicle characteristics; writes require **both** flags. Every imported car is an **unpublished draft**, with no gallery photos or claim of authenticity. There is no overwrite and no synthetic fallback. In /manage/vehicles the owner must individually attach licensed vehicle photographs, verify price/status and explicitly publish each stock item. Keep example cars CF-1001 through CF-1005 out of production.

## 4. Publish verified business details
An authorized admin signs in to https://<approved-carfolk-domain>/manage/login and updates **Dealership Settings** with only signed-off phone/WhatsApp/email/location/hours/About. Review https://<approved-carfolk-domain>/api/v1/site for non-sensitive public details. Verify actual WhatsApp, tel:, mailto: and map links on the real website. The command cannot independently attest that supplied details are truly Carfolk's.

## 5. Live customer-enquiry certification — do not skip
1. Confirm approved formal privacy notice, retention/delete process, business contact authenticity, backups and incident owner.
2. With leads **disabled**, confirm POST /api/v1/enquiries and /api/v1/test-drives return 503 and do not persist anything.
3. Once approved, set CARFOLK_PRIVACY_URL to the approved HTTPS notice; enable CARFOLK_LEADS_ENABLED=true via restricted server configuration, refresh Laravel config cache and verify /api/v1/site says leads_enabled=true.
4. With prior Carfolk permission, use an expressly consenting staff-owned test contact on the **actual HTTPS Carfolk website**. Submit one general enquiry, one vehicle-specific enquiry, and one viewing request for a real available car; capture timestamps and server-issued references in restricted test evidence (do **not** post PII into GitHub).
5. In authenticated /manage/enquiries and /manage/viewings, confirm exact matching database records, consent timestamps, vehicle UUIDs, requested Nairobi-local time / UTC storage and new/requested state. Confirm staff response and status transition; requested viewing must never be auto-confirmed.
6. Negative tests: missing consent (422), invalid phone (422), honeypot (422), reserved/sold car viewing (422), excessive request throttling (429), unauthenticated lead list blocked, test no-email-notification assumptions. Use rate-limit tests in staging if they could impact real customers.
7. Verify data deletion/retention permissions, alerts, offsite restore, WAF/TLS, mobile/desktop, accessibility, and customer approval. Record GO sign-off privately.

## 6. Go/No-Go truth
An existing GitHub Pages build is **only a noindex, API-disconnected preview**. CI uses synthetic stock and isolated PostgreSQL; it never substitutes for actual live domain, real customer consent, owner-approved vehicle content, or operational acceptance. Leave public capture disabled if any gate remains unmet.

Deliverable evidence register: infra/docs/launch-acceptance.md. Do not turn on SEO until a separate written approval and real-site crawl review.
