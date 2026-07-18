<?php

namespace Modules\Identity\App\Domain\Tenancy;

use Illuminate\Database\Eloquent\Model;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (Model $model): void {
            /** @var TenantContext $context */
            $context = app(TenantContext::class);
            $contextTenantId = $context->id();
            $modelTenantId = $model->getAttribute('tenant_id');

            if ($modelTenantId !== null && (int) $modelTenantId !== $contextTenantId) {
                throw new TenantContextException('The tenant_id does not match the current tenant context.');
            }

            $model->setAttribute('tenant_id', $contextTenantId);
        });
    }
}
