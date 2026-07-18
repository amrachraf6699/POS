# SaaS POS Web Application — MVP Product & Technical Specification

> **Purpose:** This document is a working specification to negotiate, refine, and implement with an AI coding agent.
>
> **Target stack:** PHP 8.1, Laravel 10, MySQL/PostgreSQL, Redis optional.
>
> **Project type:** Multi-tenant SaaS Point of Sale web application.

---

## 1. Project Summary

Build a multi-tenant SaaS Point of Sale application for small and medium retail businesses.

Each tenant represents a business. A business may have:

- Multiple branches
- Multiple registers
- Multiple employees
- Its own products, inventory, customers, sales, settings, and reports
- A subscription plan with feature and usage limits

The MVP must allow a business to:

1. Register and create a workspace.
2. Configure its business and first branch.
3. Invite staff.
4. Add products and opening stock.
5. Open a cash register session.
6. Process sales.
7. Record payments.
8. Print or download receipts.
9. Process returns and refunds.
10. Close the register.
11. View basic sales and inventory reports.
12. Manage its SaaS subscription.

---

## 2. Technical Constraints

### Required versions

- PHP: `8.1`
- Laravel: `10.x`
- Composer: `2.x`
- Database: MySQL 8+ or PostgreSQL 14+
- Cache: Database cache for local development; Redis recommended for production
- Queue: Database queue for MVP; Redis recommended for production
- Frontend: Blade with Livewire or Inertia.js with Vue
- API authentication: Laravel Sanctum when an API or SPA is required

### Support warning

Laravel 10 and PHP 8.1 are technically compatible, but both are now outside their official security-support periods.

For an internal prototype or constrained hosting environment, this stack can still be used. For a public production SaaS that handles payments and business data, create an upgrade plan to a supported PHP and Laravel version before launch.

### Compatibility policy

The AI agent must:

- Never install the latest package version blindly.
- Check each package's PHP and Laravel constraints before installation.
- Use Composer to resolve compatible versions.
- Record every selected package version in this document or an Architecture Decision Record.
- Avoid packages that require PHP 8.2+ or Laravel 11+.
- Prefer maintained packages with active releases and documentation.
- Run `composer audit` before each release.

Useful verification commands:

```bash
composer show package/name --all
composer why-not package/name:^VERSION
composer prohibits php 8.1
composer audit
```

---

## 3. MVP Architecture Decisions

### 3.1 Application style

Use a modular monolith.

Do not start with microservices.

Reasons:

- Faster MVP development
- Easier transactions across sales, inventory, and payments
- Easier deployment
- Easier debugging
- Lower operational cost
- Modules can later be extracted if necessary

### 3.2 Multi-tenancy strategy

Recommended initial strategy:

- One application
- One shared database
- Tenant-owned records contain a `tenant_id` or `business_id`
- Every tenant-owned query is automatically scoped
- Central/platform records remain outside tenant scope

Examples of central tables:

```text
tenants
domains
plans
subscriptions
subscription_items
platform_admins
failed_jobs
jobs
```

Examples of tenant-owned tables:

```text
branches
registers
products
categories
inventories
inventory_movements
customers
sales
sale_items
payments
returns
expenses
```

### 3.3 Tenant isolation rule

Every tenant-owned record must belong to exactly one tenant.

The application must never trust a tenant ID supplied directly by the frontend.

Tenant context must be resolved from one of the following:

- Subdomain
- Custom domain
- Authenticated user's current business
- Explicit central-to-tenant impersonation session

The tenant ID should be injected by application logic or tenancy middleware.

### 3.4 Database-per-tenant decision

Do not use database-per-tenant for the first MVP unless there is a strong legal, regulatory, or enterprise isolation requirement.

Shared-database tenancy is simpler for:

- Deployment
- Migrations
- Reporting
- Backups
- Support
- Local development
- Subscription enforcement

The data model should still make a later migration possible.

### 3.5 Domain logic

Controllers must remain thin.

Business logic should be implemented using:

- Actions
- Services
- Domain-specific value objects
- Policies
- Events and listeners
- Jobs for slow work
- Database transactions for critical operations

Avoid putting checkout, stock deduction, refunds, or subscription logic directly inside controllers.

---

## 4. Proposed Module Structure

The preferred modules are:

```text
Modules/
├── Tenancy/
├── Authentication/
├── Users/
├── Authorization/
├── Subscriptions/
├── Businesses/
├── Branches/
├── Registers/
├── Catalog/
├── Inventory/
├── Sales/
├── Payments/
├── Customers/
├── Returns/
├── Expenses/
├── Reports/
├── Notifications/
├── Audit/
├── Settings/
└── PlatformAdmin/
```

Each module may contain:

```text
ModuleName/
├── Config/
├── Console/
├── Database/
│   ├── Factories/
│   ├── Migrations/
│   └── Seeders/
├── Domain/
│   ├── Actions/
│   ├── Data/
│   ├── Enums/
│   ├── Events/
│   ├── Exceptions/
│   ├── Services/
│   └── ValueObjects/
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   ├── Requests/
│   └── Resources/
├── Models/
├── Policies/
├── Providers/
├── Routes/
├── Tests/
│   ├── Feature/
│   └── Unit/
└── module.json
```

Do not over-engineer every module. Small modules may use a simpler structure.

---

# 5. MVP Modules and Requirements

