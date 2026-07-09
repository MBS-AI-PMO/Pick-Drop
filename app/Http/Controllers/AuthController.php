<?php

namespace App\Http\Controllers;

use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;

class AuthController extends Controller
{
    /**
     * Handle an authentication attempt.
     */
  public function login(Request $request)
{
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    $remember = $request->boolean('remember');

    if (Auth::attempt($credentials, $remember)) {

        $request->session()->regenerate();

        // Sirf Admin login kar sakta hai
        if (Auth::user()->role !== 'Admin') {

            Auth::logout();

            return back()->with('error', 'Access denied. Only Admin can login.');
        }

        return redirect()
            ->intended(route('dashboard'))
            ->with('success', 'Logged in successfully.');
    }

    return back()->withErrors([
        'email' => 'These credentials do not match our records.',
    ])->onlyInput('email');
}

    /**
     * Handle user registration.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
          
            'password' => $validated['password'], // Will be hashed by User model cast
        ]);

        return redirect()
            ->route('login')
            ->with('success', 'Account created successfully! Welcome aboard.');
    }

    /**
     * Handle a forgot password request.
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('success', __($status));
        }

        return back()->withErrors([
            'email' => __($status),
        ])->onlyInput('email');
    }
    public function showResetPasswordForm(string $token)
{
    return view('pages.auth.reset-password', [
        'token' => $token,
        'email' => request()->email,
    ]);
}

public function resetPassword(Request $request)
{
    $request->validate([
        'token' => ['required'],
        'email' => ['required', 'email'],
        'password' => ['required', 'confirmed', 'min:8'],
    ]);

    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),

        function ($user, $password) {
            $user->forceFill([
                'password' => Hash::make($password),
                'remember_token' => Str::random(60),
            ])->save();

            event(new PasswordReset($user));
        }
    );

    if ($status == Password::PASSWORD_RESET) {
        return redirect()
            ->route('login')
            ->with('success', 'Password has been reset successfully.');
    }

    return back()->withErrors([
        'email' => __($status),
    ]);
}
}

