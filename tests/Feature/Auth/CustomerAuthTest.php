<?php

namespace Tests\Feature\Auth;

use App\Models\Customer;
use Tests\TenantTestCase;

class CustomerAuthTest extends TenantTestCase
{
    // ── Login ─────────────────────────────────────────────────────────────────

    public function test_login_page_is_accessible_to_guests(): void
    {
        $response = $this->tenantGet('/account/login');

        $response->assertStatus(200);
    }

    public function test_customer_can_login_with_valid_credentials(): void
    {
        $customer = Customer::factory()->create([
            'email'    => 'test@example.com',
            'password' => 'password123',
        ]);

        $response = $this->tenantPost('/account/login', [
            'email'    => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/account');
        $this->assertAuthenticatedAs($customer, 'customer');
    }

    public function test_login_fails_with_wrong_password(): void
    {
        Customer::factory()->create(['email' => 'test@example.com']);

        $response = $this->tenantPost('/account/login', [
            'email'    => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest('customer');
    }

    public function test_login_fails_for_nonexistent_email(): void
    {
        $response = $this->tenantPost('/account/login', [
            'email'    => 'nobody@example.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest('customer');
    }

    // ── Registration ──────────────────────────────────────────────────────────

    public function test_register_page_is_accessible_to_guests(): void
    {
        $this->tenantGet('/account/register')->assertStatus(200);
    }

    public function test_customer_can_register_with_valid_data(): void
    {
        $response = $this->tenantPost('/account/register', [
            'name'                  => 'Jane Doe',
            'email'                 => 'jane@example.com',
            'phone'                 => '081234567890',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/account');
        $this->assertDatabaseHas('customers', ['email' => 'jane@example.com']);
        $this->assertAuthenticated('customer');
    }

    public function test_registration_fails_with_duplicate_email(): void
    {
        Customer::factory()->create(['email' => 'existing@example.com']);

        $response = $this->tenantPost('/account/register', [
            'name'                  => 'Another Person',
            'email'                 => 'existing@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseCount('customers', 1);
    }

    public function test_registration_fails_when_password_confirmation_mismatch(): void
    {
        $response = $this->tenantPost('/account/register', [
            'name'                  => 'Jane Doe',
            'email'                 => 'jane@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'different',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest('customer');
    }

    // ── Logout ────────────────────────────────────────────────────────────────

    public function test_authenticated_customer_can_logout(): void
    {
        $customer = Customer::factory()->create();

        $response = $this->actingAsCustomer($customer)
            ->withServerVariables(['HTTP_HOST' => $this->tenantDomain])
            ->post('/account/logout');

        $response->assertRedirect('/account/login');
        $this->assertGuest('customer');
    }

    // ── Route protection ──────────────────────────────────────────────────────

    public function test_guest_is_redirected_from_protected_account_page(): void
    {
        $this->tenantGet('/account')->assertRedirect('/account/login');
    }

    public function test_authenticated_customer_can_access_account_page(): void
    {
        $customer = Customer::factory()->create();

        $this->actingAsCustomer($customer)
            ->withServerVariables(['HTTP_HOST' => $this->tenantDomain])
            ->get('/account')
            ->assertStatus(200);
    }

    public function test_logged_in_customer_is_redirected_away_from_login_page(): void
    {
        $customer = Customer::factory()->create();

        $this->actingAsCustomer($customer)
            ->withServerVariables(['HTTP_HOST' => $this->tenantDomain])
            ->get('/account/login')
            ->assertRedirect();
    }
}
