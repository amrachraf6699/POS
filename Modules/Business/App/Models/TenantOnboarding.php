<?php

namespace Modules\Business\App\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Identity\App\Domain\Tenancy\BelongsToTenant;

/**
 * @property \Illuminate\Support\Carbon|null $settings_completed_at
 * @property \Illuminate\Support\Carbon|null $staff_setup_completed_at
 */
final class TenantOnboarding extends Model
{
    use BelongsToTenant;

    protected $fillable = ['first_branch_id', 'settings_completed_at', 'staff_setup_completed_at', 'completed_at'];

    protected $casts = [
        'settings_completed_at' => 'datetime',
        'staff_setup_completed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];
}
