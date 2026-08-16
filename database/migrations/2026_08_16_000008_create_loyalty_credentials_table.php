<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_credentials', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('loyalty_customer_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('member_number', 20)->unique();
            $table->text('token_encrypted');
            $table->char('token_hash', 64)->unique();
            $table->string('status', 20)->default('active')->index();
            $table->timestamp('issued_at');
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->string('apple_serial_number')->nullable()->unique();
            $table->string('google_object_id')->nullable()->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_credentials');
    }
};
