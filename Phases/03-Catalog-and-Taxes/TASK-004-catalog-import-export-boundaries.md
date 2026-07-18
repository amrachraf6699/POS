# TASK-004 — Catalog Import and Export Boundaries

- **Objective:** Define safe CSV import/export boundaries for products.
- **Scope:** Streamed CSV format, row validation, error report, tenant filtering.
- **Non-scope:** Excel package and queued large imports.
- **Dependencies:** TASK-002.
- **Files/subsystems:** Catalog import/export actions and views.
- **Database/API/UI impact:** CSV contract and admin UI.
- **Steps:** Specify columns; validate every row; use transaction batches; return row-level errors.
- **Validation/authorization:** Product import permission; duplicate detection.
- **Tenant isolation:** Imports and exports never cross tenants.
- **Tests:** Valid/invalid rows, duplicates, empty files, leakage.
- **Definition of done:** Basic catalog transfer is documented and safe.
- **Handoff:** Optional queued-import extension.
