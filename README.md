# Carfolk LTD — Premium Dealership Website

**Business model:** Model A — own inventory only · **Brand:** Quality & Affordability.

## Project status

| Phase | Result |
|---|---|
| 0 — Foundation | React/TypeScript design system, Carfolk logo and CI |
| 1 — Landing | Branded landing page on GitHub Pages |
| 2 — Discovery | Browse, filter, vehicle gallery, compare and financing estimates (demo cars) |
| 3 — Conversion UX | About, Contact, Privacy preview, vehicle enquiry and viewing form designs (validation only) |
| **4 — Backend** | **Laravel 13, PostgreSQL migrations, admin dashboard, real inventory and lead endpoints, photo management and backend tests** |
| 5 — Integration | **Pending:** replace demo inventory with live API and connect form submissions; certify workflows |
| 6 — Release | **Pending:** production infrastructure, SEO, real content and security/handover gates |

### Repositories and apps

```
apps/web/   React, TypeScript, Vite — GitHub Pages static frontend preview
apps/api/   Laravel 13, PHP 8.3+, PostgreSQL 16 — backend and admin application
docs/       Phase specifications, architecture, API/data contracts and certification notes
.github/    Frontend CI, backend CI and static preview deployment
```

The preview is at [Carfolk LTD on GitHub Pages](https://sean-steve.github.io/Carfolk-ltd/). It is a **static preview, not the live sales platform**. The Laravel admin dashboard is available only when the backend runs on PHP-capable hosting.

### Start the frontend
```bash
cd apps/web
npm install
npm run dev
npm run typecheck && npm run test && npm run build
```

### Start the backend
See [Backend README](apps/api/README.md) for PostgreSQL Docker setup, Composer installation, migrations, admin provisioning and server startup.

### Model A documentation
- [Delivery roadmap](docs/ROADMAP.md)
- [Backend Phase 4](docs/BACKEND_PHASE_4.md)
- [Architecture](docs/ARCHITECTURE.md)
- [API contract](docs/API_CONTRACT.md)
- [Scope and exclusions](docs/MODEL_A_SCOPE.md)
- [Design system](docs/DESIGN_SYSTEM.md)
- [Phase 1](docs/PHASE_1_LANDING.md), [Phase 2](docs/PHASE_2_DISCOVERY.md), [Phase 3](docs/PHASE_3_CONVERSION.md)

### Client data still required
Carfolk must verify actual vehicles/photos/prices, business phone/WhatsApp/email, location, opening hours, and an approved production privacy notice. No fictitious stock, customers or business performance metrics should enter production.

### Constraints
- GitHub Pages cannot run Laravel or PostgreSQL.
- React is **not yet integrated** with the live backend; Phase 5 is the integration gate.
- The project excludes public marketplace sellers, checkout, M-PESA, automated financing approvals, NTSA ownership transfer and native mobile applications.
