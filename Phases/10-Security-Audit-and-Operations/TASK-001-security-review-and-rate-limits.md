# TASK-001 — Security Review and Rate Limits

- **Objective:** Review attack surfaces and rate-limit authentication, billing, exports, and sensitive actions.
- **Scope:** Threat checklist, middleware, CSRF/session headers, rate limit definitions.
- **Non-scope:** External penetration test procurement.
- **Dependencies:** All functional phases.
- **Files/subsystems:** HTTP middleware, auth/billing routes, security docs.
- **Database/API/UI impact:** Request behavior and error responses.
- **Steps:** Map assets/threats; add limits; verify secure cookies/HTTPS production settings.
- **Validation/authorization:** Policies remain server-side; rate limits fail closed where appropriate.
- **Tenant isolation:** Sensitive endpoints resolve tenant before work.
- **Tests:** Rate limits, IDOR, CSRF/auth, permission bypass.
- **Definition of done:** Security review has evidence and known residual risks.
- **Handoff:** Release security checklist.