## 5.1 Tenancy

### Responsibilities

- Create tenants
- Resolve the current tenant
- Initialize tenant context
- Scope tenant-owned records
- Separate central routes from tenant routes
- Support subdomains
- Prevent cross-tenant access
- Support controlled platform-admin impersonation
- Make queues, cache, files, and notifications tenant-aware

### Core entities

```text
Tenant
Domain
TenantUser
TenantInvitation
```

### Tenant fields

```text
id
name
slug
status
trial_ends_at
created_at
updated_at
```

### Requirements

- Tenant slugs must be unique.
- Suspended tenants must not access tenant features.
- Tenant users may belong to more than one tenant.
- A user must have a current tenant context.
- Tenant resolution must happen before tenant-owned models are queried.
- Queued jobs must restore the correct tenant context.
- Cache keys must be tenant-aware.
- Uploaded files must be tenant-isolated.
- Scheduled commands must iterate tenants safely when required.

### Acceptance criteria

- A user from Tenant A cannot read, update, delete, or infer Tenant B data.
- Route-model binding cannot resolve a record from another tenant.
- A queued receipt or notification uses the originating tenant.
- Tenant-specific storage paths cannot collide.

---

## 5.2 Authentication and User Management

### Responsibilities

- Registration
- Login
- Logout
- Password reset
- Email verification
- Staff invitations
- User activation and deactivation
- Current tenant selection
- Session management

### Suggested roles

- Owner
- Manager
- Cashier
- Inventory Staff

### Suggested permissions

```text
business.view
business.update

branches.view
branches.create
branches.update
branches.delete

registers.view
registers.manage
registers.open
registers.close
registers.cash-movement

products.view
products.create
products.update
products.delete
products.import
products.export

inventory.view
inventory.adjust
inventory.transfer
inventory.view-cost

sales.view
sales.create
sales.discount
sales.void
sales.refund

customers.view
customers.create
customers.update
customers.delete

expenses.view
expenses.create
expenses.approve

reports.view
reports.view-profit
reports.export

users.view
users.invite
users.update
users.delete
roles.manage

settings.view
settings.update
subscription.manage
```

### Rules

- Owners cannot accidentally remove their own final owner role.
- Every tenant must have at least one active owner.
- Deactivated users cannot create sales.
- Refund and stock-adjustment permissions should not automatically belong to cashiers.
- Authorization must use Laravel policies or gates, not UI visibility alone.

---

## 5.3 SaaS Plans and Subscriptions

### Responsibilities

- Define subscription plans
- Start trials
- Subscribe tenants
- Change plans
- Cancel subscriptions
- Handle payment failures
- Enforce plan limits
- Handle grace periods
- Process billing webhooks

### Plan limits

Possible limits include:

```text
max_branches
max_users
max_registers
max_products
monthly_sales_limit
custom_domain_enabled
advanced_reports_enabled
api_access_enabled
```

### Core entities

```text
Plan
PlanFeature
Subscription
SubscriptionItem
SubscriptionEvent
BillingWebhook
```

### Rules

- Subscription access decisions must be handled centrally.
- Webhooks must be idempotent.
- Webhook signatures must be verified.
- Tenant access should not be revoked instantly for a temporary billing failure.
- Existing data must remain readable after downgrading even when new records cannot be created.
- Usage limits must be enforced server-side.

### MVP billing options

Select one payment provider based on the target market.

Possible choices:

- Stripe
- Paddle
- Lemon Squeezy
- A local payment gateway
- Manual subscription management during private beta

Do not confuse SaaS billing with in-store POS payment recording.

---

## 5.4 Business Settings

### Responsibilities

- Business profile
- Legal name
- Display name
- Logo
- Contact information
- Tax registration number
- Currency
- Time zone
- Locale
- Receipt settings
- Invoice numbering
- Default tax behavior
- Low-stock settings

### Rules

- Store timestamps in UTC.
- Display dates using the tenant's time zone.
- Monetary amounts must never use floating-point columns.
- Use integer minor units or fixed decimal columns consistently.
- Historical receipts must not change when current business settings change.

---

## 5.5 Branches

### Responsibilities

- Create and manage branches
- Assign users to branches
- Assign registers to branches
- Maintain branch-specific inventory
- Maintain branch-specific sales and reports

### Core entity

```text
Branch
```

### Suggested fields

```text
id
tenant_id
name
code
phone
email
address_line_1
address_line_2
city
state
postal_code
country_code
timezone
is_active
created_at
updated_at
```

### Rules

- Branch codes must be unique per tenant.
- Inactive branches cannot open new register sessions.
- Historical sales remain accessible after a branch is deactivated.

---

## 5.6 Registers and Cash Sessions

### Responsibilities

- Create registers
- Open register sessions
- Store opening float
- Record cash in/out
- Calculate expected cash
- Close register sessions
- Record discrepancy
- Produce closing reports

### Core entities

```text
Register
RegisterSession
CashMovement
```

### Register session fields

```text
id
tenant_id
branch_id
register_id
opened_by
closed_by
opening_balance
expected_closing_balance
actual_closing_balance
difference
opened_at
closed_at
status
notes
```

### Cash movement types

```text
cash_in
cash_out
expense
cash_refund
correction
```

### Rules

