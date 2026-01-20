<?php

namespace App\Http\Controllers;

use App\Services\TrialCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TrialCodeController extends Controller
{
    protected TrialCodeService $trialCodeService;

    public function __construct(TrialCodeService $trialCodeService)
    {
        $this->trialCodeService = $trialCodeService;
    }

    /**
     * Request a new trial code (AJAX endpoint for modal)
     */
    public function request(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'organization' => 'nullable|string|max:255',
            'delivery_method' => 'required|in:email,sms',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Require phone if SMS delivery selected
        if ($request->delivery_method === 'sms' && empty($request->phone)) {
            return response()->json([
                'success' => false,
                'errors' => ['phone' => ['Phone number is required for SMS delivery.']],
            ], 422);
        }

        $result = $this->trialCodeService->requestTrialCode([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'organization' => $request->organization,
            'delivery_method' => $request->delivery_method,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'error' => $result['error'],
                'error_type' => $result['error_type'] ?? 'unknown',
            ], 400);
        }

        $deliveryMethod = $result['trial_code']->delivery_method === 'sms' ? 'SMS' : 'email';

        return response()->json([
            'success' => true,
            'message' => "Your trial code has been sent via {$deliveryMethod}. Check your {$deliveryMethod} and use the code to register.",
        ]);
    }

    /**
     * Validate a trial code (AJAX endpoint)
     */
    public function validateCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:10',
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'valid' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->trialCodeService->validateCode(
            $request->code,
            $request->email
        );

        return response()->json($result);
    }

    /**
     * Resend trial code
     */
    public function resend(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $trialCode = \App\Models\TrialCode::byEmail($request->email)
            ->active()
            ->pendingRedemption()
            ->first();

        if (!$trialCode) {
            return response()->json([
                'success' => false,
                'error' => 'No active trial code found for this email address.',
            ], 404);
        }

        $result = $this->trialCodeService->resendCode($trialCode);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'error' => $result['error'],
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Your trial code has been resent.',
        ]);
    }
}
