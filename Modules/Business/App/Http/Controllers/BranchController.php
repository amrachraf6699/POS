<?php

namespace Modules\Business\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Modules\Business\App\Actions\CreateBranchAction;
use Modules\Business\App\Actions\DeactivateBranchAction;
use Modules\Business\App\Actions\UpdateBranchAction;
use Modules\Business\App\Http\Requests\BranchRequest;
use Modules\Business\App\Models\Branch;
use Modules\Identity\App\Domain\Tenancy\TenantContext;

final class BranchController extends Controller
{
    public function __construct(private readonly TenantContext $context) {}

    public function index(): View
    {
        return view('business::branches.index', [
            'branches' => Branch::query()->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('business::branches.form', ['branch' => new Branch]);
    }

    public function store(BranchRequest $request, CreateBranchAction $action): RedirectResponse
    {
        $branch = $action->execute($request->user(), $this->context->tenant(), $request->validated());

        return redirect()->route('business.branches.edit', $branch)->with('status', 'تم إنشاء الفرع بنجاح.');
    }

    public function edit(Branch $branch): View
    {
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

    public function deactivate(Branch $branch, DeactivateBranchAction $action): RedirectResponse
    {
        $action->execute(request()->user(), $this->context->tenant(), $branch);

        return redirect()->route('business.branches.index')->with('status', 'تم تعطيل الفرع.');
    }
}
