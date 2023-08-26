<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use Database\Seeders\UsersTableSeeder;
use Database\Seeders\OrdersTableSeeder;
use Database\Seeders\ProductsTableSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (User::count() === 0) {
            $this->call(UsersTableSeeder::class);
        }

        if (Product::count() === 0) {
            $this->call(ProductsTableSeeder::class);
        }

        if (Order::count() === 0) {
            $this->call(OrdersTableSeeder::class);
        }
    }
}
