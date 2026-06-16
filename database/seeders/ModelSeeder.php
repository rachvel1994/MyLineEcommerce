<?php

namespace Database\Seeders;

use App\Models\ProductModel;
use Illuminate\Database\Seeder;

class ModelSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['name' => 'Iphone 7', 'children' => ['Iphone 7']],

            ['name' => "Iphone's", 'children' => [
                'Iphone se 2020',
                'Iphone se 2022',
                'Iphone xr'
            ]],

            ['name' => 'Iphone 11', 'children' => [
                'Iphone 11 pro'
            ]],

            ['name' => 'Iphone 12', 'children' => [
                'Iphone 12',
                'Iphone 12 mini',
                'Iphone 12 pro',
                'Iphone 12 pro max'
            ]],

            ['name' => 'Iphone 13', 'children' => [
                'Iphone 13',
                'Iphone 13 mini',
                'Iphone 13 pro',
                'Iphone 13 pro max'
            ]],

            ['name' => 'Iphone 14', 'children' => [
                'Iphone 14',
                'Iphone 14 plus',
                'Iphone 14 pro',
                'Iphone 14 pro max'
            ]],

            ['name' => 'Iphone 15', 'children' => [
                'Iphone 15',
                'Iphone 15 pro',
                'Iphone 15 pro max'
            ]],

            ['name' => 'Iphone 16', 'children' => [
                'Iphone 16',
                'Iphone 16 pro',
                'Iphone 16 pro max'
            ]],

            ['name' => 'Iphone 17', 'children' => [
                'Iphone 17 air',
                'Iphone 17 pro'
            ]],

            ['name' => 'Iphone Others', 'children' => [
                'Iphone test',
                'Iphone xs max',
                'Iphone xsmax'
            ]],

            ['name' => 'Ipad', 'children' => [
                'Ipad 8th gen + simi',
                'Ipad 10th + cellular',
                'Ipad 10th wifi only',
                'Ipad a16 11gen cellural',
                'Ipad a16 11gen wifi only',
                'Ipad air 5th wifi only',
                'Ipad mini 7th gen wifi only',
                'Ipad pro (9.7-inch) 2016',
                'Ipad pro (12.9-inch) 4th gen + cellular'
            ]],

            ['name' => 'Apple Watch', 'children' => [
                'Apple watch 7 41mm',
                'Apple watch 7 45mm',
                'Apple watch se 2 40mm',
                'Apple watch se 2 44mm',
                'Apple watch series 7 41mm',
                'Apple watch series 8 41mm',
                'Apple watch series 8 45mm',
                'Apple watch series 9 41mm',
                'Apple watch series 9 45mm',
                'Apple watch series 10 42mm',
                'Apple watch series 10 46mm',
                'Apple watch ultra'
            ]],

            ['name' => 'სხვადასხვა', 'children' => [
                'Galaxy s7',
                'Galaxy s10',
                'Galaxy s22',
                'Galaxy s23',
                'Hp laptop',
                'Lg g7 thinq',
                'No name',
                'Ჭკვიანი საკეტი'
            ]],
        ];

        foreach ($data as $group) {

            $parent = ProductModel::query()->firstOrCreate([
                'name' => $group['name']
            ], [
                'parent_id' => null,
                'is_active' => true
            ]);

            foreach ($group['children'] as $child) {

                ProductModel::query()->firstOrCreate([
                    'name' => $child
                ], [
                    'parent_id' => $parent->id,
                    'is_active' => true
                ]);

            }
        }
    }
}
