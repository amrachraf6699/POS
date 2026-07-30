<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

trait CreatesApplication
{
    /**
     * Creates the application.
     */
    public function createApplication(): Application
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        $this->ensureIsolatedTestDatabase($app);

        return $app;
    }

    private function ensureIsolatedTestDatabase(Application $app): void
    {
        $connection = (string) $app['config']->get('database.default');
        $database = (string) $app['config']->get("database.connections.{$connection}.database");

        if ($app->environment('testing') && $connection === 'sqlite' && $database === ':memory:') {
            return;
        }

        throw new \LogicException(
            'Refusing to run tests without the isolated in-memory SQLite database. Check .env.testing and phpunit.xml.',
        );
    }
}
