<?php

use App\Http\Controllers\LoyaltyRedemptionReceiptController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::middleware('auth')->get(
    '/admin/redemptions/{redemption}/receipt',
    LoyaltyRedemptionReceiptController::class,
)->name('loyalty-redemptions.receipt');
