# Invitations and Tenant Switching

Identity invitations are tenant-owned workflows. They use the active `TenantContext` for management routes and central signed routes for acceptance.

## Invitation security

Invitation records store only a SHA-256 hash of a random 32-byte token. The raw token is present only in the signed acceptance URL and in the queued notification payload required to build that URL. It must never be logged or written to tracker notes.

Acceptance URLs use Laravel temporary signed routes and the configured invitation lifetime, which defaults to 72 hours through `identity.invitation_lifetime_hours`. The controller and transactional action independently verify the signature, token hash, pending status, expiry, active tenant, and recipient email.

Invitations have durable `pending`, `accepted`, and `revoked` states. Acceptance is one-time. Resending revokes the previous pending record and creates a new token. Revoking is idempotent and does not delete history.

## Authorization and roles

Only active owner or manager memberships can create, resend, or revoke invitations. This task creates manager memberships only. Detailed manager permissions and additional staff roles belong to the later roles-and-permissions task.

## Acceptance paths

An existing account must authenticate with the invited email before acceptance. A recipient without an account completes an Arabic name/password form; user creation, manager membership creation, invitation acceptance, authentication, and `current_tenant_id` initialization occur atomically.

The login bridge stores only a same-host intended URL. It does not accept an arbitrary external redirect.

## Tenant switching

`GET /tenant/select` and `POST /tenant/select/{tenant}` remain the canonical selection and switching routes. POST revalidates active membership and tenant status, clears the current request-scoped `TenantContext`, stores `current_tenant_id`, and redirects to the intended URL or `/home`. The next tenant-owned request rebuilds context through `ResolveTenantContext`.

Invitation management routes use `auth` and `tenant` middleware. Acceptance, login, registration, and tenant selection are central routes and never query tenant-owned models without context.
