<?php

namespace Modules\Tracker\App\Domain\Services;

use JsonException;
use Modules\Tracker\App\Domain\Exceptions\TrackerStateException;

class TrackerStateWriter
{
    public function __construct(private readonly string $path) {}

    public function write(array $state): void
    {
        try {
            $json = json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL;
        } catch (JsonException $exception) {
            throw new TrackerStateException('Tracker state could not be encoded.', 0, $exception);
        }

        $handle = @fopen($this->path, 'c+');
        if ($handle === false || ! flock($handle, LOCK_EX)) {
            throw new TrackerStateException('Tracker state could not be locked for writing.');
        }

        try {
            ftruncate($handle, 0);
            rewind($handle);
            if (fwrite($handle, $json) === false) {
                throw new TrackerStateException('Tracker state could not be written.');
            }
            fflush($handle);
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }
}
