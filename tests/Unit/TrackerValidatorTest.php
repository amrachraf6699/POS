<?php

namespace Tests\Unit;

use Modules\Tracker\App\Domain\Exceptions\TrackerStateException;
use Modules\Tracker\App\Domain\Services\TrackerValidator;
use PHPUnit\Framework\TestCase;

class TrackerValidatorTest extends TestCase
{
    public function test_unknown_status_is_rejected(): void
    {
        $this->expectException(TrackerStateException::class);

        (new TrackerValidator())->validate([
            'schema_version' => 1,
            'phases' => ['phase' => [
                'status' => 'unknown',
                'weight' => 1,
                'tasks' => ['task' => ['status' => 'not_started']],
            ]],
        ], [[
            'id' => 'phase',
            'tasks' => [['id' => 'task']],
        ]]);
    }

    public function test_unknown_task_is_rejected(): void
    {
        $this->expectException(TrackerStateException::class);

        (new TrackerValidator())->validate([
            'schema_version' => 1,
            'phases' => ['phase' => [
                'status' => 'not_started',
                'weight' => 1,
                'tasks' => ['unexpected' => ['status' => 'not_started']],
            ]],
        ], [[
            'id' => 'phase',
            'tasks' => [['id' => 'task']],
        ]]);
    }
}
