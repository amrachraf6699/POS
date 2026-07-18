<?php

namespace Modules\Tracker\App\Domain\Services;

use Modules\Tracker\App\Domain\Exceptions\TrackerStateException;

class TrackerValidator
{
    public const STATUSES = ['not_started', 'planned', 'in_progress', 'review', 'done', 'blocked'];

    public function validate(array $state, array $documents): void
    {
        if (($state['schema_version'] ?? null) !== 1 || ! isset($state['phases']) || ! is_array($state['phases'])) {
            throw new TrackerStateException('Tracker state schema is invalid.');
        }

        $documentPhaseIds = array_column($documents, 'id');
        $statePhaseIds = array_keys($state['phases']);

        if (array_diff($documentPhaseIds, $statePhaseIds) || array_diff($statePhaseIds, $documentPhaseIds)) {
            throw new TrackerStateException('Tracker phase entries do not match the phase documentation.');
        }

        foreach ($documents as $document) {
            $phase = $state['phases'][$document['id']];
            $this->status($phase['status'] ?? null, "phase {$document['id']}");

            if (! isset($phase['weight']) || ! is_int($phase['weight']) || $phase['weight'] < 1) {
                throw new TrackerStateException("Invalid weight for phase {$document['id']}.");
            }

            $tasks = $phase['tasks'] ?? null;
            if (! is_array($tasks)) {
                throw new TrackerStateException("Tasks are missing for phase {$document['id']}.");
            }

            $documentTaskIds = array_column($document['tasks'], 'id');
            $stateTaskIds = array_keys($tasks);

            if (array_diff($documentTaskIds, $stateTaskIds) || array_diff($stateTaskIds, $documentTaskIds)) {
                throw new TrackerStateException("Tracker tasks do not match phase {$document['id']} documentation.");
            }

            foreach ($document['tasks'] as $task) {
                $taskState = $tasks[$task['id']];
                $this->status($taskState['status'] ?? null, "task {$task['id']}");
                $this->lists($taskState, ['notes', 'conflicts', 'problems', 'resolutions', 'evidence'], $task['id']);
            }

            $this->lists($phase, ['notes', 'conflicts', 'problems', 'resolutions'], $document['id']);
        }
    }

    private function status(mixed $status, string $subject): void
    {
        if (! in_array($status, self::STATUSES, true)) {
            throw new TrackerStateException("Invalid status for {$subject}.");
        }
    }

    private function lists(array $data, array $fields, string $subject): void
    {
        foreach ($fields as $field) {
            if (isset($data[$field]) && ! is_array($data[$field])) {
                throw new TrackerStateException("Tracker field {$field} must be an array for {$subject}.");
            }
        }
    }
}
