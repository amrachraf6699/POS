<?php

namespace Modules\Inventory\App\Http\Requests;

final class StoreInventoryTransferRequest extends ManageInventoryTransferRequest
{
    public function rules(): array
    {
        return ['source_branch_id' => ['required', 'integer', 'min:1', 'different:destination_branch_id'], 'destination_branch_id' => ['required', 'integer', 'min:1'], 'reason' => ['required', 'string', 'max:2000'], 'items' => ['required', 'array', 'min:1'], 'items.*.product_id' => ['required', 'integer', 'min:1', 'distinct'], 'items.*.quantity' => ['required', 'integer', 'min:1'], 'idempotency_key' => ['required', 'uuid']];
    }
}
