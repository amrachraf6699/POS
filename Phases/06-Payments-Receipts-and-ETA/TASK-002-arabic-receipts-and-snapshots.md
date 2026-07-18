# TASK-002 — Arabic Receipts and Snapshots

- **Objective:** Render printable Arabic receipts from immutable sale/business snapshots.
- **Scope:** Receipt template, print CSS, receipt numbering, snapshot fields.
- **Non-scope:** Hardware printer integration.
- **Dependencies:** TASK-001, Phase 02, Phase 05.
- **Files/subsystems:** Receipt domain and Blade templates.
- **Database/API/UI impact:** Receipt metadata and print UI.
- **Steps:** Define number sequence; capture business/tax fields at sale time; render RTL thermal-friendly output.
- **Validation/authorization:** Receipt access follows sale permissions.
- **Tenant isolation:** Receipt paths/data are tenant-scoped.
- **Tests:** Snapshot immutability, numbering concurrency, Arabic rendering smoke test.
- **Definition of done:** Receipt generation does not block sale completion.
- **Handoff:** Receipt document contract.
