<?php

namespace Database\Seeders;

use App\Models\TrialCode;
use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TrialCodeSeeder extends Seeder
{
    /**
     * Seed the trial_codes table with sample data.
     *
     * This seeder demonstrates proper relationships:
     * - trial_codes.user_id -> users.id (redeemed codes link to registered users)
     * - trial_codes.extended_by -> users.id (admin who extended the trial)
     */
    public function run(): void
    {
        // Get admin user for extended_by reference
        $adminUser = User::whereHas('role', function ($query) {
            $query->where('name', 'Administrator');
        })->first();

        if (!$adminUser) {
            $this->command->warn('No admin user found. Creating default admin.');
            $adminRole = Role::where('name', 'Administrator')->first();
            $adminUser = User::create([
                'first_name' => 'Admin',
                'last_name' => 'User',
                'email' => 'admin@example.com',
                'password' => Hash::make('Password123!'),
                'role_id' => $adminRole?->id ?? 1,
                'is_active' => true,
            ]);
        }

        // Get regular user role
        $userRole = Role::where('name', '!=', 'Administrator')->first();
        $userRoleId = $userRole?->id ?? 2;

        // Create trial users that will have redeemed codes
        // These users represent people who requested a trial code and then registered
        $trialUser1 = User::firstOrCreate(
            ['email' => 'sarah.johnson@example.com'],
            [
                'first_name' => 'Sarah',
                'last_name' => 'Johnson',
                'password' => Hash::make('Password123!'),
                'role_id' => $userRoleId,
                'is_active' => true,
            ]
        );

        $trialUser2 = User::firstOrCreate(
            ['email' => 'mike.chen@example.com'],
            [
                'first_name' => 'Mike',
                'last_name' => 'Chen',
                'password' => Hash::make('Password123!'),
                'role_id' => $userRoleId,
                'is_active' => true,
            ]
        );

        $this->command->info("Using admin user: {$adminUser->email} (ID: {$adminUser->id})");
        $this->command->info("Created/found trial user: {$trialUser1->email} (ID: {$trialUser1->id})");
        $this->command->info("Created/found trial user: {$trialUser2->email} (ID: {$trialUser2->id})");

        // Clear existing trial codes to avoid duplicates on re-run
        TrialCode::truncate();

        // Sample Trial Codes demonstrating various states

        // 1. REDEEMED - Active trial user (Sarah Johnson)
        // Requester info matches the linked user - this is the typical flow
        TrialCode::create([
            'code' => 'TRL8-K9M2',
            'requester_first_name' => $trialUser1->first_name,
            'requester_last_name' => $trialUser1->last_name,
            'requester_email' => $trialUser1->email,
            'requester_phone' => '+15551234567',
            'requester_organization' => 'Johnson Events LLC',
            'delivery_method' => 'email',
            'user_id' => $trialUser1->id, // Links to users table
            'status' => TrialCode::STATUS_REDEEMED,
            'sent_at' => now()->subDays(10),
            'redeemed_at' => now()->subDays(9),
            'expires_at' => now()->addDays(5),
            'extension_count' => 0,
        ]);

        // 2. REDEEMED + EXTENDED - Mike Chen with 1 extension by admin
        TrialCode::create([
            'code' => 'XYZ4-ABCD',
            'requester_first_name' => $trialUser2->first_name,
            'requester_last_name' => $trialUser2->last_name,
            'requester_email' => $trialUser2->email,
            'requester_phone' => '+15559876543',
            'requester_organization' => 'Chen Photography',
            'delivery_method' => 'sms',
            'user_id' => $trialUser2->id,
            'status' => TrialCode::STATUS_REDEEMED,
            'sent_at' => now()->subDays(25),
            'redeemed_at' => now()->subDays(24),
            'expires_at' => now()->addDays(3),
            'extension_count' => 1,
            'extended_by' => $adminUser->id, // Links to users table (admin)
            'last_extended_at' => now()->subDays(10),
        ]);

        // 3. SENT - Awaiting registration (no user_id yet)
        TrialCode::create([
            'code' => 'NEW7-PQRS',
            'requester_first_name' => 'Emily',
            'requester_last_name' => 'Davis',
            'requester_email' => 'emily.davis@example.com',
            'requester_phone' => null,
            'requester_organization' => 'Davis Community Center',
            'delivery_method' => 'email',
            'user_id' => null, // Not yet registered
            'status' => TrialCode::STATUS_SENT,
            'sent_at' => now()->subDays(2),
            'redeemed_at' => null,
            'expires_at' => now()->addDays(5),
            'extension_count' => 0,
        ]);

        // 4. PENDING - Just created, not yet sent
        TrialCode::create([
            'code' => 'PND9-WXYZ',
            'requester_first_name' => 'Robert',
            'requester_last_name' => 'Wilson',
            'requester_email' => 'robert.wilson@example.com',
            'requester_phone' => '+15552223333',
            'requester_organization' => null,
            'delivery_method' => 'sms',
            'user_id' => null,
            'status' => TrialCode::STATUS_PENDING,
            'sent_at' => null,
            'redeemed_at' => null,
            'expires_at' => now()->addDays(7),
            'extension_count' => 0,
        ]);

        // 5. EXPIRED - Never redeemed
        TrialCode::create([
            'code' => 'EXP3-LMNO',
            'requester_first_name' => 'Jennifer',
            'requester_last_name' => 'Brown',
            'requester_email' => 'jennifer.brown@example.com',
            'requester_phone' => null,
            'requester_organization' => 'Brown Catering',
            'delivery_method' => 'email',
            'user_id' => null,
            'status' => TrialCode::STATUS_EXPIRED,
            'sent_at' => now()->subDays(15),
            'redeemed_at' => null,
            'expires_at' => now()->subDays(8),
            'extension_count' => 0,
        ]);

        // 6. REVOKED - Admin revoked before use
        TrialCode::create([
            'code' => 'REV5-HIJK',
            'requester_first_name' => 'David',
            'requester_last_name' => 'Miller',
            'requester_email' => 'david.miller@example.com',
            'requester_phone' => '+15554445555',
            'requester_organization' => null,
            'delivery_method' => 'email',
            'user_id' => null,
            'status' => TrialCode::STATUS_REVOKED,
            'sent_at' => now()->subDays(5),
            'redeemed_at' => null,
            'expires_at' => now()->addDays(2),
            'extension_count' => 0,
        ]);

        // 7. REDEEMED + MULTIPLE EXTENSIONS (2 of 3 max) - Sarah's second code
        // Requester info matches the linked user (Sarah Johnson)
        TrialCode::create([
            'code' => 'MAX2-DEFG',
            'requester_first_name' => $trialUser1->first_name,
            'requester_last_name' => $trialUser1->last_name,
            'requester_email' => $trialUser1->email,
            'requester_phone' => '+15556667777',
            'requester_organization' => 'Johnson Events LLC - Extended',
            'delivery_method' => 'email',
            'user_id' => $trialUser1->id, // Links to users table
            'status' => TrialCode::STATUS_REDEEMED,
            'sent_at' => now()->subDays(40),
            'redeemed_at' => now()->subDays(39),
            'expires_at' => now()->addDays(10),
            'extension_count' => 2,
            'extended_by' => $adminUser->id,
            'last_extended_at' => now()->subDays(5),
        ]);

        // 8. SENT via SMS - Recent
        TrialCode::create([
            'code' => 'SMS8-NOPQ',
            'requester_first_name' => 'Carlos',
            'requester_last_name' => 'Garcia',
            'requester_email' => 'carlos.garcia@example.com',
            'requester_phone' => '+15558889999',
            'requester_organization' => 'Garcia Events',
            'delivery_method' => 'sms',
            'user_id' => null,
            'status' => TrialCode::STATUS_SENT,
            'sent_at' => now()->subHours(6),
            'redeemed_at' => null,
            'expires_at' => now()->addDays(7),
            'extension_count' => 0,
        ]);

        $this->command->info('');
        $this->command->info('Created 8 sample trial codes:');
        $this->command->info('  - 3 redeemed (linked to users table)');
        $this->command->info('  - 2 sent (awaiting registration)');
        $this->command->info('  - 1 pending (not yet sent)');
        $this->command->info('  - 1 expired');
        $this->command->info('  - 1 revoked');
    }
}

