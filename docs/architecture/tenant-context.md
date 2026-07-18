# Tenant Context and Scoping

Tenant-owned requests use a fail-closed context established from the authenticated session. The session key is `current_tenant_id`; request input and route parameters must never be trusted as a tenant selector.

## Middleware

The effective web request order is:

```text
web -> auth -> tenant -> controller
```

Tenant routes declare `auth` and `tenant`. Central routes declare `auth` and `central` when authenticated, or `central` for public routes. The `central` middleware is an explicit no-op marker: it documents that the route must not establish tenant context. Registration, tenant selection, and the English/LTR tracker are central functionality.

`ResolveTenantContext` validates the session tenant against the active user, active membership, active tenant status, and soft-deletion state. Missing or invalid context is cleared and redirects an authenticated user to `tenant.selection`, preserving the intended URL. A user with no active accessible tenant receives HTTP 403.

## Service contract

`Modules\Identity\App\Domain\Tenancy\TenantContext` is request-scoped and exposes:

- `set(Tenant $tenant, Membership $membership): void`
- `clear(): void`
- `hasTenant(): bool`
- `tenant(): Tenant`
- `membership(): Membership`
- `id(): int`

Access before initialization throws `TenantContextException`. The service is registered with Laravel's scoped container binding, so state does not leak between requests, jobs, or tests.

## Tenant-owned models

Tenant-owned models must use `BelongsToTenant` and have a `tenant_id` column. The trait applies `TenantScope`, requires an initialized context before querying, assigns the trusted context ID on create, and rejects an explicit mismatched `tenant_id`.

```php
use Modules\Identity\App\Domain\Tenancy\BelongsToTenant;

final class Example extends Model
{
    use BelongsToTenant;

    protected $fillable = ['business_field'];
}
```

Do not include `tenant_id` in request fillable data. Updates, deletes, and implicit route model binding use the same global scope. A resource belonging to another tenant resolves as HTTP 404.

## Selection and failure behavior

`GET /tenant/select` lists only the authenticated user's active accessible tenants. `POST /tenant/select/{tenant}` verifies the same relationship again before writing `current_tenant_id`; guessed or unauthorized tenant IDs receive HTTP 403 without tenant details. Registration writes the new tenant ID into the session.

Internal context misuse is represented by `TenantContextException` and must be logged safely without credentials or private customer data. Platform-admin impersonation is not supported by this contract.
