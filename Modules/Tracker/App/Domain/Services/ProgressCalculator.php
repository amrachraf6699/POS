<?php

namespace Modules\Tracker\App\Domain\Services;

class ProgressCalculator
{
    private const SCORES = [
        'not_started' => 0,
        'planned' => 0,
        'in_progress' => 0.5,
        'review' => 0.75,
        'done' => 1,
        'blocked' => 0,
    ];

    public function taskScore(string $status): float
    {
        return (float) self::SCORES[$status];
    }

    public function phaseProgress(array $tasks): float
    {
        if ($tasks === []) {
            return 0.0;
        }

        return array_sum(array_map(fn (array $task): float => $this->taskScore($task['status']), $tasks)) / count($tasks);
    }

    public function overallProgress(array $phases): float
    {
        $weightedTotal = 0.0;
        $weightTotal = 0;

        foreach ($phases as $phase) {
            $weightedTotal += $phase['progress'] * $phase['weight'];
            $weightTotal += $phase['weight'];
        }

        return $weightTotal > 0 ? $weightedTotal / $weightTotal : 0.0;
    }
}
