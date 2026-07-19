<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unique('tenant_id');
            $table->string('display_name');
            $table->string('legal_name')->nullable();
            $table->text('address')->nullable();
            $table->string('phone', 64)->nullable();
            $table->string('email')->nullable();
            $table->string('timezone', 64)->default('Africa/Cairo');
            $table->string('currency_code', 3)->default('EGP');
            $table->boolean('vat_enabled')->default(true);
            $table->string('vat_mode', 16)->default('inclusive');
            $table->decimal('vat_rate', 5, 2)->default(14.00);
            $table->string('receipt_prefix', 16)->default('POS');
            $table->unsignedBigInteger('next_receipt_number')->default(1);
            $table->text('receipt_header')->nullable();
            $table->text('receipt_footer')->nullable();
            $table->boolean('receipt_show_cashier')->default(true);
            $table->boolean('receipt_show_date')->default(true);
            $table->boolean('receipt_show_tax_breakdown')->default(true);
            $table->unsignedInteger('low_stock_threshold')->default(0);
            $table->boolean('allow_negative_stock')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_settings');
    }
};
