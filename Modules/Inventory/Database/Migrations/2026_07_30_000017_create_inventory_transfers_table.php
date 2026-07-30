<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_transfers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('source_branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignId('destination_branch_id')->constrained('branches')->restrictOnDelete();
            $table->enum('status', ['pending', 'posted', 'cancelled']);
            $table->boolean('requires_manager_approval')->default(false);
            $table->text('reason');
            $table->foreignId('created_by_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('posted_by_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->foreignId('cancelled_by_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->string('idempotency_key', 120);
            $table->timestamps();

            $table->unique(['tenant_id', 'idempotency_key']);
            $table->index(['tenant_id', 'source_branch_id', 'status', 'created_at']);
            $table->index(['tenant_id', 'destination_branch_id', 'status', 'created_at']);
            $table->index(['tenant_id', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transfers');
    }
};
