<?php

namespace Modules\Business\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Modules\Business\App\Data\BusinessSettingsData;
use Modules\Business\App\Domain\Settings\BusinessSettingsAuthorization;
use Modules\Business\App\Domain\Settings\BusinessSettingsService;
use Modules\Business\App\Http\Requests\UpdateBusinessSettingsRequest;
use Modules\Identity\App\Domain\Tenancy\TenantContext;

final class BusinessSettingsController extends Controller
{
    public function __construct(
        private readonly BusinessSettingsService $settings,
        private readonly BusinessSettingsAuthorization $authorization,
        private readonly TenantContext $context,
    ) {}

    public function edit(): View
    {
        abort_unless($this->authorization->canManage(request()->user(), $this->context->tenant()), 403);

        return view('business::settings.edit', [
            'settings' => $this->settings->settingsForCurrentTenant(),
            'currencies' => config('business.supported_currencies', []),
        ]);
    }

    public function update(UpdateBusinessSettingsRequest $request): RedirectResponse
    {
        $this->settings->update(BusinessSettingsData::fromArray($request->validated()));

        return back()->with('status', 'تم حفظ إعدادات النشاط التجاري.');
    }
}
