<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{

    public function getUsersWithMostExpensiveOrders()
    {
        $usersWithExpensiveOrders = User::with(['orders' => function ($query) {
            $query->join(DB::raw('(SELECT user_id, MAX(total_amount) AS max_amount FROM orders GROUP BY user_id) AS max_orders'),
                        'orders.user_id', '=', 'max_orders.user_id')
                ->whereColumn('orders.total_amount', 'max_orders.max_amount')
                ->select('orders.*');
        }])->get();
        
        return response()->json($usersWithExpensiveOrders);
    }

    public function getUsersWhoPurchasedAllProducts()
    {
        $usersWhoPurchasedAll = User::select('users.*')
        ->join('orders', 'users.id', '=', 'orders.user_id')
        ->groupBy('users.id')
        ->havingRaw('COUNT(DISTINCT orders.product_id) = ?', [Product::count()])
        ->get();

        return response()->json($usersWhoPurchasedAll);
    }
    
    public function getUsersWithHighestTotalSales()
    {
        $usersWithHighestTotalSales = User::with(['orders' => function ($query) {
            $query->select('user_id', DB::raw('SUM(total_amount) as total_sales'))
                ->groupBy('user_id')
                ->orderByDesc('total_sales');
        }])
        ->join('orders as max_sales_orders', 'users.id', '=', 'max_sales_orders.user_id')
        ->select('users.*')
        ->groupBy('users.id')
        ->havingRaw('SUM(max_sales_orders.total_amount) = (SELECT SUM(total_amount) FROM orders GROUP BY user_id ORDER BY SUM(total_amount) DESC LIMIT 1)')
        ->get();

        return response()->json($usersWithHighestTotalSales);
    }
}
