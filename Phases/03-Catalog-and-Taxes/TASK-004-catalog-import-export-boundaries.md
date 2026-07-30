# TASK-004 — Catalog Import and Export Boundaries

- **Objective:** Provide safe, tenant-scoped CSV transfer for simple products.
- **Scope:** Native CSV import/export actions, Arabic RTL admin workflow, row-level import feedback, and streamed exports.
- **Non-scope:** Excel support, queued imports, migrations, and provider integrations.
- **Dependencies:** TASK-002.
- **Files/subsystems:** Catalog actions, DTO-like result contracts, request, routes, views, and feature tests.
- **Database/API/UI impact:** Adds the documented CSV format and three authorized product routes.

## CSV contract

Files must be UTF-8 CSV and their first row must use this exact order:

```text
name,category_name,tax_rate_name,sku,barcode,description,cost_price_minor,selling_price_minor,track_inventory,low_stock_threshold,allow_negative_stock,status
```

`category_name` and `tax_rate_name` are tenant-local names. The tax rate must be active and effective on the import date. Prices and thresholds are non-negative integer minor units; inventory flags are `0` or `1`; status is `active` or `inactive`. SKU and barcode may be empty, but non-empty values must be unique both inside the file and across all current-tenant products, including soft-deleted products.

Imports are create-only and partial: valid rows are saved while every invalid row is returned with its CSV row number, field, and Arabic error. Existing products are never updated. The synchronous MVP accepts at most 500 rows and 1 MiB per file, writes valid rows in transactions of 100, and rolls back a failed batch while continuing a later batch.

Exports require `products.export`, include all non-deleted active and inactive products from the current tenant, stream in bounded chunks with a UTF-8 BOM, and prefix formula-like text with a tab for spreadsheet safety. The importer trims that tab, preserving normal round trips.

## Validation/authorization and tenant isolation

The import page and POST route require `products.import`; export requires `products.export`. Product-list buttons follow those permissions, but controller/action checks remain authoritative. Tenant context scopes category, tax rate, product-identifier, import, and export queries; CSV input never carries a tenant ID.

## Handoff

For imports larger than 500 rows or 1 MiB, add a queued, tenant-context-carrying import job with durable progress and error reporting. That extension must preserve this CSV contract, idempotency behavior, and bounded transactional writes.
