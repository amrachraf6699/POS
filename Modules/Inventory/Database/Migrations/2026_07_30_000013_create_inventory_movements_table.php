<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->enum('type', ['opening', 'adjustment_in', 'adjustment_out', 'sale_out', 'return_in', 'transfer_in', 'transfer_out']);
            $table->unsignedBigInteger('quantity');
            $table->bigInteger('quantity_delta');
            $table->bigInteger('balance_after');
            $table->string('idempotency_key', 191);
            $table->string('source_type', 100)->nullable();
            $table->string('source_id', 191)->nullable();
            $table->foreignId('actor_user_id')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['tenant_id', 'idempotency_key']);
            $table->index(['tenant_id', 'branch_id', 'product_id', 'created_at']);
            $table->index(['tenant_id', 'product_id', 'created_at']);
            $table->index(['tenant_id', 'source_type', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
