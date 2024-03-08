<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class FakeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            Fakes\TagSeeder::class,
            Fakes\UserSeeder::class,
            Fakes\ProjectSeeder::class,
            Fakes\PageMetaDataSeeder::class,
        ]);
    }
}
