# TASK-002 — Tenant Context and Scoping

- **Objective:** Establish trusted tenant context and prevent unscoped tenant queries.
- **Scope:** Middleware, context service, scoped model conventions, route binding strategy.
- **Non-scope:** Platform-admin impersonation.
- **Dependencies:** TASK-001.
- **Files/subsystems:** `app/Domain/Tenancy`, middleware, providers, policies.
- **Database/API/UI impact:** Request behavior and model query boundaries.
- **Steps:** Resolve tenant from authenticated membership; bind context before routes; define central-route exception.
- **Validation/authorization:** Reject missing, suspended, or unauthorized context.
- **Tenant isolation:** Scope route binding, searches, jobs, and exports.
- **Tests:** Cross-tenant read/update/delete and guessed-ID tests.
- **Definition of done:** No tenant feature can execute without trusted context.
- **Handoff:** Tenant context contract.
