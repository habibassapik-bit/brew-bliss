<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);
        Role::firstOrCreate(['name' => 'admin_cs']);

        User::query()->firstOrCreate(
            ['email' => 'smkall@brewbliss.test'],
            [
                'name' => 'SMKALL',
                'phone' => '081234567890',
                'password' => '12345678',
            ],
        )->assignRole($superAdminRole);

        $this->call([
            CategorySeeder::class,
            ProductSeeder::class,
            SettingSeeder::class,
        ]);
    }
}