- A register may have only one open session.
- Cash sales require an open register session.
- Register closing must be atomic.
- Closed sessions cannot receive new cash sales.
- A discrepancy should be stored, not silently corrected.
- Reopening a closed session should require an explicit privileged workflow.

---

## 5.7 Product Catalog

### Responsibilities

- Products
- Categories
- SKUs
- Barcodes
- Pricing
- Cost
- Tax assignment
- Product status
- Stock tracking flag
- Product images
- Optional variants

### Core entities

```text
Category
Product
ProductVariant
ProductBarcode
ProductPrice
TaxRate
```

### Product fields

```text
id
tenant_id
category_id
name
sku
barcode
description
cost_price
selling_price
tax_rate_id
track_inventory
allow_negative_stock
low_stock_threshold
status
created_at
updated_at
```

### MVP decision on variants

Variants may be postponed if the first target market does not require them.

If variants are supported, inventory must belong to the variant rather than only the parent product.

### Rules

- SKU must be unique per tenant.
- Barcode must be unique per tenant when present.
- Deleted products referenced by historical sales should be soft-deleted.
- Historical sale items must preserve product name, SKU, price, cost, discount, and tax snapshots.
- Product price changes must not alter previous sales.

---

## 5.8 Inventory

### Responsibilities

- Stock per branch
- Opening stock
- Stock adjustments
- Sale deductions
- Return additions
- Low-stock alerts
- Movement ledger
- Optional transfers after MVP

### Core entities

```text
InventoryBalance
InventoryMovement
StockAdjustment
StockAdjustmentItem
```

### Inventory movement fields

```text
id
tenant_id
branch_id
product_id
product_variant_id
type
quantity
unit_cost
reference_type
reference_id
performed_by
occurred_at
notes
```

### Movement types

```text
opening_stock
purchase
sale
sale_void
customer_return
supplier_return
adjustment_in
adjustment_out
transfer_in
transfer_out
```

### Critical design rule

Inventory history must use an immutable movement ledger.

Do not rely only on updating a `stock_quantity` column.

An optional balance table may be maintained for fast reads, but every change must have a matching inventory movement.

### Concurrency rules

- Checkout must lock relevant inventory rows.
- Stock must be validated inside the database transaction.
- Simultaneous sales must not oversell unless negative stock is explicitly enabled.
- Inventory updates and sale completion must succeed or fail together.

---

## 5.9 POS Checkout and Sales

### Responsibilities

- Product search
- Barcode scanning
- Cart
- Quantity changes
- Line discounts
- Order discounts
- Taxes
- Customer selection
- Hold and resume cart
- Multi-payment checkout
- Receipt generation
- Sale history
- Sale cancellation or voiding

### Core entities

```text
Sale
SaleItem
HeldCart
HeldCartItem
```

### Sale fields

```text
id
tenant_id
branch_id
register_id
register_session_id
customer_id
cashier_id
sale_number
status
subtotal
discount_total
tax_total
total
paid_amount
change_amount
completed_at
voided_at
voided_by
void_reason
created_at
updated_at
```

### Sale item snapshot fields

```text
id
sale_id
product_id
product_variant_id
product_name
sku
barcode
quantity
unit_price
unit_cost
discount_total
tax_rate
tax_total
line_total
```

### Sale statuses

```text
draft
held
completed
partially_refunded
refunded
voided
```

### Checkout transaction

The following should happen in one database transaction:

1. Validate the tenant, user, branch, register, and register session.
2. Validate product availability and status.
3. Lock inventory balance rows.
4. Recalculate all totals server-side.
5. Create the sale.
6. Create sale items with immutable snapshots.
7. Create payment records.
8. Create inventory movements.
9. Update inventory balances.
10. Update register expected cash when applicable.
11. Mark the sale as completed.
12. Dispatch receipt and notification jobs after commit.

### Rules

- Never trust totals sent by the frontend.
- Never trust tax, discount, or price calculations sent by the frontend.
- All money calculations must happen server-side.
- Completed sales cannot be edited directly.
- Corrections must use void or refund workflows.
- A retry must not create a duplicate sale.
- Use an idempotency key for checkout requests.
- Receipt generation should not block checkout completion.

---

## 5.10 Payments

### Responsibilities

Record how a customer paid for an in-store sale.

### Payment methods

```text
cash
card
bank_transfer
mobile_wallet
gift_card
store_credit
other
```

### Core entities

```text
PaymentMethod
Payment
RefundPayment
```

### Payment fields

```text
id
tenant_id
sale_id
payment_method_id
amount
reference
received_by
paid_at
metadata
```

### Rules

- A sale may have multiple payment records.
- Cash payments affect the register's expected cash.
- External card-terminal payments may be recorded without direct terminal integration.
- Payment amounts must equal or exceed the amount due according to the configured policy.
- Change is normally allowed only for cash.
- Refund payment records must reference the return or refund.

---

## 5.11 Customers

### Responsibilities

- Customer records
- Contact information
- Purchase history
- Notes
- Optional customer selection during checkout

### Core entity

```text
Customer
```

### Suggested fields

```text
id
tenant_id
name
phone
email
tax_number
address
notes
is_active
created_at
updated_at
```

### Rules

- Walk-in sales must not require a customer.
- Customer uniqueness rules must be tenant-specific.
- Customer deletion should not delete historical sales.
- Customer data export and anonymization should be possible later.

---

## 5.12 Returns and Refunds

### Responsibilities

