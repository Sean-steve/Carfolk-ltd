# Architecture / Phase 0 decision

**Frontend:** Vite + React 18 + TypeScript and CSS design tokens. This enables a working static GitHub Pages preview throughout UI phases.

**Backend planned:** Laravel REST API + PostgreSQL, dedicated private media storage for sensitive files and public optimized vehicle images, server-side sessions/auth for one admin.

This separated frontend/API direction supersedes the earlier provisional Laravel Inertia option because GitHub Pages previews cannot execute PHP.

## Modules
- Public visitor: site information, published inventory, enquiry and test-drive requests.
- Admin: authentication, vehicle photos and status, enquiries/appointments, selected page settings.
- Shared domain: vehicle, vehicle_image, enquiry, test_drive_request, admin_user, site_setting, audit_event.

## Environment strategy
- Local: Vite preview without a backend.
- Pages: static branded design demonstration, no real customer records or payments.
- Production: proper frontend hosting + HTTPS Laravel API + PostgreSQL backups; not GitHub Pages alone.

## Security and correctness
Public endpoints only expose published vehicle records. Validate all writes on server. Enforce admin authorization; limit request spam. Protect customer PII, implement retention and deletion policy, do not store secrets in Git, ensure timestamps are timezone-safe (UTC storage, Africa/Nairobi display). Ensure SEO-crawlable vehicle URLs before production; SSR or prerendering may be needed.

## Extensibility
Model A is **one dealership only**. The domain can later accommodate dealership ownership via database migration; Phase 0 does not implement marketplace tenancy.
