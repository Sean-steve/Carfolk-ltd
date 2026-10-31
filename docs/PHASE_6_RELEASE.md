# Carfolk LTD — Phase 6 Production Readiness and Launch Certification

## Result and scope
Phase 6 provides a production deployment blueprint, secure defaults, crawlable server-rendered vehicle detail pages, sitemap/robots gates, PostgreSQL backup and restore-drill tools, release rollback controls, and automated preflight checks.
**Production launch is not certified.** No Carfolk production domain/server, DNS, TLS, verified vehicle inventory, client-approved privacy policy, actual offsite backup, or independent penetration and accessibility assessment are in evidence.

## System deployment
- React/Vite is built at apps/web/dist and served on the same HTTPS domain as the Laravel API (Nginx proxy for /api, /manage, /listing, /robots.txt, /sitemap.xml, /up).
- Laravel /manage remains PHP/session based; admin credentials never travel through public React.
- PostgreSQL 16 must be private and backed up to encrypted offsite storage. Vehicle uploads persist in /srv/carfolk/shared/public-storage, not release folders.
- Release script requires explicit approval, PHP/Node lockfiles, preflight, successful DB backup before migrations, then atomically switches release pointer.
- Code pointer rollback is provided; database migration recovery still requires compatibility and backup review.

## Security baseline
- Active administrators only; session auth and CSRF for admin and cookie secure/httpOnly/same-site settings in production template.
- Strong security headers, no-store on admin responses, upload MIME validation, rate limits and server validation.
- Public lead capture is OFF until CARFOLK_LEADS_ENABLED=true and an approved valid policy URL is present. Production must use HTTPS policy URL.
- Avoid putting APP_KEY, database secrets, approved customer PII or administrator passwords into GitHub secrets logs, public assets or source.
- Independent security tests and an incident-response owner remain release gates.

## SEO strategy
- GitHub Pages preview remains noindex. Production Nginx starts with X-Robots-Tag noindex; dynamic Laravel robots disallows all and sitemap is empty by default.
- After verified live inventory, canonical HTTPS domain and signed sign-off, set CARFOLK_PUBLIC_URL, enable CARFOLK_SEO_ENABLED and remove the Nginx noindex response header.
- /listing/{slug} returns server-rendered HTML with vehicle title, description, canonical, Open Graph fields, KES Offer structured data, real photos and clear availability.
- /sitemap.xml contains only published, nonarchived vehicles; /robots.txt routes are served by Laravel. Admin and API routes remain disallowed from crawling.
- The React UI uses hash routing; indexing all frontend screens and dynamic sitemap pagination beyond 49,999 cars are future enhancement areas.

## Automated certification
- Existing Phase 5 PostgreSQL buyer-to-admin tests and desktop/mobile Chromium end-to-end tests remain required.
- Phase 6 adds dedicated Laravel SEO/headers tests, shell-script syntax validation, backup/deploy preflight, Nginx config syntax check in isolated CI, dependency lock generation, and static preview noindex checks.
- Green CI indicates code readiness only, not that any production host exists.

## Live go/no-go
See infra/docs/launch-acceptance.md. Carfolk and the delivery owner must document verification of (1) real client identity and inventory, (2) legal/privacy approval, (3) live HTTPS domain, (4) independent security/accessibility review, (5) offsite encrypted backup + actual restoration, (6) real-domain customer/admin acceptance, (7) monitoring/on-call, (8) SEO approval, and (9) commercial sign-off.

Until these are evidenced the truthful status is **Phase 6 release tooling implemented; production launch blocked**.