- Full returns
- Partial returns
- Return reasons
- Refund methods
- Inventory restoration
- Refund receipt
- Manager authorization

### Core entities

```text
SaleReturn
SaleReturnItem
RefundPayment
ReturnReason
```

### Rules

- Never modify or delete the original completed sale.
- Returned quantities cannot exceed the refundable quantity.
- Repeated return requests must be idempotent.
- Inventory should be restored only when the returned item is marked restockable.
- Refunds must update the sale's refund status.
- Cash refunds must update the current register session appropriately.
- Returns should be processed in a database transaction.

---

## 5.13 Expenses

### Responsibilities

- Expense categories
- Record business expenses
- Associate expenses with a branch
- Associate cash expenses with a register session
- Attach notes
- Optional receipt attachment

### Core entities

```text
ExpenseCategory
Expense
```

### Example categories

```text
delivery
cleaning
maintenance
utilities
office_supplies
petty_cash
other
```

### Rules

- Cash expenses must affect expected register cash.
- Expenses should be immutable after approval or register closure.
- Corrections should be auditable.

---

## 5.14 Reports

### MVP reports

- Sales summary by date range
- Daily sales
- Sales by branch
- Sales by cashier
- Sales by product
- Sales by category
- Payment-method summary
- Tax summary
- Discount summary
- Return summary
- Expense summary
- Current inventory
- Low-stock products
- Inventory movement history
- Register-session closing report
- Gross profit estimate

### Reporting rules

- Reports must always be tenant-scoped.
- Reports should support branch and date filters.
- Reports should use the tenant's time zone.
- Gross profit should use `sale_items.unit_cost`.
- Reports should exclude or separately display voided sales.
- Export can initially support CSV.
- Large reports should be generated using queued jobs.

---

## 5.15 Notifications

### MVP notifications

- Staff invitation
- Low stock
- Trial ending
- Subscription payment failed
- Subscription canceled
- Register discrepancy
- Large refund
- Failed background job for important operations

### Channels

- In-app database notifications
- Email
- Optional SMS or WhatsApp later

### Rules

- Notifications must be tenant-aware.
- Low-stock notifications should be deduplicated or throttled.
- Email sending should use queues.
- Notification failure must not roll back a completed sale.

---

## 5.16 Audit Logs

### Audit events

- User invited
- User activated or deactivated
- Role changed
- Permission changed
- Product price changed
- Product cost changed
- Inventory adjusted
- Sale voided
- Refund created
- Register opened
- Register closed
- Register discrepancy recorded
- Business settings changed
- Subscription changed
- Platform admin impersonation started or ended

### Suggested fields

```text
id
tenant_id
actor_type
actor_id
event
auditable_type
auditable_id
old_values
new_values
ip_address
user_agent
created_at
```

### Rules

- Audit logs should be append-only.
- Sensitive values must be redacted.
- Audit records should not store passwords, tokens, full card data, or secrets.
- Tenant users must not view platform-level logs.
- Platform-admin impersonation must always be logged.

---

## 5.17 Platform Administration

### Responsibilities

- View tenants
- View tenant status
- Activate or suspend tenants
- Manage plans
- View subscriptions
- View trials
- View usage
- View failed billing webhooks
- View platform jobs and failures
- Impersonate tenant support sessions
- End impersonation
- View platform metrics

### Rules

- Platform admins must be separated from tenant roles.
- Platform routes must use a separate middleware stack.
- Support impersonation must be time-limited and audited.
- Impersonation should display a persistent warning banner.
- Platform admin access should support two-factor authentication.

---

# 6. Suggested Laravel Packages

> Package versions must be selected based on compatibility with **PHP 8.1 and Laravel 10**, not based on the latest available version.

## 6.1 Multi-tenancy

### Primary candidate: `stancl/tenancy`

Package:

```bash
composer require stancl/tenancy
```

Known as **Tenancy for Laravel**.

Useful features:

- Tenant identification
- Domain and subdomain tenancy
- Single-database and multi-database support
- Tenant-aware bootstrapping
- Tenant-aware cache, storage, queues, and database connections
- Central and tenant route separation
- Event-based architecture

Recommendation:

- Use the package for tenant resolution and lifecycle.
- For shared-database tenancy, still implement explicit tenant ownership and strong query scoping.
- Do not assume installing the package automatically prevents every cross-tenant query.
- Add automated tenant-isolation tests.

Alternative:

### `spatie/laravel-multitenancy`

Consider it when you prefer a more explicit task-based approach and fewer automatic behaviors.

Decision required:

- Compare compatible versions.
- Create a small proof of concept.
- Test queues, cache, storage, route-model binding, and scheduled jobs.
- Select one tenancy package only.

---

## 6.2 Modular architecture

### Candidate: `nwidart/laravel-modules`

Package:

```bash
composer require nwidart/laravel-modules
```

Useful features:

- Module generation
- Module service providers
- Module routes
- Module migrations
- Module configuration
- Module tests
- Independent feature organization

Recommendation:

Use it as the required project-structure package. The team and AI agent must consistently maintain module boundaries.

Do not use modules as an excuse to:

- Duplicate shared logic
- Create circular dependencies
- Hide database coupling
- Put every model in a separate module
- Create excessive abstractions

Rejected fallback (not used):

