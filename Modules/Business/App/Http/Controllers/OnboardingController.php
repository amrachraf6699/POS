<?php

namespace Modules\Business\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Modules\Business\App\Actions\CreateBranchAction;
use Modules\Business\App\Data\BusinessSettingsData;
use Modules\Business\App\Domain\Onboarding\OnboardingService;
use Modules\Business\App\Domain\Settings\BusinessSettingsService;
use Modules\Business\App\Http\Requests\BranchRequest;
use Modules\Business\App\Http\Requests\UpdateBusinessSettingsRequest;
use Modules\Business\App\Models\Branch;
use Modules\Identity\App\Domain\Tenancy\TenantContext;

final class OnboardingController extends Controller
{
    public function __construct(private readonly TenantContext $context, private readonly OnboardingService $onboarding, private readonly BusinessSettingsService $settings) {}

    public function index(): RedirectResponse
    {
        $this->owner();
        $next = $this->onboarding->nextStep();

        return $next === 'complete' ? redirect()->route('business.dashboard') : redirect()->route('business.onboarding.'.$next);
    }

    public function settings(): View
    {
        $this->owner();

        return view('business::onboarding.settings', ['settings' => $this->settings->settingsForCurrentTenant(), 'currencies' => config('business.supported_currencies', [])]);
    }

    public function saveSettings(UpdateBusinessSettingsRequest $request): RedirectResponse
    {
        $this->owner();
        $this->settings->update(BusinessSettingsData::fromArray($request->validated()));
        $this->onboarding->markSettingsCompleted();

        return redirect()->route('business.onboarding.branch')->with('status', 'تم حفظ إعدادات النشاط التجاري.');
    }

    public function branch(): View
    {
        $this->owner();

        return view('business::onboarding.branch', ['branch' => new Branch]);
    }

    public function saveBranch(BranchRequest $request, CreateBranchAction $action): RedirectResponse
    {
        $this->owner();
        $action->execute($request->user(), $this->context->tenant(), $request->validated());

        return redirect()->route('business.onboarding.staff')->with('status', 'تم إنشاء أول فرع نشط.');
    }

    public function staff(): View
    {
        $this->owner();

        return view('business::onboarding.staff');
    }

    public function skipStaff(): RedirectResponse
    {
        $this->owner();
        $this->onboarding->markStaffSetupCompleted();

        return redirect()->route('business.dashboard')->with('status', 'اكتمل إعداد نشاطك التجاري.');
    }

    private function owner(): void
    {
        abort_unless($this->context->membership()->isActive() && $this->context->membership()->isOwner(), 403);
    }
}
