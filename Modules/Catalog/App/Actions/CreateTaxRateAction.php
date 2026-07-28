<?php

namespace Modules\Catalog\App\Actions;

use Illuminate\Auth\Access\AuthorizationException;
use Modules\Catalog\App\Domain\CatalogAuthorization;
use Modules\Catalog\App\Models\TaxRate;
use Modules\Identity\App\Models\Tenant;
use Modules\Identity\App\Models\User;

final class CreateTaxRateAction
{
    public function __construct(private readonly CatalogAuthorization $authorization) {}

    public function execute(User $actor, Tenant $tenant, array $attributes): TaxRate
    {
        if (! $this->authorization->canManage($actor, $tenant)) {
            throw new AuthorizationException('You are not authorized to manage the catalog.');
        } $attributes['status'] = TaxRate::STATUS_ACTIVE;

        /** @var TaxRate $taxRate */
        $taxRate = TaxRate::query()->create($attributes);

        return $taxRate;
    }
}
