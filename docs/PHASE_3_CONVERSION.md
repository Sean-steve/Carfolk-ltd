# Phase 3 — Dealership Information & Customer Conversion

## Implemented buyer experiences
- **About** (`#/about`): Carfolk branding and dealership-oriented introduction, without inventing staff biographies or business history.
- **Contact** (`#/contact`): business channels appear only when supplied by the dealership API. WhatsApp, phone, email, address, and map are never invented by the frontend.
- **Vehicle enquiry** (`#/contact?intent=vehicle&vehicle=<slug>`): preserves a published vehicle's identity and submits only the server-issued UUID.
- **Viewing request** (`#/contact?intent=test-drive&vehicle=<slug>`): picks a date and time in East Africa Time; client validation rejects past Nairobi times, and Laravel revalidates the submitted slot.
- **General enquiry**: explicit consent, field-level validation, phone normalization, optional email, message, and anti-spam honeypot.
- **Privacy** (`#/privacy`): context for collection and official privacy link when present, not a replacement for an approved formal notice.
- **Conversion CTAs**: landing, catalogue cards and vehicle detail pages route to the relevant buyer journey.
- **Temporary notifications**: acknowledgements and user feedback animate in, stay readable briefly, then dismiss. A saved enquiry does not mean a viewing is confirmed.

## Current operational truth (Phases 4–5 subsequently integrated)
The original Phase 3 delivered frontend-only validation; that description is now obsolete. The live front end calls Laravel/PostgreSQL **only when the API is connected and server-side lead acceptance is enabled**. An accepted enquiry is saved to the dealership admin inbox with status `new`. An accepted viewing request is saved with status `requested`, and staff must separately confirm it. Neither form should claim that an appointment is automatically booked. The server response determines success, not browser validation.

In local test mode, customer submissions are restricted to explicitly enabled loopback-local PostgreSQL development. **Use invented test identities only.** GitHub Pages without an approved/configured HTTPS API stays read-only with the contact form disabled. Do not submit sensitive personal data to the static preview.

## Operational sequence
1. Visitor chooses a published vehicle or starts a general enquiry.
2. Browser validates inputs and consent; viewing time is checked in Africa/Nairobi.
3. Backend verifies policy/lead-acceptance gate, throttle, vehicle status, dates, and field rules.
4. On HTTP 201, the backend persists the enquiry or viewing. The browser shows a temporary acknowledgement.
5. Authorized admins review `Customer Enquiries` and `Viewing Requests`, follow up manually and update lead statuses.
6. A proposed viewing remains **unconfirmed** until staff changes the status and communicates with the customer.

## Outstanding before real public launch
Client-verified business contacts and location; approved privacy notice and retention policy; deployed production backend, persistent PostgreSQL and media storage; SMTP/notification delivery if desired; server monitoring, backup and recovery; real vehicle inventory, operations handover and public conversion certification.

## Validation
`cd apps/web && npm ci && npm run typecheck && npm run test && npm run build`.
Cross-system CI runs Playwright + isolated PostgreSQL buyer-to-admin flows, and Laravel has feature tests for lead gates and persistence. This does **not** certify production infrastructure or actual email/WhatsApp delivery.
