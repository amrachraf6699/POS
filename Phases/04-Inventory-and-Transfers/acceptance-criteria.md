# Acceptance Criteria

- Every stock change has an immutable movement.
- Balance updates and movements succeed or fail together.
- Transfers deduct source and add destination atomically.
- Concurrent changes cannot oversell or create negative stock unless explicitly allowed.
