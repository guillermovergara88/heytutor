<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase; 

    public function testGetUsersWithMostExpensiveOrdersWithOrders()
    {
        $userWithOrders = User::factory()
        ->has(
            Order::factory()
                ->count(1)
                ->state(function (array $attribute, User $user) {
                    return ['user_id' => $user->id];
                })
        )
        ->create();

        $userWithoutOrders = User::factory()->create();

        $response = $this->get('/users/expensive-order');

        $response->assertStatus(200);

        $expectedOrderData = $userWithOrders->orders->map(function ($order) {
        return $order->toArray();
        });


        $expectedData = [
            [
                "id" => $userWithOrders->id,
                "name" => $userWithOrders->name,
                "email" => $userWithOrders->email,
                "created_at" => $userWithOrders->created_at,
                "updated_at" => $userWithOrders->updated_at,
                "orders" => $expectedOrderData->toArray(),
            ],
            [
                "id" => $userWithoutOrders->id,
                "name" => $userWithoutOrders->name,
                "email" => $userWithoutOrders->email,
                "created_at" => $userWithoutOrders->created_at,
                "updated_at" => $userWithoutOrders->updated_at,
                "orders" => [],
            ],
        ];

        $response->assertJson($expectedData);
    }

    public function testGetUsersWhoPurchasedAllProducts()
    {
        $products = Product::factory()->count(3)->create();

        $userWithAllPurchases = User::factory()->create();
        $userWithoutAllPurchases = User::factory()->create();

        foreach ($products as $product) {
            Order::factory()->create([
                'user_id' => $userWithAllPurchases->id,
                'product_id' => $product->id,
            ]);
        }

        Order::factory()->create([
            'user_id' => $userWithoutAllPurchases->id,
            'product_id' => $products[0]->id,
        ]);

        $response = $this->get('/users/purchased-all-products');

        $response->assertStatus(200);

        $expectedUserWithAllData = [
            'id' => $userWithAllPurchases->id,
            'name' => $userWithAllPurchases->name,
            'email' => $userWithAllPurchases->email,
            'created_at' => $userWithAllPurchases->created_at,
            'updated_at' => $userWithAllPurchases->updated_at,
        ];

        $expectedUserWithoutAllData = [
            'id' => $userWithoutAllPurchases->id,
            'name' => $userWithoutAllPurchases->name,
            'email' => $userWithoutAllPurchases->email,
            'created_at' => $userWithoutAllPurchases->created_at,
            'updated_at' => $userWithoutAllPurchases->updated_at,
        ];

        $response->assertJson([$expectedUserWithAllData]);
        $response->assertJsonMissing([$expectedUserWithoutAllData]);
    }

    public function testGetUsersWithHighestTotalSalesMultipleUsersWithSameSales()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Order::factory()->create([
            'user_id' => $user1->id,
            'product_id' => Product::factory()->create(['price' => 10000.00]),
            'quantity' => 1,
            'total_amount' => 10000.00,
        ]);

        Order::factory()->create([
            'user_id' => $user2->id,
            'product_id' => Product::factory()->create(['price' => 10000.00]),
            'quantity' => 1,
            'total_amount' => 10000.00,
        ]);

        $response = $this->get('/users/highest-sales');

        $response->assertStatus(200);

        $response->assertJson([
            [
                'id' => $user1->id,
                'name' => $user1->name,
                'email' => $user1->email,
                'created_at' => $user1->created_at,
                'updated_at' => $user1->updated_at,
                'orders' => [
                    [
                        'user_id' => $user1->id,
                        'total_sales' => '10000.00',
                    ],
                ],
            ],
            [
                'id' => $user2->id,
                'name' => $user2->name,
                'email' => $user2->email,
                'created_at' => $user2->created_at,
                'updated_at' => $user2->updated_at,
                'orders' => [
                    [
                        'user_id' => $user2->id,
                        'total_sales' => '10000.00',
                    ],
                ],
            ],
        ]);
    }

    public function testGetUsersWithHighestTotalSalesSingleUserWithHighestSales()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Order::factory()->create([
            'user_id' => $user1->id,
            'product_id' => Product::factory()->create(['price' => 25000.00]),
            'quantity' => 1,
            'total_amount' => 25000.00,
        ]);

        Order::factory()->create([
            'user_id' => $user2->id,
            'product_id' => Product::factory()->create(['price' => 10000.00]),
            'quantity' => 1,
            'total_amount' => 10000.00,
        ]);

        $response = $this->get('/users/highest-sales');

        $response->assertStatus(200);

        $response->assertJson([
            [
                'id' => $user1->id,
                'name' => $user1->name,
                'email' => $user1->email,
                'created_at' => $user1->created_at,
                'updated_at' => $user1->updated_at,
                'orders' => [
                    [
                        'user_id' => $user1->id,
                        'total_sales' => '25000.00',
                    ],
                ],
            ],
        ]);

        $response->assertJsonMissing(['id' => $user2->id]);
    }
}
