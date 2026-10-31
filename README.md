# Carfolk LTD — Premium Digital Showroom

**Brand:** Quality & Affordability · **Package:** Model A · Own-inventory dealership website.

## Delivery state
- **Phase 0:** React/TypeScript frontend foundation, reusable brand/components, design preview, CI and architecture documentation.
- **Phase 1:** Reconstruct the approved landing-page design for Carfolk.
- **Phase 2:** Vehicle catalogue/details, comparison and calculator.\n- **Phase 3:** Dedicated About, Contact and Privacy preview pages; enquiry/viewing UX with browser-only validation and configurable verified contact channels.
- **Phase 4:** Laravel + PostgreSQL admin/inventory API.
- **Phases 5–6:** Real integration, QA, SEO, security, deployment and handover.

The current website is a **design-system preview**. It is not an operational vehicle-selling platform.

## Start locally
```bash
cd apps/web
npm install
npm run dev
# npm run typecheck
# npm run build
```

## Repository
```text
apps/web/       Vite + React + TypeScript UI
apps/api/       Planned Laravel API boundary (Phase 4)
docs/           Product, architecture, data and design contracts
.github/        Frontend CI and optional static Pages deployment
```

## Preview hosting
GitHub Pages may host the **static** frontend preview. It cannot host PHP/Laravel or PostgreSQL. Backend and production deployment are distinct deliverables.

## Docs
- [Model A scope](docs/MODEL_A_SCOPE.md)
- [Roadmap](docs/ROADMAP.md)
- [Architecture](docs/ARCHITECTURE.md)
- [Design system](docs/DESIGN_SYSTEM.md)
- [API contract](docs/API_CONTRACT.md)
- [Data model](docs/DATA_MODEL.md)

All displayed demo elements are illustrative only. Do not publish fictitious vehicles, prices, review scores, dealer statistics, or inventory as real.

## Phase 3 business contact configuration

See `apps/web/.env.example` and `docs/PHASE_3_CONVERSION.md`. All `VITE_CARFOLK_*` values are **public** and must contain only client-confirmed business information. No real contact data or test-drive forms are submitted on GitHub Pages.
