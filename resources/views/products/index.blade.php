<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Products</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-900 antialiased">
  @include('partials.header')

  <main class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <h2 class="text-center font-extrabold tracking-wide">OUR CATEGORIES</h2>
    <div class="mt-4 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
      {{-- tombol ALL --}}
      <a href="{{ route('products.index') }}"
         class="group bg-white border rounded-xl shadow-sm p-4 flex flex-col items-center gap-2 hover:shadow-md {{ $active ? '' : 'ring-2 ring-slate-900/10' }}">
        <img src="{{ asset('images/prebuilt-icon.png') }}" alt="All" class="w-10 h-10 opacity-70 group-hover:opacity-100">
        <span class="text-xs font-extrabold tracking-wide">ALL PRODUCTS</span>
      </a>

      @foreach($categories as $cat)
        <a href="{{ route('products.index', $cat['slug']) }}"
           class="group bg-white border rounded-xl shadow-sm p-4 flex flex-col items-center gap-2 hover:shadow-md {{ $active === $cat['slug'] ? 'ring-2 ring-slate-900/10' : '' }}">
          <img src="{{ asset($cat['icon']) }}" alt="{{ $cat['name'] }}" class="w-10 h-10 opacity-70 group-hover:opacity-100">
          <span class="text-xs font-extrabold tracking-wide text-center">{{ $cat['name'] }}</span>
        </a>
      @endforeach
    </div>


    <section class="mt-8">
      <div class="text-center">
        <h3 class="font-extrabold tracking-wide">
          {{ $active ? strtoupper($active).' PRODUCTS' : 'ALL PRODUCTS' }}
        </h3>
      </div>

      @if (count($items))
        @include('components.product-grid', ['items' => $items])
      @else
        <div class="mt-10 grid place-items-center">
          <p class="text-sm font-semibold text-slate-500">
            Belum ada produk di kategori ini.
          </p>
        </div>
      @endif
    </section>

  </main>

  @include('partials.footer')
</body>
</html>
