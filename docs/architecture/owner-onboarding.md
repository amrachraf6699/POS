# Owner onboarding

The Business module owns `tenant_onboardings`. It is tenant-scoped, has one row per tenant, and records completion of business settings, the first active branch, optional staff setup, and final completion.

Only an active owner may use `/tenant/onboarding/*`. An incomplete owner is redirected from the tenant dashboard to the first unfinished step; managers and other active staff retain normal dashboard access. Existing settings, branch, and invitation flows also advance the corresponding onboarding state, so they remain valid completion paths.

The migration backfills existing tenants. A tenant with persisted business settings and an active branch is marked complete. The staff step is explicitly completed by either sending an invitation or selecting skip; no tenant identifier is accepted from the browser.
