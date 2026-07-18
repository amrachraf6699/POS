# TASK-002 — Migration, Worker, and Scheduler Runbook

- **Objective:** Define safe releases for schema migrations, queues, scheduled tasks, and cache changes.
- **Scope:** Preflight, migration order, worker restart, scheduler health, rollback boundaries.
- **Non-scope:** Zero-downtime infrastructure implementation.
- **Dependencies:** TASK-001.
- **Files/subsystems:** Release scripts/runbook and scheduler configuration.
- **Database/API/UI impact:** Deployment lifecycle.
- **Steps:** Document backup before risky changes; migrate forward; drain/restart workers; verify failed jobs.
- **Validation/authorization:** Release operator permissions and approvals.
- **Tenant isolation:** Scheduled tenant loops use safe context.
- **Tests:** Staging migration and rollback rehearsal.
- **Definition of done:** Another operator can release without guesswork.
- **Handoff:** Launch gate inputs.
