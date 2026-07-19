<?php

namespace Modules\Business\App\Domain\Settings;

use Modules\Business\App\Data\BusinessSettingsData;
use Modules\Business\App\Models\BusinessSettings;
use Modules\Identity\App\Domain\Tenancy\TenantContext;

final class BusinessSettingsService
{
    public function __construct(private readonly TenantContext $context)
    {
        // The current tenant context is request-scoped by the Identity module.
    }

    public function settingsForCurrentTenant(): BusinessSettings
    {
        $tenant = $this->context->tenant();

        /** @var BusinessSettings $settings */
        $settings = BusinessSettings::query()->firstOrCreate(
            [],
            BusinessSettings::defaults((string) $tenant->getAttribute('name'))
        );

        return $settings;
    }

    public function update(BusinessSettingsData $data): BusinessSettings
    {
        $settings = $this->settingsForCurrentTenant();
        $settings->update($data->attributes);

        return $settings->refresh();
    }

    public function snapshot(): BusinessSettingsSnapshot
    {
        return BusinessSettingsSnapshot::from($this->settingsForCurrentTenant());
    }
}
