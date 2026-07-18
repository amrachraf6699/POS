# TASK-003 — Stripe Checkout and Webhooks

- **Objective:** Integrate Stripe behind a provider adapter and process signed events.
- **Scope:** Checkout session boundary, customer mapping, webhook persistence/signature/idempotency.
- **Non-scope:** In-store card-terminal processing.
- **Dependencies:** TASK-002, compatibility gate from Phase 00.
- **Files/subsystems:** Billing adapter, webhook routes/jobs/config.
- **Database/API/UI impact:** Stripe IDs/events and billing UI.
- **Steps:** Keep provider calls out of domain entities; persist raw-safe event metadata; map events to state transitions.
- **Validation/authorization:** Verify signatures; never trust browser plan IDs.
- **Tenant isolation:** Event mapping resolves one tenant safely.
- **Tests:** Signed valid/invalid, duplicate, out-of-order, retryable events.
- **Definition of done:** Stripe integration is isolated and retry-safe.
- **Handoff:** Live credentials checklist.
