# Acceptance Criteria

- A register has at most one open session.
- Checkout requires an active session and recalculates all totals server-side.
- Sale, payments, inventory movements, and register cash update atomically.
- Duplicate checkout requests do not create duplicate sales.
