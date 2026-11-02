# Phase A — Carfolk Admin Command Center & Operational Intelligence

## Status
Implemented on the authenticated Laravel /manage dashboard. GitHub Pages remains a public, API-disconnected static preview.

## Features
- Eight linked real-database KPI cards with restored explanations: active, published, available, reserved, marked sold, drafts, new enquiries, pending viewing requests.
- Available published stock total asking value, clearly distinguished from realized sales.
- Mutually exclusive stock-status chart with visible counts, no illustrative data.
- Seven-day enquiry trend grouped by the Africa/Nairobi date, with zeroes shown honestly.
- Five recent enquiries, five upcoming requested/confirmed viewings, five recently added vehicles, six allowlisted audit actions.
- Operational alerts: aged unanswered enquiries, overdue unconfirmed viewings, published available stock without photo records, unpublished drafts, incomplete contact configuration.
- Four quick links; responsive, accessible no-JavaScript charts; appropriate empty states.

## Important interpretation
Vehicle marked sold is just a CURRENT STOCK STATUS. There is no actual sold_at timestamp, final agreed price, payment ledger or verified revenue in Model A, so the dashboard DOES NOT report monthly sales revenue or profit. Asking value is a sum of published, available asking prices, not realized sales.

New lead submission and admin authentication policies remain unchanged. Dashboard customer details are visible only to authenticated active admins and audit metadata is not displayed. Contact completeness does not prove contact authenticity. Production launch remains separately blocked by real domain, legal, stock and owner-acceptance gates.

## Certification
PostgreSQL feature tests verify authorization, empty state, status partition, Nairobi-local trend, upcoming-only viewings, overdue alerts and audit events. Backend CI, cross-system browser certification, frontend CI and release checks must pass before merge.
