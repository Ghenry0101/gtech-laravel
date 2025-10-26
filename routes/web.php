<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('home');

Route::get('/search', function (Request $request) {
    $q = trim($request->query('q', ''));
    return view('search', ['q' => $q]);
})->name('search');

});
