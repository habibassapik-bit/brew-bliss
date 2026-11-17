<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_profile(): void
    {
        $this->get('/profil')->assertRedirect('/masuk');
    }

    public function test_customer_sees_own_profile_data(): void
    {
        $user = User::factory()->create(['name' => 'Budi Santoso', 'phone' => '081234567890']);

        $response = $this->actingAs($user)->get('/profil');

        $response->assertOk();
        $response->assertSee('Budi Santoso');
        $response->assertSee($user->email);
        $response->assertSee('081234567890');
    }

    public function test_customer_can_update_own_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/profil', [
            'name' => 'Sari Dewi',
            'email' => $user->email,
            'phone' => '089876543210',
        ]);

        $response->assertRedirect('/profil');
        $user->refresh();
        $this->assertSame('Sari Dewi', $user->name);
        $this->assertSame('089876543210', $user->phone);
    }

    public function test_profile_update_rejects_duplicate_email(): void
    {
        $other = User::factory()->create(['email' => 'taken@example.com']);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/profil', [
            'name' => $user->name,
            'email' => $other->email,
            'phone' => $user->phone,
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_profile_update_validates_phone(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/profil', [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => 'abc',
        ]);

        $response->assertSessionHasErrors('phone');
    }

    public function test_password_change_requires_correct_current_password(): void
    {
        $user = User::factory()->create(['password' => 'rahasia123']);

        $response = $this->actingAs($user)->post('/profil/kata-sandi', [
            'current_password' => 'salahbenget',
            'password' => 'sandiBaru123',
            'password_confirmation' => 'sandiBaru123',
        ]);

        $response->assertSessionHasErrors('current_password');

        $user->update(['password' => 'sandiBaru123']);
        $this->assertTrue(app('hash')->check('sandiBaru123', $user->password));
    }

    public function test_password_change_works_with_correct_current_password(): void
    {
        $user = User::factory()->create(['password' => 'rahasia123']);

        $response = $this->actingAs($user)->post('/profil/kata-sandi', [
            'current_password' => 'rahasia123',
            'password' => 'sandiBaru123',
            'password_confirmation' => 'sandiBaru123',
        ]);

        $response->assertRedirect('/profil');
        $this->assertTrue(app('hash')->check('sandiBaru123', $user->fresh()->password));
    }

    public function test_customer_can_add_update_and_delete_own_address(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/profil/alamat', [
            'recipient_name' => 'Sari Dewi',
            'phone' => '089876543210',
            'address' => 'Jl. Melati No. 5, Kota',
            'label' => 'Rumah',
            'is_default' => '1',
        ]);

        $response->assertRedirect('/profil');
        $address = $user->addresses()->first();

        $this->assertNotNull($address);
        $this->assertTrue($address->is_default);

        $this->actingAs($user)->patch("/profil/alamat/{$address->id}", [
            'recipient_name' => 'Sari Dewi',
            'phone' => '089876543210',
            'address' => 'Jl. Kenanga No. 9, Kota',
            'label' => 'Rumah',
        ])->assertRedirect('/profil');

        $address->refresh();
        $this->assertSame('Jl. Kenanga No. 9, Kota', $address->address);

        $this->actingAs($user)->delete("/profil/alamat/{$address->id}")->assertRedirect('/profil');
        $this->assertDatabaseCount('addresses', 0);
    }

    public function test_customer_cannot_modify_other_users_address(): void
    {
        $user = User::factory()->create();
        $otherAddress = Address::factory()->create(['user_id' => User::factory()->create()->id]);

        $this->actingAs($user)->patch("/profil/alamat/{$otherAddress->id}", [
            'recipient_name' => 'Peretas',
            'phone' => '081111111111',
            'address' => 'Alamat peretasan',
        ])->assertNotFound();

        $this->actingAs($user)->delete("/profil/alamat/{$otherAddress->id}")->assertNotFound();
        $otherAddress->refresh();
        $this->assertNotSame('Alamat peretasan', $otherAddress->address);
    }
}
