# Quality Gates

The foundation quality baseline is designed for PHP 8.1, Laravel 10, and SQLite-backed local tests.

## Local commands

Run these from the project root:

```text
composer install
composer test
composer format:check
composer analyse
composer security-audit
composer quality
```

Expected outcomes:

- PHPUnit reports all tests passing.
- Pint reports that no files need formatting.
- Larastan completes at level 5 without errors.
- Composer audit runs as a fail-closed security gate. The current baseline reports three Laravel framework advisories (two package-security advisories and CVE-2026-48019) because Laravel 10 is outside the affected-version remediation range; this is documented as a release blocker and requires the planned Laravel upgrade before production.
- The combined `composer quality` command completes successfully.

The audit is intentionally not treated as a hidden warning. Update dependencies and record the review before production release. Do not suppress, ignore, or mark these advisories as resolved without a documented compatibility decision.

CI uses SQLite and does not require a MySQL service for foundation checks. The CI audit step reports the known Laravel 10 advisories as an explicit warning so the quality workflow can remain actionable while the approved Laravel upgrade is pending. The local `composer security-audit` command remains fail-closed.
