# TASK-004 — Low Stock and Inventory Test Suite

- **Objective:** Detect low stock and establish inventory regression coverage.
- **Scope:** Threshold queries, events/notifications, concurrency and tenant tests.
- **Non-scope:** External notification providers.
- **Dependencies:** TASK-001, TASK-002, TASK-003.
- **Files/subsystems:** Inventory reports, events, tests.
- **Database/API/UI impact:** Low-stock read model and notification event.
- **Steps:** Define threshold semantics; query current tenant/branch; dispatch after commit.
- **Validation/authorization:** Reports require inventory permission.
- **Tenant isolation:** Alerts and reports contain only current tenant data.
- **Tests:** Threshold edges, transfer effects, concurrent mutations, leakage.
- **Definition of done:** Inventory correctness is a mandatory quality gate.
- **Handoff:** POS stock availability contract.
