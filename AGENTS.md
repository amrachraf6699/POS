# AI Agent Contract — Laravel SaaS POS MVP

## Mission

Build the Egypt-focused, Arabic-only POS SaaS as a Laravel 10 modular monolith using PHP 8.1. Preserve tenant isolation, inventory correctness, payment accuracy, and historical financial records above delivery speed.

## Fixed technical decisions

- Laravel 10.x, PHP 8.1, Composer 2.x.
- SQLite for local development and fast tests; MySQL 8+ for production.
- Blade with selective JavaScript; Arabic and RTL are the initial product language.
- Shared database with application-owned `tenant_id` scoping. Do not add a tenancy package.
- Package-free domain folders under `app/Domain` unless a task explicitly approves another location.
- Integer minor units for money; VAT-inclusive retail pricing; no floating-point money.
- Simple products only; online-only POS; branch transfers are in scope.
- Stripe SaaS billing and ETA e-Invoice/e-Receipt adapters are integration boundaries, not reasons to leak provider logic into domain code.

## Architecture rules

- Keep controllers thin. Put business workflows in Actions/Services and expose stable DTO-like request/response boundaries.
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

- Use `app/Domain/<Context>/{Actions,Data,Enums,Events,Exceptions,Models,Policies,Services}` as needed.
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

Do not install packages automatically. First document the purpose, selected version, PHP/Laravel compatibility, alternatives, and rollback/removal impact. Pin Laravel 10/PHP 8.1-compatible versions and update the compatibility record.

## Required agent handoff

For each completed task, report: files changed, behavior added, schema/API impact, tests run, assumptions, known limitations, and the next unblocked task. Update the relevant phase README and task status. Never silently change architecture or scope.

## Definition of done

Requirements, validation, authorization, tenant isolation, indexes, transactions, tests, error handling, documentation, and observability considerations are complete. The implementation passes the repository checks and does not weaken historical accuracy or financial consistency.
