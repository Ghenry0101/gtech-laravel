@php
  $categories = [
  ['title' => 'PREBUILT PC', 'bg' => 'bayangan-pc.png'],
  ['title' => 'COMPONENTS',  'bg' => 'bayangan-4.png'],
  ['title' => 'GAMING',      'bg' => 'bayangan-1.png'],
  ['title' => 'OFFICE',      'bg' => 'bayangan-2.png'],
  ['title' => 'SCHOOL',      'bg' => 'bayangan-3.png'],
  ];

  $popular = [
    ['name' => 'nvidia rtx 5080','price'=>1199,'status'=>'AVAILABLE','img'=>'/images/gpu.png'],
    ['name' => 'ryzen 9 9950x3d','price'=>1199,'status'=>'AVAILABLE','img'=>'/images/cpu.png'],
    ['name' => 'high end pc build','price'=>1199,'status'=>'UNAVAILABLE','img'=>'/images/pc.png'],
    ['name' => 'ram corsair 16gb 8X','price'=>1199,'status'=>'AVAILABLE','img'=>'/images/ram.png'],
  ];
  $latest = $popular;
@endphp

@extends('layouts.app')
@section('title','Home')

@section('content')
<section class="font-aerospace">
  <section class="bg-white rounded-xl shadow-sm border p-6 md:p-8 grid md:grid-cols-2 gap-6 items-center">
    <div class="space-y-4">
      <h1 class="text-3xl md:text-4xl font-black tracking-wide">easy to use</h1>
      <p class="text-sm text-gray-600 max-w-md">"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua."</p>
      <a href="#" class="inline-block px-5 py-2 rounded-md bg-black text-white font-semibold hover:opacity-90">DISCOVER</a>
    </div>
    <div class="w-full">
    <img class=" w-full rounded-lg" src="/images/PC.png" ...>
    </div>
  </section>


  <section class="mt-10">
  <h2 class="text-center font-extrabold tracking-wide text-lg md:text-xl mb-4">
    OUR CATEGORIES
  </h2>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    @foreach (array_slice($categories, 0, 2) as $cat)
      <a href="#"
         class="group relative overflow-hidden rounded-2xl ring-1 ring-black/10 shadow-[0_6px_20px_-6px_rgba(0,0,0,0.15)] transition-transform duration-300 hover:-translate-y-[2px]">
        <img src="{{ asset('images/'.$cat['bg']) }}"
             alt="{{ $cat['title'] }}"
             class="absolute inset-0 w-full h-full object-cover opacity-[0.18] group-hover:opacity-[0.22] transition-opacity duration-300" />

        <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(255,255,255,0.88)_0%,rgba(255,255,255,0.92)_40%,rgba(255,255,255,0.96)_100%)]"></div>

        <div class="relative h-40 md:h-44 grid place-items-center px-6">
          <p class="text-3xl md:text-4xl font-black tracking-wide text-slate-900 text-center">
            {{ $cat['title'] }}
          </p>
        </div>
      </a>
    @endforeach
  </div>

  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
    @foreach (array_slice($categories, 2) as $cat)
      <a href="#"
         class="group relative overflow-hidden rounded-2xl ring-1 ring-black/10 shadow-[0_6px_20px_-6px_rgba(0,0,0,0.15)] transition-transform duration-300 hover:-translate-y-[2px]">
        <img src="{{ asset('images/'.$cat['bg']) }}"
             alt="{{ $cat['title'] }}"
             class="absolute inset-0 w-full h-full object-cover opacity-[0.18] group-hover:opacity-[0.22] transition-opacity duration-300" />
        <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(255,255,255,0.88)_0%,rgba(255,255,255,0.92)_40%,rgba(255,255,255,0.96)_100%)]"></div>

        <div class="relative h-36 md:h-40 grid place-items-center px-6">
          <p class="text-3xl md:text-4xl font-black tracking-wide text-slate-900 text-center">
            {{ $cat['title'] }}
          </p>
        </div>
      </a>
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
</section>
@endsection
