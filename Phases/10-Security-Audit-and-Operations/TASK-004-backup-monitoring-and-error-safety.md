# TASK-004 — Backup, Monitoring, and Error Safety

- **Objective:** Prepare safe operations for database, queues, health, errors, and backups.
- **Scope:** Health checks, failed-job handling, correlation IDs, backup/restore runbook, production debug settings.
- **Non-scope:** Infrastructure vendor selection.
- **Dependencies:** TASK-002, TASK-003.
- **Files/subsystems:** Logging, health routes, scheduler, operations docs.
- **Database/API/UI impact:** Operational endpoints and alerts.
- **Steps:** Add tenant-safe context; redact sensitive data; test restore procedure; define escalation thresholds.
- **Validation/authorization:** Health details do not expose secrets or tenant data.
- **Tenant isolation:** Monitoring metadata is minimized and scoped.
- **Tests:** Error rendering, failed jobs, backup restore drill, health response.
- **Definition of done:** Operators can detect and recover from common failures.
- **Handoff:** Production readiness evidence.
