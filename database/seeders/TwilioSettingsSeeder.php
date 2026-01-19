<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class TwilioSettingsSeeder extends Seeder
{
    public function run(): void
    {
        // Seed Twilio settings from environment variables
        SystemSetting::setValue(
            'twilio',
            'account_sid',
            env('TWILIO_ACCOUNT_SID', ''),
            true,
            'string',
            'Twilio Account SID'
        );

        SystemSetting::setValue(
            'twilio',
            'auth_token',
            env('TWILIO_AUTH_TOKEN', ''),
            true,
            'string',
            'Twilio Auth Token'
        );

        SystemSetting::setValue(
            'twilio',
            'from_number',
            env('TWILIO_PHONE_NUMBER', ''),
            false,
            'string',
            'Twilio From Phone Number'
        );

        SystemSetting::setValue(
            'twilio',
            'is_enabled',
            '1',
            false,
            'boolean',
            'Enable Twilio SMS'
        );

        SystemSetting::setValue(
            'twilio',
            'test_mode',
            '0',
            false,
            'boolean',
            'Twilio Test Mode'
        );
    }
}
