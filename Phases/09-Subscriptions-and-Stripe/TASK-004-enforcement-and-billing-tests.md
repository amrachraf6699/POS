# TASK-004 — Enforcement and Billing Tests

- **Objective:** Prove usage-limit enforcement across the application.
- **Scope:** Branch/user/register/product/sales limits, grace access, billing audit.
- **Non-scope:** Pricing experiments.
- **Dependencies:** TASK-001, TASK-002, TASK-003.
- **Files/subsystems:** Policies, middleware, feature tests.
- **Database/API/UI impact:** Blocked-action responses and billing screens.
- **Steps:** Add centralized checks at mutation boundaries; return Arabic actionable errors; preserve reads.
- **Validation/authorization:** Billing state and role both required.
- **Tenant isolation:** Limits cannot be consumed or queried across tenants.
- **Tests:** Every limit, concurrent creation, downgrade, grace, webhook retry.
- **Definition of done:** Limits cannot be bypassed through alternate routes.
- **Handoff:** Hardening test matrix.
