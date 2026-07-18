<?php

namespace Tests\Unit;

use Modules\Tracker\App\Domain\Services\ProgressCalculator;
use PHPUnit\Framework\TestCase;

class TrackerProgressCalculatorTest extends TestCase
{
    public function test_status_scores_are_calculated(): void
    {
        $calculator = new ProgressCalculator();

        $this->assertSame(0.0, $calculator->taskScore('not_started'));
        $this->assertSame(0.0, $calculator->taskScore('planned'));
        $this->assertSame(0.5, $calculator->taskScore('in_progress'));
        $this->assertSame(0.75, $calculator->taskScore('review'));
        $this->assertSame(1.0, $calculator->taskScore('done'));
        $this->assertSame(0.0, $calculator->taskScore('blocked'));
    }

    public function test_phase_progress_uses_task_scores(): void
    {
        $calculator = new ProgressCalculator();

        $this->assertSame(0.5625, $calculator->phaseProgress([
            ['status' => 'done'],
            ['status' => 'review'],
            ['status' => 'in_progress'],
            ['status' => 'not_started'],
        ]));
    }

    public function test_overall_progress_uses_phase_weights(): void
    {
        $calculator = new ProgressCalculator();

        $this->assertSame(0.625, $calculator->overallProgress([
            ['progress' => 1.0, 'weight' => 1],
            ['progress' => 0.5, 'weight' => 3],
        ]));
    }
}
