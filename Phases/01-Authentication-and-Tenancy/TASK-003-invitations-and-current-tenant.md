# TASK-003 — Invitations and Current Tenant

- **Objective:** Support secure staff invitations and tenant switching for multi-membership users.
- **Scope:** Invitation records, signed links, acceptance, membership status, current tenant session.
- **Non-scope:** Detailed role permissions.
- **Dependencies:** TASK-001, TASK-002.
- **Files/subsystems:** Identity actions, notifications, session middleware.
- **Database/API/UI impact:** Invitation table, auth UI, email workflow.
- **Steps:** Add expiring hashed tokens; accept once; switch only among active memberships.
- **Validation/authorization:** Owner/manager permission; reject expired/replayed tokens.
- **Tenant isolation:** Switching must rebuild context and invalidate stale tenant state.
- **Tests:** Invitation lifecycle, replay, expiry, unauthorized switch.
- **Definition of done:** Staff can join and safely switch tenants.
- **Handoff:** Membership workflow for authorization phase.
