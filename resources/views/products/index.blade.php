<x-app-layout>
@php
    $searchTerm = $search ?? '';
    $hasQuery = $hasQuery ?? false;
    $active = $active ?? null;
    $categoryMissing = $categoryMissing ?? false;
    $categoryRoute = $active
        ? route('products.index', ['categorySlug' => $active])
        : route('products.index');
@endphp
<body class="bg-slate-100 text-slate-900 antialiased font-aerospace">

  <main class="mx-auto max-w-8xl px-4 sm:px-6 lg:px-8 py-8">

    <h2 class="text-center font-medium tracking-wide">Our Categories</h2>
    <div class="mt-4 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
      <a href="{{ route('products.index') }}"
         class="group bg-white border rounded-xl shadow-sm p-4 flex flex-col items-center gap-2 hover:shadow-md {{ $active ? '' : 'ring-2 ring-slate-900/10' }}">
        <img src="{{ asset('images/prebuilt-icon.png') }}" alt="All" class="w-10 h-10 opacity-70 group-hover:opacity-100">
        <span class="text-xs font-medium tracking-wide">all products</span>
      </a>

      @foreach($categories as $cat)
        <a href="{{ route('products.index', ['categorySlug' => $cat['slug']]) }}"
           class="group bg-white border rounded-xl shadow-sm p-4 flex flex-col items-center gap-2 hover:shadow-md {{ $active === $cat['slug'] ? 'ring-2 ring-slate-900/10' : '' }}">
          <img src="{{ asset($cat['icon']) }}" alt="{{ $cat['name'] }}" class="w-10 h-10 opacity-70 group-hover:opacity-100">
          <span class="text-xs font-medium tracking-wide text-center">{{ $cat['name'] }}</span>
          @if (empty($cat['has_products']))
            <span class="text-[11px] font-semibold text-slate-400">Tidak ada produk</span>
          @endif
        </a>
      @endforeach
    </div>


    <section class="mt-8">
      <div class="text-center">
        <h3 class="font-medium tracking-wide">
          @if ($hasQuery && $searchTerm !== '')
            {{ __('Hasil Pencarian') }}
          @else
            {{ $active ? strtoupper($active).' PRODUCTS' : 'ALL PRODUCTS' }}
          @endif
        </h3>
      </div>

      @if (count($items))
        @include('components.product-grid', ['items' => $items])
      @else
        <div class="mt-10 grid place-items-center">
          <p class="text-sm font-medium text-slate-500">
            @if ($hasQuery && $searchTerm !== '')
              {{ __('Tidak ditemukan produk untuk pencarian ":term".', ['term' => $searchTerm]) }}
            @else
              {{ __('Maaf produk untuk kategori ini belum tersedia.') }}
            @endif
          </p>
        </div>
      @endif
    </section>

  </main>

  @include('partials.footer')
</body>
</x-app-layout>
