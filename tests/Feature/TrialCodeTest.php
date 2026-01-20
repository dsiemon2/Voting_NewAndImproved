<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\TrialCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class TrialCodeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(
            ['name' => 'User'],
            ['description' => 'Regular user', 'is_system' => false]
        );
    }

    /**
     * Test trial code can be created
     */
    public function test_trial_code_can_be_created(): void
    {
        $trialCode = TrialCode::create([
            'code' => 'TEST123456',
            'requester_first_name' => 'John',
            'requester_last_name' => 'Doe',
            'requester_email' => 'john@example.com',
            'delivery_method' => 'email',
            'status' => TrialCode::STATUS_PENDING,
            'expires_at' => Carbon::now()->addDays(7),
        ]);

        $this->assertDatabaseHas('trial_codes', [
            'code' => 'TEST123456',
            'requester_email' => 'john@example.com',
        ]);
    }

    /**
     * Test trial code full name accessor
     */
    public function test_trial_code_full_name_accessor(): void
    {
        $trialCode = TrialCode::create([
            'code' => 'TEST123456',
            'requester_first_name' => 'John',
            'requester_last_name' => 'Doe',
            'requester_email' => 'john@example.com',
            'delivery_method' => 'email',
            'status' => TrialCode::STATUS_PENDING,
            'expires_at' => Carbon::now()->addDays(7),
        ]);

        $this->assertEquals('John Doe', $trialCode->requester_full_name);
    }

    /**
     * Test trial code is expired check
     */
    public function test_trial_code_expired_check(): void
    {
        $expiredCode = TrialCode::create([
            'code' => 'EXPIRED123',
            'requester_first_name' => 'Jane',
            'requester_last_name' => 'Doe',
            'requester_email' => 'jane@example.com',
            'delivery_method' => 'email',
            'status' => TrialCode::STATUS_SENT,
            'expires_at' => Carbon::now()->subDay(),
        ]);

        $validCode = TrialCode::create([
            'code' => 'VALID12345',
            'requester_first_name' => 'John',
            'requester_last_name' => 'Doe',
            'requester_email' => 'john@example.com',
            'delivery_method' => 'email',
            'status' => TrialCode::STATUS_SENT,
            'expires_at' => Carbon::now()->addDays(7),
        ]);

        $this->assertTrue($expiredCode->isExpired());
        $this->assertFalse($validCode->isExpired());
    }

    /**
     * Test trial code can be redeemed check
     */
    public function test_trial_code_can_be_redeemed(): void
    {
        $role = Role::where('name', 'User')->first();
        $user = User::factory()->create(['role_id' => $role->id]);

        $redeemableCode = TrialCode::create([
            'code' => 'REDEEM1234',
            'requester_first_name' => 'John',
            'requester_last_name' => 'Doe',
            'requester_email' => 'john@example.com',
            'delivery_method' => 'email',
            'status' => TrialCode::STATUS_SENT,
            'expires_at' => Carbon::now()->addDays(7),
            'user_id' => null,
        ]);

        $alreadyRedeemedCode = TrialCode::create([
            'code' => 'REDEEMED12',
            'requester_first_name' => 'Jane',
            'requester_last_name' => 'Doe',
            'requester_email' => 'jane@example.com',
            'delivery_method' => 'email',
            'status' => TrialCode::STATUS_REDEEMED,
            'expires_at' => Carbon::now()->addDays(7),
            'user_id' => $user->id,
        ]);

        $this->assertTrue($redeemableCode->canBeRedeemed());
        $this->assertFalse($alreadyRedeemedCode->canBeRedeemed());
    }

    /**
     * Test trial code cannot be redeemed when expired
     */
    public function test_expired_code_cannot_be_redeemed(): void
    {
        $expiredCode = TrialCode::create([
            'code' => 'EXPIRED456',
            'requester_first_name' => 'John',
            'requester_last_name' => 'Doe',
            'requester_email' => 'john@example.com',
            'delivery_method' => 'email',
            'status' => TrialCode::STATUS_SENT,
            'expires_at' => Carbon::now()->subDay(),
            'user_id' => null,
        ]);

        $this->assertFalse($expiredCode->canBeRedeemed());
    }

    /**
     * Test trial code extension limits
     */
    public function test_trial_code_extension_limits(): void
    {
        $role = Role::where('name', 'User')->first();
        $user = User::factory()->create(['role_id' => $role->id]);

        $codeWithExtensions = TrialCode::create([
            'code' => 'EXTEND1234',
            'requester_first_name' => 'John',
            'requester_last_name' => 'Doe',
            'requester_email' => 'john@example.com',
            'delivery_method' => 'email',
            'status' => TrialCode::STATUS_REDEEMED,
            'expires_at' => Carbon::now()->addDays(7),
            'user_id' => $user->id,
            'extension_count' => 2,
        ]);

        $maxedOutCode = TrialCode::create([
            'code' => 'MAXED12345',
            'requester_first_name' => 'Jane',
            'requester_last_name' => 'Doe',
            'requester_email' => 'jane@example.com',
            'delivery_method' => 'email',
            'status' => TrialCode::STATUS_REDEEMED,
            'expires_at' => Carbon::now()->addDays(7),
            'user_id' => $user->id,
            'extension_count' => TrialCode::MAX_EXTENSIONS,
        ]);

        $this->assertTrue($codeWithExtensions->canBeExtended());
        $this->assertFalse($maxedOutCode->canBeExtended());
    }

    /**
     * Test trial code remaining extensions
     */
    public function test_trial_code_remaining_extensions(): void
    {
        $code = TrialCode::create([
            'code' => 'REMAIN1234',
            'requester_first_name' => 'John',
            'requester_last_name' => 'Doe',
            'requester_email' => 'john@example.com',
            'delivery_method' => 'email',
            'status' => TrialCode::STATUS_SENT,
            'expires_at' => Carbon::now()->addDays(7),
            'extension_count' => 1,
        ]);

        $expected = TrialCode::MAX_EXTENSIONS - 1;
        $this->assertEquals($expected, $code->remaining_extensions);
    }

    /**
     * Test trial code days remaining
     */
    public function test_trial_code_days_remaining(): void
    {
        $code = TrialCode::create([
            'code' => 'DAYS123456',
            'requester_first_name' => 'John',
            'requester_last_name' => 'Doe',
            'requester_email' => 'john@example.com',
            'delivery_method' => 'email',
            'status' => TrialCode::STATUS_SENT,
            'expires_at' => Carbon::now()->addDays(5)->startOfDay(),
        ]);

        // Days remaining should be 4 or 5 depending on time of day
        $this->assertTrue($code->days_remaining >= 4 && $code->days_remaining <= 5);
    }

    /**
     * Test expired code has zero days remaining
     */
    public function test_expired_code_has_zero_days_remaining(): void
    {
        $code = TrialCode::create([
            'code' => 'ZERO123456',
            'requester_first_name' => 'John',
            'requester_last_name' => 'Doe',
            'requester_email' => 'john@example.com',
            'delivery_method' => 'email',
            'status' => TrialCode::STATUS_SENT,
            'expires_at' => Carbon::now()->subDays(2),
        ]);

        $this->assertEquals(0, $code->days_remaining);
    }

    /**
     * Test trial code can be revoked
     */
    public function test_trial_code_can_be_revoked(): void
    {
        $pendingCode = TrialCode::create([
            'code' => 'PENDING123',
            'requester_first_name' => 'John',
            'requester_last_name' => 'Doe',
            'requester_email' => 'john@example.com',
            'delivery_method' => 'email',
            'status' => TrialCode::STATUS_PENDING,
            'expires_at' => Carbon::now()->addDays(7),
        ]);

        $redeemedCode = TrialCode::create([
            'code' => 'REDEEM4567',
            'requester_first_name' => 'Jane',
            'requester_last_name' => 'Doe',
            'requester_email' => 'jane@example.com',
            'delivery_method' => 'email',
            'status' => TrialCode::STATUS_REDEEMED,
            'expires_at' => Carbon::now()->addDays(7),
        ]);

        $this->assertTrue($pendingCode->canBeRevoked());
        $this->assertFalse($redeemedCode->canBeRevoked());
    }

    /**
     * Test status color accessor
     */
    public function test_status_color_accessor(): void
    {
        $pendingCode = TrialCode::create([
            'code' => 'PEND123456',
            'requester_first_name' => 'John',
            'requester_last_name' => 'Doe',
            'requester_email' => 'john@example.com',
            'delivery_method' => 'email',
            'status' => TrialCode::STATUS_PENDING,
            'expires_at' => Carbon::now()->addDays(7),
        ]);

        $sentCode = TrialCode::create([
            'code' => 'SENT123456',
            'requester_first_name' => 'Jane',
            'requester_last_name' => 'Doe',
            'requester_email' => 'jane@example.com',
            'delivery_method' => 'email',
            'status' => TrialCode::STATUS_SENT,
            'expires_at' => Carbon::now()->addDays(7),
        ]);

        $this->assertEquals('warning', $pendingCode->status_color);
        $this->assertEquals('info', $sentCode->status_color);
    }

    /**
     * Test find by code
     */
    public function test_find_by_code(): void
    {
        TrialCode::create([
            'code' => 'FINDME1234',
            'requester_first_name' => 'John',
            'requester_last_name' => 'Doe',
            'requester_email' => 'john@example.com',
            'delivery_method' => 'email',
            'status' => TrialCode::STATUS_SENT,
            'expires_at' => Carbon::now()->addDays(7),
        ]);

        $found = TrialCode::findByCode('FINDME1234');
        $notFound = TrialCode::findByCode('NOTEXIST12');

        $this->assertNotNull($found);
        $this->assertNull($notFound);
    }

    /**
     * Test find by code is case insensitive
     */
    public function test_find_by_code_case_insensitive(): void
    {
        TrialCode::create([
            'code' => 'FINDME1234',
            'requester_first_name' => 'John',
            'requester_last_name' => 'Doe',
            'requester_email' => 'john@example.com',
            'delivery_method' => 'email',
            'status' => TrialCode::STATUS_SENT,
            'expires_at' => Carbon::now()->addDays(7),
        ]);

        $found = TrialCode::findByCode('findme1234');
        $this->assertNotNull($found);
    }

    /**
     * Test email has active code check
     */
    public function test_email_has_active_code(): void
    {
        TrialCode::create([
            'code' => 'ACTIVE1234',
            'requester_first_name' => 'John',
            'requester_last_name' => 'Doe',
            'requester_email' => 'john@example.com',
            'delivery_method' => 'email',
            'status' => TrialCode::STATUS_SENT,
            'expires_at' => Carbon::now()->addDays(7),
        ]);

        $this->assertTrue(TrialCode::emailHasActiveCode('john@example.com'));
        $this->assertFalse(TrialCode::emailHasActiveCode('nobody@example.com'));
    }

    /**
     * Test active scope
     */
    public function test_active_scope(): void
    {
        TrialCode::create([
            'code' => 'ACTIVE1234',
            'requester_first_name' => 'John',
            'requester_last_name' => 'Doe',
            'requester_email' => 'john@example.com',
            'delivery_method' => 'email',
            'status' => TrialCode::STATUS_SENT,
            'expires_at' => Carbon::now()->addDays(7),
        ]);

        TrialCode::create([
            'code' => 'EXPIRED123',
            'requester_first_name' => 'Jane',
            'requester_last_name' => 'Doe',
            'requester_email' => 'jane@example.com',
            'delivery_method' => 'email',
            'status' => TrialCode::STATUS_EXPIRED,
            'expires_at' => Carbon::now()->subDay(),
        ]);

        TrialCode::create([
            'code' => 'REVOKED123',
            'requester_first_name' => 'Bob',
            'requester_last_name' => 'Smith',
            'requester_email' => 'bob@example.com',
            'delivery_method' => 'email',
            'status' => TrialCode::STATUS_REVOKED,
            'expires_at' => Carbon::now()->addDays(7),
        ]);

        $activeCodes = TrialCode::active()->get();

        $this->assertEquals(1, $activeCodes->count());
        $this->assertEquals('ACTIVE1234', $activeCodes->first()->code);
    }

    /**
     * Test by email scope
     */
    public function test_by_email_scope(): void
    {
        TrialCode::create([
            'code' => 'EMAIL12345',
            'requester_first_name' => 'John',
            'requester_last_name' => 'Doe',
            'requester_email' => 'john@example.com',
            'delivery_method' => 'email',
            'status' => TrialCode::STATUS_SENT,
            'expires_at' => Carbon::now()->addDays(7),
        ]);

        TrialCode::create([
            'code' => 'OTHER12345',
            'requester_first_name' => 'Jane',
            'requester_last_name' => 'Doe',
            'requester_email' => 'jane@example.com',
            'delivery_method' => 'email',
            'status' => TrialCode::STATUS_SENT,
            'expires_at' => Carbon::now()->addDays(7),
        ]);

        $johnCodes = TrialCode::byEmail('john@example.com')->get();

        $this->assertEquals(1, $johnCodes->count());
        $this->assertEquals('EMAIL12345', $johnCodes->first()->code);
    }
}
