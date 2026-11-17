<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminStoreSettingTest extends TestCase
{
    use RefreshDatabase;

    private function createSuperAdmin(): User
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Role::firstOrCreate(['name' => 'super_admin']));

        return $superAdmin;
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'store_name' => 'Brew & Bliss',
            'store_description' => 'Makanan ringan, dessert, dan minuman buatan rumah.',
            'store_address' => 'Permata Puri Harmoni 2 Blok L 5 No 38',
            'store_phone' => '0895415567493',
            'store_open_hours' => 'Setiap hari 08.00 - 18.00 WIB',
            'store_whatsapp' => '0895415567493',
        ], $overrides);
    }

    public function test_guest_and_customer_cannot_access_store_settings(): void
    {
        $customer = User::factory()->create();

        $this->get(route('admin.settings.edit'))->assertRedirect('/masuk');
        $this->actingAs($customer)->get(route('admin.settings.edit'))->assertForbidden();
        $this->actingAs($customer)->put(route('admin.settings.update'), $this->validPayload())->assertForbidden();
    }

    public function test_super_admin_can_view_settings_form(): void
    {
        Setting::set('store_address', 'Jl. Contoh No. 1, Kota');

        $superAdmin = $this->createSuperAdmin();

        $response = $this->actingAs($superAdmin)->get(route('admin.settings.edit'));

        $response->assertOk();
        $response->assertSee('Pengaturan Toko');
        $response->assertSee('Jl. Contoh No. 1, Kota');
    }

    public function test_super_admin_can_update_store_settings(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $response = $this->actingAs($superAdmin)->put(route('admin.settings.update'), $this->validPayload());

        $response->assertRedirect(route('admin.settings.edit'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('settings', ['key' => 'store_address', 'value' => 'Permata Puri Harmoni 2 Blok L 5 No 38']);
        $this->assertDatabaseHas('settings', ['key' => 'store_phone', 'value' => '0895415567493']);
        $this->assertDatabaseHas('settings', ['key' => 'store_open_hours', 'value' => 'Setiap hari 08.00 - 18.00 WIB']);
        $this->assertDatabaseHas('settings', ['key' => 'store_whatsapp', 'value' => '62895415567493']);
    }

    public function test_whatsapp_number_is_normalized_to_international_format(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $this->actingAs($superAdmin)->put(route('admin.settings.update'), $this->validPayload([
            'store_whatsapp' => '0812-3456-7890',
        ]));

        $this->assertDatabaseHas('settings', ['key' => 'store_whatsapp', 'value' => '6281234567890']);

        $this->actingAs($superAdmin)->put(route('admin.settings.update'), $this->validPayload([
            'store_whatsapp' => '6281234567890',
        ]));

        $this->assertDatabaseHas('settings', ['key' => 'store_whatsapp', 'value' => '6281234567890']);
    }

    public function test_invalid_phone_is_rejected(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $response = $this->actingAs($superAdmin)->put(route('admin.settings.update'), $this->validPayload([
            'store_phone' => 'bukan-angka',
        ]));

        $response->assertSessionHasErrors('store_phone');
        $this->assertDatabaseMissing('settings', ['key' => 'store_phone', 'value' => 'bukan-angka']);
    }

    public function test_floating_whatsapp_button_visibility_on_storefront(): void
    {
        Setting::set('store_whatsapp', '62895415567493');

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('https://wa.me/62895415567493');

        Setting::set('store_whatsapp', '');

        $this->get(route('home'))->assertOk()->assertDontSee('wa.me');
    }

    public function test_whatsapp_info_row_is_removed_from_home_page(): void
    {
        Setting::set('store_whatsapp', '62895415567493');

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Telepon');
        $response->assertDontSee('>WhatsApp<', false);
    }
}
