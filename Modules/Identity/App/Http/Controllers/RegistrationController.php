<?php

namespace Modules\Identity\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Modules\Identity\App\Actions\RegisterOwnerAction;
use Modules\Identity\App\Http\Requests\RegisterOwnerRequest;

class RegistrationController extends Controller
{
    public function __construct(private readonly RegisterOwnerAction $registerOwner) {}

    public function create(): View
    {
        return view('identity::auth.register');
    }

    public function store(RegisterOwnerRequest $request): RedirectResponse
    {
        $this->registerOwner->execute($request->toData());

        return redirect()->intended('/home');
    }
}
