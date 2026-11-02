<?php
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

Artisan::command('carfolk:admin', function() {
   $name=text('Administrator full name',required:true);
   $email=text('Administrator email address',required:true);
   $secret=password('Administrator password (minimum 12 characters)',required:true);
   if(!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($secret)<12){
       $this->error('Invalid email or password shorter than 12 characters.');
       return 1;
   }
   User::updateOrCreate(['email'=>strtolower(trim($email))],[
      'name'=>trim($name),
      'password'=>Hash::make($secret),
      'is_admin'=>true,
      'is_active'=>true,
   ]);
   $this->info('Dealership administrator created or updated. Do not share the password.');
   return 0;
})->purpose('Provision the single Carfolk dealership administrator securely.');

/**
 * Minimum-data retention helper. Never delete open customer requests. The
 * approved retention period and authorized execution are required separately.
 */
Artisan::command('carfolk:privacy-prune {--execute}', function() {
    $approved = (bool) config('carfolk.retention_approved');
    $days = (int) config('carfolk.retention_days');
    if (!$approved || $days < 30 || $days > 3650) {
        $this->error('Pruning blocked: an approved 30–3650 day retention policy is required.');
        return 1;
    }
    $cutoff = now()->subDays($days);
    $enquiries = \App\Models\VehicleEnquiry::where('status','closed')->where('created_at','<',$cutoff);
    $viewings = \App\Models\TestDriveRequest::whereIn('status',['completed','cancelled'])->where('created_at','<',$cutoff);
    $a = $enquiries->count();
    $b = $viewings->count();
    if (!$this->option('execute')) {
        $this->info("DRY RUN ONLY: {$a} closed enquiries and {$b} finalized viewings eligible for retention cleanup.");
        return 0;
    }
    \Illuminate\Support\Facades\DB::transaction(function () use ($enquiries,$viewings) {
        $enquiries->delete();
        $viewings->delete();
    });
    $this->info("Executed approved retention cleanup: {$a} closed enquiries and {$b} finalized viewings.");
    return 0;
})->purpose('Dry-run or explicitly execute client-approved lead retention (closed cases only).');

/**
 * Add five ordinary-looking vehicles for exercising local dealership operations.
 * Existing admin edits are preserved when this command is repeated.
 */
Artisan::command('carfolk:local-inventory {--publish : Publish newly created local vehicles to the local website}', function () {
    if (!\Database\Seeders\LocalInventorySeeder::isAllowed()) {
        $this->error('Refused: this command only runs for local/testing PostgreSQL on localhost with database carfolk (or carfolk_test).');
        return 1;
    }

    $result = app(\Database\Seeders\LocalInventorySeeder::class)
        ->run((bool) $this->option('publish'));

    $this->info("Carfolk inventory ready: {$result['created']} created, {$result['existing']} already present.");
    $this->line('Existing records were not changed. Open http://127.0.0.1:8000/manage/vehicles');

    if (!$this->option('publish')) {
        $this->comment('New entries are drafts. Publish them from the admin to display them on the local website.');
    }

    return 0;
})->purpose('Create five illustrative vehicles for the local Carfolk administrator.');


/**
 * For client-supplied, individually verified dealer stock only.
 * The CSV must remain outside webroot/source control and contain no visitor PII.
 * Production writes require two explicit switches. All records remain unpublished.
 */
Artisan::command('carfolk:inventory-import {file : Absolute path to the owner-approved CSV} {--apply : Write drafts after validation} {--attested : Operator confirms the inventory came from Carfolk LTD}', function () {
    try {
        $rows = \App\Support\InventoryCsvImport::parse((string)$this->argument('file'));
        $this->info(count($rows).' vehicle rows verified against CSV format and unique stock references.');
        $this->warn('CSV validation does not independently prove vehicle ownership, price, condition or photo rights.');
        if (!$this->option('apply')) {
            $this->info('DRY RUN ONLY: zero records changed. To import drafts use --apply --attested after client verification.');
            return 0;
        }
        if (!$this->option('attested')) {
            $this->error('Refused: --attested is required for database writes.');
            return 1;
        }
        $count = \App\Support\InventoryCsvImport::apply($rows);
        $this->info("IMPORTED {$count} UNPUBLISHED DRAFTS. Upload verified photos and approve each listing in admin.");
        return 0;
    } catch (\Throwable $error) {
        $this->error('Inventory import BLOCKED: '.$error->getMessage());
        return 1;
    }
})->purpose('Validate approved Carfolk stock CSV and import only unpublished draft vehicles.');

