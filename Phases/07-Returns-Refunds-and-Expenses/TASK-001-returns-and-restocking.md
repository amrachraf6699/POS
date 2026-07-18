# TASK-001 — Returns and Restocking

- **Objective:** Return full/partial quantities against original sales when available.
- **Scope:** Return documents/items/reasons, quantity validation, restockable flag, movements.
- **Non-scope:** Independent no-receipt returns beyond approved policy.
- **Dependencies:** Phase 05, Phase 04.
- **Files/subsystems:** Returns and inventory domains.
- **Database/API/UI impact:** Return schema and manager/cashier UI.
- **Steps:** Lock sale items and stock; prevent repeated quantity return; create movement atomically.
- **Validation/authorization:** Refund permission; original sale and branch policy required.
- **Tenant isolation:** Sale, customer, branch, and product must share tenant.
- **Tests:** Full/partial/repeated/non-restockable/concurrent returns.
- **Definition of done:** Returns correct stock without mutating sale history.
- **Handoff:** Refund input contract.
