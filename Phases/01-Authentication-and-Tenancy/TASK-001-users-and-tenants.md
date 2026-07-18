# TASK-001 — Users and Tenants

- **Objective:** Model users, tenants, memberships, statuses, and ownership.
- **Scope:** Migrations, models, factories, seed data, registration service.
- **Non-scope:** Staff invitations and permissions.
- **Dependencies:** Phase 00.
- **Files/subsystems:** `app/Domain/Identity`, `database/migrations`, auth routes.
- **Database/API/UI impact:** Central `users`, `tenants`, membership schema; registration UI.
- **Steps:** Add status/slug rules; create owner membership atomically; add indexes and soft lifecycle behavior.
- **Validation/authorization:** Unique tenant slug; active owner required.
- **Tenant isolation:** Tenant membership is the only source of access.
- **Tests:** Registration, duplicate slug, owner creation, inactive tenant rejection.
- **Definition of done:** Tenant and owner lifecycle is persisted safely.
- **Handoff:** Tenant identity model for middleware.
