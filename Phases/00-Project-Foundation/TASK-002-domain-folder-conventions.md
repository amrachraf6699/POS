# TASK-002 — Domain Folder Conventions

- **Objective:** Establish package-free domain organization and dependency rules.
- **Scope:** `app/Domain` layout, namespaces, autoloading, architecture documentation.
- **Non-scope:** Feature implementation.
- **Dependencies:** TASK-001.
- **Files/subsystems:** `app/Domain`, `tests/Feature`, `tests/Unit`, project docs.
- **Database/API/UI impact:** None.
- **Steps:** Create approved context list; define Actions/Data/Models/Policies usage; document cross-context dependency direction.
- **Validation/authorization:** N/A; rules are enforced by review/static checks.
- **Tenant isolation:** Every future tenant context must declare its boundary.
- **Tests:** Namespace/autoload smoke test.
- **Definition of done:** Agents have one unambiguous location and naming scheme for new code.
- **Handoff:** Domain structure decision record.
