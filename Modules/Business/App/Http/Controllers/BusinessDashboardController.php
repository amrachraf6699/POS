<?php

namespace Modules\Business\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Modules\Business\App\Domain\Dashboard\BusinessDashboardService;
use Modules\Business\App\Domain\Onboarding\OnboardingService;

final class BusinessDashboardController extends Controller
{
    public function __construct(private readonly BusinessDashboardService $dashboard, private readonly OnboardingService $onboarding) {}

    public function __invoke(): View|RedirectResponse
    {
        if (app(\Modules\Identity\App\Domain\Tenancy\TenantContext::class)->membership()->isOwner() && ! $this->onboarding->isComplete()) {
            return redirect()->route('business.onboarding.index');
        }

        return view('business::dashboard.index', [
            'dashboard' => $this->dashboard->summarize(request()->user()),
        ]);
    }
}
