<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request): RedirectResponse
    {
        $query = trim($request->string('query')->toString());

        $params = ['categorySlug' => null];
        if ($query !== '') {
            $params['q'] = $query;
        }

        // Keeping search logic as a redirect keeps the navigation responsive: the product grid controller receives the query and builds the list.
        return redirect()->route('products.index', $params);
    }
}
