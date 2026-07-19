<?php

namespace Modules\Business\App\Domain\Settings;

use Modules\Business\App\Models\BusinessSettings;

final class BusinessSettingsSnapshot
{
    public function __construct(public readonly array $attributes) {}

    public static function from(BusinessSettings $settings): self
    {
        return new self([
            'display_name' => $settings->display_name,
            'legal_name' => $settings->legal_name,
            'address' => $settings->address,
            'phone' => $settings->phone,
            'email' => $settings->email,
            'timezone' => $settings->timezone,
            'currency_code' => $settings->currency_code,
            'vat_enabled' => $settings->vat_enabled,
            'vat_mode' => $settings->vat_mode,
            'vat_rate' => $settings->vat_rate,
            'receipt_prefix' => $settings->receipt_prefix,
            'receipt_header' => $settings->receipt_header,
            'receipt_footer' => $settings->receipt_footer,
            'receipt_show_cashier' => $settings->receipt_show_cashier,
            'receipt_show_date' => $settings->receipt_show_date,
            'receipt_show_tax_breakdown' => $settings->receipt_show_tax_breakdown,
        ]);
    }
}
