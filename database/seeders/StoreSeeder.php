<?php

namespace Database\Seeders;

use App\Models\Store;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        $stores = [
            ['slug' => 'amazon',        'name' => 'Amazon',        'color' => '#FF9900', 'text_color' => '#000000'],
            ['slug' => 'shopee',        'name' => 'Shopee',        'color' => '#EE4D2D', 'text_color' => '#ffffff'],
            ['slug' => 'mercado-livre', 'name' => 'Mercado Livre', 'color' => '#FFE600', 'text_color' => '#333333'],
            ['slug' => 'magalu',        'name' => 'Magalu',        'color' => '#0086FF', 'text_color' => '#ffffff'],
            ['slug' => 'kabum',         'name' => 'KaBuM!',        'color' => '#FF6600', 'text_color' => '#ffffff'],
        ];

        foreach ($stores as $data) {
            Store::updateOrCreate(['slug' => $data['slug']], $data);
        }
    }
}
