# TASK-004 — Final Acceptance and Rollback Drill

- **Objective:** Validate the MVP vertical slice and recovery procedures in a production-like environment.
- **Scope:** End-to-end business cycle, permissions, reports, backup restore, rollback drill, known limitations.
- **Non-scope:** Post-MVP features.
- **Dependencies:** TASK-001, TASK-002, TASK-003 and all phase acceptance criteria.
- **Files/subsystems:** Acceptance tests and release report.
- **Database/API/UI impact:** Release decision only.
- **Steps:** Create tenant through report; test sale/return/close; simulate failures; record evidence and blockers.
- **Validation/authorization:** Use owner, manager, cashier, and inventory roles.
- **Tenant isolation:** Include two-tenant adversarial acceptance run.
- **Tests:** Full suite plus smoke/concurrency/restore checks.
- **Definition of done:** Release is accepted only when critical blockers are zero.
- **Handoff:** Signed MVP readiness report and post-MVP backlog.
