# TASK-003 — Branch Transfers

- **Objective:** Move stock between branches atomically.
- **Scope:** Transfer document/status, source/destination validation, paired movements.
- **Non-scope:** Supplier receiving and offline transfers.
- **Dependencies:** TASK-001, TASK-002.
- **Files/subsystems:** Inventory transfer domain, policies, views.
- **Database/API/UI impact:** Transfer schema and branch UI.
- **Steps:** Lock both balances in stable order; reject same/inactive branches; create out/in movements together.
- **Validation/authorization:** Inventory staff create; manager approval policy if configured.
- **Tenant isolation:** Both branches and products must belong to the tenant.
- **Tests:** Success, insufficient stock, concurrency, rollback, duplicate submission.
- **Definition of done:** Transfers cannot lose or duplicate units.
- **Handoff:** Transfer report inputs.
