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
            PageSeeder::class,
            MenuSeeder::class,
            HomeBlockSeeder::class,
            SidebarSectionSeeder::class,
        ]);

        if (app()->isProduction()) {
            $this->command->warn('Sample accounts are not created in production.');

            return;
        }

        $this->call([
            SampleAccountSeeder::class,
            SampleProceedingSeeder::class,
            SampleManuscriptSeeder::class,
        ]);
    }
}
