<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loyalty_customers', function (Blueprint $table): void {
            $table->string('customer_type', 20)->default('person')->after('name');
            $table->boolean('rewards_enabled')->default(true)->after('points_balance');
        });

        Schema::create('loyalty_program_settings', function (Blueprint $table): void {
            $table->id();
            $table->decimal('cashback_percent', 7, 4)->default(5);
            $table->decimal('point_value_mxn', 10, 4)->default(1);
            $table->decimal('minimum_redemption_points', 14, 2)->default(20);
            $table->unsignedSmallInteger('expiration_months')->default(6);
            $table->boolean('tips_earn_points')->default(false);
            $table->boolean('discounted_sales_earn_points')->default(true);
            $table->decimal('maximum_redemption_percent', 5, 2)->default(100);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        DB::table('loyalty_program_settings')->insert([
            'id' => 1,
            'cashback_percent' => 5,
            'point_value_mxn' => 1,
            'minimum_redemption_points' => 20,
            'expiration_months' => 6,
            'tips_earn_points' => false,
            'discounted_sales_earn_points' => true,
            'maximum_redemption_percent' => 100,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::table('loyalty_movements', function (Blueprint $table): void {
            $table->decimal('remaining_points', 14, 2)->nullable()->after('points');
            $table->timestamp('expires_at')->nullable()->after('occurred_at')->index();
            $table->timestamp('expired_at')->nullable()->after('expires_at');
            $table->json('metadata')->nullable()->after('notes');
            $table->unique(['sale_id', 'type'], 'loyalty_movements_sale_type_unique');
        });

        Schema::create('loyalty_redemption_allocations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('redemption_movement_id')->constrained('loyalty_movements')->cascadeOnDelete();
            $table->foreignId('earning_movement_id')->constrained('loyalty_movements')->restrictOnDelete();
            $table->decimal('points', 14, 2);
            $table->timestamps();

            $table->unique(['redemption_movement_id', 'earning_movement_id'], 'loyalty_redemption_earning_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_redemption_allocations');

        Schema::table('loyalty_movements', function (Blueprint $table): void {
            $table->dropUnique('loyalty_movements_sale_type_unique');
            $table->dropIndex(['expires_at']);
            $table->dropColumn(['remaining_points', 'expires_at', 'expired_at', 'metadata']);
        });

        Schema::dropIfExists('loyalty_program_settings');

        Schema::table('loyalty_customers', function (Blueprint $table): void {
            $table->dropColumn(['customer_type', 'rewards_enabled']);
        });
    }
};
