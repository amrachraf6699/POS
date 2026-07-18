<?php

namespace Modules\Identity\App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Identity\App\Domain\Tenancy\TenantContext;
use Modules\Identity\App\Models\Membership;
use Modules\Identity\App\Models\Tenant;
use Symfony\Component\HttpFoundation\Response;

final class ResolveTenantContext
{
    public function __construct(private readonly TenantContext $context) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            abort(401);
        }

        $accessibleTenants = $user->accessibleTenants();

        if (! $accessibleTenants->exists()) {
            abort(403);
        }

        $tenantId = $request->session()->get('current_tenant_id');
        $membership = null;

        if (is_int($tenantId) || (is_string($tenantId) && ctype_digit($tenantId))) {
            $membership = Membership::query()
                ->with('tenant')
                ->where('user_id', $user->getKey())
                ->where('tenant_id', (int) $tenantId)
                ->where('status', Membership::STATUS_ACTIVE)
                ->whereHas('tenant', function ($query): void {
                    $query->where('status', Tenant::STATUS_ACTIVE);
                })
                ->first();
        }

        if ($membership === null || ! $membership->tenant instanceof Tenant || ! $membership->tenant->isActive()) {
            $request->session()->forget('current_tenant_id');
            $request->session()->put('url.intended', $request->fullUrl());

            return redirect()->route('tenant.selection');
        }

        $this->context->set($membership->tenant, $membership);

        return $next($request);
    }
}
