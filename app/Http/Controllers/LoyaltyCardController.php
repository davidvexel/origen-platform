<?php

namespace App\Http\Controllers;

use App\Domain\Loyalty\Actions\IssueLoyaltyCredential;
use App\Domain\Loyalty\Models\LoyaltyCredential;
use App\Domain\Loyalty\Models\LoyaltyCustomer;
use App\Domain\Loyalty\Support\LoyaltyQrCode;
use Illuminate\Contracts\View\View;

class LoyaltyCardController extends Controller
{
    public function admin(
        LoyaltyCustomer $customer,
        IssueLoyaltyCredential $issuer,
        LoyaltyQrCode $qrCode,
    ): View {
        abort_unless(auth()->user()?->canAccessPanel(filament()->getPanel('admin')), 403);
        $credential = $issuer->execute($customer);

        return view('loyalty.card', [
            'customer' => $customer,
            'credential' => $credential,
            'qrSvg' => $qrCode->svg($customer->external_id),
            'adminPreview' => true,
        ]);
    }

    public function show(string $token, LoyaltyQrCode $qrCode): View
    {
        $credential = LoyaltyCredential::query()
            ->where('token_hash', hash('sha256', $token))
            ->where('status', 'active')
            ->with('customer')
            ->firstOrFail();
        $credential->update(['last_used_at' => now()]);

        return view('loyalty.card', [
            'customer' => $credential->customer,
            'credential' => $credential,
            'qrSvg' => $qrCode->svg($credential->customer->external_id),
            'adminPreview' => false,
        ]);
    }
}
