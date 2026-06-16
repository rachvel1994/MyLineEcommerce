<?php

namespace Database\Seeders;

use App\Models\Battery;
use Illuminate\Database\Seeder;

class BatterySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $batteries = [];

        for ($i = 80; $i <= 100; $i++) {
            $batteries[] = ['name' => $i . '%'];
        }


        Battery::query()->insertOrIgnore($batteries);
    }
}
