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
