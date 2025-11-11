@extends('layouts.app')

@section('content')
<div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 py-8 font-aerospace">

  <nav class="mb-4 text-xs text-slate-500">
    <a href="{{ url('/') }}" class="hover:underline">Home</a>
    <span class="mx-1">/</span>
    <a href="#" class="hover:underline">{{ strtoupper($p['kategori_id']) }}</a>
    <span class="mx-1">/</span>
    <span class="text-slate-700">{{ $p['nama_produk'] }}</span>
  </nav>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

    <section>
      <div class="aspect-[4/3] rounded-xl border bg-white overflow-hidden">
        <img src="{{ $p['image_full'] }}"
             alt="{{ $p['nama_produk'] }}"
             class="w-full h-full object-cover"
             onerror="this.onerror=null;this.src='{{ asset('images/PC.png') }}'">
      </div>

      @if(!empty($p['gallery_full']))
      <div class="mt-3 grid grid-cols-4 gap-3">
        @foreach($p['gallery_full'] as $g)
          <button type="button"
                  class="rounded-lg border bg-white overflow-hidden h-20"
                  onclick="document.getElementById('mainImg').src='{{ $g }}'">
            <img src="{{ $g }}" class="w-full h-full object-cover"
                 onerror="this.onerror=null;this.src='{{ asset('images/PC.png') }}'">
          </button>
        @endforeach
      </div>
      @endif
    </section>

    <section>
      <div class="flex items-center gap-2 text-[11px]">
        <span class="px-2 py-1 rounded bg-slate-100">{{ strtoupper($p['kategori_id']) }}</span>
        <span class="px-2 py-1 rounded bg-slate-100">MODEL {{ $p['id'] }}</span>
      </div>

      <h1 class="mt-2 text-2xl md:text-3xl font-semibold tracking-wide">
        {{ $p['nama_produk'] }}
      </h1>

      <p class="mt-2 text-xl font-semibold">{{ $p['harga_fmt'] }}</p>

      <div class="mt-3 flex items-center gap-4 text-xs">
        <div class="flex items-center gap-2">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a9.99 9.99 0 0 0-7.07 17.07A9.99 9.99 0 1 0 12 2Zm1 14h-2v-2h2v2Zm0-4h-2V6h2v6Z"/></svg>
          <span>{{ $p['status_label'] }}</span>
        </div>
        <div class="flex items-center gap-2">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M20.5 6.5h-17v11h17v-11Zm-15 9v-7h13v7h-13Z"/></svg>
          <span>SAFE PACKAGING</span>
        </div>
      </div>

      <form action="{{ route('cart.add') }}" method="POST" class="mt-5">
        @csrf
        <input type="hidden" name="id" value="{{ $p['id'] }}">
        <input type="hidden" name="title" value="{{ $p['nama_produk'] }}">
        <input type="hidden" name="price" value="{{ $p['harga'] }}">
        <input type="hidden" name="image" value="{{ $p['gambar_produk'] }}">
        <div class="flex items-center gap-3">
          <input type="number" name="qty" min="1" value="1"
                 class="w-20 h-10 rounded-lg border text-center">
          <button type="submit"
                  class="flex-1 h-10 rounded-lg bg-slate-900 text-white font-semibold hover:opacity-90">
            ADD TO CART
          </button>
        </div>
      </form>

      <div class="mt-5 text-sm text-slate-600 grid grid-cols-2 md:grid-cols-4 gap-3">
        <div><span class="block text-xs text-slate-400">WEIGHT</span>{{ $p['weight'] ?? '-' }} kg</div>
        <div><span class="block text-xs text-slate-400">LENGTH</span>{{ $p['length'] ?? '-' }} cm</div>
        <div><span class="block text-xs text-slate-400">WIDTH</span>{{ $p['width'] ?? '-' }} cm</div>
        <div><span class="block text-xs text-slate-400">HEIGHT</span>{{ $p['height'] ?? '-' }} cm</div>
      </div>
    </section>
  </div>

  <div class="mt-10">
    <div class="flex gap-4 text-sm">
      <button type="button" class="tab-btn px-3 py-2 rounded bg-slate-900 text-white" data-tab="desc">DESCRIPTION</button>
      <button type="button" class="tab-btn px-3 py-2 rounded bg-slate-100" data-tab="spec">SPECIFICATION</button>
      <button type="button" class="tab-btn px-3 py-2 rounded bg-slate-100" data-tab="rev">REVIEW</button>
    </div>

    <div class="mt-4">
      <div id="tab-desc" class="tab-pane">
        <p class="leading-relaxed text-slate-700">
          {{ $p['deskripsi'] }}
        </p>
      </div>

      <div id="tab-spec" class="tab-pane hidden">
        <div class="grid sm:grid-cols-2 gap-y-2 text-sm">
          @foreach(($p['spesifikasi'] ?? []) as $k => $v)
            <div class="flex justify-between border-b py-1">
              <span class="text-slate-500">{{ $k }}</span>
              <span class="font-medium">{{ $v }}</span>
            </div>
          @endforeach
        </div>
      </div>

      <div id="tab-rev" class="tab-pane hidden">
        <div class="space-y-4">
          @forelse(($p['ulasan'] ?? []) as $u)
            <div class="rounded-lg border bg-white p-3">
              <div class="flex items-center justify-between">
                <strong>{{ $u['nama'] }}</strong>
                <span class="text-xs">⭐ {{ $u['rating'] }}/5</span>
              </div>
              <p class="mt-1 text-slate-700">{{ $u['isi'] }}</p>
            </div>
          @empty
            <p class="text-slate-500 text-sm">Belum ada ulasan.</p>
          @endforelse
        </div>
      </div>
    </div>
  </div>

</div>

<script>
  (function(){
    const btns = document.querySelectorAll('.tab-btn');
    const panes = {
      desc: document.getElementById('tab-desc'),
      spec: document.getElementById('tab-spec'),
      rev:  document.getElementById('tab-rev'),
    };
    btns.forEach(b=>{
      b.addEventListener('click', ()=>{
        const k = b.dataset.tab;
        Object.keys(panes).forEach(key=>{
          panes[key].classList.toggle('hidden', key !== k);
        });
        btns.forEach(x=>{
          x.classList.toggle('bg-slate-900', x===b);
          x.classList.toggle('text-white',   x===b);
          x.classList.toggle('bg-slate-100', x!==b);
        });
      });
    });
  })();
</script>
@endsection
