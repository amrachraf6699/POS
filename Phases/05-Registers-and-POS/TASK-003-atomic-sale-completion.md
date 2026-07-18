# TASK-003 — Atomic Sale Completion

- **Objective:** Complete a sale with payments, stock deduction, and register impact in one transaction.
- **Scope:** Sale/sale-item/payment schema, locks, snapshots, idempotency.
- **Non-scope:** Refunds and ETA submission.
- **Dependencies:** TASK-001, TASK-002, Phase 04.
- **Files/subsystems:** Sales, payments, inventory integration.
- **Database/API/UI impact:** Financial tables and checkout action.
- **Steps:** Lock stock/session; calculate totals; create snapshots; write movements/payments; dispatch after commit.
- **Validation/authorization:** Active session, valid payment sum/change, sufficient stock.
- **Tenant isolation:** Every referenced record must belong to current tenant.
- **Tests:** Cash, card, split payment, insufficient stock, duplicate request, rollback, concurrency.
- **Definition of done:** Vertical-slice sale is financially and inventory consistent.
- **Handoff:** Completed sale event and receipt input.
