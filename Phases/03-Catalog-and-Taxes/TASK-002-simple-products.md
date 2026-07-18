# TASK-002 — Simple Products

- **Objective:** Create and manage non-variant products.
- **Scope:** Product fields, SKU/barcode, status, inventory flag, low-stock threshold.
- **Non-scope:** Variants, bundles, suppliers.
- **Dependencies:** TASK-001.
- **Files/subsystems:** Catalog models, actions, policies, views.
- **Database/API/UI impact:** Product table and CRUD UI.
- **Steps:** Enforce tenant uniqueness; support soft deletion; validate money and references.
- **Validation/authorization:** Catalog permissions; inactive products unavailable for sale.
- **Tenant isolation:** Product IDs always resolve inside current tenant.
- **Tests:** CRUD, duplicate identifiers, soft delete, inactive sale rejection.
- **Definition of done:** Products are safe inputs to stock and checkout.
- **Handoff:** Product read model for POS.
