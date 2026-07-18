<?php

namespace Modules\Identity\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Modules\Identity\App\Http\Requests\LoginRequest;

final class LoginController extends Controller
{
    public function create(Request $request): View
    {
        $intended = $request->query('url');

        if (is_string($intended) && parse_url($intended, PHP_URL_HOST) === $request->getHost()) {
            $request->session()->put('url.intended', $intended);
        }

        return view('identity::auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();

        $credentials['email'] = strtolower(trim($credentials['email']));
        $credentials['status'] = 'active';

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages(['email' => 'بيانات الدخول غير صحيحة.']);
        }

        $request->session()->regenerate();

        return redirect()->intended('/home');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
