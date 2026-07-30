# Phase 03 — Catalog and Taxes

Implement the Catalog and Taxes modules with simple products, categories, SKUs/barcodes, VAT-inclusive prices, soft deletion, and immutable sales snapshots.

## Dependencies

Phase 02.

## Exit criteria

An authorized user can manage Arabic products and calculate VAT consistently without changing historical sales.

## Implemented pricing contract

TASK-003 provides a Catalog-only, in-memory contract for later checkout workflows. `PricingCalculationInput` carries the selected product, positive quantity, displayed unit gross amount, and calculation date. `VatInclusivePricingService` resolves fresh tenant-scoped product and tax records, rejects stale or unavailable sources, and calculates line VAT using integer-only half-up rounding. `PricedProductLineSnapshot` preserves product and tax display values, totals, basis points, and a fixed `taxIncluded` flag without introducing an early sale schema, route, UI, migration, or Finance-module dependency.

## Tasks

- TASK-001-categories-and-tax-rates.md
- TASK-002-simple-products.md
- TASK-003-pricing-and-vat-calculation.md
- TASK-004-catalog-import-export-boundaries.md
