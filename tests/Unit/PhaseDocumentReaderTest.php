<?php

namespace Tests\Unit;

use Modules\Tracker\App\Domain\Services\PhaseDocumentReader;
use PHPUnit\Framework\TestCase;

class PhaseDocumentReaderTest extends TestCase
{
    public function test_all_phase_and_task_documents_are_discovered(): void
    {
        $documents = (new PhaseDocumentReader(dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'Phases'))->read();

        $this->assertCount(12, $documents);
        $this->assertCount(48, array_merge(...array_column($documents, 'tasks')));
        $this->assertSame('Project Foundation', $documents[0]['title']);
        $this->assertNotEmpty($documents[0]['tasks'][0]['objective']);
    }
}
