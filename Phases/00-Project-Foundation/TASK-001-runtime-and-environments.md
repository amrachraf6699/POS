# TASK-001 — Runtime and Environments

- **Objective:** Define reproducible PHP 8.1/Laravel 10 environments with SQLite local/test and MySQL production.
- **Scope:** `.env.example`, database config, runtime documentation, health checks.
- **Non-scope:** Production provisioning.
- **Dependencies:** None.
- **Files/subsystems:** `composer.json`, `config/database.php`, environment docs.
- **Database/API/UI impact:** No domain schema; no public API; no UI.
- **Steps:** Document required extensions; configure SQLite defaults; document MySQL variables; add environment validation.
- **Validation/authorization:** Reject missing or unsafe production settings.
- **Tenant isolation:** No tenant data may be read during environment boot checks.
- **Tests:** Configuration tests for SQLite and required production variables.
- **Definition of done:** A new agent can boot and test the application using the documented commands.
- **Handoff:** Runtime baseline and environment checklist.
