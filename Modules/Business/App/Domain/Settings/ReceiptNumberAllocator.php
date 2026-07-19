<?php

namespace Modules\Business\App\Domain\Settings;

use Illuminate\Support\Facades\DB;
use Modules\Business\App\Models\BusinessSettings;

final class ReceiptNumberAllocator
{
    public function __construct(private readonly BusinessSettingsService $settings) {}

    public function next(): string
    {
        return DB::transaction(function (): string {
            $current = $this->settings->settingsForCurrentTenant();
            /** @var BusinessSettings $locked */
            /** @phpstan-ignore-next-line */
            $locked = BusinessSettings::query()->whereKey($current->getKey())->lockForUpdate()->firstOrFail();
            $number = (int) $locked->getAttribute('next_receipt_number');
            $locked->update(['next_receipt_number' => $number + 1]);

            return sprintf('%s-%06d', $locked->getAttribute('receipt_prefix'), $number);
        });
    }
}
