# PostgreSQL planning model — NOT MIGRATED

- **vehicles:** UUID id, slug unique, stock_ref unique, make, model, trim, year, price_kes BIGINT, mileage_km BIGINT, engine_cc, fuel, transmission, drivetrain, body_type, condition, colour, location, description, features JSONB, status (available/reserved/sold), featured, published, timestamps.
- **vehicle_images:** UUID id, vehicle_id FK, storage_path, alt_text, sort_order, primary, timestamps.
- **vehicle_enquiries:** UUID id, vehicle_id nullable FK, customer name/phone/email, message, status, consent_at, timestamps.
- **test_drive_requests:** UUID id, vehicle_id FK, name/phone/email, requested_at TIMESTAMPTZ, message, status, consent_at, timestamps.
- **admin_users:** login identity, password hash, timestamps.
- **site_settings:** verified about, contact, location, socials, hours, finance parameters.
- **audit_events:** actor, action, record reference, time.

Use constraints/indexes for price, year, status, published, slug and browse filters. Archive sold vehicles instead of deleting historical enquiry associations. Define customer PII retention with client before live operations.
