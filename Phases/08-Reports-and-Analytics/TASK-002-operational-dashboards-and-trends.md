# TASK-002 — Operational Dashboards and Trends

- **Objective:** Build Arabic operational dashboard with comparisons and trends.
- **Scope:** Sales trends, product/category performance, cashier/branch breakdown, payments/VAT.
- **Non-scope:** Custom analytics builder and profit analytics.
- **Dependencies:** TASK-001.
- **Files/subsystems:** Reports services and Blade/JS charts.
- **Database/API/UI impact:** Dashboard UI and read queries.
- **Steps:** Define metric cards; add comparison periods; render RTL-safe charts/tables.
- **Validation/authorization:** Owner/manager/report permission.
- **Tenant isolation:** Filters cannot escape tenant/branch assignment.
- **Tests:** Metric fixtures, comparisons, empty states, permission/leakage.
- **Definition of done:** Launch analytics boundary is explicit and usable.
- **Handoff:** Report acceptance fixtures.
