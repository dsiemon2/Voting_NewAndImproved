<?php

namespace App\Services;

use App\Models\TrialCode;
use App\Models\User;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client as TwilioClient;
use Exception;

class TrialCodeService
{
    /**
     * Characters used for code generation (excluding confusing chars: 0, O, I, L, 1)
     */
    const CODE_CHARACTERS = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    /**
     * Generate a unique trial code
     * Format: XXXX-XXXX
     */
    public function generateCode(): string
    {
        $maxAttempts = 10;

        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $code = '';
            $chars = self::CODE_CHARACTERS;
            $charLength = strlen($chars);

            for ($i = 0; $i < 8; $i++) {
                $code .= $chars[random_int(0, $charLength - 1)];
            }

            // Format as XXXX-XXXX
            $formattedCode = substr($code, 0, 4) . '-' . substr($code, 4, 4);

            // Check uniqueness
            if (!TrialCode::where('code', $formattedCode)->exists()) {
                return $formattedCode;
            }
        }

        throw new Exception('Unable to generate unique trial code after ' . $maxAttempts . ' attempts');
    }

    /**
     * Request a new trial code
     */
    public function requestTrialCode(array $data): array
    {
        $email = strtolower($data['email']);
        $phone = $data['phone'] ?? null;

        // Check if email already has an active code
        if (TrialCode::emailHasActiveCode($email)) {
            return [
                'success' => false,
                'error' => 'A trial code has already been sent to this email address.',
                'error_type' => 'duplicate_email',
            ];
        }

        // Check if phone already has an active code (if phone provided)
        if ($phone && TrialCode::phoneHasActiveCode($phone)) {
            return [
                'success' => false,
                'error' => 'A trial code has already been sent to this phone number.',
                'error_type' => 'duplicate_phone',
            ];
        }

        // Generate unique code
        $code = $this->generateCode();

        // Calculate expiration (7 days for unredeemed codes)
        $expiresAt = now()->addDays(TrialCode::UNREDEEMED_EXPIRY_DAYS);

        // Create trial code record
        $trialCode = TrialCode::create([
            'code' => $code,
            'requester_first_name' => $data['first_name'],
            'requester_last_name' => $data['last_name'],
            'requester_email' => $email,
            'requester_phone' => $phone,
            'requester_organization' => $data['organization'] ?? null,
            'delivery_method' => $data['delivery_method'] ?? 'email',
            'status' => TrialCode::STATUS_PENDING,
            'expires_at' => $expiresAt,
            'ip_address' => $data['ip_address'] ?? null,
            'user_agent' => $data['user_agent'] ?? null,
        ]);

        // Send the code
        $sendResult = $this->sendCode($trialCode);

        if ($sendResult['success']) {
            $trialCode->update([
                'status' => TrialCode::STATUS_SENT,
                'sent_at' => now(),
            ]);
        }

        return [
            'success' => true,
            'trial_code' => $trialCode,
            'delivery_result' => $sendResult,
        ];
    }

    /**
     * Send trial code via email or SMS
     */
    public function sendCode(TrialCode $trialCode): array
    {
        if ($trialCode->delivery_method === 'sms' && $trialCode->requester_phone) {
            return $this->sendSms($trialCode);
        }

        return $this->sendEmail($trialCode);
    }

    /**
     * Send trial code via email
     */
    protected function sendEmail(TrialCode $trialCode): array
    {
        try {
            $registrationUrl = route('register', ['plan' => 'free', 'code' => $trialCode->code]);

            Mail::send('emails.trial-code', [
                'trialCode' => $trialCode,
                'registrationUrl' => $registrationUrl,
            ], function ($message) use ($trialCode) {
                $message->to($trialCode->requester_email, $trialCode->requester_full_name)
                    ->subject('Your My Voting Software Trial Code');
            });

            Log::info('Trial code email sent', ['code' => $trialCode->code, 'email' => $trialCode->requester_email]);

            return ['success' => true, 'method' => 'email'];
        } catch (Exception $e) {
            Log::error('Failed to send trial code email', [
                'code' => $trialCode->code,
                'email' => $trialCode->requester_email,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'method' => 'email', 'error' => $e->getMessage()];
        }
    }

    /**
     * Send trial code via SMS using Twilio
     */
    protected function sendSms(TrialCode $trialCode): array
    {
        try {
            $sid = config('services.twilio.sid');
            $token = config('services.twilio.token');
            $from = config('services.twilio.from');

            if (!$sid || !$token || !$from) {
                throw new Exception('Twilio credentials not configured');
            }

            $client = new TwilioClient($sid, $token);

            $registrationUrl = route('register', ['plan' => 'free', 'code' => $trialCode->code]);
            $shortUrl = $registrationUrl; // Could use a URL shortener here

            $message = "My Voting Software Trial Code: {$trialCode->code}\n\n" .
                "Register at: {$shortUrl}\n\n" .
                "Expires: {$trialCode->expires_at->format('M d, Y')}";

            $client->messages->create(
                $trialCode->requester_phone,
                [
                    'from' => $from,
                    'body' => $message,
                ]
            );

            Log::info('Trial code SMS sent', ['code' => $trialCode->code, 'phone' => $trialCode->requester_phone]);

            return ['success' => true, 'method' => 'sms'];
        } catch (Exception $e) {
            Log::error('Failed to send trial code SMS', [
                'code' => $trialCode->code,
                'phone' => $trialCode->requester_phone,
                'error' => $e->getMessage(),
            ]);

            // Fallback to email
            Log::info('Falling back to email for trial code delivery');
            return $this->sendEmail($trialCode);
        }
    }

    /**
     * Validate a trial code for redemption
     */
    public function validateCode(string $code, string $email): array
    {
        $trialCode = TrialCode::findByCode($code);

        if (!$trialCode) {
            return [
                'valid' => false,
                'error' => 'Invalid trial code.',
            ];
        }

        if ($trialCode->isExpired()) {
            return [
                'valid' => false,
                'error' => 'This trial code has expired.',
            ];
        }

        if ($trialCode->status === TrialCode::STATUS_REDEEMED) {
            return [
                'valid' => false,
                'error' => 'This trial code has already been used.',
            ];
        }

        if ($trialCode->status === TrialCode::STATUS_REVOKED) {
            return [
                'valid' => false,
                'error' => 'This trial code has been revoked.',
            ];
        }

        if (strtolower($trialCode->requester_email) !== strtolower($email)) {
            return [
                'valid' => false,
                'error' => 'This trial code was issued to a different email address.',
            ];
        }

        return [
            'valid' => true,
            'trial_code' => $trialCode,
        ];
    }

    /**
     * Redeem a trial code for a user
     */
    public function redeemCode(TrialCode $trialCode, User $user): array
    {
        // Update trial code
        $trialCode->update([
            'status' => TrialCode::STATUS_REDEEMED,
            'user_id' => $user->id,
            'redeemed_at' => now(),
            'expires_at' => now()->addDays(TrialCode::TRIAL_PERIOD_DAYS),
        ]);

        // Create free trial subscription
        $freePlan = SubscriptionPlan::where('code', 'free')->first();

        if ($freePlan) {
            UserSubscription::create([
                'user_id' => $user->id,
                'subscription_plan_id' => $freePlan->id,
                'status' => 'active',
                'trial_ends_at' => $trialCode->expires_at,
                'current_period_start' => now(),
                'current_period_end' => $trialCode->expires_at,
            ]);
        }

        return [
            'success' => true,
            'trial_code' => $trialCode->fresh(),
            'trial_ends_at' => $trialCode->expires_at,
        ];
    }

    /**
     * Extend a trial code (admin only)
     */
    public function extendTrial(TrialCode $trialCode, User $admin): array
    {
        if (!$trialCode->canBeExtended()) {
            return [
                'success' => false,
                'error' => $trialCode->extension_count >= TrialCode::MAX_EXTENSIONS
                    ? 'Maximum extensions (' . TrialCode::MAX_EXTENSIONS . ') reached for this trial.'
                    : 'This trial code cannot be extended.',
            ];
        }

        // Generate new extension code
        $newCode = $this->generateCode();
        $newExpiresAt = now()->addDays(TrialCode::TRIAL_PERIOD_DAYS);

        // Create extension code record
        $extensionCode = TrialCode::create([
            'code' => $newCode,
            'requester_first_name' => $trialCode->requester_first_name,
            'requester_last_name' => $trialCode->requester_last_name,
            'requester_email' => $trialCode->requester_email,
            'requester_phone' => $trialCode->requester_phone,
            'requester_organization' => $trialCode->requester_organization,
            'delivery_method' => $trialCode->delivery_method,
            'user_id' => $trialCode->user_id,
            'status' => TrialCode::STATUS_REDEEMED,
            'sent_at' => now(),
            'redeemed_at' => now(),
            'expires_at' => $newExpiresAt,
            'extension_count' => $trialCode->extension_count + 1,
            'extended_by' => $admin->id,
            'last_extended_at' => now(),
            'parent_code_id' => $trialCode->parent_code_id ?? $trialCode->id,
        ]);

        // Update user's subscription
        if ($trialCode->user) {
            $subscription = $trialCode->user->activeSubscription();
            if ($subscription) {
                $subscription->update([
                    'trial_ends_at' => $newExpiresAt,
                    'current_period_end' => $newExpiresAt,
                ]);
            }
        }

        // Mark original as expired (replaced by extension)
        $trialCode->update([
            'status' => TrialCode::STATUS_EXPIRED,
        ]);

        // Send notification to user
        $this->sendExtensionNotification($extensionCode);

        Log::info('Trial extended by admin', [
            'original_code' => $trialCode->code,
            'new_code' => $newCode,
            'admin_id' => $admin->id,
            'extension_count' => $extensionCode->extension_count,
        ]);

        return [
            'success' => true,
            'original_code' => $trialCode,
            'extension_code' => $extensionCode,
            'new_expires_at' => $newExpiresAt,
        ];
    }

    /**
     * Send extension notification
     */
    protected function sendExtensionNotification(TrialCode $trialCode): void
    {
        try {
            Mail::send('emails.trial-extended', [
                'trialCode' => $trialCode,
            ], function ($message) use ($trialCode) {
                $message->to($trialCode->requester_email, $trialCode->requester_full_name)
                    ->subject('Your Trial Has Been Extended!');
            });
        } catch (Exception $e) {
            Log::error('Failed to send trial extension notification', [
                'code' => $trialCode->code,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Revoke a trial code (admin only)
     */
    public function revokeCode(TrialCode $trialCode, User $admin, ?string $reason = null): array
    {
        if (!$trialCode->canBeRevoked()) {
            return [
                'success' => false,
                'error' => 'This trial code cannot be revoked.',
            ];
        }

        $trialCode->update([
            'status' => TrialCode::STATUS_REVOKED,
        ]);

        Log::info('Trial code revoked by admin', [
            'code' => $trialCode->code,
            'admin_id' => $admin->id,
            'reason' => $reason,
        ]);

        return [
            'success' => true,
            'trial_code' => $trialCode->fresh(),
        ];
    }

    /**
     * Resend trial code
     */
    public function resendCode(TrialCode $trialCode): array
    {
        if (!in_array($trialCode->status, [TrialCode::STATUS_PENDING, TrialCode::STATUS_SENT])) {
            return [
                'success' => false,
                'error' => 'Cannot resend this trial code.',
            ];
        }

        if ($trialCode->isExpired()) {
            return [
                'success' => false,
                'error' => 'This trial code has expired.',
            ];
        }

        $result = $this->sendCode($trialCode);

        if ($result['success']) {
            $trialCode->update([
                'status' => TrialCode::STATUS_SENT,
                'sent_at' => now(),
            ]);
        }

        return $result;
    }

    /**
     * Expire old unredeemed codes (for scheduled job)
     */
    public function expireOldCodes(): int
    {
        return TrialCode::whereIn('status', [TrialCode::STATUS_PENDING, TrialCode::STATUS_SENT])
            ->where('expires_at', '<', now())
            ->update(['status' => TrialCode::STATUS_EXPIRED]);
    }
}
