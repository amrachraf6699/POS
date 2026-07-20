<?php

namespace Modules\Identity\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Notification;
use Modules\Identity\App\Actions\CreateInvitationAction;
use Modules\Identity\App\Domain\Invitations\InvitationAuthorization;
use Modules\Identity\App\Domain\Tenancy\TenantContext;
use Modules\Identity\App\Http\Requests\CreateInvitationRequest;
use Modules\Identity\App\Http\Requests\ResendInvitationRequest;
use Modules\Identity\App\Http\Requests\RevokeInvitationRequest;
use Modules\Identity\App\Models\Invitation;
use Modules\Identity\App\Notifications\InvitationNotification;

final class InvitationController extends Controller
{
    public function __construct(
        private readonly CreateInvitationAction $createInvitation,
        private readonly InvitationAuthorization $authorization,
        private readonly TenantContext $context,
    ) {
        // Dependencies are injected by the Identity module container.
    }

    public function index(): View
    {
        $tenant = $this->context->tenant();
        abort_unless($this->authorization->canManage(request()->user(), $tenant), 403);

        return view('identity::invitations.index', [
            'invitations' => Invitation::query()->where('tenant_id', $tenant->getKey())->latest()->get(),
        ]);
    }

    public function store(CreateInvitationRequest $request): RedirectResponse
    {
        $tenant = $this->context->tenant();
        $result = $this->createInvitation->execute($request->user(), $tenant, $request->string('email')->toString());
        $this->notify($result->invitation, $result->plainToken);

        return back()->with('status', 'تم إرسال الدعوة.');
    }

    public function resend(ResendInvitationRequest $request, Invitation $invitation): RedirectResponse
    {
        $tenant = $this->context->tenant();
        abort_unless((int) $invitation->tenant_id === (int) $tenant->getKey(), 404);
        abort_unless($this->authorization->canManage($request->user(), $tenant), 403);
        abort_unless($invitation->isPending(), 422);

        $result = $this->createInvitation->execute($request->user(), $tenant, $invitation->email);
        $this->notify($result->invitation, $result->plainToken);

        return back()->with('status', 'تم إرسال الدعوة مرة أخرى.');
    }

    public function revoke(RevokeInvitationRequest $request, Invitation $invitation): RedirectResponse
    {
        $tenant = $this->context->tenant();
        abort_unless((int) $invitation->tenant_id === (int) $tenant->getKey(), 404);
        abort_unless($this->authorization->canManage(request()->user(), $tenant), 403);

        if ($invitation->isPending()) {
            $invitation->update(['status' => Invitation::STATUS_REVOKED, 'revoked_at' => now()]);
        }

        return back()->with('status', 'تم إلغاء الدعوة.');
    }

    private function notify(Invitation $invitation, string $plainToken): void
    {
        Notification::route('mail', $invitation->email)
            ->notify(new InvitationNotification($invitation->load('tenant'), $plainToken));
    }
}
