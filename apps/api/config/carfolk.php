<?php
return [
  // The privacy policy must be approved by Carfolk before accepting any contact PII.
  'leads_enabled' => (bool) env('CARFOLK_LEADS_ENABLED', false),
  'privacy_url' => env('CARFOLK_PRIVACY_URL'),
];
