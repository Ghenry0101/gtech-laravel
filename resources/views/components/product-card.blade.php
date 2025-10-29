@php
  // aman buat array
  $title = $p['title']   ?? '';
  $price = $p['price']   ?? 0;         // dalam sen
  $status= $p['status']  ?? 'available';
  $file  = ltrim(($p['image'] ?? 'PC.png'), '/');  // filename saja
  $src   = asset('images/'.$file);
  $available = $status === 'available';
@endphp

<div class="rounded-2xl ring-1 ring-black/10 shadow-[0_6px_20px_-6px_rgba(0,0,0,0.15)] bg-white overflow-hidden">
  {{-- Gambar --}}
  <div class="h-48 bg-slate-100">
    <img
      src="{{ $src }}"
      alt="{{ $title }}"
      class="w-full h-full object-cover"
      onerror="this.onerror=null;this.src='{{ asset('images/PC.png') }}'">
  </div>

  {{-- Body --}}
  <div class="p-4">
    <h4 class="font-extrabold text-slate-900">{{ $title }}</h4>

    <p class="mt-1 text-xs font-bold {{ $available ? 'text-green-600' : 'text-red-600' }}">
      {{ $available ? 'AVAILABLE' : 'UNAVAILABLE' }}
    </p>

    <p class="mt-3 font-black">
      ${{ number_format($price / 100, 0, '.', ',') }}
    </p>

    <button
      class="mt-4 w-full h-10 rounded-lg {{ $available ? 'bg-slate-900 text-white' : 'bg-slate-400 text-white/80 cursor-not-allowed' }}"
      {{ $available ? '' : 'disabled' }}>
      ADD TO CART
    </button>
  </div>
</div>
