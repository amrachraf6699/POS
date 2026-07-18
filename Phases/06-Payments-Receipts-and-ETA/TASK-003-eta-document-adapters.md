# TASK-003 — ETA Document Adapters

- **Objective:** Separate e-Invoice and e-Receipt payload generation/submission contracts.
- **Scope:** Internal tax document model, adapter interfaces, credential/config boundary, sandbox fixtures.
- **Non-scope:** Live certification and real credentials.
- **Dependencies:** TASK-002.
- **Files/subsystems:** Compliance domain, HTTP client boundary, config.
- **Database/API/UI impact:** Tax document/submission tables; admin status UI.
- **Steps:** Map snapshots to provider DTOs; persist provider IDs/statuses; redact secrets and payload logs.
- **Validation/authorization:** Compliance admin access; validate required taxpayer data.
- **Tenant isolation:** Provider documents and credentials are tenant-scoped.
- **Tests:** Payload mapping, signature/config errors, sandbox responses, duplicate submission.
- **Definition of done:** ETA integration can be completed without changing sales core.
- **Handoff:** Submission job contract.
