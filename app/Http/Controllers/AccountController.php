<?php

namespace App\Http\Controllers;

use App\Models\UserPaymentMethod;
use App\Models\UserNotificationPreference;
use App\Models\UserDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();

        return view('account.index', [
            'user' => $user,
        ]);
    }

    public function getData(Request $request)
    {
        $user = Auth::user();

        $paymentMethods = $user->paymentMethods()
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $notificationPrefs = $user->notificationPreferences;
        if (!$notificationPrefs) {
            $notificationPrefs = UserNotificationPreference::create(['user_id' => $user->id]);
        }

        $clientIp = $request->ip();
        $devices = $user->devices()
            ->orderBy('last_seen_at', 'desc')
            ->get()
            ->map(function ($device) use ($clientIp) {
                $device->is_current = $device->ip_address === $clientIp;
                return $device;
            });

        return response()->json([
            'success' => true,
            'profile' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
            'paymentMethods' => $paymentMethods,
            'notificationPrefs' => $notificationPrefs,
            'devices' => $devices,
        ]);
    }

    public function updateName(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|min:2|max:100',
            'last_name' => 'required|string|min:2|max:100',
        ]);

        $user = Auth::user();
        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
        ]);

        return response()->json([
            'success' => true,
            'user' => [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
            ],
        ]);
    }

    public function updateEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid password',
            ], 401);
        }

        $existingUser = \App\Models\User::where('email', strtolower($request->email))
            ->where('id', '!=', $user->id)
            ->first();

        if ($existingUser) {
            return response()->json([
                'success' => false,
                'error' => 'Email is already in use',
            ], 400);
        }

        $user->update(['email' => strtolower($request->email)]);

        return response()->json([
            'success' => true,
            'user' => ['email' => $user->email],
        ]);
    }

    public function updatePhone(Request $request)
    {
        $request->validate([
            'phone' => 'nullable|string|max:20',
        ]);

        $user = Auth::user();
        $user->update(['phone' => $request->phone]);

        return response()->json([
            'success' => true,
            'user' => ['phone' => $user->phone],
        ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'error' => 'Current password is incorrect',
            ], 401);
        }

        $user->update(['password' => Hash::make($request->new_password)]);

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully',
        ]);
    }

    public function getPaymentMethods()
    {
        $paymentMethods = Auth::user()->paymentMethods()
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    public function addPaymentMethod(Request $request)
    {
        $request->validate([
            'card_type' => 'required|string',
            'card_last4' => 'required|string|size:4',
            'card_holder_name' => 'required|string',
            'expiry_month' => 'required|integer|between:1,12',
            'expiry_year' => 'required|integer|min:' . date('Y'),
        ]);

        $user = Auth::user();
        $isDefault = $request->boolean('is_default');

        if ($isDefault) {
            $user->paymentMethods()->update(['is_default' => false]);
        }

        $existingCount = $user->paymentMethods()->count();

        $paymentMethod = $user->paymentMethods()->create([
            'card_type' => $request->card_type,
            'card_last4' => $request->card_last4,
            'card_holder_name' => $request->card_holder_name,
            'expiry_month' => $request->expiry_month,
            'expiry_year' => $request->expiry_year,
            'is_default' => $isDefault || $existingCount === 0,
            'gateway' => $request->gateway,
            'gateway_customer_id' => $request->gateway_customer_id,
            'gateway_payment_method_id' => $request->gateway_payment_method_id,
        ]);

        return response()->json([
            'success' => true,
            'paymentMethod' => $paymentMethod,
        ], 201);
    }

    public function setDefaultPaymentMethod($id)
    {
        $user = Auth::user();
        $paymentMethod = $user->paymentMethods()->findOrFail($id);

        $user->paymentMethods()->update(['is_default' => false]);
        $paymentMethod->update(['is_default' => true]);

        return response()->json([
            'success' => true,
            'paymentMethod' => $paymentMethod,
        ]);
    }

    public function deletePaymentMethod($id)
    {
        $user = Auth::user();
        $paymentMethod = $user->paymentMethods()->findOrFail($id);
        $wasDefault = $paymentMethod->is_default;

        $paymentMethod->delete();

        if ($wasDefault) {
            $newest = $user->paymentMethods()->orderBy('created_at', 'desc')->first();
            if ($newest) {
                $newest->update(['is_default' => true]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment method removed',
        ]);
    }

    public function getNotifications()
    {
        $user = Auth::user();
        $prefs = $user->notificationPreferences;

        if (!$prefs) {
            $prefs = UserNotificationPreference::create(['user_id' => $user->id]);
        }

        return response()->json([
            'success' => true,
            'preferences' => $prefs,
        ]);
    }

    public function updateNotifications(Request $request)
    {
        $user = Auth::user();

        $validKeys = [
            'event_updates_email', 'event_updates_sms', 'event_updates_push',
            'voting_reminder_email', 'voting_reminder_sms', 'voting_reminder_push',
            'results_available_email', 'results_available_sms', 'results_available_push',
            'subscription_email', 'subscription_sms', 'subscription_push',
            'payment_email', 'payment_sms', 'payment_push',
            'security_email', 'security_sms', 'security_push',
        ];

        $updates = [];
        foreach ($validKeys as $key) {
            if ($request->has($key)) {
                $updates[$key] = $request->boolean($key);
            }
        }

        $prefs = UserNotificationPreference::updateOrCreate(
            ['user_id' => $user->id],
            $updates
        );

        return response()->json([
            'success' => true,
            'preferences' => $prefs,
        ]);
    }

    public function getDevices(Request $request)
    {
        $user = Auth::user();
        $clientIp = $request->ip();

        $devices = $user->devices()
            ->orderBy('last_seen_at', 'desc')
            ->get()
            ->map(function ($device) use ($clientIp) {
                $device->is_current = $device->ip_address === $clientIp;
                return $device;
            });

        return response()->json([
            'success' => true,
            'devices' => $devices,
        ]);
    }

    public function signOutDevice($id)
    {
        $user = Auth::user();
        $device = $user->devices()->findOrFail($id);
        $device->delete();

        return response()->json([
            'success' => true,
            'message' => 'Device signed out',
        ]);
    }

    public function signOutAllDevices(Request $request)
    {
        $user = Auth::user();
        $clientIp = $request->ip();

        $user->devices()->where('ip_address', '!=', $clientIp)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Signed out of all other devices',
        ]);
    }
}
