# TASK-001 — Plans, Trials, and Usage

- **Objective:** Model fixed Starter/Business plans, trial state, feature flags, and usage counters.
- **Scope:** Central plan data, trial dates, usage query/service, configurable trial period.
- **Non-scope:** Stripe integration.
- **Dependencies:** Phase 01, Phase 08.
- **Files/subsystems:** Subscription domain, migrations, seeders.
- **Database/API/UI impact:** Plan/subscription schema and owner UI.
- **Steps:** Seed exact limits; define period boundaries; calculate usage from authoritative records.
- **Validation/authorization:** Owner billing access; no client-provided usage.
- **Tenant isolation:** Usage queries always select one tenant.
- **Tests:** Trial active/expired, limits, period reset, timezone boundary.
- **Definition of done:** Product limits are deterministic before payment integration.
- **Handoff:** Subscription state machine inputs.
