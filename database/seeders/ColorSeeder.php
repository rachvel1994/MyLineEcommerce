<?php

namespace Database\Seeders;

use App\Models\Color;
use Illuminate\Database\Seeder;

class ColorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $colors = [
            ['name' => 'Black'],
            ['name' => 'Black titanium'],
            ['name' => 'Blue'],
            ['name' => 'Deep Purple'],
            ['name' => 'Desert'],
            ['name' => 'Desert titanium'],
            ['name' => 'Gold'],
            ['name' => 'Green'],
            ['name' => 'Grey'],
            ['name' => 'Jet Black'],
            ['name' => 'Lavender - Purple'],
            ['name' => 'Natural Titanium'],
            ['name' => 'Orange'],
            ['name' => 'Pink'],
            ['name' => 'Purple'],
            ['name' => 'Red'],
            ['name' => 'Rose Gold'],
            ['name' => 'Silver'],
            ['name' => 'Space Gray'],
            ['name' => 'Starlight'],
            ['name' => 'White'],
            ['name' => 'White Titanium'],
            ['name' => 'Yellow'],
        ];

        Color::query()->insertOrIgnore($colors);
    }
}
