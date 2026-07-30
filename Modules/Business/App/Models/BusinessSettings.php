<?php

namespace Modules\Business\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Business\Database\Factories\BusinessSettingsFactory;
use Modules\Identity\App\Domain\Tenancy\BelongsToTenant;

/**
 * @property int $id
 * @property int $tenant_id
 * @property string $display_name
 * @property string|null $legal_name
 * @property string|null $address
 * @property string|null $phone
 * @property string|null $email
 * @property string $timezone
 * @property string $currency_code
 * @property bool $vat_enabled
 * @property string $vat_mode
 * @property string $vat_rate
 * @property string $receipt_prefix
 * @property int $next_receipt_number
 * @property string|null $receipt_header
 * @property string|null $receipt_footer
 * @property bool $receipt_show_cashier
 * @property bool $receipt_show_date
 * @property bool $receipt_show_tax_breakdown
 * @property int $low_stock_threshold
 * @property bool $allow_negative_stock
 * @property bool $transfer_requires_manager_approval
 */
class BusinessSettings extends Model
{
    use BelongsToTenant, HasFactory;

    public const VAT_MODE_INCLUSIVE = 'inclusive';

    protected $fillable = [
        'display_name', 'legal_name', 'address', 'phone', 'email', 'timezone', 'currency_code',
        'vat_enabled', 'vat_mode', 'vat_rate', 'receipt_prefix', 'next_receipt_number',
        'receipt_header', 'receipt_footer', 'receipt_show_cashier', 'receipt_show_date',
        'receipt_show_tax_breakdown', 'low_stock_threshold', 'allow_negative_stock', 'transfer_requires_manager_approval',
    ];

    protected $casts = [
        'vat_enabled' => 'boolean',
        'vat_rate' => 'decimal:2',
        'next_receipt_number' => 'integer',
        'receipt_show_cashier' => 'boolean',
        'receipt_show_date' => 'boolean',
        'receipt_show_tax_breakdown' => 'boolean',
        'low_stock_threshold' => 'integer',
        'allow_negative_stock' => 'boolean',
        'transfer_requires_manager_approval' => 'boolean',
    ];

    public static function defaults(string $displayName): array
    {
        return [
            'display_name' => $displayName,
            'timezone' => config('business.default_timezone', 'Africa/Cairo'),
            'currency_code' => config('business.default_currency', 'EGP'),
            'vat_enabled' => true,
            'vat_mode' => self::VAT_MODE_INCLUSIVE,
            'vat_rate' => config('business.default_vat_rate', 14.00),
            'receipt_prefix' => 'POS',
            'next_receipt_number' => 1,
            'receipt_show_cashier' => true,
            'receipt_show_date' => true,
            'receipt_show_tax_breakdown' => true,
            'low_stock_threshold' => 0,
            'allow_negative_stock' => false,
            'transfer_requires_manager_approval' => false,
        ];
    }

    protected static function newFactory(): BusinessSettingsFactory
    {
        return BusinessSettingsFactory::new();
    }
}
