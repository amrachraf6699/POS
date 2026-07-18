# TASK-003 — Concurrency and Penetration Tests

- **Objective:** Validate financial and tenant safety under simultaneous requests and hostile identifiers.
- **Scope:** Checkout, stock transfer, return, register close, webhook duplicates, IDOR matrix.
- **Non-scope:** Load testing at production scale.
- **Dependencies:** All functional phases.
- **Files/subsystems:** Feature/integration tests and test helpers.
- **Database/API/UI impact:** Test-only; may expose required indexes/locks.
- **Steps:** Run simultaneous scenarios; assert one winner and consistent totals; fuzz IDs/filters.
- **Validation/authorization:** Test unauthorized and stale-state requests.
- **Tenant isolation:** Cross-tenant requests must have zero data/side effects.
- **Tests:** Required scenarios above plus rollback assertions.
- **Definition of done:** Critical race and leakage classes are covered.
- **Handoff:** Release blocker list.
