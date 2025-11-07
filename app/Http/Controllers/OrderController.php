<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        // sementara kosongkan / ambil dari session kalau mau simulasi
        $orders = session('orders', []); 
        return view('orders.index', compact('orders'));
    }
}
