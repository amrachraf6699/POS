# TASK-003 — Stripe and ETA Launch Gates

- **Objective:** Define evidence required before enabling live billing and tax submission.
- **Scope:** Stripe live credentials/webhooks, ETA taxpayer onboarding/certification, sandbox-to-live checklist.
- **Non-scope:** Obtaining credentials on the customer’s behalf.
- **Dependencies:** Phase 06, Phase 09, TASK-001.
- **Files/subsystems:** Compliance/billing release docs and config.
- **Database/API/UI impact:** Production integrations and feature flags.
- **Steps:** Verify signatures, callbacks, identifiers, retries, accepted documents, and support contacts.
- **Validation/authorization:** Only approved operators enable live flags.
- **Tenant isolation:** Provider accounts and credentials map to intended tenant/business.
- **Tests:** Sandbox acceptance and disabled-live behavior.
- **Definition of done:** No false compliance or accidental live billing occurs.
- **Handoff:** Final acceptance checklist.
