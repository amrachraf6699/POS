# TASK-003 — Pricing and VAT Calculation

- **Objective:** Implement deterministic VAT-inclusive price and total calculations.
- **Scope:** Money value rules, rounding, tax extraction, calculation service, snapshots.
- **Non-scope:** Discounts beyond MVP policy definition.
- **Dependencies:** TASK-001, TASK-002.
- **Files/subsystems:** Catalog pricing-domain contracts and calculation service only; no Finance dependency in this phase.
- **Database/API/UI impact:** Calculation contract; no public API requirement.
- **Steps:** Define minor-unit formulas; calculate line-level tax; preserve rate and inclusion flags.
- **Validation/authorization:** Reject invalid negative values and stale prices.
- **Tenant isolation:** Rates must come from current tenant only.
- **Tests:** Zero/full/decimal VAT, rounding, quantities, historical snapshots.
- **Definition of done:** Backend is the sole source of financial totals.
- **Handoff:** Checkout calculation contract.

## Implemented contract

The Catalog module now exposes an immutable calculation input and line snapshot. It calculates VAT once per VAT-inclusive line with integer-only half-up rounding, snapshots product/tax display data in memory, and rejects missing tenant context, cross-tenant sources, stale displayed prices, unavailable products, invalid quantities, ineffective tax rates, and integer overflow. Sale-item persistence remains deferred to Phase 05.
