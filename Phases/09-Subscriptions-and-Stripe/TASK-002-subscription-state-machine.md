# TASK-002 — Subscription State Machine

- **Objective:** Define active, trialing, past-due, grace, canceled, and suspended behavior.
- **Scope:** State transitions, access policy, downgrade preservation.
- **Non-scope:** Stripe webhooks.
- **Dependencies:** TASK-001.
- **Files/subsystems:** Subscription services and policies.
- **Database/API/UI impact:** Access middleware and billing UI.
- **Steps:** Document allowed transitions; keep historical data readable; block only disallowed writes.
- **Validation/authorization:** Billing owner permission; state changes are audited.
- **Tenant isolation:** Subscription access belongs to tenant context.
- **Tests:** All transitions, grace, expired trial, downgrade, suspension.
- **Definition of done:** Every feature can query one central access decision.
- **Handoff:** Provider event mapping.
