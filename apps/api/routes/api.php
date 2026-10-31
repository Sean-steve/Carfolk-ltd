<?php
use App\Http\Controllers\Api\ShowroomController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\AdminApiController;
use App\Http\Middleware\EnsureLeadCollectionIsApproved;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function() {
    Route::get('vehicles',[ShowroomController::class,'index']);
    // Must precede the {slug} route.
    Route::get('vehicles/options',[ShowroomController::class,'options']);
    Route::get('vehicles/compare',[ShowroomController::class,'compare']);
    Route::get('vehicles/{slug}',[ShowroomController::class,'show']);
    Route::get('site',[ShowroomController::class,'site']);
    Route::middleware([EnsureLeadCollectionIsApproved::class,'throttle:5,1'])->group(function() {
       Route::post('enquiries',[LeadController::class,'enquiry']);
       Route::post('test-drives',[LeadController::class,'viewing']);
    });
    // Same-origin session authentication + CSRF; admin UI is never hosted on GitHub Pages.
    Route::middleware(['web','auth','admin'])->prefix('admin')->group(function(){
       Route::get('summary',[AdminApiController::class,'summary']);
       Route::get('vehicles',[AdminApiController::class,'vehicles']);
       Route::post('vehicles',[AdminApiController::class,'store']);
       Route::patch('vehicles/{vehicle}',[AdminApiController::class,'update']);
       Route::delete('vehicles/{vehicle}',[AdminApiController::class,'archive']);
       Route::post('vehicles/{vehicle}/images',[AdminApiController::class,'upload']);
       Route::delete('vehicles/{vehicle}/images/{image}',[AdminApiController::class,'removePhoto']);
       Route::get('enquiries',[AdminApiController::class,'enquiries']);
       Route::patch('enquiries/{enquiry}',[AdminApiController::class,'updateEnquiry']);
       Route::get('test-drives',[AdminApiController::class,'viewings']);
       Route::patch('test-drives/{viewing}',[AdminApiController::class,'updateViewing']);
       Route::get('site',[AdminApiController::class,'settings']);
       Route::patch('site',[AdminApiController::class,'saveSettings']);
    });
});
