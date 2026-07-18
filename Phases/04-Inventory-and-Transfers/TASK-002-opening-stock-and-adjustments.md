# TASK-002 — Opening Stock and Adjustments

- **Objective:** Add authorized opening-stock and adjustment workflows.
- **Scope:** Adjustment documents, reasons, items, approvals if required, movements.
- **Non-scope:** Purchases and suppliers.
- **Dependencies:** TASK-001.
- **Files/subsystems:** Inventory actions, policies, views.
- **Database/API/UI impact:** Adjustment schema and UI.
- **Steps:** Validate branch/product; lock balances; create document plus movements in one transaction.
- **Validation/authorization:** Inventory permission; reason required; no direct quantity edits.
- **Tenant isolation:** Branch/product references must belong to current tenant.
- **Tests:** In/out, invalid quantity, permission, rollback, audit.
- **Definition of done:** Authorized stock corrections are traceable.
- **Handoff:** Opening inventory for vertical slice.
