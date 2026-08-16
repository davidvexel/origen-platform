<?php

use App\Http\Controllers\Api\V1\SaleController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->middleware('connector.token')
    ->group(function (): void {
        Route::post('/sales', SaleController::class)->name('api.v1.sales.store');
    });
