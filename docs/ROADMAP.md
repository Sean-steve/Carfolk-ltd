# Carfolk delivery phases (Model A)

| Phase | Required outcome |
|---|---|
| 0 — Foundation | Branded React/TS scaffold; component tokens; logo; CI; Pages preview; documented boundaries |
| 1 — Landing Page | Approved visual reference adapted to Carfolk: responsive hero, search presentation, featured inventory layout, financing, trust and CTAs |
| 2 — Discovery | Vehicle catalogue, details, gallery, filters, sort, 3-car comparison, financing estimator |
| 3 — Conversion | Dedicated About/Contact/Privacy, vehicle-specific lead forms (preview-only validation), gated verified WhatsApp/call/email, test-drive UX |
| 4 — Backend | Laravel, PostgreSQL, single admin login, vehicle/media CRUD, enquiries, test drives, selected site content |
| 5 — Integration | Real API/data, end-to-end scenarios, performance and accessibility QA, privacy/security checks |
| 6 — Release | SEO implementation, production deployment, smoke tests, handover and sign-off |

**Acceptance gate:** no fake inventory in production, all specified business workflows actually work and are tested, no critical unresolved bugs.

**Commercial boundary:** only single-dealership Model A. Marketplace sellers, checkout, M-PESA and full CRM are not included.

## Phase 5 implementation

React uses Laravel for live inventory/contacts and stores approved leads through a consent-gated API. Cross-system PostgreSQL and Chromium tests prove frontend-to-admin continuity. **Not deployed to a production server**; Phase 6 is required.
