<?php

namespace Modules\Tracker\App\Domain\Services;

use Modules\Tracker\App\Domain\Exceptions\TrackerStateException;

class PhaseDocumentReader
{
    public function __construct(private readonly string $phasesPath) {}

    public function read(): array
    {
        if (! is_dir($this->phasesPath)) {
            throw new TrackerStateException('Phase documentation directory is missing.');
        }

        $documents = [];
        $directories = array_values(array_filter(glob($this->phasesPath.DIRECTORY_SEPARATOR.'*') ?: [], 'is_dir'));
        sort($directories);

        foreach ($directories as $directory) {
            $readmePath = $directory.DIRECTORY_SEPARATOR.'README.md';

            if (! is_file($readmePath)) {
                continue;
            }

            $id = basename($directory);
            $readme = (string) file_get_contents($readmePath);
            $taskReferences = $this->taskReferences($readme);
            $tasks = [];

            foreach ($taskReferences as $taskReference) {
                $taskPath = $directory.DIRECTORY_SEPARATOR.$taskReference;

                if (! is_file($taskPath)) {
                    throw new TrackerStateException("Task documentation is missing: {$id}/{$taskReference}");
                }

                $taskContents = (string) file_get_contents($taskPath);
                $tasks[] = [
                    'id' => pathinfo($taskReference, PATHINFO_FILENAME),
                    'filename' => $taskReference,
                    'title' => $this->title($taskContents, pathinfo($taskReference, PATHINFO_FILENAME)),
                    'objective' => $this->field($taskContents, 'Objective'),
                    'dependencies' => $this->field($taskContents, 'Dependencies'),
                    'definition_of_done' => $this->field($taskContents, 'Definition of done'),
                ];
            }

            $documents[] = [
                'id' => $id,
                'title' => $this->title($readme, $id),
                'tasks' => $tasks,
            ];
        }

        return $documents;
    }

    private function taskReferences(string $contents): array
    {
        if (preg_match_all('/^- (TASK-[^\s`]+\.md)$/m', $contents, $matches) === false) {
            return [];
        }

        return array_values(array_unique($matches[1]));
    }

    private function title(string $contents, string $fallback): string
    {
        $firstLine = trim((string) strtok(str_replace(["\r\n", "\r"], "\n", $contents), "\n"));
        $title = preg_replace('/^#\s*(?:Phase\s+\S+|TASK-\S+)\s*(?:—|-)\s*/u', '', $firstLine);

        return trim((string) ($title ?: $fallback));
    }

    private function field(string $contents, string $name): ?string
    {
        preg_match('/^- \*\*'.preg_quote($name, '/').'\:\*\*\s*(.+)$/mi', $contents, $matches);

        return isset($matches[1]) ? trim($matches[1]) : null;
    }
}
