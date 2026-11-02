# Phase A.1 — Full-System Carfolk UX Restoration & Cleanup Reconciliation

## Context
The Nov 1 Phase 1 UI cleanup removed genuinely useful task context along with repetitive developer notices. The current Phase A Command Center already restored dashboard operations. This phase rebuilds **all remaining admin modules** and the **public buyer journey** without reverting secure behavior or reintroducing placeholder business facts.

## Admin experience
| Route | Restored/improved |
| --- | --- |
| /manage/vehicles | Actual stock summaries; validated search by make/model/reference; status and draft/public filters; pagination retains filters; actionable empty states |
| /manage/vehicles/create and edit | Grouped identity, pricing, specifications and availability sections; publication guidance; stock context and approved-photo workflow |
| /manage/enquiries | Four factual status totals, status filters, explicit new → contacted → closed follow-up sequence, >48-hour pending indicators, Nairobi-local received times |
| /manage/viewings | Five factual status totals, status filtering, requested/confirmed/completed/cancelled clarification, overdue flag and Nairobi-local dates |
| /manage/settings | Configured-contact completeness summaries, grouped brand/contact/showroom/company copy and operator verification instructions |
| /manage overview | Phase A KPI cards, stock/enquiry charts, recent activity and quick actions retained |

These are authenticated admin features, not a newly deployed public admin. No lead PII in query filters, no new database schema, and no changes to state-transition security.

## Public experience
- Home: a concise 3-step buying journey with links; honest no-inventory state.
- Catalogue: budget and year filter guidance, clarification about published stock and comparing cars, improved no-results recovery.
- Vehicle detail: a concrete pre-visit checklist, documentation/inspection questions, and accurate status explanation.
- Compare: instructions to build a shortlist, explanation for missing specs, a direct next-step link.
- Financing: 3 practical checks for price, lender terms and full ownership cost; illustrative nature unchanged.
- About: a 4-step buyer pathway; no invented Carfolk company history or staff claims.
- Contact: verified channels only; booking guidance always available even when no address/hours are supplied.
- Enquiry form: context for mode selection, an honest disabled-submission explanation, Nairobi-time viewing confirmation reminders, and privacy help. Existing server-side lead gate, CSRF, consent, validation and statuses remain intact.

## Certification
- Dedicated Laravel PostgreSQL feature tests assert inventory filters, summaries, enquiry/viewing filters, restored guidance and strict admin access.
- Existing backend, frontend and browser-to-PostgreSQL CI plus expanded mobile/desktop public and admin UX checks must pass before main merge.
- No screenshots in GitHub Pages can prove the Laravel admin is hosted there. Current Pages is a noindex, disconnected preview and real Carfolk business facts/production host still require owner approval.

## Release boundaries
Do not enable production customer-data collection, indexing, WhatsApp/email defaults, pretend lender approval, or invent inventory. Future improvements may introduce customer follow-up notes, validated sales ledger and comprehensive WCAG audits after owner acceptance.
