@extends('layouts.app')

@section('content')
<div class="max-w-[900px] mx-auto px-4 sm:px-6 lg:px-8 py-8 font-aerospace">

  <div class="flex items-start gap-6">
    <img src="{{ $p['image_full'] }}"
         alt="{{ $p['nama_produk'] }}"
         class="w-32 h-32 object-contain bg-slate-100 rounded-lg border">

    <div class="min-w-0">
      <h1 class="text-xl font-extrabold">{{ $p['nama_produk'] }}</h1>
      <p class="text-xs text-slate-500 mt-1">{{ $p['status'] === 'available' ? 'AVAILABLE' : 'UNAVAILABLE' }}</p>
      <p class="text-sm font-black mt-2">${{ number_format($p['harga'], 2) }}</p>
    </div>
  </div>

  <form action="{{ route('reviews.store') }}" method="POST" class="mt-8 bg-white border rounded-xl p-5 shadow-[0_6px_20px_-6px_rgba(0,0,0,.08)]">
    @csrf
    <input type="hidden" name="product_slug" value="{{ $p['slug'] }}">

    <div class="grid sm:grid-cols-2 gap-4">
      <div>
        <label class="text-xs font-semibold">Nama (opsional)</label>
        <input name="nama" type="text"
               class="mt-1 w-full h-10 rounded-md border px-3"
               placeholder="Nama kamu (boleh dikosongkan)">
      </div>

      <div>
        <label class="text-xs font-semibold">Rating</label>
        <div class="mt-1 flex items-center gap-2">
          <select name="rating" class="h-10 rounded-md border px-3">
            @for ($i=5; $i>=1; $i--)
              <option value="{{ $i }}">{{ $i }} ★</option>
            @endfor
          </select>
          <span class="text-xs text-slate-500">1 = buruk, 5 = sangat bagus</span>
        </div>
        @error('rating') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
      </div>
    </div>

    <div class="mt-4">
      <label class="text-xs font-semibold">Ulasan</label>
      <textarea name="isi" rows="5"
                class="mt-1 w-full rounded-md border px-3 py-2"
                placeholder="Tulis pengalamanmu mengenai produk ini…"></textarea>
      @error('isi') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="mt-6 flex gap-3">
      <a href="{{ url()->previous() }}"
         class="h-11 inline-flex items-center px-4 rounded-lg border font-extrabold hover:bg-slate-50">
        Batal
      </a>

      <button type="submit"
              class="h-11 inline-flex items-center px-5 rounded-lg bg-slate-900 text-white font-extrabold hover:opacity-90">
        Kirim Ulasan
      </button>
    </div>
  </form>
</div>
@endsection
