<?php

namespace Modules\Business\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Modules\Business\App\Domain\Dashboard\BusinessDashboardService;

final class BusinessDashboardController extends Controller
{
    public function __construct(private readonly BusinessDashboardService $dashboard) {}

    public function __invoke(): View
    {
        return view('business::dashboard.index', [
            'dashboard' => $this->dashboard->summarize(request()->user()),
        ]);
    }
}
