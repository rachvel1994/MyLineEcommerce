<?php

namespace Database\Seeders;

use App\Models\RepairInformation;
use Illuminate\Database\Seeder;

class RepairInformationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $repairInformation = [
            ['name' => 'ელემენტ შეცვლილი'],
            ['name' => 'კამერა შეცვლილი'],
            ['name' => 'ეკრან შეცვლილი'],
            ['name' => 'აშშ სერვის გავლილი'],
        ];

        RepairInformation::query()->insertOrIgnore($repairInformation);
    }
}
