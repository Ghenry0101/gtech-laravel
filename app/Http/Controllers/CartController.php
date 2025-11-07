<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cart = session('cart', []);
        return view('cart.index', compact('cart'));
    }

    public function add(Request $request, $id)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'price' => 'required|numeric',
            'image' => 'nullable|string',
        ]);

        $cart = session('cart', []);
        $found = false;

        foreach ($cart as &$item) {
            if ($item['id'] == $id) {
                $item['qty'] = ($item['qty'] ?? 1) + 1;
                $found = true;
                break;
            }
        }
        unset($item);

        if (!$found) {
            $cart[] = [
                'id'    => $id,
                'title' => $data['title'],
                'price' => (float)$data['price'],
                'image' => $data['image'] ?? asset('images/PC.png'),
                'qty'   => 1,
            ];
        }

        session(['cart' => $cart]);

        return redirect()->back()->with('success', 'Berhasil ditambahkan ke keranjang!');
    }

    public function update(Request $request, $id)
    {
        $qty = max(1, (int)$request->input('qty', 1));
        $cart = session('cart', []);

        foreach ($cart as &$item) {
            if ($item['id'] == $id) {
                $item['qty'] = $qty;
                break;
            }
        }
        unset($item);

        session(['cart' => $cart]);
        return back()->with('success', 'Kuantitas diperbarui.');
    }

    public function remove($id)
    {
        $cart = session('cart', []);
        $cart = array_values(array_filter($cart, fn($i) => $i['id'] != $id));
        session(['cart' => $cart]);
        return back()->with('success', 'Item dihapus dari keranjang.');
    }
}
