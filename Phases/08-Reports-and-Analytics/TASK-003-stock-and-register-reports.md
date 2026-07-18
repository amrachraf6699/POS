# TASK-003 — Stock and Register Reports

- **Objective:** Report stock levels/movements and register session outcomes.
- **Scope:** Low stock, movement history, opening/closing, discrepancy, payment totals.
- **Non-scope:** Full accounting or valuation engine.
- **Dependencies:** TASK-001, Phase 07.
- **Files/subsystems:** Reports queries and views.
- **Database/API/UI impact:** Report screens and indexes.
- **Steps:** Reconcile balances to movements; display expected vs actual cash; include return/expense effects.
- **Validation/authorization:** Inventory/register report permissions.
- **Tenant isolation:** Branch filters are constrained by membership.
- **Tests:** Reconciliation and session scenarios.
- **Definition of done:** Operational reports match transactional sources.
- **Handoff:** Release reporting evidence.
