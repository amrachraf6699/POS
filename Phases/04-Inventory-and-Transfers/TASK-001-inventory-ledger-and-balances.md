# TASK-001 — Inventory Ledger and Balances

- **Objective:** Create tenant/branch/product inventory balances and immutable movements.
- **Scope:** Schema, movement types, balance updater, row locks, indexes.
- **Non-scope:** Sales and transfers.
- **Dependencies:** Phase 03.
- **Files/subsystems:** Inventory domain, migrations, services.
- **Database/API/UI impact:** Inventory tables and read endpoints/views.
- **Steps:** Define movement contract; update balances transactionally; prohibit editing historical movements.
- **Validation/authorization:** Valid positive quantities with direction encoded by type.
- **Tenant isolation:** Tenant and branch scope required for every movement.
- **Tests:** Ledger/balance consistency, rollback, lock behavior.
- **Definition of done:** Inventory has one auditable source of truth.
- **Handoff:** Stock mutation service.
