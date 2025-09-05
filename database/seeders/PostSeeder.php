<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;
use App\Models\Category;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $posts = [
            [
                'title' => 'Laravel 11 ya disponible',
                'content' => 'Laravel 11 llega con nuevas mejoras en rendimiento y soporte a PHP 9.',
                'author' => 'Admin',
                'category_id' => Category::where('name', 'Noticias')->first()->id,
            ],
            [
                'title' => 'Cómo crear un CRUD con Filament',
                'content' => 'En este tutorial aprenderás paso a paso cómo crear un CRUD con Filament en Laravel.',
                'author' => 'Juan Pérez',
                'category_id' => Category::where('name', 'Tutoriales')->first()->id,
            ],
            [
                'title' => 'Opinión: el futuro de la IA en la programación',
                'content' => 'Cada vez más herramientas de inteligencia artificial están ayudando a los programadores.',
                'author' => 'María López',
                'category_id' => Category::where('name', 'Opinión')->first()->id,
            ],
        ];

        foreach ($posts as $post) {
            Post::create($post);
        }
    }
}