```text
app/
├── Domain/
│   ├── Sales/
│   ├── Inventory/
│   ├── Catalog/
│   └── ...
```

The package-free domain-folder alternative is not approved for this project.

Decision rule:

Use `nwidart/laravel-modules` as the required structure package after confirming a Laravel-10/PHP-8.1-compatible release through Composer. If no compatible release exists, stop and request a version decision; do not silently fall back to package-free domain folders.

---

## 6.3 Roles and permissions

### Recommended: `spatie/laravel-permission`

```bash
composer require spatie/laravel-permission
```

Use it for:

- Roles
- Permissions
- Laravel Gate integration
- Policy authorization
- Permission caching

Important tenancy decision:

- Roles and permissions should normally belong to a tenant.
- The same user may have different roles in different tenants.
- Ensure the selected package version and schema support team/tenant scoping.
- Test permission cache isolation between tenants.

---

## 6.4 Activity and audit logging

### Candidate: `spatie/laravel-activitylog`

```bash
composer require spatie/laravel-activitylog
```

Use it for:

- Model activity
- Actor tracking
- Old and new values
- Custom event descriptions

Do not log secrets or sensitive payment data.

For critical audit requirements, wrap the package behind an application-level audit service so it can later be replaced.

---

## 6.5 SaaS billing

### Stripe option: `laravel/cashier`

```bash
composer require laravel/cashier
```

Use it when Stripe is the SaaS subscription provider.

Features:

- Subscriptions
- Trials
- Plan changes
- Invoices
- Payment methods
- Grace periods

Important:

- Cashier manages SaaS billing, not POS checkout payments.
- Verify the Cashier version compatible with Laravel 10 and PHP 8.1.
- Billing webhooks must be verified and idempotent.

For Paddle, use the appropriate Laravel Cashier Paddle package version.

For a local payment gateway, implement a provider adapter:

```php
interface SubscriptionBillingGateway
{
    public function createCustomer(/* ... */);
    public function subscribe(/* ... */);
    public function cancel(/* ... */);
    public function resume(/* ... */);
    public function handleWebhook(/* ... */);
}
```

---

## 6.6 Authentication

Choose based on the frontend.

### Blade or Livewire

- Laravel Breeze
- Laravel Fortify when custom authentication screens are needed

### Inertia SPA

- Laravel Breeze with Inertia
- Laravel Sanctum for authenticated API requests

### API-first

- Laravel Sanctum
- Fortify for authentication actions

Avoid installing Passport unless OAuth2 authorization-server functionality is genuinely required.

---

## 6.7 Frontend

### Option A: Livewire

Potential packages:

```text
livewire/livewire
alpinejs
```

Advantages:

- Laravel-centered development
- Fast dashboard and CRUD development
- Less frontend infrastructure

Consider package versions carefully because recent Livewire versions may have higher PHP or Laravel requirements.

### Option B: Inertia.js with Vue

Potential packages:

```text
inertiajs/inertia-laravel
@inertiajs/vue3
vue
```

Advantages:

- Better fit for a highly interactive POS screen
- Clear frontend component architecture
- Easier advanced local cart state

Recommended frontend choice for this project:

- Inertia.js + Vue for the POS and dashboard, or
- Blade/Livewire for admin screens with a dedicated Vue POS interface

Avoid unnecessarily maintaining two frontend stacks unless the benefit is clear.

---

## 6.8 Data transfer objects

### Candidate: `spatie/laravel-data`

```bash
composer require spatie/laravel-data
```

Use it for:

- Request-to-action data transfer
- Typed application data
- API response data
- Reducing array-based domain logic

Verify that the selected release supports PHP 8.1.

Alternative:

Create simple native PHP DTO classes manually.

---

## 6.9 Media and uploads

### Candidate: `spatie/laravel-medialibrary`

```bash
composer require spatie/laravel-medialibrary
```

Use it for:

- Product images
- Business logos
- Expense receipt attachments

Important:

- Storage paths must be tenant-aware.
- Validate file size and MIME type.
- Private documents should not be publicly accessible.
- Verify PHP 8.1 compatibility.

A simpler MVP can use Laravel's native filesystem without a media package.

---

## 6.10 Excel and CSV

### Candidate: `maatwebsite/excel`

```bash
composer require maatwebsite/excel
```

Use it for:

- Product import
- Customer import
- Report export
- Inventory export

For a smaller MVP:

- Use native streamed CSV exports.
- Add Excel support later.

Imports must:

- Validate every row
- Be tenant-scoped
- Report row-level errors
- Use queues for large files
- Avoid duplicate SKUs and barcodes

---

## 6.11 PDF and receipt generation

Possible candidates:

```text
barryvdh/laravel-dompdf
spatie/browsershot
```

Recommendation:

- Use HTML/CSS browser printing for thermal receipts when possible.
- Generate PDF invoices asynchronously when necessary.
- Do not make PDF generation part of the core checkout transaction.
- Verify package compatibility with PHP 8.1.

---

## 6.12 Barcode generation

Possible candidates:

```text
picqer/php-barcode-generator
milon/barcode
```

Use barcode generation only when the application needs to print labels.

Barcode scanning generally works as keyboard input and does not require a Laravel package.

---

## 6.13 Backup

### Candidate: `spatie/laravel-backup`

```bash
composer require spatie/laravel-backup
```

Use it for application-level backup workflows.

Production backup policy should also include infrastructure-level database backups.

