<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            ['name' => 'მაღაზიაშია', 'color' => '#2196f3'],
            ['name' => 'საწყობშია', 'color' => '#8bc34a'],
            ['name' => 'სერვისშია', 'color' => '#f44336'],
            ['name' => 'გაყიდულია', 'color' => '#00bcd4'],
            ['name' => 'უკან დაბრუნებულია', 'color' => '#aa0af5'],
            ['name' => 'ნაწილები', 'color' => '#c60af5'],
            ['name' => 'გაკეთებულია', 'color' => '#4caf50'],
            ['name' => 'ჩანთაშია', 'color' => '#b0aca5'],
            ['name' => 'დარეზერვებულია', 'color' => '#e6ae1e'],
            ['name' => 'კონსიგნაცია', 'color' => '#4d3336'],
            ['name' => 'პასაჟი სერვისი', 'color' => '#0af5e4'],
            ['name' => 'სერვისში დაბრუნებული', 'color' => '#470af5'],
        ];

        Status::query()->insertOrIgnore($statuses);
    }
}
