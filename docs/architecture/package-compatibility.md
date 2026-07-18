# Package Compatibility Record

Approved foundation versions:

| Component | Version / constraint | Verification |
| --- | --- | --- |
| PHP | `^8.1` | `php -v` and CI PHP 8.1 job |
| Laravel | `^10.10` | `composer show laravel/framework` |
| Laravel Modules | `10.0.6` | `composer show nwidart/laravel-modules` |
| Larastan | `2.9.14` | `composer show larastan/larastan` |
| PHPStan | Resolved by Larastan (`1.12.33` at baseline) | `composer show phpstan/phpstan` |

Composer pins the dependency solver platform to PHP `8.1.0` so lockfile generation on a newer developer PHP cannot select packages that break the PHP 8.1 CI job. The runtime itself remains constrained by the root `^8.1` requirement.

Repeat compatibility checks with:

```text
composer validate --strict
composer update --dry-run
composer show laravel/framework nwidart/laravel-modules larastan/larastan phpstan/phpstan
php artisan module:list
```

Do not upgrade Laravel, PHP, or module tooling independently. Review the compatibility impact and update this record when a version changes.
