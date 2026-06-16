<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sellFrom = [
            ['name' => 'წერეთლის ფილიალი'],
            ['name' => 'ყაზბეგის ფილიალი'],
        ];

        Branch::query()->insertOrIgnore($sellFrom);
    }
}
