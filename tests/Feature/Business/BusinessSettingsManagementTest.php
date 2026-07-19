<?php

namespace Tests\Feature\Business;

use Modules\Business\App\Domain\Settings\BusinessSettingsService;
use Modules\Business\App\Domain\Settings\ReceiptNumberAllocator;
use Modules\Identity\App\Models\Membership;
use Modules\Identity\App\Models\Tenant;
use Modules\Identity\App\Models\User;
use Tests\Support\Tenancy\TenantIsolationTestCase;

class BusinessSettingsManagementTest extends TenantIsolationTestCase
{
    public function test_owner_can_view_and_update_settings_without_changing_tenant_identity(): void
    {
        [$owner, $tenant] = $this->makeMembership();
        $originalSlug = $tenant->getAttribute('slug');

        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->getKey()])
            ->get('/tenant/settings/business')
            ->assertOk()
            ->assertSee('إعدادات النشاط التجاري')
            ->assertSee('dir="rtl"', false);

        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->getKey()])
            ->put('/tenant/settings/business', $this->validPayload(['display_name' => 'Updated Business', '_token' => csrf_token()]))
            ->assertRedirect();

        $this->assertSame('Updated Business', app(BusinessSettingsService::class)->settingsForCurrentTenant()->display_name);
        $this->assertSame($originalSlug, $tenant->fresh()->getAttribute('slug'));
    }

    public function test_active_manager_can_update_but_inactive_membership_cannot(): void
    {
        [$owner, $tenant] = $this->makeMembership();
        /** @var User $manager */
        $manager = User::factory()->create();
        Membership::factory()->create(['user_id' => $manager->getKey(), 'tenant_id' => $tenant->getKey(), 'role' => Membership::ROLE_MANAGER]);

        $this->actingAs($manager)->withSession(['current_tenant_id' => $tenant->getKey()])
            ->put('/tenant/settings/business', $this->validPayload(['display_name' => 'Manager Update', '_token' => csrf_token()]))
            ->assertRedirect();

        Membership::query()->where('user_id', $manager->getKey())->update(['status' => Membership::STATUS_INACTIVE]);
        $this->actingAs($manager)->withSession(['current_tenant_id' => $tenant->getKey()])
            ->put('/tenant/settings/business', $this->validPayload(['display_name' => 'Blocked Update', '_token' => csrf_token()]))
            ->assertForbidden();
        $this->assertSame('Manager Update', app(BusinessSettingsService::class)->settingsForCurrentTenant()->display_name);
        $this->assertTrue($owner->isActive());
    }

    public function test_invalid_settings_are_rejected_without_changing_existing_values(): void
    {
        [$owner, $tenant] = $this->makeMembership();
        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->getKey()])->get('/tenant/settings/business');
        $before = app(BusinessSettingsService::class)->settingsForCurrentTenant();

        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->getKey()])
            ->put('/tenant/settings/business', $this->validPayload([
                '_token' => csrf_token(),
                'currency_code' => 'XXX',
                'vat_mode' => 'exclusive',
                'timezone' => 'Not/A_Timezone',
                'receipt_prefix' => 'bad prefix',
            ]))
            ->assertSessionHasErrors(['currency_code', 'vat_mode', 'timezone', 'receipt_prefix']);

        $after = app(BusinessSettingsService::class)->settingsForCurrentTenant();
        $this->assertSame($before->currency_code, $after->currency_code);
        $this->assertSame($before->vat_mode, $after->vat_mode);
        $this->assertSame($before->receipt_prefix, $after->receipt_prefix);
    }

    public function test_receipt_numbers_are_atomic_and_isolated_per_tenant(): void
    {
        [$owner, $tenant] = $this->makeMembership();
        app(\Modules\Identity\App\Domain\Tenancy\TenantContext::class)->set($tenant, Membership::query()->where('tenant_id', $tenant->getKey())->firstOrFail());
        $allocator = app(ReceiptNumberAllocator::class);

        $this->assertSame('POS-000001', $allocator->next());
        $this->assertSame('POS-000002', $allocator->next());

        /** @var Tenant $secondTenant */
        $secondTenant = Tenant::factory()->create();
        $secondMembership = Membership::factory()->create(['user_id' => $owner->getKey(), 'tenant_id' => $secondTenant->getKey()]);
        app(\Modules\Identity\App\Domain\Tenancy\TenantContext::class)->set($secondTenant, $secondMembership);
        $this->assertSame('POS-000001', $allocator->next());
    }

    /** @return array<string, mixed> */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'display_name' => 'Business',
            'legal_name' => 'Business Legal',
            'address' => 'Cairo',
            'phone' => '01000000000',
            'email' => 'business@example.com',
            'timezone' => 'Africa/Cairo',
            'currency_code' => 'EGP',
            'vat_enabled' => '1',
            'vat_mode' => 'inclusive',
            'vat_rate' => '14.00',
            'receipt_prefix' => 'POS',
            'receipt_header' => 'Header',
            'receipt_footer' => 'Footer',
            'receipt_show_cashier' => '1',
            'receipt_show_date' => '1',
            'receipt_show_tax_breakdown' => '1',
            'low_stock_threshold' => '5',
            'allow_negative_stock' => '0',
        ], $overrides);
    }
}
