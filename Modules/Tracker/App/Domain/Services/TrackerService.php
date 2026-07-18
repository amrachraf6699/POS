<?php

namespace Modules\Tracker\App\Domain\Services;

class TrackerService
{
    public function __construct(
        private readonly TrackerStateReader $stateReader,
        private readonly PhaseDocumentReader $documentReader,
        private readonly TrackerValidator $validator,
        private readonly ProgressCalculator $calculator,
    ) {}

    public function dashboard(): array
    {
        $state = $this->stateReader->read();
        $documents = $this->documentReader->read();
        $this->validator->validate($state, $documents);

        $phases = [];
        $statusCounts = array_fill_keys(TrackerValidator::STATUSES, 0);
        $taskCount = 0;
        $issueCounts = ['conflicts' => 0, 'problems' => 0, 'blocked' => 0];

        foreach ($documents as $document) {
            $phaseState = $state['phases'][$document['id']];
            $tasks = [];

            foreach ($document['tasks'] as $documentTask) {
                $taskState = $phaseState['tasks'][$documentTask['id']];
                $task = array_merge($documentTask, $taskState, [
                    'score' => $this->calculator->taskScore($taskState['status']),
                ]);
                $tasks[] = $task;
                $statusCounts[$task['status']]++;
                $taskCount++;
                $issueCounts['conflicts'] += count($task['conflicts'] ?? []);
                $issueCounts['problems'] += count($task['problems'] ?? []);
                $issueCounts['blocked'] += $task['status'] === 'blocked' ? 1 : 0;
            }

            $phase = array_merge($document, $phaseState, [
                'progress' => $this->calculator->phaseProgress($tasks),
                'tasks' => $tasks,
            ]);
            $phases[] = $phase;
            $issueCounts['conflicts'] += count($phase['conflicts'] ?? []);
            $issueCounts['problems'] += count($phase['problems'] ?? []);
        }

        return [
            'meta' => [
                'last_updated_at' => $state['last_updated_at'] ?? null,
                'updated_by' => $state['updated_by'] ?? null,
            ],
            'summary' => [
                'progress' => $this->calculator->overallProgress($phases),
                'phase_count' => count($phases),
                'task_count' => $taskCount,
                'status_counts' => $statusCounts,
                'issues' => $issueCounts,
            ],
            'statuses' => TrackerValidator::STATUSES,
            'phases' => $phases,
        ];
    }
}
