<?php

namespace Database\Seeders;

use App\Models\Niche;
use Illuminate\Database\Seeder;

class NicheSeeder extends Seeder
{
    public function run(): void
    {
        $niches = [
            ['slug' => 'casa',        'name' => 'Casa',        'icon' => '🏠', 'bg_color' => '#fef3c7', 'description' => 'Decoração, organização e itens essenciais para o seu lar.'],
            ['slug' => 'ferramentas', 'name' => 'Ferramentas', 'icon' => '🔧', 'bg_color' => '#dbeafe', 'description' => 'Ferramentas e utilidades para reparos e projetos em casa.'],
            ['slug' => 'higiene',     'name' => 'Higiene',     'icon' => '🧴', 'bg_color' => '#d1fae5', 'description' => 'Produtos de higiene pessoal e cuidados com o corpo.'],
            ['slug' => 'cozinha',     'name' => 'Cozinha',     'icon' => '🍳', 'bg_color' => '#ffe4e6', 'description' => 'Utensílios, eletrodomésticos e acessórios para a cozinha.'],
            ['slug' => 'tecnologia',  'name' => 'Tecnologia',  'icon' => '💻', 'bg_color' => '#ede9fe', 'description' => 'Gadgets, eletrônicos e acessórios tech.'],
            ['slug' => 'pets',        'name' => 'Pets',        'icon' => '🐾', 'bg_color' => '#fce7f3', 'description' => 'Produtos selecionados para o bem-estar dos seus animais.'],
        ];

        foreach ($niches as $data) {
            Niche::updateOrCreate(['slug' => $data['slug']], $data);
        }
    }
}
