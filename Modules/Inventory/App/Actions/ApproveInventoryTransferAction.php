<?php

namespace Modules\Inventory\App\Actions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Modules\Identity\App\Domain\Authorization\TenantAuthorization;
use Modules\Identity\App\Domain\Tenancy\TenantContext;
use Modules\Identity\App\Domain\Tenancy\TenantContextException;
use Modules\Identity\App\Models\Tenant;
use Modules\Identity\App\Models\User;
use Modules\Inventory\App\Domain\Data\InventoryTransferResult;
use Modules\Inventory\App\Domain\Enums\InventoryTransferStatus;
use Modules\Inventory\App\Domain\Exceptions\InventoryTransferException;
use Modules\Inventory\App\Models\InventoryTransfer;

final class ApproveInventoryTransferAction
{
    public function __construct(private readonly TenantContext $context, private readonly TenantAuthorization $authorization, private readonly PostInventoryTransferAction $post) {}

    public function execute(User $actor, Tenant $tenant, InventoryTransfer $transfer): InventoryTransferResult
    {
        if (! $this->context->hasTenant() || ! $this->context->tenant()->is($tenant) || $this->context->userId() !== (int) $actor->getKey()) {
            throw new TenantContextException('Transfer approval requires the current tenant context and actor.');
        }
        if (! $this->authorization->allows($actor, $tenant, 'inventory.transfer') || (! $this->context->membership()->isOwner() && ! $this->context->membership()->isManager())) {
            throw new AuthorizationException('Only an owner or manager can approve an inventory transfer.');
        }

        return DB::transaction(function () use ($actor, $transfer): InventoryTransferResult {
            $locked = InventoryTransfer::query()->whereKey($transfer->getKey())->lockForUpdate()->first();
            if (! $locked instanceof InventoryTransfer) {
                throw new InventoryTransferException('The transfer was not found.');
            }
            if ($locked->status === InventoryTransferStatus::Posted) {
                return new InventoryTransferResult($locked->load(['items.product']), true);
            }
            if ($locked->status !== InventoryTransferStatus::Pending || ! $locked->requires_manager_approval) {
                throw new InventoryTransferException('This transfer cannot be approved.');
            }

            return new InventoryTransferResult($this->post->post($locked, $actor)->load(['items.product']), false);
        }, 3);
    }
}
