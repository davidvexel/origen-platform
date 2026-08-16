<?php

namespace App\Http\Controllers;

use App\Domain\Loyalty\Models\LoyaltyRedemption;
use Illuminate\Contracts\View\View;

class LoyaltyRedemptionReceiptController extends Controller
{
    public function __invoke(LoyaltyRedemption $redemption): View
    {
        abort_unless(auth()->user()?->canAccessPanel(filament()->getPanel('admin')), 403);

        return view('loyalty.redemption-receipt', [
            'redemption' => $redemption->load(['customer', 'cashier']),
        ]);
    }
}
