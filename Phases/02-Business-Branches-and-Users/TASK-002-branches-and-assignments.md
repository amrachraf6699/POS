# TASK-002 — Branches and Assignments

- **Objective:** Create branches and assign active users to allowed branches.
- **Scope:** Branch CRUD, branch-user membership, active status.
- **Non-scope:** Registers and inventory.
- **Dependencies:** TASK-001.
- **Files/subsystems:** Branch domain, policies, views.
- **Database/API/UI impact:** Branch and assignment tables.
- **Steps:** Add tenant-scoped code uniqueness; block deactivation with required operational checks; add assignment actions.
- **Validation/authorization:** Owner/manager only; inactive branches cannot be selected.
- **Tenant isolation:** All branch IDs are checked against current tenant.
- **Tests:** CRUD, duplicate code, inactive branch, assignment access.
- **Definition of done:** Branch ownership and staff reach are explicit.
- **Handoff:** Branch context for inventory/POS.
