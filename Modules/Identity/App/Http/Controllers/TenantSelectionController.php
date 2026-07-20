<?php

namespace Modules\Identity\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Identity\App\Domain\Tenancy\TenantContext;
use Modules\Identity\App\Models\Tenant;

final class TenantSelectionController extends Controller
{
    public function __construct(private readonly TenantContext $context) {}

    public function index(Request $request): View
    {
        return view('identity::tenants.select', [
            'tenants' => $request->user()->accessibleTenants()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, Tenant $tenant): RedirectResponse
    {
        $selectedTenant = $request->user()
            ->accessibleTenants()
            ->whereKey($tenant->getKey())
            ->first();

        if ($selectedTenant === null) {
            abort(403);
        }

        $request->session()->put('current_tenant_id', $selectedTenant->getKey());
        $this->context->clear();

        return redirect()->intended(route('business.dashboard'));
    }
}
