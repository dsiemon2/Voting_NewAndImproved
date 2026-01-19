<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Twilio\Rest\Client as TwilioClient;
use Exception;

class TwilioSettingsController extends Controller
{
    /**
     * Display Twilio settings
     */
    public function index()
    {
        $settings = SystemSetting::getGroup('twilio');

        // Set defaults if not configured
        $settings = array_merge([
            'account_sid' => '',
            'auth_token' => '',
            'from_number' => '',
            'is_enabled' => false,
            'test_mode' => true,
        ], $settings);

        // Mask sensitive data for display
        $maskedSettings = $settings;
        if (!empty($settings['account_sid'])) {
            $maskedSettings['account_sid_masked'] = substr($settings['account_sid'], 0, 6) . '...' . substr($settings['account_sid'], -4);
        }
        if (!empty($settings['auth_token'])) {
            $maskedSettings['auth_token_masked'] = '****' . substr($settings['auth_token'], -4);
        }

        return view('admin.twilio-settings.index', [
            'settings' => $maskedSettings,
            'hasCredentials' => !empty($settings['account_sid']) && !empty($settings['auth_token']),
        ]);
    }

    /**
     * Update Twilio settings
     */
    public function update(Request $request)
    {
        $request->validate([
            'account_sid' => 'nullable|string|max:100',
            'auth_token' => 'nullable|string|max:100',
            'from_number' => 'nullable|string|max:20',
            'is_enabled' => 'boolean',
            'test_mode' => 'boolean',
        ]);

        // Only update credentials if provided (not empty placeholder)
        if ($request->filled('account_sid') && !str_contains($request->account_sid, '...')) {
            SystemSetting::setValue('twilio', 'account_sid', $request->account_sid, true, 'string', 'Twilio Account SID');
        }

        if ($request->filled('auth_token') && !str_contains($request->auth_token, '****')) {
            SystemSetting::setValue('twilio', 'auth_token', $request->auth_token, true, 'string', 'Twilio Auth Token');
        }

        if ($request->filled('from_number')) {
            SystemSetting::setValue('twilio', 'from_number', $request->from_number, false, 'string', 'Twilio From Phone Number');
        }

        SystemSetting::setValue('twilio', 'is_enabled', $request->boolean('is_enabled') ? '1' : '0', false, 'boolean', 'Enable Twilio SMS');
        SystemSetting::setValue('twilio', 'test_mode', $request->boolean('test_mode') ? '1' : '0', false, 'boolean', 'Twilio Test Mode');

        // Also update config/services.php values in .env style for compatibility
        $this->updateEnvFile($request);

        return redirect()->route('admin.twilio-settings.index')
            ->with('success', 'Twilio settings have been updated.');
    }

    /**
     * Test Twilio connection
     */
    public function testConnection(Request $request)
    {
        $settings = SystemSetting::getGroup('twilio');

        if (empty($settings['account_sid']) || empty($settings['auth_token'])) {
            return response()->json([
                'success' => false,
                'error' => 'Twilio credentials are not configured.',
            ]);
        }

        try {
            $client = new TwilioClient($settings['account_sid'], $settings['auth_token']);

            // Test by fetching account info
            $account = $client->api->v2010->accounts($settings['account_sid'])->fetch();

            return response()->json([
                'success' => true,
                'message' => 'Connection successful! Account: ' . $account->friendlyName,
                'account_status' => $account->status,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Connection failed: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Send test SMS
     */
    public function sendTestSms(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:20',
        ]);

        $settings = SystemSetting::getGroup('twilio');

        if (empty($settings['account_sid']) || empty($settings['auth_token']) || empty($settings['from_number'])) {
            return response()->json([
                'success' => false,
                'error' => 'Twilio is not fully configured. Please set Account SID, Auth Token, and From Number.',
            ]);
        }

        try {
            $client = new TwilioClient($settings['account_sid'], $settings['auth_token']);

            $message = $client->messages->create(
                $request->phone,
                [
                    'from' => $settings['from_number'],
                    'body' => 'This is a test message from My Voting Software. If you received this, your Twilio integration is working correctly!',
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Test SMS sent successfully!',
                'message_sid' => $message->sid,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to send SMS: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Update .env file with Twilio settings for config/services.php compatibility
     */
    protected function updateEnvFile(Request $request): void
    {
        $envPath = base_path('.env');

        if (!file_exists($envPath)) {
            return;
        }

        $envContent = file_get_contents($envPath);

        $updates = [];

        if ($request->filled('account_sid') && !str_contains($request->account_sid, '...')) {
            $updates['TWILIO_SID'] = $request->account_sid;
        }

        if ($request->filled('auth_token') && !str_contains($request->auth_token, '****')) {
            $updates['TWILIO_TOKEN'] = $request->auth_token;
        }

        if ($request->filled('from_number')) {
            $updates['TWILIO_FROM'] = $request->from_number;
        }

        foreach ($updates as $key => $value) {
            if (preg_match("/^{$key}=.*/m", $envContent)) {
                $envContent = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $envContent);
            } else {
                $envContent .= "\n{$key}={$value}";
            }
        }

        file_put_contents($envPath, $envContent);
    }
}
