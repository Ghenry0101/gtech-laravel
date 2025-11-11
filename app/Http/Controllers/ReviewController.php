<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReviewController extends Controller
{
    // Tampilkan form ulasan
    public function create(string $slug)
    {
        $p = config("products.$slug.items");
        abort_if(!$p, 404, 'Produk tidak ditemukan.');

        // untuk tampilan
        $p['slug']       = $slug;
        $p['image_full'] = asset($p['gambar_produk'] ?? 'images/PC.png');

        return view('reviews.create', ['p' => $p]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_slug' => 'required|string',
            'rating'       => 'required|integer|min:1|max:5',
            'isi'          => 'required|string|min:6',
            'nama'         => 'nullable|string|max:100',
        ]);

        $data['nama']       = $data['nama'] ?? (auth()->user()->name ?? 'Tamu');
        $data['created_at'] = now()->toDateTimeString();

        $path = storage_path('app/reviews.json');
        $all  = file_exists($path) ? json_decode(file_get_contents($path), true) : [];
        $all[] = $data;

        file_put_contents($path, json_encode($all, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));

        return redirect()->route('orders.index')
            ->with('ok', 'Terima kasih! Ulasanmu sudah terkirim.');
    }
}
