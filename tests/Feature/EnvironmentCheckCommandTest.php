<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class EnvironmentCheckCommandTest extends TestCase
{
    public function test_environment_check_passes_for_the_local_test_configuration(): void
    {
        $this->assertSame(0, Artisan::call('app:environment-check'));
        $this->assertStringContainsString('Environment configuration passed.', Artisan::output());
    }
}
