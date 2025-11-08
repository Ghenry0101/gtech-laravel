<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = session('orders', []); 
        return view('orders.index', compact('orders'));
    }
}
