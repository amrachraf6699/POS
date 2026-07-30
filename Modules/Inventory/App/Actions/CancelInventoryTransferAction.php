<?php

namespace Modules\Inventory\App\Actions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Modules\Identity\App\Domain\Authorization\TenantAuthorization;
use Modules\Identity\App\Domain\Tenancy\TenantContext;
use Modules\Identity\App\Domain\Tenancy\TenantContextException;
use Modules\Identity\App\Models\Tenant;
use Modules\Identity\App\Models\User;
use Modules\Inventory\App\Domain\Enums\InventoryTransferStatus;
use Modules\Inventory\App\Domain\Exceptions\InventoryTransferException;
use Modules\Inventory\App\Models\InventoryTransfer;

final class CancelInventoryTransferAction
{
    public function __construct(private readonly TenantContext $context, private readonly TenantAuthorization $authorization) {}

    public function execute(User $actor, Tenant $tenant, InventoryTransfer $transfer, string $reason): InventoryTransfer
    {
        if (! $this->context->hasTenant() || ! $this->context->tenant()->is($tenant) || $this->context->userId() !== (int) $actor->getKey()) {
            throw new TenantContextException('Transfer cancellation requires the current tenant context and actor.');
        }
        if (! $this->authorization->allows($actor, $tenant, 'inventory.transfer')) {
            throw new AuthorizationException('The current actor cannot cancel inventory transfers.');
        }
        if (trim($reason) === '' || mb_strlen(trim($reason)) > 2000) {
            throw new InventoryTransferException('A cancellation reason is required.');
        }

        return DB::transaction(function () use ($actor, $transfer, $reason): InventoryTransfer {
            $locked = InventoryTransfer::query()->whereKey($transfer->getKey())->lockForUpdate()->first();
            if (! $locked instanceof InventoryTransfer) {
                throw new InventoryTransferException('The transfer was not found.');
            }
            $isManager = $this->context->membership()->isOwner() || $this->context->membership()->isManager();
            if ((int) $locked->created_by_user_id !== (int) $actor->getKey() && ! $isManager) {
                throw new AuthorizationException('Only the creator, an owner, or a manager can cancel this transfer.');
            }
            if ($locked->status !== InventoryTransferStatus::Pending) {
                throw new InventoryTransferException('Only pending transfers can be cancelled.');
            }
            $locked->forceFill(['status' => InventoryTransferStatus::Cancelled, 'cancelled_by_user_id' => $actor->getKey(), 'cancelled_at' => now(), 'cancellation_reason' => trim($reason)])->save();

            return $locked->refresh();
        }, 3);
    }
}
