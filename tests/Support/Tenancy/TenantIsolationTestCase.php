<?php

namespace Tests\Support\Tenancy;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Modules\Identity\App\Models\Membership;
use Modules\Identity\App\Models\Tenant;
use Modules\Identity\App\Models\User;
use Tests\Support\Models\TenantNote;
use Tests\TestCase;

abstract class TenantIsolationTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('tenant_notes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('label');
            $table->timestamps();
            $table->index('tenant_id');
        });

        Route::middleware(['web', 'auth', 'tenant'])
            ->get('/__test/tenant-notes/{tenantNote}', function (TenantNote $tenantNote) {
                return response()->json([
                    'id' => $tenantNote->getKey(),
                    'tenant_id' => $tenantNote->getAttribute('tenant_id'),
                ]);
            })
            ->name('testing.tenant-notes.show');
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('tenant_notes');

        parent::tearDown();
    }

    /** @return array{0: User, 1: Tenant, 2: Membership} */
    protected function makeMembership(
        string $role = Membership::ROLE_OWNER,
        string $userStatus = User::STATUS_ACTIVE,
        string $membershipStatus = Membership::STATUS_ACTIVE,
        string $tenantStatus = Tenant::STATUS_ACTIVE,
    ): array {
        /** @var User $user */
        $user = User::factory()->create(['status' => $userStatus]);
        /** @var Tenant $tenant */
        $tenant = Tenant::factory()->create(['status' => $tenantStatus]);
        /** @var Membership $membership */
        $membership = Membership::factory()->create([
            'user_id' => $user->getKey(),
            'tenant_id' => $tenant->getKey(),
            'role' => $role,
            'status' => $membershipStatus,
        ]);

        return [$user, $tenant, $membership];
    }
}
