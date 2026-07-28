<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_rates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('rate_basis_points');
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->unique(['tenant_id', 'name', 'effective_from']);
            $table->index(['tenant_id', 'status', 'effective_from']);
            $table->index(['tenant_id', 'effective_to']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_rates');
    }
};
