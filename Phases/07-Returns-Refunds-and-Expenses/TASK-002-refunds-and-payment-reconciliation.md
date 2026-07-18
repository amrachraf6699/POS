# TASK-002 — Refunds and Payment Reconciliation

- **Objective:** Record refunds and reconcile them to returns and payment methods.
- **Scope:** Refund payment records, limits, statuses, receipt/return references.
- **Non-scope:** External card refund API.
- **Dependencies:** TASK-001, Phase 06.
- **Files/subsystems:** Payments/returns services.
- **Database/API/UI impact:** Refund tables and return UI.
- **Steps:** Enforce refund <= paid/returned amount; store method/reference; update sale status.
- **Validation/authorization:** Manager/owner for sensitive methods.
- **Tenant isolation:** Refunds cannot reference another tenant’s sale.
- **Tests:** Partial/full refund, over-refund, duplicate retry, reconciliation.
- **Definition of done:** Refund history is complete and immutable.
- **Handoff:** Reporting/refund metrics.
