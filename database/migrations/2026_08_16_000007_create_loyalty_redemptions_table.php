<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_redemptions', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 30)->unique();
            $table->foreignId('loyalty_customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('movement_id')->unique()->constrained('loyalty_movements')->restrictOnDelete();
            $table->foreignId('sale_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->foreignId('cashier_id')->constrained('users')->restrictOnDelete();
            $table->string('location_id', 100)->index();
            $table->unsignedBigInteger('sr_folio')->index();
            $table->string('status', 20)->default('pending')->index();
            $table->decimal('points', 14, 2);
            $table->decimal('value_mxn', 14, 2);
            $table->decimal('purchase_total_mxn', 14, 2);
            $table->decimal('point_value_mxn', 10, 4);
            $table->text('notes')->nullable();
            $table->timestamp('requested_at');
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['location_id', 'sr_folio', 'status'], 'loyalty_redemptions_reconciliation_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_redemptions');
    }
};
