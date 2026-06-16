<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdministratorSeeder::class,
            ConditionSeeder::class,
            DeliverySeeder::class,
            HearAboutSeeder::class,
            ModelSeeder::class,
            RepairInformationSeeder::class,
            BranchSeeder::class,
            StatusSeeder::class,
            PaymentSeeder::class,
            GuaranteeSeeder::class,
            ColorSeeder::class,
            BatterySeeder::class,
            StorageSeeder::class,
            CategorySeeder::class,
            ShieldSeeder::class,
            RoleSeeder::class,
        ]);
    }
}
