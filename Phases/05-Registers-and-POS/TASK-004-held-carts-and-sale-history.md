# TASK-004 — Held Carts and Sale History

- **Objective:** Hold/resume carts and provide authorized sale history.
- **Scope:** Held cart schema, expiry policy, history filters, Arabic UI.
- **Non-scope:** Offline carts.
- **Dependencies:** TASK-003.
- **Files/subsystems:** POS domain, jobs, views.
- **Database/API/UI impact:** Held cart tables and history screens.
- **Steps:** Tenant-scope carts; prevent stale product assumptions; expose immutable completed sale snapshots.
- **Validation/authorization:** Cashier branch access; manager access to broader history.
- **Tenant isolation:** Search and exports remain tenant/branch scoped.
- **Tests:** Hold/resume, expiry, authorization, historical immutability.
- **Definition of done:** Cashiers can manage queues without editing completed sales.
- **Handoff:** POS operations baseline.
