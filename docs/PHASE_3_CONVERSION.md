# Phase 3 — Dealership Information & Customer Conversion

## Delivered frontend experiences
1. **Dedicated About page** (#/about): client branding, grounded company introduction, platform capabilities, buyer journey, CTA.
2. **Dedicated Contact page** (#/contact): verified contact channel cards, conditional WhatsApp/call/email links, business address/hours only when supplied.
3. **Vehicle-specific enquiry flow** (#/contact?intent=vehicle&vehicle=slug): originating vehicle context carried into the form.
4. **Viewing/test-drive request flow** (#/contact?intent=test-drive&vehicle=slug): vehicle, proposed Nairobi-local date/time, customer information and consent.
5. **General enquiry form** with responsive fields, accessible messages, validation, privacy-link disclosure and an anti-bot honeypot.
6. **Preview Privacy Notice** (#/privacy) explains the current data handling limitations; must be legally reviewed/replaced before live collection.
7. **Connected CTAs** from landing page, catalogue vehicle cards and vehicle details to contact journeys.
8. **Public business channels** configured only from client-verified `VITE_CARFOLK_*` variables. Without verified values, no WhatsApp, call, email or map URL is invented.
9. **Automated unit coverage** for validation, Nairobi date, contact URL generation, and conversion routing.

## Critical distinction: preview versus real submissions
**No visitor enquiry or booking is transmitted, persisted, emailed or accepted in Phase 3.** The form explicitly asks users to enter *fictitious* data and validates on the browser only. A validated form displays “Validation successful — this is only a preview”; not “message sent” or “appointment confirmed”.

The static GitHub Pages preview cannot securely collect personal leads. Phase 4 must provide a Laravel API and PostgreSQL persistence with authorization, validation, rate limiting, spam control and appropriate privacy handling. Phase 5 must connect the front end, produce real acknowledgement only after server success, and certify full workflows.

## Pending from Carfolk LTD
- Verify phone number, WhatsApp number, official email address, physical location and operating hours.
- Provide approved About/business copy, official social media links and any genuinely substantiated dealership claims.
- Approve a production privacy notice/retention schedule and decide who responds to enquiries.
- Supply actual verified vehicle stock and legal media assets.
- Decide contact inquiry categories and test-drive/viewing operating process (calendar, cancellation, confirmation).
- Validate production notification and fallback delivery process. Avoid exposing secrets in Vite public vars.

## Architectural API handoff
The UI draft schema contains `kind`, `vehicleId`, `name`, `phone`, optional `email`, `message`, `preferredDate`, `preferredTime`, and explicit `consent`. The API should accept only whitelisted fields and bind `vehicleId` to actual published vehicles. It must not trust synthetic demo IDs in production. Store submission and consent timestamp server-side, send necessary acknowledgement only when accepted, and expose enquiries only to authorized dealership admins.

## Quality gate
`npm run typecheck && npm run test && npm run build`; code must pass GitHub Actions. Preview must maintain non-indexing and clear sample disclosures until real content and privacy controls are ready. **Browser-level visual QA remains a separate release validation step.**
