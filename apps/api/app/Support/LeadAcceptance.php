<?php
namespace App\Support;

class LeadAcceptance
{
    /** Approved privacy notice + explicit switch for live customer requests. */
    public static function approvedEnabled(): bool
    {
        $policy=config('carfolk.privacy_url');
        return (bool)config('carfolk.leads_enabled')
            && is_string($policy) && (bool)filter_var($policy,FILTER_VALIDATE_URL)
            && (str_starts_with($policy,'https://') || app()->environment('testing'));
    }

    /**
     * Explicit opt-in strictly for isolated local development. Never accept
     * customer data through this path on a production/staging host.
     */
    public static function localTestEnabled(): bool
    {
        if(!app()->environment('local') || !config('carfolk.local_enquiries_enabled'))return false;
        if(config('database.default')!=='pgsql')return false;
        $db=config('database.connections.pgsql');
        if(!is_array($db) || !empty($db['url'])
            || !in_array($db['host']??'', ['127.0.0.1','localhost'],true)
            || ($db['database']??'')!=='carfolk')return false;
        // The Laravel server must be accessed from the same computer.
        return in_array(request()->ip(),['127.0.0.1','::1'],true);
    }

    public static function mode(): string
    {
        if(self::approvedEnabled())return 'live';
        if(self::localTestEnabled())return 'local-test';
        return 'disabled';
    }

    public static function enabled(): bool { return self::mode()!=='disabled'; }
}
