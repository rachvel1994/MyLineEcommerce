<?php

namespace Database\Seeders;

use App\Models\Payment;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $payments = [
            ['name' => 'BOG ტერმინალით გადახდა'],
            ['name' => 'TBC ტერმინალით გადახდა'],
            ['name' => 'BOG განვადება'],
            ['name' => 'TBC განვადება'],
            ['name' => 'Credo განვადება'],
            ['name' => 'Top Card - Liberty'],
            ['name' => 'Silk bank'],
            ['name' => 'ქეში'],
            ['name' => 'BOG ნაწილ-ნაწილ'],
            ['name' => 'TBC ნაწილ-ნაწილი'],
            ['name' => 'Online გადახდა'],
            ['name' => 'Credo საინვოისო'],
            ['name' => 'TBC საინვოისო'],
            ['name' => 'BOG საინვოისო'],
            ['name' => 'Liberty საინვოისო'],
            ['name' => 'პირადზე დარიცხვა ბექასთან'],
            ['name' => 'პირადზე დარიცხვა ნინისთან'],
        ];

        Payment::query()->insertOrIgnore($payments);
    }
}
