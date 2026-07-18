# TASK-004 — Isolation and Authorization Test Suite

- **Objective:** Prove tenant isolation and baseline role authorization.
- **Scope:** Feature tests, policy tests, route binding tests, search/export leakage tests.
- **Non-scope:** Business-domain permissions not yet implemented.
- **Dependencies:** TASK-002, TASK-003.
- **Files/subsystems:** `tests/Feature/Tenancy`, `tests/Feature/Auth`.
- **Database/API/UI impact:** Test-only.
- **Steps:** Create two tenants/users; exercise every access path; assert no leakage or side effects.
- **Validation/authorization:** Cover inactive users/tenants and unauthorized membership.
- **Tenant isolation:** This task is the baseline security gate for all future phases.
- **Tests:** Full isolation matrix and failure rollback assertions.
- **Definition of done:** CI fails when tenant boundaries regress.
- **Handoff:** Reusable tenancy test helpers.
