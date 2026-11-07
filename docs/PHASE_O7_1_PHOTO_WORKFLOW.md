# Carfolk O7.1 — Visible vehicle photo workflow

## Scope delivered

This phase fixes photo-upload discoverability without changing image processing or server upload limits.

- Add Vehicle shows both steps from the beginning, including a visible locked photo workspace that explains why a vehicle record must be saved first.
- The top of the Add/Edit Vehicle page shows a two-stage progress navigator. On edit, the Photos step links to the actual upload workspace.
- Creating a vehicle redirects to the editing page at `#vehicle-photos` with a success message, so the browser navigates to the now-enabled upload section immediately.
- Edit Vehicle shows a prominent **Go to photo upload** call-to-action.
- The upload controls appear **above** existing photos, so users do not have to scroll through the gallery to find the chooser.
- Vehicle Inventory provides an **Add photos** / **Manage photos** link for each row.
- Photo uploads remain restricted to the authenticated active dealership admin.

## Not in O7.1

The current upload pipeline is unchanged: 1–5 JPEG/PNG/WebP photos per request, 8 MiB each, maximum 20 per vehicle. No automatic resizing, image compression, 20-file batch selection, drag/drop sorting, or new public listing preview is claimed in this phase.

Those belong to later O7 phases.

## Local verification

```bash
cd apps/api
php artisan test --filter=VisibleVehiclePhotoWorkflowTest
```

Open the Laravel admin at `http://localhost:8000/manage/vehicles/create`.
Check that the Photos step is visible before creation, save a valid vehicle, and confirm the browser lands on `/manage/vehicles/<id>/edit#vehicle-photos` with the photo picker ready. Check the Vehicle Inventory row's direct photo link and the edit screen's top **Go to photo upload** button. Test on a narrow phone viewport as well.

Production release must follow existing cPanel CI, backup and approval gates; merging into GitHub alone does not deploy to HostPinnacle.
