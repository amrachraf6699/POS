# Tenant roles and permissions

The Identity module uses `spatie/laravel-permission` 6.25.0 with Laravel teams enabled and `tenant_id` as the team key. The package supports PHP 8.1 and Laravel 10. Permissions are global catalog entries; roles and role assignments are tenant-scoped. Removing the package requires replacing the permission tables, User `HasRoles` trait, and Identity authorization service together.

Four fixed roles are provisioned per tenant: owner, manager, cashier, and inventory staff. The membership `role` remains a synchronized compatibility projection; `TenantRoleService` is the only role-assignment boundary.

Every request establishes tenant context before the permission team ID. Authorization checks require an active user, tenant, and membership, then consult the tenant-specific permission assignment. Managers can change only cashier and inventory-staff memberships. An active tenant can never lose its final owner.
