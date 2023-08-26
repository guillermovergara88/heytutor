<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::group(['prefix' => 'users'], function () {
    Route::get('expensive-order', [UserController::class, 'getUsersWithMostExpensiveOrders']);
    Route::get('purchased-all-products', [UserController::class, 'getUsersWhoPurchasedAllProducts']);
    Route::get('highest-sales', [UserController::class, 'getUsersWithHighestTotalSales']);
});
