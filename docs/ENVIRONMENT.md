# Environment and Runtime

## Supported baseline

- PHP 8.1 or newer; production should remain on the same supported PHP minor line as the deployment image.
- Laravel 10.x.
- Composer 2.x.
- SQLite for local development and tests.
- MySQL 8+ for production.
- UTC application timestamps; tenant-local display timezones will be added with tenant settings.

## Local setup

```text
copy .env.example .env
php artisan key:generate
New-Item -ItemType File -Force database/database.sqlite
php artisan app:environment-check
php artisan migrate
php artisan serve
```

On macOS/Linux, replace the `New-Item` command with:

```bash
touch database/database.sqlite
```

The local `.env.example` deliberately uses SQLite and does not require a running database server.

## Production MySQL

Set these values in the deployment secret store, not in committed files:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://pos.example.com
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pos
DB_USERNAME=least_privilege_user
DB_PASSWORD=secret-from-secret-store
```

Production also requires a valid `APP_KEY`, HTTPS, writable `storage` and `bootstrap/cache` directories, queue workers, and a scheduler process.

## Services

The MVP may use database/file-backed services locally. Production deployment must explicitly configure:

- Queue workers for asynchronous jobs.
- A durable cache backend when multiple application instances are used.
- Private or S3-compatible storage for uploaded documents.
- SMTP or an approved transactional mail provider.

## Validation

Run the environment check before migrations and releases:

```bash
php artisan app:environment-check
```

The command reports configuration failures without printing passwords, keys, or full filesystem paths.
