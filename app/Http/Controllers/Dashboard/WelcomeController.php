<?php

namespace App\Http\Controllers\Dashboard;

use App\Product;
use App\User;
use App\Order;
use App\Category;
use App\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class WelcomeController extends Controller
{
    public function index() {
    	$products_count = Product::count(); 
    	$users_count = User::whereRoleIs('admin')->count(); 
    	$clients_count = Client::count(); 
    	$categories_count = Category::count(); 
 
    	$sales_data = Order::select(
    		DB::raw('YEAR(created_at) as year'),
    		DB::raw('MONTH(created_at) as month'),
    		DB::raw('SUM(total_price) as sum'),
    	)->groupBy('month')->get();


    	return view('dashboard.welcome', compact('products_count','users_count','clients_count','categories_count', 'sales_data'));
    } // end of index
} // end of controller
