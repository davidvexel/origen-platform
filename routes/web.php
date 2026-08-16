<?php

use App\Http\Controllers\LoyaltyCardController;
use App\Http\Controllers\LoyaltyRedemptionReceiptController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::middleware('auth')->get(
    '/admin/redemptions/{redemption}/receipt',
    LoyaltyRedemptionReceiptController::class,
)->name('loyalty-redemptions.receipt');

Route::middleware('auth')->get('/admin/loyalty-customers/{customer}/card', [LoyaltyCardController::class, 'admin'])
    ->name('loyalty-card.admin');
Route::get('/c/{token}', [LoyaltyCardController::class, 'show'])
    ->where('token', '[A-Za-z0-9]{64}')
    ->middleware('throttle:60,1')
    ->name('loyalty-card.show');
