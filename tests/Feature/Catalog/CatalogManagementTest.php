<?php

namespace Tests\Feature\Catalog;

use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Database\Eloquent\Model;
use Modules\Catalog\App\Models\Category;
use Modules\Catalog\App\Models\TaxRate;
use Modules\Identity\App\Domain\Tenancy\TenantContext;
use Modules\Identity\App\Models\Membership;
use Modules\Identity\App\Models\User;
use Tests\Support\Tenancy\TenantIsolationTestCase;

class CatalogManagementTest extends TenantIsolationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    public function test_owner_can_manage_categories_and_date_effective_tax_rate_versions(): void
    {
        [$owner, $tenant, $membership] = $this->makeMembership();
        $session = ['current_tenant_id' => $tenant->getKey()];

        $this->actingAs($owner)->withSession($session)->get('/tenant/catalog')->assertOk()->assertSee('الكتالوج والضرائب');
        $this->actingAs($owner)->withSession($session)->post('/tenant/catalog/categories', ['name' => 'مشروبات', 'description' => 'باردة وساخنة'])->assertRedirect();
        $this->assertDatabaseHas('categories', ['tenant_id' => $tenant->getKey(), 'name' => 'مشروبات']);

        $this->actingAs($owner)->withSession($session)->post('/tenant/catalog/tax-rates', ['name' => 'ضريبة القيمة المضافة', 'rate_basis_points' => 1400, 'effective_from' => '2026-01-01'])->assertRedirect();
        app(TenantContext::class)->set($tenant, $membership);
        /** @var TaxRate $rate */
        $rate = TaxRate::query()->firstOrFail();
        $this->actingAs($owner)->withSession($session)->post('/tenant/catalog/tax-rates/'.$rate->getKey().'/versions', ['name' => 'ضريبة القيمة المضافة', 'rate_basis_points' => 1500, 'effective_from' => '2026-02-01'])->assertRedirect();

        $this->assertSame('2026-02-01', $rate->fresh()->effective_to?->toDateString());
        /** @var TaxRate|null $successor */
        $successor = TaxRate::query()->get()->first(fn (Model $candidate): bool => $candidate instanceof TaxRate && $candidate->effective_from->toDateString() === '2026-02-01');
        $this->assertInstanceOf(TaxRate::class, $successor);
        $this->assertSame(1500, $successor->rate_basis_points);
    }

    public function test_validation_authorization_and_tenant_binding_are_enforced(): void
    {
        [$owner, $tenant, $membership] = $this->makeMembership();
        [$otherOwner, $otherTenant, $otherMembership] = $this->makeMembership();
        $session = ['current_tenant_id' => $tenant->getKey()];

        $this->actingAs($owner)->withSession($session)->post('/tenant/catalog/categories', ['name' => 'أغذية'])->assertRedirect();
        $this->actingAs($owner)->withSession($session)->post('/tenant/catalog/categories', ['name' => 'أغذية'])->assertSessionHasErrors('name');
        $this->actingAs($owner)->withSession($session)->post('/tenant/catalog/tax-rates', ['name' => 'ضريبة', 'rate_basis_points' => 10001, 'effective_from' => '2026-01-01'])->assertSessionHasErrors('rate_basis_points');

        app(TenantContext::class)->set($tenant, $membership);
        /** @var Category $category */
        $category = Category::query()->firstOrFail();
        app(TenantContext::class)->set($otherTenant, $otherMembership);
        $this->actingAs($otherOwner)->withSession(['current_tenant_id' => $otherTenant->getKey()])->get('/tenant/catalog/categories/'.$category->getKey().'/edit')->assertNotFound();

        /** @var User $cashier */
        $cashier = User::factory()->create();
        Membership::factory()->create(['tenant_id' => $tenant->getKey(), 'user_id' => $cashier->getKey(), 'role' => Membership::ROLE_CASHIER]);
        $this->actingAs($cashier)->withSession(['current_tenant_id' => $tenant->getKey()])->get('/tenant/catalog')->assertForbidden();
    }
}
