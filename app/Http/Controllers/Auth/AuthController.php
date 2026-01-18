<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Log successful login
            AuditLog::log('login', 'user', auth()->id());

            return redirect()->intended(route('dashboard'));
        }

        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }

    /**
     * Show registration form
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Handle registration request
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // Get the default "User" role
        $userRole = Role::where('name', 'User')->first();

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $userRole?->id,
            'is_active' => true,
        ]);

        // Log the registration
        AuditLog::log('register', 'user', $user->id);

        // Log the user in
        Auth::login($user);

        return redirect()->route('dashboard')
            ->with('success', 'Welcome! Your account has been created.');
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        // Log logout
        if (auth()->check()) {
            AuditLog::log('logout', 'user', auth()->id());
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Clear the managed event cookie so next login starts fresh
        Cookie::queue(Cookie::forget('managing_event_id'));

        return redirect()->route('login');
    }
}
