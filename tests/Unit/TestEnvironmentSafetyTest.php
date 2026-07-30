<?php

namespace Tests\Unit;

use Tests\TestCase;

class TestEnvironmentSafetyTest extends TestCase
{
    public function test_the_suite_uses_an_isolated_in_memory_sqlite_database(): void
    {
        $this->assertTrue(app()->environment('testing'));
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
    }
}
