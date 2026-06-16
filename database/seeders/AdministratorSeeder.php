<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class AdministratorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()->insertOrIgnore([
            'name' => 'aleksandre',
            'email' => 'agugesashvili@gmail.com',
            'password' => '$2y$12$7BOepGqmckX9uxXVmNdI8uishCALRzk.dLM9ygE/IS.CRC6tKwjwC'
        ]);

        Artisan::call('shield:super-admin --user=1 --panel=backend');
    }
}
