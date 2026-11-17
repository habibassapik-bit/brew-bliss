<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_page_can_be_rendered(): void
    {
        $response = $this->get('/daftar');

        $response->assertOk();
        $response->assertSee('Daftar Akun');
    }

    public function test_customer_can_register_and_is_logged_in(): void
    {
        $response = $this->post('/daftar', [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'phone' => '081234567890',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertDatabaseHas('users', ['email' => 'budi@example.com']);
        $this->assertAuthenticatedAs(User::query()->where('email', 'budi@example.com')->first());
    }

    public function test_registration_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->post('/daftar', [
            'name' => 'Budi Santoso',
            'email' => 'taken@example.com',
            'phone' => '081234567890',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_customer_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create(['password' => 'rahasia123']);

        $response = $this->post('/masuk', [
            'email' => $user->email,
            'password' => 'rahasia123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = User::factory()->create(['password' => 'rahasia123']);

        $response = $this->post('/masuk', [
            'email' => $user->email,
            'password' => 'salah-banget',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_logged_in_customer_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/keluar');

        $response->assertRedirect(route('home'));
        $this->assertGuest();
    }
}
