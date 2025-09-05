<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,          
            UserSeeder::class,          
            CategorySeeder::class,
            PostSeeder::class,
            PhaseSeeder::class,         
            ControlProcessSeeder::class,
            ControlProcessPhaseLogSeeder::class, 
            SpecializedPhasesSeeder::class,
            PermissionSeeder::class,
            AddCatalogacionSeeder::class,
        ]);
    }
}
