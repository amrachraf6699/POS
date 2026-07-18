# TASK-001 — Deployment and Environment Checklist

- **Objective:** Document production application, database, queue, storage, mail, and secrets configuration.
- **Scope:** Deployment topology, PHP extensions, HTTPS, MySQL, workers, scheduler, storage.
- **Non-scope:** Cloud-provider automation.
- **Dependencies:** Phase 10.
- **Files/subsystems:** Deployment and operations docs.
- **Database/API/UI impact:** Runtime configuration only.
- **Steps:** Enumerate required variables; separate secrets; verify production debug/logging settings.
- **Validation/authorization:** Least-privilege credentials and access review.
- **Tenant isolation:** Storage and queue configuration must preserve context.
- **Tests:** Staging smoke and configuration validation.
- **Definition of done:** Deployment has no undocumented required setting.
- **Handoff:** Runbook for migration task.
