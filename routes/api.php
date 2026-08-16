<?php

use App\Http\Controllers\Api\V1\ConnectorStatusController;
use App\Http\Controllers\Api\V1\SaleController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->middleware('connector.token')
    ->group(function (): void {
        Route::get('/connector', ConnectorStatusController::class)->name('api.v1.connector.show');
        Route::post('/sales', SaleController::class)->name('api.v1.sales.store');
    });
