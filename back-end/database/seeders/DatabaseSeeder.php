<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            
            PermissionSeeder::class,
            RoleSeeder::class,
            RolesPerssionsSeeder::class,
            // StatusTypeSeeder::class,
            // StatusSeeder::class,
            // ServicesSeeder::class,
            // AttributeSeeder::class,
            // CitiesSeeder::class,
            // NeighborhoodSeeder::class,
            // AttributeValueSeeder::class
        ]);
    }
}
