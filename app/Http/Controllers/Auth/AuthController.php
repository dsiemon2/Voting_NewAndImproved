<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Models\Role;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
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
    public function showRegister(Request $request)
    {
        $planCode = $request->query('plan', 'free');
        $plan = SubscriptionPlan::where('code', $planCode)->first();

        return view('auth.register', [
            'selectedPlan' => $plan,
            'planCode' => $planCode,
        ]);
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
            'plan' => ['nullable', 'string'],
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

        // Assign the selected plan (default to free)
        $planCode = $validated['plan'] ?? 'free';
        $plan = SubscriptionPlan::where('code', $planCode)->first();

        if ($plan) {
            // For free plan, create subscription directly
            // For paid plans, redirect to pricing/checkout
            if ($plan->price == 0) {
                UserSubscription::create([
                    'user_id' => $user->id,
                    'subscription_plan_id' => $plan->id,
                    'status' => 'active',
                    'current_period_start' => now(),
                    'current_period_end' => now()->addYear(),
                ]);
            } else {
                // Store selected plan in session for checkout
                session(['selected_plan_id' => $plan->id]);
            }
        }

        // Log the user in
        Auth::login($user);

        // Redirect to checkout for paid plans, dashboard for free
        if ($plan && $plan->price > 0) {
            return redirect()->route('subscription.pricing')
                ->with('success', 'Account created! Complete your subscription to get started.');
        }

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
