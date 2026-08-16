<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_customers', function (Blueprint $table): void {
            $table->id();
            $table->string('external_id', 100)->nullable()->unique();
            $table->string('name');
            $table->string('email')->nullable()->index();
            $table->string('phone', 30)->nullable()->index();
            $table->date('birthday')->nullable();
            $table->decimal('points_balance', 14, 2)->default(0);
            $table->string('status', 20)->default('active');
            $table->string('sr_sync_status', 20)->default('pending')->index();
            $table->timestamp('sr_synced_at')->nullable();
            $table->text('sr_sync_notes')->nullable();
            $table->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('loyalty_movements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('loyalty_customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sale_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 20);
            $table->decimal('points', 14, 2);
            $table->decimal('balance_before', 14, 2);
            $table->decimal('balance_after', 14, 2);
            $table->string('reference', 100)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['loyalty_customer_id', 'occurred_at']);
        });

        Schema::table('sales', function (Blueprint $table): void {
            $table->foreignId('loyalty_customer_id')
                ->nullable()
                ->after('customer_name')
                ->constrained()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('loyalty_customer_id');
        });

        Schema::dropIfExists('loyalty_movements');
        Schema::dropIfExists('loyalty_customers');
    }
};