Test restoration, not only backup creation.

---

## 6.14 Health monitoring

Possible candidates:

```text
spatie/laravel-health
spatie/laravel-schedule-monitor
```

Useful checks:

- Database connectivity
- Cache connectivity
- Queue health
- Disk space
- Failed jobs
- Scheduled task health
- Billing webhook failures

Verify compatible versions.

---

## 6.15 Error monitoring

Possible services:

- Sentry
- Bugsnag
- Flare

Requirements:

- Include tenant ID in error context.
- Do not include sensitive customer or payment data.
- Include correlation IDs.
- Separate production from staging.

---

## 6.16 Development and code quality

Suggested tools, using Laravel-10/PHP-8.1-compatible versions:

```text
laravel/pint
larastan/larastan
pestphp/pest
pestphp/pest-plugin-laravel
nunomaduro/collision
barryvdh/laravel-debugbar
```

Rules:

- Debugbar must never be enabled in production.
- Static analysis should start at a practical level and become stricter gradually.
- CI must run tests, formatting checks, static analysis, and `composer audit`.

---

# 7. Package Selection Matrix

| Concern | Primary Candidate | MVP Decision |
|---|---|---|
| Tenancy | `stancl/tenancy` | Evaluate and likely use |
| Modules | `nwidart/laravel-modules` | Required; pin Laravel-10/PHP-8.1-compatible release |
| Permissions | `spatie/laravel-permission` | Use |
| Audit logging | `spatie/laravel-activitylog` | Use or wrap |
| Stripe subscriptions | `laravel/cashier` | Use only for Stripe |
| API authentication | `laravel/sanctum` | Use when needed |
| Authentication UI | `laravel/breeze` | Use |
| DTOs | `spatie/laravel-data` | Optional |
| Media | `spatie/laravel-medialibrary` | Optional |
| CSV/Excel | `maatwebsite/excel` | Post-MVP or optional |
| PDF | DOMPDF or Browsershot | Optional |
| Barcode | Picqer barcode generator | Optional |
| Backup | `spatie/laravel-backup` | Recommended |
| Health checks | `spatie/laravel-health` | Recommended |
| Static analysis | Larastan | Recommended |
| Testing | PHPUnit or Pest | Required |

---

# 8. Proposed Database Tables

## Central tables

```text
users
tenants
domains
tenant_user
tenant_invitations
plans
plan_features
subscriptions
subscription_items
subscription_events
billing_webhooks
platform_admins
personal_access_tokens
password_reset_tokens
jobs
job_batches
failed_jobs
notifications
```

## Tenant-owned tables

```text
branches
branch_user
registers
register_sessions
cash_movements

categories
products
product_variants
product_barcodes
tax_rates

inventory_balances
inventory_movements
stock_adjustments
stock_adjustment_items

customers

sales
sale_items
payments
held_carts
held_cart_items

sale_returns
sale_return_items
refund_payments
return_reasons

expense_categories
expenses

business_settings
receipt_settings
number_sequences

audit_logs
```

## Tenant database conventions

All tenant-owned tables should generally include:

```text
id
tenant_id
created_at
updated_at
```

Where appropriate:

```text
created_by
updated_by
deleted_at
```

Use composite unique indexes such as:

```text
unique(tenant_id, sku)
unique(tenant_id, barcode)
unique(tenant_id, branch_code)
unique(tenant_id, sale_number)
```

Create indexes for common tenant queries:

```text
index(tenant_id, created_at)
index(tenant_id, branch_id)
index(tenant_id, status)
index(tenant_id, product_id)
```

---

# 9. Money, Tax, and Precision

### Money representation

Choose one policy and use it everywhere.

Preferred:

- Store money as integer minor units, such as cents.

Alternative:

- Use `DECIMAL(19, 4)` consistently.

Do not use:

- `FLOAT`
- `DOUBLE`
- JavaScript floating-point totals as the source of truth

### Tax rules

The system must decide:

- Tax-inclusive or tax-exclusive pricing
- Line-level or invoice-level rounding
- Rounding precision
- Multiple tax rates
- Zero-rated products
- Tax-exempt customers, if required

For MVP, select one tax model based on the first target country.

Historical sale items must snapshot:

```text
tax_rate
taxable_amount
tax_total
price_includes_tax
```

---

# 10. Number Sequences

Use tenant-aware number sequences for:

- Sales
- Returns
- Register sessions
- Expenses
- Stock adjustments
- Invoices

Example:

```text
SALE-2026-000001
RET-2026-000001
EXP-2026-000001
```

Rules:

- Numbers must be unique per tenant.
- Sequence generation must be concurrency-safe.
- Gaps may be acceptable unless local law requires strict sequences.
- Do not generate critical document numbers using `MAX(number) + 1`.

---

# 11. Security Requirements

- Validate all input using Form Requests.
- Authorize all tenant actions using policies.
- Never accept tenant identity from untrusted form input.
- Use CSRF protection.
- Rate-limit authentication and sensitive endpoints.
- Use secure cookies.
- Enable email verification.
- Support two-factor authentication for owners and platform admins.
- Hash passwords using Laravel defaults.
- Encrypt secrets and sensitive configuration.
- Never store raw card details.
- Verify billing webhook signatures.
- Use idempotency keys for checkout and webhooks.
- Log sensitive administrative actions.
- Apply tenant scoping to route-model binding.
- Test for IDOR vulnerabilities.
- Disable debug mode in production.
- Use HTTPS only in production.
- Run dependency audits.
- Keep backups encrypted.
- Restrict file uploads.
- Use least-privilege database credentials.

