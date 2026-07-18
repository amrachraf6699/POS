# TASK-003 — Roles and Permissions

- **Objective:** Implement owner, manager, cashier, and inventory-staff authorization.
- **Scope:** Permission catalog, role assignment, policies/gates, owner safeguards.
- **Non-scope:** UI hiding only; platform-admin role.
- **Dependencies:** Phase 01, TASK-002.
- **Files/subsystems:** Authorization domain, policies, seeders.
- **Database/API/UI impact:** Permission schema and staff screens.
- **Steps:** Define permission matrix; assign defaults; enforce policies on each action boundary.
- **Validation/authorization:** Server-side policy checks for every mutation.
- **Tenant isolation:** Permission assignments are tenant-scoped.
- **Tests:** Role matrix and final-owner protection.
- **Definition of done:** Authorization is testable without relying on UI state.
- **Handoff:** Permission matrix for all later phases.
