<?php

namespace Database\Seeders;

use App\Models\HearAbout;
use Illuminate\Database\Seeder;

class HearAboutSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hearAbout = [
            ['name' => 'Facebook რეკლამა'],
            ['name' => 'INSTAGRAM'],
            ['name' => 'TIKTOK'],
            ['name' => 'GOOGLE'],
            ['name' => 'MYMARKET'],
            ['name' => 'მეგობრისგან'],
            ['name' => 'ძველი მომხმარებელი'],
            ['name' => 'ლოკალური მაღაზიის ბანერიდან'],
            ['name' => 'რენდომულად'],
            ['name' => 'სხვა საძიებო სისტემებიდან'],
            ['name' => 'სხვა'],
        ];

        HearAbout::query()->insertOrIgnore($hearAbout);
    }
}
