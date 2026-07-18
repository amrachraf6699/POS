<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckEnvironment extends Command
{
    protected $signature = 'app:environment-check';

    protected $description = 'Validate the application runtime and environment configuration.';

    public function handle(): int
    {
        $errors = [];
        $warnings = [];

        if (PHP_VERSION_ID < 80100) {
            $errors[] = 'PHP 8.1 or newer is required.';
        }

        foreach (['ctype', 'curl', 'dom', 'fileinfo', 'json', 'mbstring', 'openssl', 'pdo', 'tokenizer', 'xml'] as $extension) {
            if (! extension_loaded($extension)) {
                $errors[] = "Required PHP extension is missing: {$extension}.";
            }
        }

        $driver = (string) config('database.default');
        $driverExtension = $driver === 'sqlite' ? 'pdo_sqlite' : ($driver === 'mysql' ? 'pdo_mysql' : null);
        if ($driverExtension && ! extension_loaded($driverExtension)) {
            $errors[] = "The {$driver} database driver requires PHP extension {$driverExtension}.";
        }

        if (! config('app.key')) {
            $errors[] = 'APP_KEY is missing. Generate it before running the application.';
        }

        if (app()->environment('production')) {
            if (config('app.debug')) {
                $errors[] = 'APP_DEBUG must be false in production.';
            }

            if (! str_starts_with((string) config('app.url'), 'https://')) {
                $errors[] = 'APP_URL must use HTTPS in production.';
            }

            if ($driver !== 'mysql') {
                $errors[] = 'Production must use the MySQL connection.';
            }
        }

        foreach ([storage_path(), storage_path('framework/cache'), storage_path('framework/sessions'), storage_path('framework/views'), base_path('bootstrap/cache')] as $directory) {
            if (! is_dir($directory) || ! is_writable($directory)) {
                $errors[] = 'Required writable directory is unavailable: '.basename($directory).'.';
            }
        }

        if ($driver === 'sqlite' && ! is_file((string) config('database.connections.sqlite.database'))) {
            $warnings[] = 'The SQLite database file does not exist yet. Create it before migrating.';
        }

        foreach ($warnings as $warning) {
            $this->warn($warning);
        }

        if ($errors !== []) {
            foreach ($errors as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $this->info('Environment configuration passed.');
        $this->line('PHP: '.PHP_VERSION);
        $this->line('Database: '.$driver);

        return self::SUCCESS;
    }
}
