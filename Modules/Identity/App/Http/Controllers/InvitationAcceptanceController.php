<?php

namespace Modules\Identity\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Modules\Identity\App\Actions\AcceptInvitationAction;
use Modules\Identity\App\Http\Requests\AcceptInvitationRequest;
use Modules\Identity\App\Models\Invitation;
use Modules\Identity\App\Models\User;

final class InvitationAcceptanceController extends Controller
{
    public function __construct(private readonly AcceptInvitationAction $acceptInvitation) {}

    public function show(Request $request, Invitation $invitation, string $token): View|RedirectResponse
    {
        $this->acceptInvitation->assertUsable($invitation->load('tenant'), $token);
        $existingUser = User::query()->where('email', $invitation->email)->first();

        if ($existingUser !== null && ! $request->user()) {
            return view('identity::invitations.login-required', ['invitation' => $invitation]);
        }

        if ($existingUser !== null) {
            $this->assertAuthenticatedRecipient($request, $invitation);

            return view('identity::invitations.accept-existing', [
                'invitation' => $invitation,
                'token' => $token,
            ]);
        }

        return view('identity::invitations.accept', [
            'invitation' => $invitation,
            'token' => $token,
        ]);
    }

    public function store(AcceptInvitationRequest $request, Invitation $invitation, string $token): RedirectResponse
    {
        $invitation->load('tenant');
        $existingUser = User::query()->where('email', $invitation->email)->first();

        if ($existingUser !== null) {
            $this->assertAuthenticatedRecipient($request, $invitation);
            $this->acceptInvitation->execute($invitation, $token, $request->user());
        } else {
            $this->acceptInvitation->executeForNewUser($invitation, $token, $request->string('name')->toString(), $request->string('password')->toString());
        }

        return redirect()->intended('/home');
    }

    private function assertAuthenticatedRecipient(Request $request, Invitation $invitation): void
    {
        $user = $request->user();

        if (! $user instanceof User || strtolower($user->email) !== strtolower($invitation->email)) {
            Auth::logout();
            throw ValidationException::withMessages(['invitation' => 'Sign in with the invited email address.']);
        }
    }
}
