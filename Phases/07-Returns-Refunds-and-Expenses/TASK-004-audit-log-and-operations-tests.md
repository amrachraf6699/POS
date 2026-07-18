# TASK-004 — Audit Log and Operations Tests

- **Objective:** Record sensitive operational actions and test correction workflows.
- **Scope:** Audit event schema, actor/context metadata, retention/redaction, tests.
- **Non-scope:** External SIEM integration.
- **Dependencies:** TASK-001, TASK-002, TASK-003.
- **Files/subsystems:** Audit domain, listeners, tests.
- **Database/API/UI impact:** Audit table and admin read UI.
- **Steps:** Log invite/permission/void/refund/adjustment/close actions; exclude secrets; expose filters.
- **Validation/authorization:** Audit visibility limited to owner/platform roles.
- **Tenant isolation:** Every tenant audit event includes tenant ID.
- **Tests:** Required events, redaction, access controls, tenant leakage.
- **Definition of done:** Financial corrections are explainable.
- **Handoff:** Security review evidence.
