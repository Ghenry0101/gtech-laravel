@php
  $items = is_array($items ?? null) ? $items : [];
@endphp

@if(empty($items))
  <div class="text-center text-sm text-gray-400 mt-4">No items.</div>
@else
  <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
    @foreach ($items as $p)
      @php
        $title = $p['title'] ?? '-';
        $price = $p['price'] ?? 0;
        $status = strtolower($p['status'] ?? 'available');
        $image = $p['image'] ?? 'PC.png';
      @endphp

      <div class="rounded-2xl ring-1 ring-black/10 shadow-[0_6px_20px_-6px_rgba(0,0,0,.15)] bg-white overflow-hidden">
        <div class="h-48 bg-slate-100">
          <img
            src="{{ asset('images/'.$image) }}"
            alt="{{ $title }}"
            class="w-full h-full object-cover"
            onerror="this.onerror=null;this.src='{{ asset('images/PC.png') }}'">
        </div>

        <div class="p-4">
          <h4 class="font-medium text-slate-900 ">{{ $title }}</h4>

          <p class="mt-1 text-xs font-medium {{ $status === 'available' ? 'text-green-600' : 'text-red-600' }}">
            {{ $status === 'available' ? 'AVAILABLE' : 'UNAVAILABLE' }}
          </p>

          <p class="mt-3 font-medium">${{ number_format(($price ?? 0)/100, 2) }}</p>

          <button class="mt-4 w-full h-10 rounded-lg bg-slate-900 text-white text-xs font-medium hover:opacity-90">
            add to cart
          </button>
        </div>
      </div>
    @endforeach
  </div>
@endif
