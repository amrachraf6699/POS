<?php

namespace Tests\Feature\Business;

use Modules\Business\App\Data\BusinessSettingsData;
use Modules\Business\App\Domain\Settings\BusinessSettingsService;
use Modules\Identity\App\Domain\Tenancy\TenantContext;
use Tests\Support\Tenancy\TenantIsolationTestCase;

class BusinessSettingsSnapshotTest extends TenantIsolationTestCase
{
    public function test_snapshot_preserves_historical_values_after_settings_change(): void
    {
        [$user, $tenant, $membership] = $this->makeMembership();
        app(TenantContext::class)->set($tenant, $membership);
        $service = app(BusinessSettingsService::class);
        $service->settingsForCurrentTenant();
        $before = $service->snapshot();

        $service->update(BusinessSettingsData::fromArray([
            'display_name' => 'Historical Business',
            'timezone' => 'Europe/London',
            'currency_code' => 'USD',
            'vat_enabled' => true,
            'vat_mode' => 'inclusive',
            'vat_rate' => '15.00',
            'receipt_prefix' => 'HIST',
            'receipt_show_cashier' => false,
            'receipt_show_date' => true,
            'receipt_show_tax_breakdown' => false,
            'low_stock_threshold' => 10,
            'allow_negative_stock' => false,
        ]));

        $after = $service->snapshot();
        $this->assertSame('EGP', $before->attributes['currency_code']);
        $this->assertSame('USD', $after->attributes['currency_code']);
        $this->assertSame('Africa/Cairo', $before->attributes['timezone']);
        $this->assertSame('Europe/London', $after->attributes['timezone']);
        $this->assertSame('POS', $before->attributes['receipt_prefix']);
        $this->assertSame('HIST', $after->attributes['receipt_prefix']);
    }
}
