<?php

namespace Modules\Identity\App\Domain\Authorization;

use Modules\Identity\App\Models\Membership;

final class PermissionCatalog
{
    /** @return array<int, string> */
    public static function all(): array
    {
        return [
            'business.view', 'business.update', 'branches.view', 'branches.create', 'branches.update', 'branches.delete',
            'registers.view', 'registers.manage', 'registers.open', 'registers.close', 'registers.cash-movement',
            'products.view', 'products.create', 'products.update', 'products.delete', 'products.import', 'products.export',
            'inventory.view', 'inventory.adjust', 'inventory.transfer', 'inventory.view-cost',
            'sales.view', 'sales.create', 'sales.discount', 'sales.void', 'sales.refund',
            'customers.view', 'customers.create', 'customers.update', 'customers.delete',
            'expenses.view', 'expenses.create', 'expenses.approve',
            'reports.view', 'reports.view-profit', 'reports.export',
            'users.view', 'users.invite', 'users.update', 'users.delete', 'roles.manage',
            'settings.view', 'settings.update', 'subscription.manage',
        ];
    }

    /** @return array<int, string> */
    public static function permissionsFor(string $role): array
    {
        return match ($role) {
            Membership::ROLE_OWNER => self::all(),
            Membership::ROLE_MANAGER => array_values(array_diff(self::all(), ['subscription.manage'])),
            Membership::ROLE_CASHIER => [
                'branches.view', 'products.view', 'registers.view', 'registers.manage', 'registers.open', 'registers.close', 'registers.cash-movement',
                'sales.view', 'sales.create', 'customers.view', 'customers.create', 'customers.update',
            ],
            Membership::ROLE_INVENTORY_STAFF => [
                'branches.view', 'products.view', 'products.create', 'products.update', 'products.delete', 'products.import', 'products.export',
                'inventory.view', 'inventory.adjust', 'inventory.transfer', 'inventory.view-cost',
            ],
            default => [],
        };
    }
}
