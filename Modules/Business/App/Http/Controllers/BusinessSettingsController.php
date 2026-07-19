<?php

namespace Modules\Business\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Modules\Business\App\Data\BusinessSettingsData;
use Modules\Business\App\Domain\Settings\BusinessSettingsService;
use Modules\Business\App\Http\Requests\UpdateBusinessSettingsRequest;

final class BusinessSettingsController extends Controller
{
    public function __construct(private readonly BusinessSettingsService $settings) {}

    public function edit(): View
    {
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
