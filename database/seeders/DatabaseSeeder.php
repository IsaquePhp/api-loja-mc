<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\CustomerSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\ClientSeeder;
use Database\Seeders\AdditionalCustomersSeeder;
use Database\Seeders\AdditionalProductsSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            AdminUserSeeder::class,
            ProductSeeder::class,
            CustomerSeeder::class,
            CategorySeeder::class,
            ClientSeeder::class,
            AdditionalCustomersSeeder::class,
            AdditionalProductsSeeder::class,
            // Add other seeders here
        ]);
    }
}
