<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Pengaturan toko default (fleksibel, dapat diubah Super Admin).
     *
     * @var array<string, string>
     */
    private const DEFAULTS = [
        'store_name' => 'Brew & Bliss',
        'store_description' => 'Makanan ringan, dessert, dan minuman buatan rumah.',
        'store_phone' => '0895415567493',
        'store_whatsapp' => '62895415567493',
        'store_address' => 'Permata Puri Harmoni 2 Blok L 5 No 38',
        'store_open_hours' => 'Setiap hari 08.00 - 18.00 WIB',
        'store_is_open' => '1',
        'active_payment_methods' => 'qris,bank_transfer,ewallet,cod',
        'bank_accounts' => '{"bank_transfer":[]}',
        'delivery_fee' => '3000',
        'enable_delivery' => '1',
        'enable_pickup' => '1',
        'order_scheduling_enabled' => '0',
        'guest_checkout_enabled' => '0',
    ];

    public function run(): void
    {
        foreach (self::DEFAULTS as $key => $value) {
            Setting::query()->firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
