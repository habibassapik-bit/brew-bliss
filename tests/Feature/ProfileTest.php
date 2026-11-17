<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_profile(): void
    {
        $this->get('/profil')->assertRedirect('/masuk');
    }

    public function test_customer_can_view_own_profile(): void
    {
        $user = User::factory()->create(['name' => 'Budi Santoso', 'phone' => '081234567890']);

        $response = $this->actingAs($user)->get('/profil');

        $response->assertOk();
        $response->assertSee('Budi Santoso');
        $response->assertSee($user->email);
        $response->assertSee('081234567890');
    }

    public function test_customer_can_update_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/profil', [
            'name' => 'Sari Dewi',
            'email' => $user->email,
            'phone' => '089876543210',
        ]);

        $response->assertRedirect('/profil');
        $response->assertSessionHas('status');
        $user->refresh();
        $this->assertSame('Sari Dewi', $user->name);
        $this->assertSame('089876543210', $user->phone);
    }

    public function test_profile_update_validates_unique_email(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $response = $this->actingAs($user)->post('/profil', [
            'name' => 'Budi',
            'email' => $other->email,
            'phone' => '081234567890',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_profile_update_rejects_invalid_phone(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/profil', [
            'name' => 'Budi',
            'email' => $user->email,
            'phone' => 'abc',
        ]);

        $response->assertSessionHasErrors('phone');
    }

    public function test_customer_can_change_password_with_current_password(): void
    {
        $user = User::factory()->create(['password' => 'rahasia123']);

        $response = $this->actingAs($user)->post('/profil/kata-sandi', [
            'current_password' => 'rahasia123',
            'password' => 'sandiBaru456',
            'password_confirmation' => 'sandiBaru456',
        ]);

        $response->assertRedirect('/profil');
        $response->assertSessionHas('status');
        $this->assertTrue(Hash::check('sandiBaru456', $user->fresh()->password));
    }

    public function test_password_change_rejects_wrong_current_password(): void
    {
        $user = User::factory()->create(['password' => 'rahasia123']);

        $response = $this->actingAs($user)->post('/profil/kata-sandi', [
            'current_password' => 'salahbanget',
            'password' => 'sandiBaru456',
            'password_confirmation' => 'sandiBaru456',
        ]);

        $response->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check('rahasia123', $user->fresh()->password));
    }

    public function test_customer_can_create_edit_and_delete_own_address(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/profil/alamat', [
            'recipient_name' => 'Sari Dewi',
            'phone' => '089876543210',
            'address' => 'Jl. Melati No. 5, Kota',
            'label' => 'Rumah',
        ]);

        $response->assertRedirect('/profil');
        $address = Address::query()->where('user_id', $user->id)->first();

        $this->assertNotNull($address);
        $this->assertTrue((bool) $address->is_default, 'Alamat pertama otomatis menjadi utama.');

        $response = $this->actingAs($user)->patch(route('profile.addresses.update', $address), [
            'recipient_name' => 'Sari D.',
            'phone' => '089876543210',
            'address' => 'Jl. Anggrek No. 9, Kota',
        ]);

        $response->assertRedirect('/profil');
        $address->refresh();
        $this->assertSame('Jl. Anggrek No. 9, Kota', $address->address);

        $response = $this->actingAs($user)->delete(route('profile.addresses.destroy', $address));

        $response->assertRedirect('/profil');
        $this->assertDatabaseMissing('addresses', ['id' => $address->id]);
    }

    public function test_customer_cannot_edit_or_delete_other_users_address(): void
    {
        $user = User::factory()->create();
        $otherAddress = Address::factory()->create(['user_id' => User::factory()->create()->id]);

        $this->actingAs($user)->patch(route('profile.addresses.update', $otherAddress), [
            'recipient_name' => 'Hacker',
            'phone' => '081234567890',
            'address' => 'Alamat orang lain',
        ])->assertNotFound();

        $this->actingAs($user)->delete(route('profile.addresses.destroy', $otherAddress))->assertNotFound();
        $this->actingAs($user)->post(route('profile.addresses.default', $otherAddress))->assertNotFound();

        $otherAddress->refresh();
        $this->assertNotSame('Hacker', $otherAddress->recipient_name);
    }

    public function test_only_one_default_address_per_customer(): void
    {
        $user = User::factory()->create();
        $first = Address::factory()->create(['user_id' => $user->id, 'is_default' => true]);
        $second = Address::factory()->create(['user_id' => $user->id, 'is_default' => false]);

        $this->actingAs($user)->post(route('profile.addresses.default', $second));

        $this->assertDatabaseHas('addresses', ['id' => $second->id, 'is_default' => true]);
        $this->assertDatabaseHas('addresses', ['id' => $first->id, 'is_default' => false]);
    }
}
