<?php
namespace App\Support;
class LeadAcceptance
{
    public static function enabled(): bool
    {
        $policy = config('carfolk.privacy_url');
        if (!(bool) config('carfolk.leads_enabled') || !is_string($policy) || !filter_var($policy, FILTER_VALIDATE_URL)) {
            return false;
        }
        // Only CI testing can use an HTTP policy URL. Production MUST be HTTPS.
        return str_starts_with($policy,'https://') || app()->environment('testing');
    }
}
