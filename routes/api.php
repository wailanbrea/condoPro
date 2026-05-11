<?php

use App\Http\Controllers\Api\MobileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile API Routes (CondoPro App)
|--------------------------------------------------------------------------
*/

// Login
Route::post('/mobile/login', [MobileController::class, 'login']);

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/mobile/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/mobile/condominiums', [MobileController::class, 'condominiums']);
    Route::get('/mobile/gas-periods/current', [MobileController::class, 'currentPeriod']);
    Route::get('/mobile/apartments/readings', [MobileController::class, 'apartmentReadings']);
    Route::post('/mobile/gas-readings', [MobileController::class, 'storeReading']);
    Route::post('/mobile/gas-readings/bulk-sync', [MobileController::class, 'bulkSync']);
    Route::get('/mobile/gas-inventory', [MobileController::class, 'gasInventory']);
    Route::get('/mobile/gas-deliveries', [MobileController::class, 'gasDeliveryList']);
    Route::post('/mobile/gas-deliveries', [MobileController::class, 'storeGasDelivery']);
    Route::post('/mobile/gas-deliveries/{gasDelivery}/receiving', [MobileController::class, 'updateGasDeliveryReceiving']);
    Route::post('/mobile/gas-deliveries/{gasDelivery}/complete', [MobileController::class, 'completeGasDelivery']);
    Route::post('/mobile/gas-readings/{reading}/photo', [MobileController::class, 'uploadPhoto']);
});