---

# 12. Queue Jobs

Potential jobs:

```text
SendStaffInvitation
SendReceiptEmail
GenerateInvoicePdf
GenerateReportExport
ImportProducts
SendLowStockNotification
ProcessBillingWebhook
RecalculateInventoryBalance
CleanupExpiredHeldCarts
SendTrialEndingReminder
```

Rules:

- Jobs must contain or restore tenant context.
- Jobs should be idempotent.
- Jobs must define retry and timeout policies.
- Business-critical jobs should use unique job constraints where appropriate.
- Dispatch after database commit when the job depends on newly committed data.
- Notifications must not block checkout.

---

# 13. Events and Listeners

Suggested events:

```text
TenantCreated
TenantSuspended
UserInvited
RegisterOpened
RegisterClosed
SaleCompleted
SaleVoided
SaleRefunded
InventoryAdjusted
InventoryLevelChanged
LowStockDetected
SubscriptionStarted
SubscriptionChanged
SubscriptionCanceled
BillingPaymentFailed
```

Example listeners:

```text
CreateDefaultBusinessSettings
CreateDefaultRolesAndPermissions
CreateDefaultBranch
CreateInventoryMovements
UpdateInventoryBalance
UpdateRegisterExpectedCash
WriteAuditLog
SendReceipt
CheckLowStock
```

Do not use events to hide essential transactional logic that must succeed synchronously.

For example, sale creation and inventory deduction must happen together in the checkout transaction. An event may notify other modules after successful completion.

---

# 14. Testing Strategy

## Required test groups

### Tenant isolation tests

- Tenant A cannot access Tenant B products.
- Tenant A cannot access Tenant B sales by guessing an ID.
- Route-model binding is tenant-scoped.
- Search endpoints do not leak cross-tenant results.
- Exports contain only tenant data.
- Queued jobs execute in the correct tenant.
- Permission cache is tenant-isolated.

### Checkout tests

- Complete cash sale
- Complete card sale
- Split payment
- Correct totals
- Correct taxes
- Correct discounts
- Correct change
- Inventory deduction
- Duplicate request prevention
- Insufficient stock
- Concurrent checkout
- Closed register rejection

### Return tests

- Full return
- Partial return
- Repeated return prevention
- Refund payment
- Restockable return
- Non-restockable return
- Permission enforcement

### Register tests

- Open register
- Prevent second open session
- Cash movement
- Expected cash calculation
- Close register
- Record discrepancy

### Subscription tests

- Trial access
- Expired trial
- Plan limit enforcement
- Upgrade
- Downgrade
- Grace period
- Failed webhook retry
- Duplicate webhook

### Authorization tests

- Cashier restrictions
- Manager permissions
- Owner protections
- Platform-admin separation

---

# 15. API and UI Boundaries

The frontend may submit:

- Product or variant IDs
- Quantities
- Requested discounts
- Customer ID
- Payment method selections
- Amount tendered
- Idempotency key

The backend must determine:

- Current tenant
- Current branch
- Current register session
- Product availability
- Product price
- Discount validity
- Tax
- Totals
- Inventory impact
- Permission to complete the action

The frontend is not a trusted accounting source.

---

# 16. MVP Scope

## Must include

- Multi-tenancy
- Authentication
- Staff invitations
- Roles and permissions
- Business settings
- Branches
- Registers
- Register sessions
- Products
- Categories
- Inventory ledger
- POS checkout
- Cash and card payment recording
- Customers
- Returns and refunds
- Expenses
- Core reports
- SaaS plans and subscriptions
- Notifications
- Audit logs
- Platform administration
- Automated tests for critical workflows

## Add after MVP

- Suppliers
- Purchase orders
- Goods receiving
- Stock transfers
- Product variants, if postponed
- Quotations
- Customer credit
- Gift cards
- Loyalty points
- Offline mode
- Accounting integrations
- E-commerce integrations
- Advanced analytics
- Mobile application
- Public API
- Custom domains
- Hardware integrations
- Multi-currency
- Multiple tax jurisdictions

## Explicitly avoid in MVP

- Full accounting
- Payroll
- Manufacturing
- Advanced CRM
- Microservices
- Event sourcing
- AI sales forecasting
- Complex workflow engine
- Marketplace integrations
- Native mobile apps
- Database-per-tenant unless mandatory
- Deep hardware integration
- Unlimited receipt-template customization

---

# 17. Recommended Delivery Phases

## Phase 1 — Foundation

- Laravel project setup
- CI pipeline
- Authentication
- Tenancy proof of concept
- Tenant isolation tests
- Modules or domain-folder structure
- Permissions
- Business onboarding
- Platform-admin separation

## Phase 2 — Catalog and Inventory

- Branches
- Products
- Categories
- Taxes
- Inventory balances
- Inventory movements
- Opening stock
- Stock adjustments
- Low-stock detection

## Phase 3 — POS

- Registers
- Register sessions
- POS interface
- Cart
- Checkout service
- Payments
- Receipt
- Held carts
- Sale history

## Phase 4 — Returns and Operations

