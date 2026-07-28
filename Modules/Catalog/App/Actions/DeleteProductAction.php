<?php

namespace Modules\Catalog\App\Actions;

use Illuminate\Auth\Access\AuthorizationException;
use Modules\Catalog\App\Domain\CatalogAuthorization;
use Modules\Catalog\App\Models\Product;
use Modules\Identity\App\Models\Tenant;
use Modules\Identity\App\Models\User;

final class DeleteProductAction
{
    public function __construct(private readonly CatalogAuthorization $a) {}

    public function execute(User $u, Tenant $t, Product $p): void
    {
        if (! $this->a->allows($u, $t, 'products.delete')) {
            throw new AuthorizationException;
        } $p->delete();
    }
}
