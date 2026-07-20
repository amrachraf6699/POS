<?php

namespace Modules\Business\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Modules\Business\App\Actions\AssignBranchUserAction;
use Modules\Business\App\Actions\DeactivateBranchAssignmentAction;
use Modules\Business\App\Http\Requests\AssignBranchUserRequest;
use Modules\Business\App\Http\Requests\UpdateBranchAssignmentRequest;
use Modules\Business\App\Models\Branch;
use Modules\Identity\App\Domain\Tenancy\TenantContext;
use Modules\Identity\App\Models\User;

final class BranchAssignmentController extends Controller
{
    public function __construct(private readonly TenantContext $context) {}

    public function store(AssignBranchUserRequest $request, Branch $branch, AssignBranchUserAction $action): RedirectResponse
    {
        $user = $this->tenantUser((int) $request->validated('user_id'));
        $action->execute($request->user(), $this->context->tenant(), $branch, $user);

        return back()->with('status', 'تم تفعيل تعيين المستخدم للفرع.');
    }

    public function update(
        UpdateBranchAssignmentRequest $request,
        Branch $branch,
        int $user,
        AssignBranchUserAction $assign,
        DeactivateBranchAssignmentAction $deactivate,
    ): RedirectResponse {
        $target = $this->tenantUser($user);

        if ($request->validated('status') === 'active') {
            $assign->execute($request->user(), $this->context->tenant(), $branch, $target);
        } else {
            $deactivate->execute($request->user(), $this->context->tenant(), $branch, $target);
        }

        return back()->with('status', 'تم تحديث تعيين المستخدم.');
    }

    public function destroy(Branch $branch, int $user, DeactivateBranchAssignmentAction $action): RedirectResponse
    {
        $action->execute(request()->user(), $this->context->tenant(), $branch, $this->tenantUser($user));

        return back()->with('status', 'تم تعطيل تعيين المستخدم.');
    }

    private function tenantUser(int $userId): User
    {
        /** @var User|null $user */
        $user = $this->context->tenant()->users()->whereKey($userId)->first();

        abort_if($user === null, 404);

        return $user;
    }
}
