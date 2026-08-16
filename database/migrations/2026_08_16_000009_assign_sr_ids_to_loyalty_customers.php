<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        DB::table('loyalty_customers')->whereNull('external_id')->orderBy('id')->get()->each(function (object $customer) use ($alphabet): void {
            do {
                $suffix = '';
                for ($position = 0; $position < 6; $position++) {
                    $suffix .= $alphabet[random_int(0, strlen($alphabet) - 1)];
                }
                $externalId = 'ON-'.$suffix;
            } while (DB::table('loyalty_customers')->where('external_id', $externalId)->exists());

            DB::table('loyalty_customers')->where('id', $customer->id)->update(['external_id' => $externalId]);
        });
    }

    public function down(): void
    {
        // Los IDs pueden haberse copiado a SoftRestaurant y no deben eliminarse al revertir.
    }
};
