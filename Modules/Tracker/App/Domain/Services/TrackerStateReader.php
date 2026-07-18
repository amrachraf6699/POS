<?php

namespace Modules\Tracker\App\Domain\Services;

use JsonException;
use Modules\Tracker\App\Domain\Exceptions\TrackerStateException;

class TrackerStateReader
{
    public function __construct(private readonly string $path)
    {
    }

    public function read(): array
    {
        if (! is_file($this->path)) {
            throw new TrackerStateException('Tracker state file is missing.');
        }

        try {
            $state = json_decode(
                (string) file_get_contents($this->path),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $exception) {
            throw new TrackerStateException('Tracker state contains invalid JSON.', 0, $exception);
        }

        if (! is_array($state)) {
            throw new TrackerStateException('Tracker state must be a JSON object.');
        }

        return $state;
    }
}
