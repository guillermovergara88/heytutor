<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class OrdersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $products = Product::all();
        foreach ($users as $user) {
            foreach ($products as $product) {
                $quantity = rand(1, 7);
                $totalAmount = $quantity * $product->price;

                Order::create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'total_amount' => $totalAmount,
                ]);
            }
        }
    }
}