<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProductsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Product::create([
            'name' => 'Toyota Corolla',
            'price' => 5000.00,
        ]);

        Product::create([
            'name' => 'Red Hat',
            'price' => 15.00,
        ]);
    }
}
