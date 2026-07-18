<?php

namespace Tests\Unit;

use Modules\Tracker\App\Domain\Exceptions\TrackerStateException;
use Modules\Tracker\App\Domain\Services\TrackerStateReader;
use PHPUnit\Framework\TestCase;

class TrackerStateReaderTest extends TestCase
{
    public function test_malformed_json_is_rejected(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'tracker-');
        file_put_contents($path, '{invalid');

        try {
            $this->expectException(TrackerStateException::class);
            (new TrackerStateReader($path))->read();
        } finally {
            @unlink($path);
        }
    }
}
