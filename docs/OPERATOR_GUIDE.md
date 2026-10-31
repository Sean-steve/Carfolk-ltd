# Carfolk LTD — Dealership Administrator Guide

Model A serves one dealership selling only its own vehicles. There is no third-party vehicle-owner portal, online checkout or payment collection.

## Access
1. After production is activated, browse to https://<approved-domain>/manage/login over HTTPS.
2. Sign in using administrator credentials provisioned privately with php artisan carfolk:admin.
3. Never share passwords, store passwords in customer enquiry notes or use production admin accounts for testing.
4. On a shared computer, sign out after every session. Access is limited to active authorized administrators.

## Add a car
1. Open Vehicle Inventory > Add Vehicle.
2. Enter a UNIQUE stock reference, make, model, model year, exact current price in KES and availability.
3. Complete the optional trim, mileage, engine, fuel, transmission, condition, location, features and description from confirmed records.
4. Leave Publish publicly OFF until the stock information and image permissions have been verified.
5. Save, open the edit screen, upload approved JPEG/PNG/WebP photos (max 8MB each; 20 images per vehicle). The first photo is primary.
6. Once confirmed, set status Available and enable Publish publicly. Review the public URL and the crawler-friendly /listing/<slug> page.

## Price, reservation and sale changes
- Update the live price immediately when Carfolk changes it; avoid quoting outdated listing screenshots.
- When a car is reserved, set status Reserved; it remains visible but cannot accept new viewing requests.
- When sold, change status to Sold; the public listing updates and viewing CTA is removed.
- Archive to remove a vehicle from the public catalogue. Existing enquiry records remain for the approved retention process.

## Customer enquiries
- In Customer Enquiries, open newly received requests, confirm stock information, contact the buyer privately, and update status to Contacted or Closed.
- The website confirms receipt only after the backend creates a record. Do not assume email notification exists; staff must monitor the inbox.
- Do not disclose the customer's contact information outside authorized dealership personnel.

## Viewing requests
- Review the requested vehicle and Nairobi-local date/time in Viewing Requests.
- Contact the buyer to confirm stock availability and the agreed meeting time.
- Mark Confirmed only after explicit acceptance. Mark Completed or Cancelled after the outcome.
- A website request is NOT a confirmed booking.

## Business settings
- Use Dealership Settings to publish only verified phone, WhatsApp, business email, address, hours, tagline and approved about copy.
- Enquiry collection remains disabled until the operator activates approved production policy controls.

## Data protection & incident procedures
- Obtain written client approval for privacy notice and record retention/deletion schedule before enabling collection.
- Use the explicit dry-run prune command before considering deletion. Only closed/finalized records older than approved retention period can be pruned.
- If unexpected customer data access, suspicious admin activity, lost backups or broken enquiry workflow is observed, disable lead collection and contact the designated incident owner immediately.

## Operational reporting
- Confirm fresh PostgreSQL backups, offsite copy status, storage space and uptime alerts every business day.
- Review inventory accuracy and outstanding enquiries daily; run a monthly restoration drill and document it.
- The delivery team must hand over real admin credentials through a secure channel, not GitHub or this guide.
