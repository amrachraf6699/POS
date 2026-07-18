# TASK-002 — Queue, Cache, File, and Tenant Context

- **Objective:** Make asynchronous and stored data tenant-safe.
- **Scope:** Job payload/context, cache keys, file paths, notifications, scheduled loops.
- **Non-scope:** Redis production migration.
- **Dependencies:** Phases 01, 06, 08, 09.
- **Files/subsystems:** Jobs, cache, storage, notifications, scheduler.
- **Database/API/UI impact:** Operational behavior only.
- **Steps:** Add context middleware; namespace keys/files; dispatch after commit; define cleanup policies.
- **Validation/authorization:** Jobs fail safely without context; private files require authorization.
- **Tenant isolation:** Test every tenant-aware mechanism.
- **Tests:** Queued receipt/ETA/billing/report jobs and cache/file leakage.
- **Definition of done:** Async work cannot silently run as the wrong tenant.
- **Handoff:** Deployment worker requirements.
