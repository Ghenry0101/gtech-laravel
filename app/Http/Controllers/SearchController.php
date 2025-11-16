<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request): RedirectResponse
    {
        $query = trim($request->string('query')->toString());

        $params = [];
        if ($query !== '') {
            $params['q'] = $query;
        }

        return redirect()->route('products.index', $params);
    }
}
