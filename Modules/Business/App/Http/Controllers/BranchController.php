<?php

namespace Modules\Business\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Modules\Business\App\Actions\CreateBranchAction;
use Modules\Business\App\Actions\DeactivateBranchAction;
use Modules\Business\App\Actions\UpdateBranchAction;
use Modules\Business\App\Domain\Branches\BranchAuthorization;
use Modules\Business\App\Http\Requests\BranchRequest;
use Modules\Business\App\Models\Branch;
use Modules\Identity\App\Domain\Tenancy\TenantContext;

final class BranchController extends Controller
{
    public function __construct(
        private readonly TenantContext $context,
        private readonly BranchAuthorization $authorization,
    ) {}

    public function index(): View
    {
        $user = request()->user();
        $canManage = $this->authorization->canManage($user, $this->context->tenant());

        $branches = Branch::query();
        if (! $canManage) {
            /** @phpstan-ignore-next-line dynamic Eloquent scope */
            $branches = $branches->accessibleTo($user);
        }

        return view('business::branches.index', [
            'branches' => $branches->orderBy('name')->get(),
            'canManage' => $canManage,
        ]);
    }

    public function create(): View
    {
        $this->ensureCanManage();

        return view('business::branches.form', ['branch' => new Branch]);
    }

    public function store(BranchRequest $request, CreateBranchAction $action): RedirectResponse
    {
        $branch = $action->execute($request->user(), $this->context->tenant(), $request->validated());

        return redirect()->route('business.branches.edit', $branch)->with('status', 'تم إنشاء الفرع بنجاح.');
    }

    public function edit(Branch $branch): View
    {
        $this->ensureCanManage();
        $tenant = $this->context->tenant();

        return view('business::branches.form', [
            'branch' => $branch->load('assignments.user'),
            'users' => $tenant->users()->orderBy('name')->get(),
            'branchesCount' => Branch::query()->count(),
        ]);
    }

    public function update(BranchRequest $request, Branch $branch, UpdateBranchAction $action): RedirectResponse
    {
        $action->execute($request->user(), $this->context->tenant(), $branch, $request->validated());

        return back()->with('status', 'تم حفظ بيانات الفرع.');
    }

    private function ensureCanManage(): void
    {
        abort_unless($this->authorization->canManage(request()->user(), $this->context->tenant()), 403);
    }

    public function deactivate(Branch $branch, DeactivateBranchAction $action): RedirectResponse
    {
        $action->execute(request()->user(), $this->context->tenant(), $branch);

        return redirect()->route('business.branches.index')->with('status', 'تم تعطيل الفرع.');
    }
}
