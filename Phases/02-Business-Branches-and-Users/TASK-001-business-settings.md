# TASK-001 — Business Settings

- **Objective:** Store tenant identity, timezone, currency, VAT mode, receipt defaults, and operational settings.
- **Scope:** Settings model, validation, Arabic UI, snapshot policy.
- **Non-scope:** ETA credentials and Stripe settings.
- **Dependencies:** Phase 01.
- **Files/subsystems:** Business domain, settings migrations, settings views.
- **Database/API/UI impact:** Tenant settings schema and forms.
- **Steps:** Add defaults; validate EGP/VAT-inclusive policy; preserve historical values through snapshots.
- **Validation/authorization:** Owner/manager only for updates.
- **Tenant isolation:** Settings queries require current tenant.
- **Tests:** Defaults, update authorization, timezone display.
- **Definition of done:** Business configuration is safe and reusable by all modules.
- **Handoff:** Settings service contract.
