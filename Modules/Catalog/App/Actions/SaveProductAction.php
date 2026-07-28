<?php

namespace Modules\Catalog\App\Actions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Catalog\App\Domain\CatalogAuthorization;
use Modules\Catalog\App\Models\Product;
use Modules\Catalog\App\Models\TaxRate;
use Modules\Identity\App\Models\Tenant;
use Modules\Identity\App\Models\User;

final class SaveProductAction
{
    public function __construct(private readonly CatalogAuthorization $authorization) {}

    public function create(User $u, Tenant $t, array $a): Product
    {
        return $this->save($u, $t, null, $a, 'products.create');
    }

    public function update(User $u, Tenant $t, Product $p, array $a): Product
    {
        return $this->save($u, $t, $p, $a, 'products.update');
    }

    private function save(User $u, Tenant $t, ?Product $p, array $a, string $permission): Product
    {
        if (! $this->authorization->allows($u, $t, $permission)) {
            throw new AuthorizationException;
        }

        return DB::transaction(function () use ($p, $a): Product {
            /** @var TaxRate|null $tax */
            $tax = TaxRate::query()->whereKey($a['tax_rate_id'])->first();
            if (! $tax instanceof TaxRate) {
                throw ValidationException::withMessages(['tax_rate_id' => 'نسبة الضريبة غير متاحة.']);
            }
            if (! $tax->isEffectiveOn(now()->toImmutable())) {
                throw ValidationException::withMessages(['tax_rate_id' => 'يجب اختيار نسبة ضريبة نشطة وسارية.']);
            }
            if ($p === null) {
                $a['status'] = Product::STATUS_ACTIVE;
                /** @var Product $p */
                $p = Product::query()->create($a);

                return $p;
            }
            $p->update($a);

            return $p->refresh();
        });
    }
}
