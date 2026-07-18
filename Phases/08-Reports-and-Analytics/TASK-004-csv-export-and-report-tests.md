# TASK-004 — CSV Export and Report Tests

- **Objective:** Add safe streamed CSV exports and complete report regression coverage.
- **Scope:** Export formats, authorization, escaping, memory-safe streaming.
- **Non-scope:** Excel/PDF packages.
- **Dependencies:** TASK-002, TASK-003.
- **Files/subsystems:** Report exporters, routes, tests.
- **Database/API/UI impact:** Download endpoints.
- **Steps:** Reuse report filters; stream rows; include Arabic UTF-8 handling; audit exports.
- **Validation/authorization:** Export permission and bounded filters.
- **Tenant isolation:** Assert exports contain only current tenant.
- **Tests:** Headers, Unicode, large dataset, leakage, permission.
- **Definition of done:** Reports are exportable without loading all rows in memory.
- **Handoff:** Report package for billing/admin.
