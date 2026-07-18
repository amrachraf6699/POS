# TASK-001 — Registers and Sessions

- **Objective:** Manage branch registers, opening floats, cash movements, and session state.
- **Scope:** Register/session schema, open action, cash-in/out, permissions.
- **Non-scope:** Closing reports.
- **Dependencies:** Phase 02.
- **Files/subsystems:** Register domain, migrations, policies, views.
- **Database/API/UI impact:** Register tables and cashier UI.
- **Steps:** Enforce one open session; store balances in minor units; record every movement.
- **Validation/authorization:** Open/close/cash movement permissions; active branch required.
- **Tenant isolation:** Register and branch must resolve in current tenant.
- **Tests:** Open, duplicate open, inactive branch, cash movement, authorization.
- **Definition of done:** Sessions are reliable prerequisites for checkout.
- **Handoff:** Active-session contract.
