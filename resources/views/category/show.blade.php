@extends('layouts.app')

@section('content')
<div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 py-8">

  <h2 class="text-center font-extrabold tracking-wide text-xl md:text-2xl mb-6">
    {{ strtoupper($slug) }} PRODUCTS
  </h2>

  @forelse ($items as $p)
    <x-product-card
      :id="$p['slug']"
      :title="$p['nama_produk']"
      :price="$p['harga']"
      :image="$p['gambar_produk']"
      :available="($p['status'] ?? '') === 'available'"
      detail-url="{{ route('product.detail', $p['slug']) }}"
    />
  @empty
    <p class="text-center text-slate-500 font-semibold">
      Belum ada produk di kategori ini.
    </p>
  @endforelse

</div>
@endsection
