<?php

namespace Database\Seeders\Initial;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Technology;

class TechnologySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $technologies = [
            [
                'icon' => 'fab-php',
                'technology' => 'PHP'
            ],
            [
                'icon' => 'fab-js',
                'technology' => 'JavaScript & TypeScript'
            ],
            [
                'icon' => 'fab-java',
                'technology' => 'Java'
            ],
            [
                'icon' => 'fab-react',
                'technology' => 'React'
            ],
            [
                'icon' => 'fab-node',
                'technology' => 'NodeJS'
            ],
            [
                'icon' => 'fab-laravel',
                'technology' => 'Laravel'
            ],
        ];

        foreach ($technologies as $technology) {
            Technology::create($technology);
        }
    }
}
