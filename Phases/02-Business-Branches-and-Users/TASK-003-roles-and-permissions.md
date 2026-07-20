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

## Dashboard and Navigation Authorization

The authenticated tenant dashboard is available to active tenant members, but navigation visibility is role-aware. Active owners and managers may see business settings, branch management, and invitation management. Other active members may see the dashboard, tenant switching, and only branches they can access.

UI visibility is not authorization. Every management route and action must enforce the active user, active membership, tenant status, role, and tenant/branch ownership server-side. Branch-aware links must resolve only to the current tenant and must not expose guessed or inaccessible branch IDs.

The current owner/manager checks are a transitional foundation. This task must later replace them with the complete permission catalog and cashier/inventory-staff matrix without weakening existing middleware, policy, Form Request, or action checks.

Role and permission work must include feature tests for dashboard access, navigation visibility, route authorization, branch reach, tenant isolation, and direct requests that bypass the UI.
