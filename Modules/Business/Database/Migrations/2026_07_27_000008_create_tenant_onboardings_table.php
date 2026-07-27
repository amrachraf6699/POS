<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_onboardings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->unique()->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('first_branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->timestamp('settings_completed_at')->nullable();
            $table->timestamp('staff_setup_completed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        $now = now();
        DB::table('tenants')->orderBy('id')->each(function (object $tenant) use ($now): void {
            $settings = DB::table('business_settings')->where('tenant_id', $tenant->id)->exists();
            $branch = DB::table('branches')->where('tenant_id', $tenant->id)->where('status', 'active')->orderBy('id')->first();
            DB::table('tenant_onboardings')->insert([
                'tenant_id' => $tenant->id,
                'first_branch_id' => $branch?->id,
                'settings_completed_at' => $settings ? $now : null,
                'staff_setup_completed_at' => ($settings && $branch) ? $now : null,
                'completed_at' => ($settings && $branch) ? $now : null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_onboardings');
    }
};