- Returns
- Refunds
- Expenses
- Register closing
- Audit logs
- Notifications

## Phase 5 — Reports and Billing

- Core reports
- CSV export
- Plans
- Trials
- Billing integration
- Webhooks
- Limits
- Suspension and grace periods

## Phase 6 — Hardening

- Concurrency tests
- Tenant penetration tests
- Performance tests
- Backup restoration test
- Monitoring
- Rate limits
- Security review
- Production deployment checklist

---

# 18. Definition of Done

A feature is complete only when:

- Requirements are implemented.
- Input validation exists.
- Authorization exists.
- Tenant isolation is enforced.
- Database indexes are added.
- Transactions are used where required.
- Events and jobs are tenant-aware.
- Audit logging is considered.
- Feature tests exist.
- Error cases are covered.
- API resources or UI responses are consistent.
- No sensitive data is logged.
- The feature passes static analysis and formatting.
- Documentation is updated.

---

# 19. AI Agent Implementation Rules

The AI coding agent must follow these rules:

1. Do not generate all modules at once.
2. Work phase by phase.
3. Present the proposed schema before writing migrations.
4. Present important architectural decisions before implementation.
5. State assumptions explicitly.
6. Do not add packages without explaining their purpose.
7. Verify every package against PHP 8.1 and Laravel 10.
8. Never trust frontend totals.
9. Never bypass policies.
10. Never query tenant-owned models without tenant context.
11. Use database transactions for checkout, returns, register closing, and stock adjustments.
12. Use row locks for inventory-sensitive operations.
13. Add tests with each feature.
14. Do not silently change the architecture.
15. Do not use floating-point money.
16. Do not edit completed sales.
17. Do not delete financial history.
18. Do not dispatch tenant jobs without tenant context.
19. Do not expose stack traces in production.
20. Keep controllers thin.
21. Prefer framework features before adding a package.
22. Do not install latest versions blindly.
23. Keep a changelog of major decisions.
24. Stop and flag any requirement that can cause cross-tenant leakage or financial inconsistency.

---

# 20. Open Decisions for Negotiation

The AI agent should discuss and resolve these before implementation:

1. What retail niche is the first target?
2. Which country and tax model is required?
3. MySQL or PostgreSQL?
4. Blade/Livewire or Inertia/Vue?
5. Shared database or database-per-tenant?
6. `stancl/tenancy` or `spatie/laravel-multitenancy`?
7. Which Laravel-10/PHP-8.1-compatible `nwidart/laravel-modules` release should be pinned?
8. Which SaaS billing provider?
9. Are product variants required in MVP?
10. Is negative stock allowed?
11. Are purchases and suppliers required in MVP?
12. Are returns required to reference the original sale?
13. Are invoices legally different from receipts?
14. Is offline selling required?
15. Is multi-currency required?
16. Is Arabic or RTL support required?
17. Are custom tenant domains required?
18. Is a public API required?
19. Which reports are mandatory for launch?
20. What are the initial plan limits?

---

# 21. Recommended Initial Decisions

Unless project requirements say otherwise:

```text
Architecture: Modular monolith
Database: MySQL 8
Tenancy: Shared database with tenant_id
Tenant resolution: Subdomain plus authenticated current tenant
Tenancy package: stancl/tenancy, after compatibility proof
Modules package: Required; use `nwidart/laravel-modules` after compatibility proof and pinning
Authorization: spatie/laravel-permission
Frontend: Inertia.js + Vue
Authentication: Breeze + Sanctum
Queues: Database initially, Redis in production
Cache: Redis in production
Storage: S3-compatible object storage
Money: Integer minor units
Taxes: One configurable tax model for the first country
Products: Simple products first
Billing: Manual beta billing or one supported provider
Testing: Feature-heavy PHPUnit or Pest suite
Deployment: Single application with queue workers and scheduler
```

---

# 22. First End-to-End Success Flow

The first complete vertical slice should be:

```text
Create tenant
→ create owner
→ create business settings
→ create branch
→ create register
→ create product
→ add opening inventory
→ open register
→ create cash sale
→ deduct inventory
→ record payment
→ generate receipt
→ close register
→ view daily report
```

Do not build many disconnected CRUD screens before this flow works end to end.

---

# 23. Suggested First Prompt for the AI Agent

```text
Review this SaaS POS MVP specification as a senior Laravel architect.

Constraints:
- PHP 8.1
- Laravel 10
- Modular monolith
- Multi-tenant SaaS
- Strong tenant isolation
- Financial and inventory consistency are higher priority than development speed

Before generating code:

1. Identify contradictions, missing decisions, and risky assumptions.
2. Recommend whether to use stancl/tenancy and nwidart/laravel-modules under these version constraints.
3. Propose a package version compatibility plan.
4. Propose the tenant data model.
5. Propose module boundaries and dependency rules.
6. Propose the first vertical slice.
7. List the tests required to prove tenant isolation.
8. Do not generate migrations or implementation code until the architecture is agreed.
```

---

# 24. Final MVP Principle

The product is ready for MVP launch when a tenant can reliably perform this business cycle:

```text
Subscribe
→ configure business
→ add staff
→ add products and stock
→ open register
→ sell
→ receive payment
→ print receipt
→ return or refund when required
→ close register
→ review accurate reports
```

Correct tenant isolation, inventory consistency, payment records, and historical accuracy are more important than the number of features.
