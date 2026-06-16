<?php

namespace Database\Seeders;

use App\Models\Condition;
use Illuminate\Database\Seeder;

class ConditionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $conditions = [
            ['name' => 'A - კატეგორია ყუთით'],
            ['name' => 'A - კატეგორია უყუთოთ'],
            ['name' => 'B - კატეგორია ყუთით'],
            ['name' => 'B - კატეგორია უყუთოთ'],
            ['name' => 'C - კატეგორია ყუთით'],
            ['name' => 'C - კატეგორია უყუთოთ'],
            ['name' => 'ახალი გახსნილი ყუთით'],
            ['name' => 'ახალი ყუთში შეფუთული'],
            ['name' => 'ახალი უყუთო'],
            ['name' => 'A/B - კატეგორია'],
            ['name' => 'B/C - კატეგორია'],
        ];

        Condition::query()->insertOrIgnore($conditions);
    }
}
