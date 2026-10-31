# Phase 1 — Premium Carfolk Landing Page

## Delivered public homepage
- Client logo and responsive primary navigation
- Cinematic automotive hero with meaningful browse/viewing CTAs
- Search dock with make, model, year, body-type preview filtering and budget UI (budget awaits real price data)
- Four value propositions phrased as experience commitments, not unverified dealership certifications
- Featured vehicle layout using **clearly labelled illustrative examples**, not real Carfolk inventory or prices
- Working sample comparison selection (max three) and sample comparison modal
- Working example financing calculator; user-adjustable price, deposit, APR, term; explicit no-offer disclaimer
- About, buyer journey, contact conversion area and branded footer
- Informational interactions for contact channels pending business verification
- Responsive layout, keyboard focus, mobile navigation, Esc-to-close modal, reduced-motion preference

## Not represented as live
Vehicle prices, availability, stock and contact channels are deliberately **not live**. No customer enquiries are submitted, since the API is not yet implemented.

## Before production
1. Obtain and verify Carfolk business phone, WhatsApp, email, location and hours.
2. Replace all illustrative image URLs and sample vehicle cards with licensed photos of client-owned vehicles.
3. Wire search/cards to published inventory API, and capture enquiries/test drives in Laravel.
4. Implement working privacy/contact pages and SEO metadata, remove preview noindex only once content is verified.
5. Verify hero and card images load and optimize actual supplied vehicle photography locally/object storage.
6. Complete browser-level responsive and accessibility testing before release.

## Design references and photos
Layout is inspired by the user-approved automotive showroom mockup. Client-provided Carfolk logo used through `Brand`. Preview automotive photographs are loaded from Unsplash CDN for **illustrative design purposes**; commercial use is generally permitted under the Unsplash License, but actual Carfolk vehicle photographs should replace them before production.
