<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{

    public function getUsersWithMostExpensiveOrders()
    {
        $subquery = Order::select('user_id', DB::raw('MAX(total_amount) AS max_amount'))
        ->groupBy('user_id');

        $usersWithExpensiveOrders = User::with(['orders' => function ($query) use ($subquery) {
            $query->joinSub($subquery, 'max_orders', function ($join) {
                $join->on('orders.user_id', '=', 'max_orders.user_id')
                    ->whereColumn('orders.total_amount', '=', 'max_orders.max_amount');
            })->select('orders.*');
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
        $subquery = Order::select('user_id', DB::raw('SUM(total_amount) as total_sales'))
        ->groupBy('user_id');

        $maxTotalSalesSubquery = Order::select(DB::raw('SUM(total_amount) as max_total_sales'))
            ->groupBy('user_id')
            ->orderByDesc('max_total_sales')
            ->limit(1);

        $usersWithHighestTotalSales = User::with(['orders' => function ($query) {
            $query->select('user_id', 'total_amount')
                ->orderBy('total_amount', 'desc');
        }])
        ->whereIn('users.id', function ($query) use ($subquery, $maxTotalSalesSubquery) {
            $query->select('user_id')
                ->fromSub($subquery, 'user_sales')
                ->where('user_sales.total_sales', '>=', DB::raw("({$maxTotalSalesSubquery->toSql()})"));
        })
        ->get();

        return response()->json($usersWithHighestTotalSales);
    }
}
