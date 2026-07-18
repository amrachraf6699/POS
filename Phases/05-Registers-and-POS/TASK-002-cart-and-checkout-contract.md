# TASK-002 — Cart and Checkout Contract

- **Objective:** Define the POS cart payload and backend checkout boundary.
- **Scope:** Product IDs, quantities, discounts, customer, payments, idempotency key.
- **Non-scope:** Final persistence and receipt generation.
- **Dependencies:** Phase 03, TASK-001.
- **Files/subsystems:** POS requests/data objects, Blade/JS cart.
- **Database/API/UI impact:** Checkout request contract and POS UI.
- **Steps:** Reject client totals as authoritative; resolve current price/tax/stock; define error format.
- **Validation/authorization:** Cashier permission and field validation.
- **Tenant isolation:** Product/customer/register references are tenant and branch checked.
- **Tests:** Payload validation, stale price, invalid payment, cross-tenant references.
- **Definition of done:** Frontend and backend share a precise checkout contract.
- **Handoff:** Input contract for sale action.
