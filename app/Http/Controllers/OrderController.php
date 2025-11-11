<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        // ambil riwayat dari session (atau dari DB kalau sudah ada)
        $orders = $request->session()->get('orders', []);

        // ambil ulasan yang sudah tersimpan (sementara di file)
        $path = storage_path('app/reviews.json');
        $reviews = file_exists($path)
            ? json_decode(file_get_contents($path), true)
            : [];

        // daftar slug produk yang sudah pernah diulas
        $reviewedSlugs = collect($reviews)
            ->pluck('product_slug')
            ->unique()
            ->values()
            ->all();

        return view('orders.index', compact('orders', 'reviewedSlugs'));
    }
}
