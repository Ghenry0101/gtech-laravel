<header class="bg-white border-b sticky top-0 z-40 font-aerospace">
  <div class="max-w-[1200px] mx-auto h-14 px-4 sm:px-6 lg:px-8 flex items-center relative">

    <a href="{{ url('/') }}" class="tracking-wider text-xl">
      <span class="font-medium">G</span>-<span class="font-medium">TECH</span>
    </a>

    <form action="{{ route('search') }}" method="GET"
          class="absolute left-1/2 -translate-x-1/2 w-full max-w-3xl px-4">
      <div class="relative">
        <input
          type="text"
          name="q"
          value="{{ request('q') }}"
          placeholder="Search products…"
          class="w-150 h-10 rounded-lg border border-slate-300 bg-slate-50 pl-11 pr-4 text-sm outline-none
                 focus:bg-white focus:ring-2 focus:ring-slate-900/10"
          autocomplete="off" />
          
        <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 " fill="black" viewBox="0 0 640 640" width="16" height="16">
          <path d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376C296.3 401.1 253.9 416 208 416 93.1 416 0 322.9 0 208S93.1 0 208 0 416 93.1 416 208zM208 352a144 144 0 1 0 0-288 144 144 0 1 0 0 288z"/>
        </svg>
      </div>
    </form>

    <div class="flex-1">
      
    </div>

    <div class="flex items-center gap-2">

      <a href="#" class="px-3 py-1.5 rounded-md bg-blue-600 text-white text-xs font-medium hover:opacity-90">Daftar</a>
      <a href="{{ route('login') }}" class="px-3 py-1.5 rounded-md border border-blue-600 text-blue-600 text-xs font-medium hover:bg-blue-50">Masuk</a>
    <a href="{{ route('cart.index') }}" class="px-3 py-1.5 inline-flex items-center justify-center w-9 h-9 rounded-md hover:bg-slate-50 hover:opacity-90" title="Cart" aria-label="Cart">
        <svg class="w-4 h-4 shrink-0" viewBox="0 0 640 640" fill="currentColor" width="16" height="16">
          <path d="M24 48C10.7 48 0 58.7 0 72S10.7 96 24 96H69.3c3.9 0 7.2 2.8 7.9 6.6L129.3 388.9C135.5 423.1 165.3 448 200.1 448H456c13.3 0 24-10.7 24-24s-10.7-24-24-24H200.1c-11.6 0-21.5-8.3-23.6-19.7L171.4 352H475c30.8 0 57.2-21.9 62.9-52.2L568.9 133.9C572.6 114.2 557.5 96 537.4 96H124.7l-.4-2C119.5 67.4 96.3 48 69.2 48H24zM208 576a48 48 0 1 0 0-96a48 48 0 1 0 0 96zm224 0a48 48 0 1 0 0-96a48 48 0 1 0 0 96z"/>
        </svg>
      </a>
    </div>
  </div>
</header>
