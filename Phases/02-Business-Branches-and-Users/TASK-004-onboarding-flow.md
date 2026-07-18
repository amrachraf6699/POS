# TASK-004 — Business Onboarding Flow

- **Objective:** Guide an owner through business settings, first branch, and first user setup.
- **Scope:** Arabic onboarding screens, progress state, validation, completion status.
- **Non-scope:** Product/import onboarding.
- **Dependencies:** TASK-001, TASK-002, TASK-003.
- **Files/subsystems:** Onboarding domain and Blade views.
- **Database/API/UI impact:** Onboarding state and UI flow.
- **Steps:** Make steps resumable; create defaults atomically; prevent access to incomplete tenant features where required.
- **Validation/authorization:** Owner-only completion.
- **Tenant isolation:** Onboarding state belongs only to current tenant.
- **Tests:** Complete, resume, invalid, and cross-tenant flows.
- **Definition of done:** New owner reaches a usable configured workspace.
- **Handoff:** Ready tenant for catalog work.