/**
 * Inspects the actual application/database state without transmitting or
 * printing customer contact details or secrets. Not a substitute for live UAT.
 */
Artisan::command('carfolk:launch-audit', function () {
    $checks = [];
    $checks['Production mode, debug disabled'] = app()->environment('production') && config('app.debug') === false;
    $origin = rtrim((string) config('app.url'), '/');
    $publicOrigin = rtrim((string) config('carfolk.public_url'), '/');
    $checks['Configured production HTTPS origin'] = str_starts_with($origin, 'https://')
        && !str_contains($origin, 'example.') && $origin === $publicOrigin;
    $checks['PostgreSQL selected'] = config('database.default') === 'pgsql';
    $checks['Approved HTTPS privacy URL configured'] = is_string(config('carfolk.privacy_url'))
        && filter_var(config('carfolk.privacy_url'), FILTER_VALIDATE_URL)
        && str_starts_with(config('carfolk.privacy_url'), 'https://')
        && !str_contains(config('carfolk.privacy_url'), 'example.');
    $checks['Retention policy approved and configured'] = config('carfolk.retention_approved')
        && config('carfolk.retention_days') >= 30 && config('carfolk.retention_days') <= 3650;
    $checks['Production lead acceptance enabled'] = \App\Support\LeadAcceptance::approvedEnabled();
    $checks['Local developer enquiry mode disabled'] = !config('carfolk.local_enquiries_enabled');
    try {
        \Illuminate\Support\Facades\DB::select('SELECT 1');
        $checks['PostgreSQL connection successful'] = true;
        $site = \App\Models\SiteSetting::first();
        $checks['Business contact values configured (client verification remains manual)'] =
            $site !== null && collect(['phone','whatsapp','email','address','opening_hours','about'])
                ->every(fn($field) => is_string($site->{$field}) && trim($site->{$field}) !== '');
        $inventory = \App\Models\Vehicle::published()->where('status', 'available');
        $checks['Available published inventory exists'] = (clone $inventory)->exists();
        $checks['Every available published car has a photo record'] = (clone $inventory)->exists()
            && !(clone $inventory)->whereDoesntHave('images')->exists();
        $checks['Local sample stock is not published'] = !\App\Models\Vehicle::published()
            ->whereIn('stock_reference', ['CF-1001','CF-1002','CF-1003','CF-1004','CF-1005'])->exists();
        $checks['Active authorized administrator exists'] = \App\Models\User::query()
            ->where('is_admin', true)->where('is_active', true)->exists();
    } catch (\Throwable $error) {
        $checks['PostgreSQL connection successful'] = false;
        $this->warn('Database-dependent inventory, contacts and administrator checks could not be completed.');
    }
    foreach ($checks as $label => $ok) $this->line(($ok ? 'PASS' : 'BLOCKED').': '.$label);
    if (in_array(false, $checks, true)) {
        $this->error('NO GO: automated production prerequisites remain unmet.');
        return 1;
    }
    $this->info('AUTOMATED CHECKS PASS, BUT NOT A LIVE LAUNCH CERTIFICATE.');
    $this->warn('Still required: client-signed stock/contact/privacy approval, actual TLS/DNS, independent security, offsite restore proof, real-domain buyer/admin UAT and final written GO.');
    return 0;
})->purpose('Read-only audit of Carfolk production configuration, contact completeness and published stock.');
