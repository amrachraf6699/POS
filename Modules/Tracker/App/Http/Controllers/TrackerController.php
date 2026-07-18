<?php

namespace Modules\Tracker\App\Http\Controllers;

use App\Http\Controllers\Controller;
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
}
