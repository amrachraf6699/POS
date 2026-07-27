<?php

namespace Tests\Feature\Business;

use Illuminate\Support\Facades\DB;
use Modules\Business\App\Models\Branch;
use Modules\Identity\App\Models\Membership;
use Tests\Support\Tenancy\TenantIsolationTestCase;

class OnboardingFlowTest extends TenantIsolationTestCase
{
    public function test_owner_is_redirected_through_required_steps_and_can_skip_staff_setup(): void
    {
        [$owner, $tenant] = $this->makeMembership();
        $session = ['current_tenant_id' => $tenant->getKey()];

        $this->actingAs($owner)->withSession($session)->get('/tenant/dashboard')->assertRedirect(route('business.onboarding.index'));
        $this->actingAs($owner)->withSession($session)->get('/tenant/onboarding')->assertRedirect(route('business.onboarding.settings'));
        $this->actingAs($owner)->withSession($session)->put('/tenant/onboarding/settings', $this->settingsPayload(['_token' => csrf_token()]))->assertRedirect(route('business.onboarding.branch'));
        $this->actingAs($owner)->withSession($session)->post('/tenant/onboarding/branch', ['name' => 'الفرع الرئيسي', 'code' => 'MAIN', 'country_code' => 'EG', 'timezone' => 'Africa/Cairo', '_token' => csrf_token()])->assertRedirect(route('business.onboarding.staff'));
        $this->actingAs($owner)->withSession($session)->post('/tenant/onboarding/staff/skip', ['_token' => csrf_token()])->assertRedirect(route('business.dashboard'));

        $state = DB::table('tenant_onboardings')->where('tenant_id', $tenant->getKey())->first();
        $this->assertNotNull($state->settings_completed_at);
        $this->assertNotNull($state->staff_setup_completed_at);
        $this->assertNotNull($state->completed_at);
        $this->actingAs($owner)->withSession($session)->get('/tenant/dashboard')->assertOk()->assertDontSee('إكمال إعداد النشاط');
    }

    public function test_manager_and_inactive_owner_cannot_open_onboarding(): void
    {
        [$manager, $tenant] = $this->makeMembership(Membership::ROLE_MANAGER);
        $this->actingAs($manager)->withSession(['current_tenant_id' => $tenant->getKey()])->get('/tenant/onboarding/settings')->assertForbidden();

        [$owner, $ownerTenant] = $this->makeMembership(Membership::ROLE_OWNER, membershipStatus: Membership::STATUS_INACTIVE);
        $this->actingAs($owner)->withSession(['current_tenant_id' => $ownerTenant->getKey()])->get('/tenant/onboarding/settings')->assertForbidden();
    }

    public function test_existing_settings_and_branch_routes_advance_the_owner_progress(): void
    {
        [$owner, $tenant] = $this->makeMembership();
        $session = ['current_tenant_id' => $tenant->getKey()];
        $this->actingAs($owner)->withSession($session)->put('/tenant/settings/business', $this->settingsPayload(['_token' => csrf_token()]))->assertRedirect();
        $this->actingAs($owner)->withSession($session)->post('/tenant/branches', ['name' => 'Existing Route Branch', 'code' => 'EXISTING', 'country_code' => 'EG', 'timezone' => 'Africa/Cairo', '_token' => csrf_token()])->assertRedirect();
        $this->actingAs($owner)->withSession($session)->get('/tenant/onboarding')->assertRedirect(route('business.onboarding.staff'));
        $this->assertTrue(Branch::query()->where('code', 'EXISTING')->exists());
    }

    public function test_onboarding_state_is_tenant_scoped(): void
    {
        [$owner, $tenant] = $this->makeMembership();
        $this->actingAs($owner)->withSession(['current_tenant_id' => $tenant->getKey()])->get('/tenant/onboarding/settings')->assertOk();
        [$otherOwner, $otherTenant] = $this->makeMembership();
        $this->actingAs($otherOwner)->withSession(['current_tenant_id' => $otherTenant->getKey()])->get('/tenant/onboarding')->assertRedirect(route('business.onboarding.settings'));
        $this->assertSame(2, DB::table('tenant_onboardings')->count());
    }

    /** @return array<string, string> */
    private function settingsPayload(array $overrides = []): array
    {
        return array_merge(['display_name' => 'Business', 'legal_name' => 'Business Legal', 'address' => 'Cairo', 'phone' => '01000000000', 'email' => 'business@example.com', 'timezone' => 'Africa/Cairo', 'currency_code' => 'EGP', 'vat_enabled' => '1', 'vat_mode' => 'inclusive', 'vat_rate' => '14.00', 'receipt_prefix' => 'POS', 'receipt_header' => 'Header', 'receipt_footer' => 'Footer', 'receipt_show_cashier' => '1', 'receipt_show_date' => '1', 'receipt_show_tax_breakdown' => '1', 'low_stock_threshold' => '5', 'allow_negative_stock' => '0'], $overrides);
    }
}
