<?php

namespace Modules\Tracker\App\Domain\Services;

use Modules\Tracker\App\Domain\Exceptions\TrackerStateException;

class TrackerService
{
    public function __construct(
        private readonly TrackerStateReader $stateReader,
        private readonly PhaseDocumentReader $documentReader,
        private readonly TrackerValidator $validator,
        private readonly ProgressCalculator $calculator,
        private readonly TrackerStateWriter $writer,
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

        $issueItems = $this->issueItems($phases);

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
            'issue_items' => $issueItems,
        ];
    }

    public function issue(string $issueId): array
    {
        $issue = collect($this->dashboard()['issue_items'])->firstWhere('id', $issueId);

        if (! is_array($issue)) {
            abort(404);
        }

        return $issue;
    }

    public function phase(string $phaseId): array
    {
        $phase = collect($this->dashboard()['phases'])->firstWhere('id', $phaseId);

        if (! is_array($phase)) {
            abort(404);
        }

        return $phase;
    }

    public function task(string $phaseId, string $taskId): array
    {
        $phase = $this->phase($phaseId);
        $task = collect($phase['tasks'])->firstWhere('id', $taskId);

        if (! is_array($task)) {
            abort(404);
        }

        return ['phase' => $phase, 'task' => $task];
    }

    public function resolveIssue(string $issueId, string $resolution): array
    {
        $dashboard = $this->dashboard();
        $issue = collect($dashboard['issue_items'])->firstWhere('id', $issueId);

        if (! is_array($issue)) {
            abort(404);
        }

        $state = $this->stateReader->read();
        if ($issue['task_id']) {
            $scope = &$state['phases'][$issue['phase_id']]['tasks'][$issue['task_id']];
        } else {
            $scope = &$state['phases'][$issue['phase_id']];
        }
        $messages = $scope[$issue['type'].'s'] ?? [];
        $messageIndex = array_search($issue['message'], $messages, true);

        if ($messageIndex === false) {
            throw new TrackerStateException('The selected tracker issue is stale. Reload the dashboard.');
        }

        array_splice($scope[$issue['type'].'s'], $messageIndex, 1);
        $scope['resolutions'][] = '['.now('UTC')->toIso8601String().'] '.$resolution;
        $state['last_updated_at'] = now('UTC')->toIso8601String();
        $state['updated_by'] = 'Tracker dashboard';
        $this->writer->write($state);

        return $issue;
    }

    private function issueItems(array $phases): array
    {
        $items = [];
        foreach ($phases as $phase) {
            foreach (['conflict' => $phase['conflicts'] ?? [], 'problem' => $phase['problems'] ?? []] as $type => $messages) {
                foreach ($messages as $index => $message) {
                    $items[] = $this->issueItem($phase, null, $type, $index, $message);
                }
            }

            foreach ($phase['tasks'] as $task) {
                foreach (['conflict' => $task['conflicts'] ?? [], 'problem' => $task['problems'] ?? []] as $type => $messages) {
                    foreach ($messages as $index => $message) {
                        $items[] = $this->issueItem($phase, $task, $type, $index, $message);
                    }
                }
            }
        }

        return $items;
    }

    private function issueItem(array $phase, ?array $task, string $type, int $index, string $message): array
    {
        $scope = $task ? $task['id'] : 'phase';

        return [
            'id' => substr(hash('sha256', $phase['id'].'|'.$scope.'|'.$type.'|'.$index.'|'.$message), 0, 16),
            'type' => $type,
            'label' => ucfirst($type),
            'message' => $message,
            'phase_id' => $phase['id'],
            'phase_title' => $phase['title'],
            'task_id' => $task['id'] ?? null,
            'task_title' => $task['title'] ?? null,
            'status' => $task['status'] ?? $phase['status'],
            'latest_commit' => $task['latest_commit'] ?? null,
        ];
    }
}
