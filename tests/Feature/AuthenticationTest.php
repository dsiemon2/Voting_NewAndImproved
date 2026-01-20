<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\SubscriptionPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create required roles
        Role::firstOrCreate(
            ['name' => 'User'],
            ['description' => 'Regular user', 'is_system' => false]
        );

        Role::firstOrCreate(
            ['name' => 'Administrator'],
            ['description' => 'Full system access', 'is_system' => true]
        );

        // Create free subscription plan
        SubscriptionPlan::firstOrCreate(
            ['code' => 'free'],
            [
                'name' => 'Free Trial',
                'description' => 'Free trial plan with basic features',
                'price' => 0,
                'billing_period' => 'monthly',
                'max_events' => 1,
                'max_entries_per_event' => 20,
                'is_active' => true,
            ]
        );
    }

    /**
     * Test login page is accessible
     */
    public function test_login_page_is_accessible(): void
    {
        $response = $this->get(route('login'));
        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    /**
     * Test user can login with valid credentials
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        $role = Role::where('name', 'User')->first();
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $role->id,
        ]);

        $response = $this->post(route('login'), [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test user cannot login with invalid password
     */
    public function test_user_cannot_login_with_invalid_password(): void
    {
        $role = Role::where('name', 'User')->first();
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $role->id,
        ]);

        $response = $this->post(route('login'), [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Test user cannot login with non-existent email
     */
    public function test_user_cannot_login_with_nonexistent_email(): void
    {
        $response = $this->post(route('login'), [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Test registration page is accessible
     */
    public function test_registration_page_is_accessible(): void
    {
        $response = $this->get(route('register'));
        $response->assertStatus(200);
        $response->assertViewIs('auth.register');
    }

    /**
     * Test user can register with valid data
     */
    public function test_user_can_register_with_valid_data(): void
    {
        $response = $this->post(route('register'), [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $this->assertAuthenticated();
    }

    /**
     * Test registration fails with duplicate email
     */
    public function test_registration_fails_with_duplicate_email(): void
    {
        $role = Role::where('name', 'User')->first();
        User::factory()->create([
            'email' => 'existing@example.com',
            'role_id' => $role->id,
        ]);

        $response = $this->post(route('register'), [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * Test registration fails with password mismatch
     */
    public function test_registration_fails_with_password_mismatch(): void
    {
        $response = $this->post(route('register'), [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'differentpassword',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * Test registration fails with short password
     */
    public function test_registration_fails_with_short_password(): void
    {
        $response = $this->post(route('register'), [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'newuser@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * Test authenticated user can logout
     */
    public function test_authenticated_user_can_logout(): void
    {
        $role = Role::where('name', 'User')->first();
        $user = User::factory()->create(['role_id' => $role->id]);

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    /**
     * Test remember me functionality
     */
    public function test_login_with_remember_me(): void
    {
        $role = Role::where('name', 'User')->first();
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $role->id,
        ]);

        $response = $this->post(route('login'), [
            'email' => 'test@example.com',
            'password' => 'password123',
            'remember' => true,
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);

        // User should have remember token set
        $user->refresh();
        $this->assertNotNull($user->remember_token);
    }

    /**
     * Test inactive user login behavior
     * Note: Currently the application allows inactive users to login.
     * This test verifies the current behavior.
     */
    public function test_inactive_user_can_login(): void
    {
        $role = Role::where('name', 'User')->first();
        $user = User::factory()->create([
            'email' => 'inactive@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $role->id,
            'is_active' => false,
        ]);

        $response = $this->post(route('login'), [
            'email' => 'inactive@example.com',
            'password' => 'password123',
        ]);

        // Currently, inactive users can still login
        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test required fields for registration
     */
    public function test_registration_requires_all_fields(): void
    {
        $response = $this->post(route('register'), []);

        $response->assertSessionHasErrors(['first_name', 'last_name', 'email', 'password']);
    }

    /**
     * Test email must be valid format
     */
    public function test_registration_requires_valid_email(): void
    {
        $response = $this->post(route('register'), [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'not-an-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * Test registration with subscription plan
     */
    public function test_registration_with_free_plan_creates_subscription(): void
    {
        $response = $this->post(route('register'), [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'planuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'plan' => 'free',
        ]);

        $response->assertRedirect(route('dashboard'));

        $user = User::where('email', 'planuser@example.com')->first();
        $this->assertNotNull($user);

        // Check subscription was created
        $this->assertDatabaseHas('user_subscriptions', [
            'user_id' => $user->id,
            'status' => 'active',
        ]);
    }
}
