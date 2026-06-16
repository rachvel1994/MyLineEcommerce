<?php

namespace Database\Seeders;

use App\Models\Storage;
use Illuminate\Database\Seeder;

class StorageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $storage = [
            ['name' => '16 GB'],
            ['name' => '32 GB'],
            ['name' => '64 GB'],
            ['name' => '128 GB'],
            ['name' => '256 GB'],
            ['name' => '512 GB'],
            ['name' => '1 TB'],
        ];

        Storage::query()->insertOrIgnore($storage);
    }
}
