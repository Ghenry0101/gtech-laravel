<x-app-layout>
@php
    $searchTerm = $search ?? '';
    $hasQuery = $hasQuery ?? false;
    $categoryRoute = $active ? route('products.index', $active) : route('products.index');
@endphp
<body class="bg-slate-100 text-slate-900 antialiased font-aerospace">

  <main class="mx-auto max-w-[1200px] px-4 sm:px-6 lg:px-8 py-8">

    <h2 class="text-center font-medium tracking-wide">Our Categories</h2>
    <div class="mt-4 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
      <a href="{{ route('products.index') }}"
         class="group bg-white border rounded-xl shadow-sm p-4 flex flex-col items-center gap-2 hover:shadow-md {{ $active ? '' : 'ring-2 ring-slate-900/10' }}">
        <img src="{{ asset('images/prebuilt-icon.png') }}" alt="All" class="w-10 h-10 opacity-70 group-hover:opacity-100">
        <span class="text-xs font-medium tracking-wide">all products</span>
      </a>

      @foreach($categories as $cat)
        <a href="{{ route('products.index', $cat['slug']) }}"
           class="group bg-white border rounded-xl shadow-sm p-4 flex flex-col items-center gap-2 hover:shadow-md {{ $active === $cat['slug'] ? 'ring-2 ring-slate-900/10' : '' }}">
          <img src="{{ asset($cat['icon']) }}" alt="{{ $cat['name'] }}" class="w-10 h-10 opacity-70 group-hover:opacity-100">
          <span class="text-xs font-medium tracking-wide text-center">{{ $cat['name'] }}</span>
          @if (empty($cat['has_products']))
            <span class="text-[11px] font-semibold text-slate-400">Tidak ada produk</span>
          @endif
        </a>
      @endforeach
    </div>

    <div class="mt-8">
      <form method="GET" action="{{ $categoryRoute }}" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between bg-white border rounded-xl shadow-sm p-4">
        <label class="flex-1">
          <span class="sr-only">Search products</span>
          <input
            type="text"
            name="q"
            value="{{ $searchTerm }}"
            placeholder="Cari produk atau kategori..."
            class="w-full rounded-md border border-slate-200 px-4 py-2 text-sm focus:border-slate-400 focus:outline-none focus:ring-0"
          >
        </label>
        <button
          type="submit"
          class="inline-flex items-center justify-center rounded-md bg-slate-900 px-5 py-2 text-xs font-semibold uppercase tracking-wide text-white hover:bg-slate-800"
        >
          Search
        </button>
      </form>
      @if ($hasQuery && $searchTerm !== '')
        <p class="mt-2 text-center text-sm text-slate-500">Menampilkan hasil pencarian untuk <span class="font-semibold">"{{ $searchTerm }}"</span></p>
      @endif
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
              {{ __('Belum ada produk di kategori ini.') }}
            @endif
          </p>
        </div>
      @endif
    </section>

  </main>

  @include('partials.footer')
</body>
</x-app-layout>
