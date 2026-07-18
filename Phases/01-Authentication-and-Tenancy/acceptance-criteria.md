# Acceptance Criteria

- Tenant-owned records always carry `tenant_id`.
- Current tenant is resolved by trusted application context, never request input.
- Tenant A cannot read, mutate, search, export, or infer Tenant B data.
- Invitations, activation, and current-tenant selection are authorized and audited.
