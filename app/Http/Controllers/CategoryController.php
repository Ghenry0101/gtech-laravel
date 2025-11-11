<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function show(string $slug)
    {
        $all = config('products.items', []);

        $items = collect($all)->where('kategori', $slug)->values();

        return view('category.show', [
            'slug'  => $slug,
            'items' => $items,
        ]);
    }
}
