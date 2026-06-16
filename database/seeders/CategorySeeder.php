<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'აქსესუარი'],
            ['name' => 'მობილური'],
            ['name' => 'პლანშეტი'],
            ['name' => 'Smart საათი'],
            ['name' => 'ლეპტოპი'],
        ];

        Category::query()->insertOrIgnore($categories);
    }
}
