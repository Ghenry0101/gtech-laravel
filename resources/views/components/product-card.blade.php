@php
  $id    = $p['id']    ?? ($loop->index + 1);
  $title = $p['title']   ?? '';
  $price = $p['price']   ?? 0;         
  $status= $p['status']  ?? 'available';
  $file  = ltrim(($p['image'] ?? 'PC.png'), '/');  
  $src   = asset('images/'.$file);
  $available = $status === 'available';
@endphp

<div class="rounded-2xl ring-1 ring-black/10 shadow-[0_6px_20px_-6px_rgba(0,0,0,.15)] bg-white overflow-hidden">

  <a href="{{ route('products.show', $slug) }}" class="block">
    <div class="h-48 bg-slate-100">
      <img
        src="{{ asset($image ?? 'images/PC.png') }}"
        alt="{{ $title }}"
        class="w-full h-full object-cover"
        onerror="this.onerror=null;this.src='{{ asset('images/PC.png') }}'">
    </div>
  </a>

  <div class="p-4">
    <h4 class="font-extrabold text-slate-900">{{ $title }}</h4>
    <p class="mt-3 font-black">${{ number_format($price, 2) }}</p>

    <form action="{{ route('cart.add') }}" method="POST" class="mt-4">
      @csrf
      <input type="hidden" name="id"    value="{{ $id }}">
      <input type="hidden" name="title" value="{{ $title }}">
      <input type="hidden" name="price" value="{{ $price }}">
      <input type="hidden" name="image" value="{{ $image ?? 'images/PC.png' }}">
      <input type="hidden" name="qty"   value="1">

      <button type="submit"
        class="w-full h-10 rounded-lg bg-slate-900 text-white text-xs font-medium hover:opacity-90">
        add to cart
      </button>
    </form>
  </div>
</div>

