<?php

namespace Modules\Inventory\App\Http\Requests;

final class CancelInventoryTransferRequest extends ManageInventoryTransferRequest
{
    public function rules(): array
    {
        return ['reason' => ['required', 'string', 'max:2000']];
    }
}
