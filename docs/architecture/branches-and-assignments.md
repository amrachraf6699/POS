# Branches and Assignments

Branches are tenant-owned records in the Business module. Every branch query requires the request-scoped `TenantContext` through `BelongsToTenant`; route model binding therefore cannot resolve a branch from another tenant.

## Branch lifecycle

Branch codes are trimmed, normalized to uppercase, and unique within the tenant. Branches use `active` and `inactive` statuses rather than physical deletion. The final active branch cannot be deactivated. Inactive branches remain available for historical references but cannot receive new active assignments or be selected by future operational features.

## User reach

Owners have implicit access to every active branch in their tenant. Managers and other tenant members require an active `branch_user` assignment, active membership, active user status, and an active branch. Assignment rows are retained and use `active`/`inactive` status so access can be withdrawn without deleting history.

Only active owner and manager memberships may manage branches and assignments. The server checks membership and tenant ownership; request input is never trusted for `tenant_id`.

## Interfaces

Branch management is available through the authenticated tenant routes named `business.branches.*`. Branch creation, updates, deactivation, assignment activation, and assignment deactivation are implemented as actions behind Form Requests and tenant middleware. Branch switching and branch operational dependencies belong to later POS and inventory tasks.
