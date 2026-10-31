# Phase 2 — Vehicle Discovery & Buying Experience

## Implemented
- GitHub Pages-safe hash routes: `#/cars`, `#/cars/:slug`, `#/compare`, `#/finance`; existing home, about and contact anchors remain reachable.
- Car catalogue with search, make/model, sample price, year, body, fuel, transmission, condition, advanced filters, reset, active chips, sorting, responsive filters, and load-more.
- Detail route per demo vehicle with gallery thumbnails, larger view, keyboard navigation, specs, features, related vehicles, and clear availability/contact limitations.
- Shared 3-vehicle comparison shortlist accessible from home, catalogue and detail pages; comparison matrix and removal/clear controls.
- Financing page with deposit, sample APR, term, principal, monthly amortization estimate, total interest and total cost. Calculation tests.
- Navigation links connected, responsive styling and image fallbacks.
- Automated unit tests for filtering/sorting/query round-trip and amortization.

## Important product truth
**ALL VEHICLE RECORDS ARE SYNTHETIC DEMONSTRATION FIXTURES.** They are isolated in `src/data/showroom.ts`, clearly labelled in UI, and not presented as real Carfolk inventory. Photos are illustrative (some may not match exact model); the fallback SVG is also labelled. Prices and financing rates are examples only. No backend/API integration, live enquiries, deposits or verified availability are claimed.

## Phase 3/4 dependency
- Confirm real business location, WhatsApp number, phone, email and operating hours with the client.
- Replace fixtures with real client-uploaded inventory and actual vehicle media via Laravel REST + PostgreSQL (Phase 4/5).
- Implement enquiry/test drive request submission and privacy handling (Phase 3/4/5).
- Validate real listing availability, image licenses, search-engine metadata and SEO indexability before production.
- GitHub Pages only serves the static demo frontend.

## Test expectations
`npm run typecheck && npm run test && npm run build` in apps/web. CI must pass and Pages deploy after merge.
