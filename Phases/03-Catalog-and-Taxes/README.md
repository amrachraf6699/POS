# Phase 03 — Catalog and Taxes

Implement the Catalog and Taxes modules with simple products, categories, SKUs/barcodes, VAT-inclusive prices, soft deletion, and immutable sales snapshots.

## Dependencies

Phase 02.

## Exit criteria

An authorized user can manage Arabic products and calculate VAT consistently without changing historical sales.

## Implemented pricing contract

TASK-003 provides a Catalog-only, in-memory contract for later checkout workflows. `PricingCalculationInput` carries the selected product, positive quantity, displayed unit gross amount, and calculation date. `VatInclusivePricingService` resolves fresh tenant-scoped product and tax records, rejects stale or unavailable sources, and calculates line VAT using integer-only half-up rounding. `PricedProductLineSnapshot` preserves product and tax display values, totals, basis points, and a fixed `taxIncluded` flag without introducing an early sale schema, route, UI, migration, or Finance-module dependency.

## Implemented catalog CSV transfer

TASK-004 adds an Arabic RTL, permission-gated product CSV workflow. It imports a documented UTF-8 create-only format using tenant-local category/tax-rate names, reports invalid rows without discarding independent valid rows, rejects identifiers held by soft-deleted products, and rolls back only a failed write batch. Exports are tenant-scoped streamed CSV with a UTF-8 BOM, active/inactive status, and formula-safe text. The synchronous MVP is intentionally limited to 500 rows and 1 MiB; a queued extension remains a later handoff.

## Tasks

- TASK-001-categories-and-tax-rates.md
- TASK-002-simple-products.md
- TASK-003-pricing-and-vat-calculation.md
- TASK-004-catalog-import-export-boundaries.md
