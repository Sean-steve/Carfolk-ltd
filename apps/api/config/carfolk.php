<?php
return [
    // A valid URL does not replace real legal approval. Leave PII capture OFF until approval.
    'leads_enabled' => (bool) env('CARFOLK_LEADS_ENABLED', false),
    'privacy_url' => env('CARFOLK_PRIVACY_URL'),
    // Test-only opt-in: requires APP_ENV=local, localhost PostgreSQL and local requests.
    'local_enquiries_enabled' => (bool) env('CARFOLK_LOCAL_ENQUIRIES_ENABLED', false),

    // Search visibility must be explicitly approved after real inventory/domain verification.
    'seo_enabled' => (bool) env('CARFOLK_SEO_ENABLED', false),
    'public_url' => env('CARFOLK_PUBLIC_URL'),
    'retention_approved' => (bool) env('CARFOLK_RETENTION_APPROVED', false),
    'retention_days' => (int) env('CARFOLK_RETENTION_DAYS', 0),
];
