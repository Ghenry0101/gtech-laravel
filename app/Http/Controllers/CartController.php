<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $req)
    {
        $items = $req->session()->get('cart', []);  
        return view('cart.index', compact('items'));
    }

    public function add(Request $req)
    {
        // Validasi minimal data yang dikirim dari tombol "Add to Cart"
        $data = $req->validate([
            'id'    => 'required',
            'title' => 'required',
            'price' => 'required|numeric',
            'image' => 'nullable|string',
            'qty'   => 'nullable|integer|min:1'
        ]);

        $qty   = $data['qty'] ?? 1;
        $id    = (string) $data['id'];

        $cart = $req->session()->get('cart', []);

        if (isset($cart[$id])) {
            $cart[$id]['qty'] += $qty; 
        } else {
            $cart[$id] = [
                'id'    => $id,
                'title' => $data['title'],
                'price' => (float)$data['price'],
                'image' => $data['image'] ?? null,
                'qty'   => $qty,
                'checked' => true,    
            ];
        }

        $req->session()->put('cart', $cart);

        return back()->with('ok', 'Ditambahkan ke cart');
    }

    public function update(Request $req)
    {
        $data = $req->validate([
            'id'      => 'required',
            'qty'     => 'nullable|integer|min:1',
            'checked' => 'nullable|boolean',
        ]);

        $cart = $req->session()->get('cart', []);
        $id = (string)$data['id'];

        if (isset($cart[$id])) {
            if (isset($data['qty']))     $cart[$id]['qty'] = (int)$data['qty'];
            if (isset($data['checked'])) $cart[$id]['checked'] = (bool)$data['checked'];
            $req->session()->put('cart', $cart);
        }

        return back();
    }

    public function remove(Request $req, $id)
    {
        $cart = $req->session()->get('cart', []);
        unset($cart[(string)$id]);
        $req->session()->put('cart', $cart);
        return back();
    }
}
