<?php

namespace Modules\Catalog\App\Actions;

use Illuminate\Auth\Access\AuthorizationException;
use Modules\Catalog\App\Domain\CatalogAuthorization;
use Modules\Catalog\App\Models\TaxRate;
use Modules\Identity\App\Models\Tenant;
use Modules\Identity\App\Models\User;

final class UpdateTaxRateMetadataAction
{
    public function __construct(private readonly CatalogAuthorization $authorization) {}

    public function execute(User $actor, Tenant $tenant, TaxRate $taxRate, array $attributes): TaxRate
    {
        if (! $this->authorization->canManage($actor, $tenant)) {
            throw new AuthorizationException('You are not authorized to manage the catalog.');
        } $taxRate->update($attributes);

        return $taxRate->refresh();
    }
}
