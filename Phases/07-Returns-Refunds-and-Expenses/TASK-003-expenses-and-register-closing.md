# TASK-003 — Expenses and Register Closing

- **Objective:** Record expenses/cash movements and close sessions atomically.
- **Scope:** Expense categories, expenses, expected/actual cash, discrepancy, closing report.
- **Non-scope:** Accounting ledger.
- **Dependencies:** Phase 05, TASK-002.
- **Files/subsystems:** Expenses/register domains.
- **Database/API/UI impact:** Expense and closing data plus UI.
- **Steps:** Lock session; calculate expected cash; persist actual/difference; reject new sale after close.
- **Validation/authorization:** Close permission; active session required.
- **Tenant isolation:** Branch/register/session references must share tenant.
- **Tests:** Expected cash, cash-out, discrepancy, duplicate close, rollback.
- **Definition of done:** Sessions close with an auditable financial snapshot.
- **Handoff:** Register report source.
