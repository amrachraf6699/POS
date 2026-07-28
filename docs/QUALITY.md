# Quality Gates

The foundation quality baseline is designed for PHP 8.1, Laravel 10, and SQLite-backed local tests.

## Local commands

Run these from the project root:

```text
composer install
composer test
composer format:check
composer analyse
composer quality
```

Expected outcomes:

- PHPUnit reports all tests passing.
- Pint reports that no files need formatting.
- Larastan completes at level 5 without errors.
- The combined `composer quality` command completes successfully.

## Laravel 10 audit exception

The project has an approved exception to skip Composer's Laravel 10 advisory audit. The known Laravel framework advisories are tracked as resolved in the project tracker, so `composer quality` and CI do not run `composer audit`.

`composer audit` remains available for an explicit, manually requested dependency review. Reconsider this exception when the application upgrades beyond Laravel 10 or when dependency policy changes.

CI uses SQLite and runs formatting, tests, and static analysis.
