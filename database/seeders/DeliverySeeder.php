<?php

namespace Database\Seeders;

use App\Models\Delivery;
use Illuminate\Database\Seeder;

class DeliverySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $deliveries = [
            ['name' => 'წერეთლის ფილიალი'],
            ['name' => 'ყაზბეგის ფილიალი'],
            ['name' => 'მისამართზე თბილისში'],
            ['name' => 'მისამართზე რეგიონში'],
            ['name' => 'მისამართზე უცხოეთში'],
        ];

        Delivery::query()->insertOrIgnore($deliveries);
    }
}
