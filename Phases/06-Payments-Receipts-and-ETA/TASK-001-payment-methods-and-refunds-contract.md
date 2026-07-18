# TASK-001 — Payment Methods and Refunds Contract

- **Objective:** Define cash/card payment records and refund references.
- **Scope:** Payment method catalog, payment metadata, refund record boundary.
- **Non-scope:** Return workflow implementation.
- **Dependencies:** Phase 05.
- **Files/subsystems:** Payments domain.
- **Database/API/UI impact:** Payment tables and checkout display.
- **Steps:** Enforce minor-unit amounts; restrict change to cash; preserve external references without card data.
- **Validation/authorization:** Payment and refund permissions.
- **Tenant isolation:** Payment and sale references must share tenant.
- **Tests:** Cash/card/split/change and invalid payment cases.
- **Definition of done:** Payment records are auditable and provider-neutral.
- **Handoff:** Returns and Stripe separation contract.
