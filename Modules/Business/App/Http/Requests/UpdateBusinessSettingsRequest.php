<?php

namespace Modules\Business\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Business\App\Domain\Settings\BusinessSettingsAuthorization;
use Modules\Identity\App\Domain\Tenancy\TenantContext;
use Modules\Identity\App\Models\User;

class UpdateBusinessSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user instanceof User
            && app(BusinessSettingsAuthorization::class)->canManage($user, app(TenantContext::class)->tenant());
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'currency_code' => strtoupper(trim((string) $this->input('currency_code'))),
            'receipt_prefix' => strtoupper(trim((string) $this->input('receipt_prefix'))),
            'vat_mode' => strtolower(trim((string) $this->input('vat_mode'))),
        ]);
    }

    public function rules(): array
    {
        return [
            'display_name' => ['required', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:2000'],
            'phone' => ['nullable', 'string', 'max:64'],
            'email' => ['nullable', 'email', 'max:255'],
            'timezone' => ['required', 'timezone'],
            'currency_code' => ['required', 'in:'.implode(',', config('business.supported_currencies', []))],
            'vat_enabled' => ['required', 'boolean'],
            'vat_mode' => ['required', 'in:inclusive'],
            'vat_rate' => ['required', 'numeric', 'between:0,100'],
            'receipt_prefix' => ['required', 'regex:/^[A-Z0-9][A-Z0-9_-]{0,15}$/'],
            'receipt_header' => ['nullable', 'string', 'max:2000'],
            'receipt_footer' => ['nullable', 'string', 'max:2000'],
            'receipt_show_cashier' => ['required', 'boolean'],
            'receipt_show_date' => ['required', 'boolean'],
            'receipt_show_tax_breakdown' => ['required', 'boolean'],
            'low_stock_threshold' => ['required', 'integer', 'min:0'],
            'allow_negative_stock' => ['required', 'boolean'],
        ];
    }
}
