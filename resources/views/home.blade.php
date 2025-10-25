@php
  $categories = [
    ['title' => 'PREBUILT PC'], ['title' => 'COMPONENTS'], ['title' => 'GAMING'],
    ['title' => 'OFFICE'], ['title' => 'SCHOOL'],
  ];

  $popular = [
    ['name' => 'NVIDIA RTX 5080','price'=>1199,'status'=>'AVAILABLE','img'=>'/images/gpu.png'],
    ['name' => 'RYZEN 9 9950X3D','price'=>1199,'status'=>'AVAILABLE','img'=>'/images/cpu.png'],
    ['name' => 'HIGH END PC BUILD','price'=>1199,'status'=>'UNAVAILABLE','img'=>'/images/pc.png'],
    ['name' => 'RAM CORSAIR DOMINATOR 16GB 8X','price'=>1199,'status'=>'AVAILABLE','img'=>'/images/ram.png'],
  ];
  $latest = $popular; // contoh sama
@endphp

@extends('layouts.app')
@section('title','Home')

@section('content')
  {{-- HERO --}}
  <section class="bg-white rounded-xl shadow-sm border p-6 md:p-8 grid md:grid-cols-2 gap-6 items-center">
    <div class="space-y-4">
      <h1 class="text-3xl md:text-4xl font-black tracking-wide">EASY TO USE</h1>
      <p class="text-sm text-gray-600 max-w-md">"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua."</p>
      <a href="#" class="inline-block px-5 py-2 rounded-md bg-black text-white font-semibold hover:opacity-90">DISCOVER</a>
    </div>
    <div class="w-full">
      <div class="aspect-[4/3] w-full rounded-lg bg-gray-200"></div>
      {{-- ganti div di atas dengan <img src="/images/your-hero.png" ...> --}}
    </div>
  </section>


  <section class="mt-8">
    <h2 class="text-center font-extrabold tracking-wide">OUR CATEGORIES</h2>
    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
      @foreach ($categories as $i => $cat)
        <div class="bg-white border rounded-xl shadow-sm p-6 relative overflow-hidden">
          <div class="absolute inset-0 opacity-10 bg-[url('/images/placeholder.jpg')] bg-cover bg-center"></div>
          <div class="relative">
            <p class="text-3xl md:text-4xl font-black tracking-wide">{{ $cat['title'] }}</p>
          </div>
        </div>
        @if ($i==0)
          {{-- baris kedua (3 kolom) --}}
          <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
            @foreach (array_slice($categories,2) as $sub)
              <div class="bg-white border rounded-xl shadow-sm p-6 relative overflow-hidden">
                <div class="absolute inset-0 opacity-10 bg-[url('/images/placeholder.jpg')] bg-cover bg-center"></div>
                <div class="relative">
                  <p class="text-3xl md:text-4xl font-black tracking-wide">{{ $sub['title'] }}</p>
                </div>
              </div>
            @endforeach
          </div>
          @break
        @endif
      @endforeach
    </div>
  </section>


  @php
    $renderCards = function($items) {
      echo '<div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">';
      foreach ($items as $p) {
        $statusColor = $p['status']==='AVAILABLE' ? 'text-green-600' : 'text-red-600';
        echo '
        <div class="bg-white border rounded-xl shadow-sm overflow-hidden flex flex-col">
          <div class="p-4">
            <div class="aspect-[4/3] w-full rounded-md bg-gray-200"></div>
          </div>
          <div class="px-4 pb-4 mt-auto">
            <p class="font-semibold text-sm leading-tight">'.htmlspecialchars($p['name']).'</p>
            <p class="text-xs '.$statusColor.' mt-1">'.htmlspecialchars($p['status']).'</p>
            <p class="mt-3 font-extrabold">$'.number_format($p['price'],0).'</p>
          </div>
          <a href="#" class="mx-4 mb-4 mt-2 inline-block text-center px-4 py-2 rounded-md bg-[#1b1b1b] text-white text-xs font-semibold hover:opacity-90">ADD TO CART</a>
        </div>';
      }
      echo '</div>';
    };
  @endphp


  <section class="mt-10">
    <div class="text-center">
      <h3 class="font-extrabold tracking-wide">OUR POPULAR PRODUCTS</h3>
      <a href="#" class="text-xs text-gray-500 font-semibold">DISCOVER →</a>
    </div>
    {!! $renderCards($popular) !!}
  </section>


  <section class="mt-10">
    <div class="text-center">
      <h3 class="font-extrabold tracking-wide">OUR LATEST PRODUCTS</h3>
      <a href="#" class="text-xs text-gray-500 font-semibold">DISCOVER →</a>
    </div>
    {!! $renderCards($latest) !!}
  </section>
@endsection
