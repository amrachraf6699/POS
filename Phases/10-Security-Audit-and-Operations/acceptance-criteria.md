# Acceptance Criteria

- No tested tenant or IDOR path leaks data.
- Critical jobs restore tenant context and are idempotent.
- Checkout, transfers, returns, and closing pass concurrency tests.
- Production errors, logs, backups, and health checks are safe and actionable.
