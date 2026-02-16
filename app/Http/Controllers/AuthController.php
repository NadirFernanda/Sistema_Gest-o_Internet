<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        try {
            Log::info('auth.showLoginForm', ['session_id' => session()->getId(), 'page_csrf' => csrf_token(), 'session_cookie' => request()->cookie('laravel-session')]);
        } catch (\Throwable $e) {
            // ignore logging failures during debug
        }
        if (Auth::check()) {
            return redirect('/dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        try {
            Log::info('auth.login.attempt', ['session_id' => session()->getId(), 'cookie' => $request->cookie('laravel-session'), 'input_csrf' => $request->input('_token')]);
        } catch (\Throwable $e) {
            // ignore
        }
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            try {
                Log::info('auth.login.success', ['new_session_id' => session()->getId()]);
            } catch (\Throwable $e) {}
            return redirect()->intended('/dashboard');
        }

        // Verifica se o email existe
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return back()->withErrors([
                'email' => 'O email informado não está cadastrado.'
            ])->withInput($request->only('email'));
        }

        // Email existe, então a senha está incorreta
        return back()->withErrors([
            'password' => 'A senha informada está incorreta.'
        ])->withInput($request->only('email'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
