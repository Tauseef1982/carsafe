<?php

use App\Http\Controllers\TripController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/createTrip',[\App\Http\Controllers\ApiController::class,'createTrip']);
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/testwebhook', [TripController::class, 'cubePayment']);
Route::any('/webhook-store-trip', [\App\Http\Controllers\ApiController::class, 'getWebHookTrip']);
Route::post('/voice/call', [\App\Http\Controllers\ApiController::class, 'voiceCall']);
Route::post('/try-charge', [\App\Http\Controllers\ApiController::class, 'tryrecharge']);
Route::post('/try-charge2', [\App\Http\Controllers\ApiController::class, 'tryrechargefinal']);
Route::post('/add-card', [\App\Http\Controllers\ApiController::class, 'addcard']);
Route::post('/twilio/findvehicle', [\App\Http\Controllers\ApiController::class, 'driverByVehicle']);
Route::post('/twilio/payTripAccount', [\App\Http\Controllers\ApiController::class, 'payTripAccount']);
Route::post('/twilio/payTripCard', [\App\Http\Controllers\ApiController::class, 'payTripCard']);
Route::post('/twilio/payTripCard2', [\App\Http\Controllers\ApiController::class, 'payTripCard2']);
Route::post('/create-trip', [\App\Http\Controllers\ApikeyController::class, 'createTrip'])
    ->middleware('api.key');


