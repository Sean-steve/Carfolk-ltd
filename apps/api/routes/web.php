<?php
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\VehicleAdminController;
use App\Http\Controllers\Admin\LeadAdminController;
use App\Http\Controllers\Admin\SiteAdminController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn()=>redirect()->route('manage.dashboard'));
Route::prefix('manage')->name('manage.')->group(function() {
    Route::get('/login',[AuthController::class,'show'])->name('login');
    Route::post('/login',[AuthController::class,'login'])->middleware('throttle:5,1')->name('login.submit');

    Route::middleware(['auth','admin'])->group(function(){
        Route::get('/',[DashboardController::class,'index'])->name('dashboard');
        Route::post('/logout',[AuthController::class,'logout'])->name('logout');
        Route::get('/vehicles',[VehicleAdminController::class,'index'])->name('vehicles.index');
        Route::get('/vehicles/create',[VehicleAdminController::class,'create'])->name('vehicles.create');
        Route::post('/vehicles',[VehicleAdminController::class,'store'])->name('vehicles.store');
        Route::get('/vehicles/{vehicle}/edit',[VehicleAdminController::class,'edit'])->name('vehicles.edit');
        Route::patch('/vehicles/{vehicle}',[VehicleAdminController::class,'update'])->name('vehicles.update');
        Route::delete('/vehicles/{vehicle}',[VehicleAdminController::class,'archive'])->name('vehicles.archive');
        Route::post('/vehicles/{vehicle}/photos',[VehicleAdminController::class,'upload'])->name('vehicles.photos.add');
        Route::delete('/vehicles/{vehicle}/photos/{image}',[VehicleAdminController::class,'removePhoto'])->name('vehicles.photos.remove');
        Route::get('/enquiries',[LeadAdminController::class,'enquiries'])->name('enquiries');
        Route::patch('/enquiries/{enquiry}',[LeadAdminController::class,'updateEnquiry'])->name('enquiries.update');
        Route::get('/viewings',[LeadAdminController::class,'viewings'])->name('viewings');
        Route::patch('/viewings/{viewing}',[LeadAdminController::class,'updateViewing'])->name('viewings.update');
        Route::get('/settings',[SiteAdminController::class,'edit'])->name('settings');
        Route::patch('/settings',[SiteAdminController::class,'save'])->name('settings.update');
    });
});
