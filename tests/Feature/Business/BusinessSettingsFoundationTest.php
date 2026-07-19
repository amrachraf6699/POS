<?php

namespace Tests\Feature\Business;

use Illuminate\Support\Facades\Schema;
use Modules\Business\App\Domain\Settings\BusinessSettingsService;
use Modules\Business\App\Models\BusinessSettings;
use Modules\Identity\App\Domain\Tenancy\TenantContext;
use Modules\Identity\App\Domain\Tenancy\TenantContextException;
use Tests\Support\Tenancy\TenantIsolationTestCase;

class BusinessSettingsFoundationTest extends TenantIsolationTestCase
{
    public function test_business_module_is_enabled_and_defaults_are_created_from_tenant_identity(): void
    {
        [$user, $tenant, $membership] = $this->makeMembership();
        app(TenantContext::class)->set($tenant, $membership);

        $settings = app(BusinessSettingsService::class)->settingsForCurrentTenant();

        $this->assertTrue(Schema::hasTable('business_settings'));
        $this->assertSame($tenant->getAttribute('name'), $settings->display_name);
        $this->assertSame('Africa/Cairo', $settings->timezone);
        $this->assertSame('EGP', $settings->currency_code);
        $this->assertTrue($settings->vat_enabled);
        $this->assertSame(BusinessSettings::VAT_MODE_INCLUSIVE, $settings->vat_mode);
        $this->assertSame('14.00', $settings->vat_rate);
    }

    public function test_business_settings_are_fail_closed_without_tenant_context(): void
    {
        $this->expectException(TenantContextException::class);

        BusinessSettings::query()->get();
    }

    public function test_settings_are_isolated_between_tenants(): void
    {
        [$firstUser, $firstTenant, $firstMembership] = $this->makeMembership();
        [$secondUser, $secondTenant, $secondMembership] = $this->makeMembership();
        app(TenantContext::class)->set($firstTenant, $firstMembership);
        $first = app(BusinessSettingsService::class)->settingsForCurrentTenant();
        app(TenantContext::class)->set($secondTenant, $secondMembership);
        $second = app(BusinessSettingsService::class)->settingsForCurrentTenant();

        $this->assertNotSame($first->getKey(), $second->getKey());
        $this->assertSame($secondTenant->getKey(), $second->tenant_id);
        $this->assertSame($firstTenant->getKey(), $first->tenant_id);
    }
}
