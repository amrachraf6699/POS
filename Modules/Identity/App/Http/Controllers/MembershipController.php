<?php

namespace Modules\Identity\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Modules\Identity\App\Actions\UpdateMembershipAction;
use Modules\Identity\App\Domain\Authorization\TenantAuthorization;
use Modules\Identity\App\Domain\Tenancy\TenantContext;
use Modules\Identity\App\Http\Requests\UpdateMembershipRequest;
use Modules\Identity\App\Models\Membership;

final class MembershipController extends Controller
{
    public function __construct(private readonly TenantContext $context, private readonly TenantAuthorization $authorization) {}

    public function index(): View
    {
        abort_unless($this->authorization->allows(request()->user(), $this->context->tenant(), 'users.view'), 403);

        return view('identity::memberships.index', ['memberships' => Membership::query()->where('tenant_id', $this->context->id())->with('user')->where()->orderBy('created_at')->get()]);
    }

    public function update(UpdateMembershipRequest $request, Membership $membership, UpdateMembershipAction $action): RedirectResponse
    {
        abort_unless((int) $membership->tenant_id === (int) $this->context->id(), 404);
        $action->execute($request->user(), $this->context->tenant(), $membership, $request->string('role')->toString(), $request->string('status')->toString());

        return back()->with('status', 'تم تحديث صلاحيات العضو وحالته.');
    }
}
