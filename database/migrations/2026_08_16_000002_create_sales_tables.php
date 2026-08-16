<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('api_client_id')->constrained()->restrictOnDelete();
            $table->string('source', 50);
            $table->string('location_id', 100);
            $table->unsignedBigInteger('folio');
            $table->unsignedBigInteger('ticket');
            $table->dateTime('opened_at');
            $table->dateTime('closed_at');
            $table->string('station', 100)->nullable();
            $table->string('customer_external_id', 100)->nullable();
            $table->string('customer_name')->nullable();
            $table->decimal('subtotal', 18, 6);
            $table->decimal('tax', 18, 6);
            $table->decimal('total', 18, 6);
            $table->decimal('tip', 18, 6);
            $table->decimal('total_with_tip', 18, 6);
            $table->char('payload_hash', 64);
            $table->json('raw_payload');
            $table->timestamp('received_at');
            $table->timestamps();

            $table->unique(['source', 'location_id', 'ticket'], 'sales_source_location_ticket_unique');
            $table->index(['location_id', 'closed_at']);
        });

        Schema::create('sale_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position');
            $table->string('product_id', 100);
            $table->string('name')->nullable();
            $table->decimal('quantity', 18, 6);
            $table->decimal('unit_price', 18, 6);
            $table->decimal('discount', 18, 6)->default(0);
            $table->boolean('modifier')->default(false);
            $table->string('compound_id', 100)->nullable();
            $table->boolean('compound_main')->default(false);
            $table->timestamps();

            $table->unique(['sale_id', 'position']);
        });

        Schema::create('sale_payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position');
            $table->string('method', 50);
            $table->decimal('amount', 18, 6);
            $table->decimal('tip', 18, 6)->default(0);
            $table->string('reference')->nullable();
            $table->timestamps();

            $table->unique(['sale_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_payments');
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
    }
};
