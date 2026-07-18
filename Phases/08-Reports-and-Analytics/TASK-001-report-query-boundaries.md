# TASK-001 — Report Query Boundaries

- **Objective:** Define read-only report query services and timezone/date semantics.
- **Scope:** Filters, date conversion, branch/cashier/product dimensions, query indexes.
- **Non-scope:** Dashboard presentation.
- **Dependencies:** Phases 03–07.
- **Files/subsystems:** Reports domain and query objects.
- **Database/API/UI impact:** Report contracts and indexes.
- **Steps:** Convert tenant-local dates to UTC boundaries; define status inclusion; avoid N+1 queries.
- **Validation/authorization:** Report permission and valid ranges.
- **Tenant isolation:** Every query begins with tenant scope.
- **Tests:** Timezone boundaries, filters, reconciliation, leakage.
- **Definition of done:** All reports share consistent source semantics.
- **Handoff:** Dashboard data contracts.
