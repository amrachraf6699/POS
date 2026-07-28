<?php

namespace Modules\Catalog\App\Actions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Catalog\App\Domain\CatalogAuthorization;
use Modules\Catalog\App\Models\TaxRate;
use Modules\Identity\App\Models\Tenant;
use Modules\Identity\App\Models\User;

final class CreateTaxRateVersionAction
{
    public function __construct(private readonly CatalogAuthorization $authorization) {}

    public function execute(User $actor, Tenant $tenant, TaxRate $taxRate, array $attributes): TaxRate
    {
        if (! $this->authorization->canManage($actor, $tenant)) {
            throw new AuthorizationException('You are not authorized to manage the catalog.');
        }

        return DB::transaction(function () use ($taxRate, $attributes): TaxRate {
            /** @var TaxRate|null $current */
            $current = TaxRate::query()->whereKey($taxRate->getKey())->lockForUpdate()->first();

            if (! $current instanceof TaxRate) {
                throw (new \Illuminate\Database\Eloquent\ModelNotFoundException)->setModel(TaxRate::class, [$taxRate->getKey()]);
            }
            $effectiveFrom = $attributes['effective_from'];

            if (! $current->isActive() || $current->effective_from->toDateString() >= $effectiveFrom || $current->effective_to !== null) {
                throw ValidationException::withMessages(['effective_from' => 'يجب أن يبدأ الإصدار الجديد بعد الإصدار النشط الحالي.']);
            }

            $current->update(['effective_to' => $effectiveFrom]);
            /** @var TaxRate $successor */
            $successor = TaxRate::query()->create([
                'name' => $attributes['name'],
                'rate_basis_points' => $attributes['rate_basis_points'],
                'effective_from' => $effectiveFrom,
                'status' => TaxRate::STATUS_ACTIVE,
            ]);

            return $successor;
        });
    }
}
