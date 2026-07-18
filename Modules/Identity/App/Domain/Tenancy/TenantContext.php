<?php

namespace Modules\Identity\App\Domain\Tenancy;

use Modules\Identity\App\Models\Membership;
use Modules\Identity\App\Models\Tenant;

final class TenantContext
{
    private ?Tenant $tenant = null;

    private ?Membership $membership = null;

    public function set(Tenant $tenant, Membership $membership): void
    {
        if ((int) $membership->getAttribute('tenant_id') !== (int) $tenant->getKey()) {
            throw new TenantContextException('The membership does not belong to the selected tenant.');
        }

        $this->tenant = $tenant;
        $this->membership = $membership;
    }

    public function clear(): void
    {
        $this->tenant = null;
        $this->membership = null;
    }

    public function hasTenant(): bool
    {
        return $this->tenant !== null && $this->membership !== null;
    }

    public function tenant(): Tenant
    {
        if ($this->tenant === null) {
            throw new TenantContextException('A tenant context has not been established.');
        }

        return $this->tenant;
    }

    public function membership(): Membership
    {
        if ($this->membership === null) {
            throw new TenantContextException('A tenant membership context has not been established.');
        }

        return $this->membership;
    }

    public function id(): int
    {
        return (int) $this->tenant()->getKey();
    }

    public function userId(): int
    {
        return (int) $this->membership()->getAttribute('user_id');
    }
}
