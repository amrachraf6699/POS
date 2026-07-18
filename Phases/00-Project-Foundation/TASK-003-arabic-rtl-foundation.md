# TASK-003 — Arabic and RTL Foundation

- **Objective:** Make Arabic the initial locale and establish RTL-safe layouts and validation messages.
- **Scope:** Locale, direction, base Blade layout, typography hooks, translation structure.
- **Non-scope:** Complete feature translations.
- **Dependencies:** TASK-001.
- **Files/subsystems:** `config/app.php`, `resources/lang`, `resources/views`, CSS entrypoint.
- **Database/API/UI impact:** No schema/API; base UI direction changes.
- **Steps:** Set Arabic defaults; add direction helper/layout; define Arabic translation namespaces; verify forms and tables.
- **Validation/authorization:** Validation messages must render in Arabic.
- **Tenant isolation:** None.
- **Tests:** View smoke test asserting locale and RTL direction.
- **Definition of done:** Every later UI task has an established Arabic/RTL extension point.
- **Handoff:** Reusable RTL layout and translation conventions.
