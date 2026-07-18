# AI Agent Contract — Laravel SaaS POS MVP

## Mission

Build the Egypt-focused, Arabic-only POS SaaS as a Laravel 10 modular monolith using PHP 8.1. Preserve tenant isolation, inventory correctness, payment accuracy, and historical financial records above delivery speed.

## Fixed technical decisions

- Laravel 10.x, PHP 8.1, Composer 2.x.
- SQLite for local development and fast tests; MySQL 8+ for production.
- Blade with selective JavaScript; Arabic and RTL are the initial product language.
- Shared database with application-owned `tenant_id` scoping. Do not add a tenancy package.
- Use `nwidart/laravel-modules` as the project structure package. Each business area must be represented as a module under `Modules/<ModuleName>`.
- Integer minor units for money; VAT-inclusive retail pricing; no floating-point money.
- Simple products only; online-only POS; branch transfers are in scope.
- Stripe SaaS billing and ETA e-Invoice/e-Receipt adapters are integration boundaries, not reasons to leak provider logic into domain code.

## Architecture rules

- Keep controllers thin. Put business workflows in Actions/Services and expose stable DTO-like request/response boundaries.
- Use `nwidart/laravel-modules` module boundaries for domain code, migrations, routes, providers, views, and module tests. Do not bypass the module structure with a second competing `app/Domain` architecture.
- Keep module dependencies explicit and one-directional. Shared abstractions belong in a deliberately named shared module or approved application layer.
- Tenant context must be established before tenant-owned queries. Never trust a tenant ID from request input.
- Every tenant-owned table includes `tenant_id` and appropriate composite indexes.
- Use policies/Form Requests for authorization and validation. UI visibility is not authorization.
- Use database transactions for checkout, returns, stock adjustments/transfers, register closing, and billing state changes.
- Lock inventory and register rows inside the transaction when concurrent writes are possible.
- Financial records are append-only or corrected through explicit void/refund/adjustment workflows.
- Historical sale, tax, product, customer, and receipt values must be snapshotted.
- Queue jobs, cache keys, files, notifications, exports, and audit records must carry tenant context.
- Events may trigger secondary work but may not hide essential inventory or money writes that must succeed synchronously.

## Naming and structure

- Use `Modules/<ModuleName>/{Config,Console,Database,Domain,Http,Models,Policies,Providers,Resources,Routes,Tests}` as needed, following the package's generated module conventions.
- Put module business logic in `Modules/<ModuleName>/Domain/{Actions,Data,Enums,Events,Exceptions,Services,ValueObjects}`.
- Keep module migrations, factories, seeders, routes, views, and tests inside their owning module.
- Use Laravel conventions for migrations, Form Requests, policies, jobs, notifications, and tests.
- Prefer explicit names such as `CompleteSaleAction`, `TransferStockAction`, and `TenantContextMiddleware`.
- Keep each task focused and avoid unrelated refactors.

## Security and compliance

- Prevent IDOR and cross-tenant access in models, policies, route binding, searches, exports, jobs, and reports.
- Never log passwords, tokens, card data, private customer data, or ETA/Stripe secrets.
- Never store raw card details.
- Verify webhook signatures and make webhook processing idempotent.
- Treat ETA production certification, taxpayer credentials, and live Stripe credentials as release gates.
- Stop and report any ambiguity that could cause tenant leakage, duplicate financial records, incorrect stock, or false compliance claims.

## Testing requirements

Every behavior task adds or updates tests. Required coverage includes happy path, validation, authorization, tenant isolation, duplicate/retry behavior, and failure rollback where applicable. Critical workflows require feature tests; pure calculation and policy rules may also have unit tests. Run formatting, tests, static analysis, and dependency audit before release.

## Package policy

`nwidart/laravel-modules` is an approved required dependency for project structure. Before installation, verify and record the exact Laravel 10/PHP 8.1-compatible release with Composer. Do not silently substitute a different module package or a package-free structure. For every other package, first document the purpose, selected version, PHP/Laravel compatibility, alternatives, and rollback/removal impact. Pin all versions and update the compatibility record.

## Incremental commit policy

Git history is part of the implementation contract.

- Before changing files, inspect the current branch and working tree. Preserve unrelated user changes.
- Before the first implementation step, confirm that the project is a Git repository and that commits can be created. If commits are impossible, stop and report the blocker instead of continuing silently.
- Define each implementation step as one coherent, reviewable change. A step may include several related files and tests; it must not mix unrelated features or cleanup.
- Commit immediately after each coherent step passes its relevant checks. Do not wait until the phase or project is finished.
- Group commits by meaningful related change, not mechanically by file and not as one commit per task when several task steps form one coherent change.
- Use messages that identify the phase and outcome, for example: `phase-01: enforce tenant-scoped route binding` or `phase-05: complete atomic cash checkout`.
- Keep the working tree clean between steps. Never carry a pile of completed steps into a final catch-all commit.
- Do not squash incremental implementation commits into one final commit. Do not amend an earlier commit unless explicitly asked or the immediately preceding commit is being corrected before the next step begins.
- After every commit, push that commit to the configured remote before beginning the next implementation step. Push the current branch explicitly; do not use force-push or rewrite remote history.
- If pushing fails, stop before starting the next step, report the exact push blocker, and do not pretend the work is remotely synchronized.
- After every commit and push, report the commit hash, message, remote/branch, push result, checks run, and the next uncommitted step.
- Documentation-only changes follow the same policy.

## Required agent handoff

For each completed task, report: files changed, behavior added, schema/API impact, tests run, assumptions, known limitations, and the next unblocked task. Update the relevant phase README and task status. Never silently change architecture or scope.

## Tracker maintenance contract

AI agents are the only actors authorized to update project tracking state. Humans do not update tracker state through the application.

The local-only problem-resolution workflow is an exception for development support: when `TRACKER_WEB_UPDATES=true` (never enabled in production), the dashboard may record a proposed resolution in `tracker/tracker.json`. An AI agent must immediately review the change, verify the evidence, update the relevant task/phase metadata, commit it with a meaningful message, and push it. Production and any non-local environment must remain read-only.

- Maintain live tracker state in the committed `tracker/tracker.json` file.
- After every coherent implementation step, update the affected task status, parent phase status, notes, conflicts, problems, resolutions, evidence, timestamps, and latest commit as applicable.
- Use only the approved statuses: `not_started`, `planned`, `in_progress`, `review`, `done`, and `blocked`.
- Do not mark a task `done` until its task definition of done and required tests are satisfied.
- Do not mark a blocked task done because work moved to another task.
- Record conflicts and problems immediately; never hide, overwrite, or silently delete unresolved issues.
- Record a resolution when a conflict or problem is genuinely addressed.
- Preserve unrelated tracker entries when updating one task or phase.
- Validate the JSON and recalculate progress before committing.
- Commit and push the tracker update together with the related implementation step, using a meaningful phase/outcome commit message.
- Report changed tracker entries, progress change, new/resolved issues, checks, commit hash, and push result in the handoff.
- Update phase/task Markdown only when requirements, acceptance criteria, dependencies, or implementation scope genuinely change; status and operational progress belong in `tracker/tracker.json`.
- Never put secrets, credentials, private customer data, absolute filesystem paths, or sensitive infrastructure details in tracker notes.

## Definition of done

Requirements, validation, authorization, tenant isolation, indexes, transactions, tests, error handling, documentation, and observability considerations are complete. The implementation passes the repository checks and does not weaken historical accuracy or financial consistency.
