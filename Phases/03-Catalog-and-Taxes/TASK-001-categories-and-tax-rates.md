# TASK-001 — Categories and Tax Rates

- **Objective:** Manage tenant categories and configurable VAT rates.
- **Scope:** Models, CRUD, active/inactive rates, Arabic labels.
- **Non-scope:** Multi-jurisdiction tax engine.
- **Dependencies:** Phase 02.
- **Files/subsystems:** Catalog domain and migrations.
- **Database/API/UI impact:** Category/tax tables and admin UI.
- **Steps:** Add tenant indexes; prevent deleting referenced records; define effective-date behavior.
- **Validation/authorization:** Manager/owner changes; valid nonnegative rates.
- **Tenant isolation:** All category/rate lookups are tenant-scoped.
- **Tests:** CRUD, references, rate validation, leakage.
- **Definition of done:** Product tax assignment has stable source data.
- **Handoff:** Tax service inputs.
