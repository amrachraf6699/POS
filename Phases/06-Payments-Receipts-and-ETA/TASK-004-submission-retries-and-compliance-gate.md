# TASK-004 — Submission Retries and Compliance Gate

- **Objective:** Make ETA submission retryable, observable, and release-gated.
- **Scope:** Queue jobs, idempotency, backoff, status transitions, operational checklist.
- **Non-scope:** Live taxpayer onboarding.
- **Dependencies:** TASK-003.
- **Files/subsystems:** Jobs, events, compliance status screens, release docs.
- **Database/API/UI impact:** Retry metadata and admin status UI.
- **Steps:** Dispatch after commit; deduplicate by document key; classify retryable/permanent errors.
- **Validation/authorization:** Only authorized admins can retry/correct documents.
- **Tenant isolation:** Jobs restore originating tenant context.
- **Tests:** Retry, duplicate job, permanent error, tenant job context.
- **Definition of done:** Sandbox proof exists and production ETA is an explicit release gate.
- **Handoff:** Compliance deployment checklist.
