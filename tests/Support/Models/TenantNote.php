<?php

namespace Tests\Support\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Identity\App\Domain\Tenancy\BelongsToTenant;

/** @property int $tenant_id */
class TenantNote extends Model
{
    use BelongsToTenant;

    protected $table = 'tenant_notes';

    protected $fillable = ['label'];
}
