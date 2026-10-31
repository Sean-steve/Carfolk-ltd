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
