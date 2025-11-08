<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $categories = [
            ['title' => 'PREBUILT PC'],
            ['title' => 'COMPONENTS'],
            ['title' => 'GAMING'],
            ['title' => 'OFFICE'],
            ['title' => 'SCHOOL'],
        ];

        $popular = [
            ['title' => 'NVIDIA RTX 5080',      'price' => 119900, 'status' => 'available',   'image' => 'GPU.png'],
            ['title' => 'Ryzen 9 9950X3D',      'price' => 119900, 'status' => 'available',   'image' => 'CPU.png'],
            ['title' => 'High End PC Build',    'price' => 119900, 'status' => 'unavailable', 'image' => 'PC.png'],
            ['title' => 'RAM Corsair 16GB 8x',  'price' => 119900, 'status' => 'available',   'image' => 'RAM.png'],
        ];

        $latest = $popular; 

        return view('home', compact('categories', 'popular', 'latest'));
    }
}
