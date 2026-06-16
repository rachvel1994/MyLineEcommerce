<?php

namespace Database\Seeders;

use App\Models\Guarantee;
use Illuminate\Database\Seeder;

class GuaranteeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $guaranty = [
            ['name' => '1 თვე'],
            ['name' => '2 თვე'],
            ['name' => '3 თვე'],
            ['name' => '6 თვე'],
            ['name' => '1 წელი'],
        ];

        Guarantee::query()->insertOrIgnore($guaranty);
    }
}
