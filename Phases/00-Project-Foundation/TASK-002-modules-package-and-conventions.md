# TASK-002 — Modules Package and Conventions

- **Objective:** Install and configure `nwidart/laravel-modules` as the mandatory project-structure package.
- **Scope:** Composer dependency, module discovery, generated module conventions, namespaces, module test locations, and dependency rules.
- **Non-scope:** Implementing business modules.
- **Dependencies:** TASK-001.
- **Files/subsystems:** `composer.json`, `Modules/`, module configuration, autoloading, architecture documentation.
- **Database/API/UI impact:** No business schema; establishes module routes/views/providers boundaries.
- **Steps:** Verify a Laravel 10/PHP 8.1-compatible release; install the pinned version; generate a proof module; document the approved module layout; commit the dependency and structure as one coherent step.
- **Validation/authorization:** N/A; package and module discovery must pass framework checks.
- **Tenant isolation:** The proof module must demonstrate that tenant-owned code remains behind the tenant context boundary.
- **Tests:** Module discovery, provider loading, route/view loading, and namespace smoke tests.
- **Definition of done:** All future business code has one official `Modules/<ModuleName>` structure and no package-free replacement is permitted.
- **Handoff:** Module template and dependency rules for all later phases.
