<?php

namespace Modules\Tracker\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Tracker\App\Domain\Exceptions\TrackerStateException;
use Modules\Tracker\App\Domain\Services\TrackerService;

class TrackerController extends Controller
{
    public function __construct(private readonly TrackerService $tracker) {}

    public function index()
    {
        try {
            return view('tracker::index', $this->tracker->dashboard());
        } catch (TrackerStateException $exception) {
            Log::error('Tracker state is invalid.', ['exception' => $exception]);

            return response()->view('tracker::error', status: 500);
        }
    }

    public function problems()
    {
        return view('tracker::problems.index', $this->tracker->dashboard());
    }

    public function phase(string $phase)
    {
        return view('tracker::phases.show', [
            'dashboard' => $this->tracker->dashboard(),
            'phase' => $this->tracker->phase($phase),
        ]);
    }

    public function task(string $phase, string $task)
    {
        $data = $this->tracker->task($phase, $task);

        return view('tracker::tasks.show', [
            'dashboard' => $this->tracker->dashboard(),
            'phase' => $data['phase'],
            'task' => $data['task'],
        ]);
    }

    public function problem(string $issue)
    {
        return view('tracker::problems.show', [
            'dashboard' => $this->tracker->dashboard(),
            'issue' => $this->tracker->issue($issue),
        ]);
    }

    public function resolve(Request $request, string $issue)
    {
        abort_unless(config('tracker.web_updates'), 403, 'Tracker updates are disabled outside local development.');

        $data = $request->validate([
            'resolution' => ['required', 'string', 'max:2000'],
        ]);

        $this->tracker->resolveIssue($issue, $data['resolution']);

        return redirect()->route('tracker.problems.show', $issue)->with('status', 'Issue resolved and tracker state updated.');
    }
}
