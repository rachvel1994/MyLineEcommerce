<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $payments = [
            ['name' => 'კომპანია', 'guard_name' => 'web'],
            ['name' => 'მომხმარებელი', 'guard_name' => 'web'],
            ['name' => 'ტექნიკოსი', 'guard_name' => 'web'],
            ['name' => 'ქოლცენტრი', 'guard_name' => 'web'],
            ['name' => 'მოდერატორი', 'guard_name' => 'web'],
        ];

        Role::query()->insertOrIgnore($payments);
    }
}
