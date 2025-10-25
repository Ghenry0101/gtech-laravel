<header class="bg-white border-b sticky top-0 z-40">
  <div class="max-w-[1200px] mx-auto h-12 px-4 sm:px-6 lg:px-8 flex items-center gap-4">
    {{-- Logo --}}
    <a href="{{ url('/') }}" class="font-black tracking-wider text-lg">G-<span class="font-extrabold">TECH</span></a>

    <nav class="hidden md:flex items-center gap-6 text-sm font-semibold">
      <a href="#" class="hover:text-blue-600">GAMING</a>
      <a href="#" class="hover:text-blue-600">PREBUILT</a>
      <a href="#" class="hover:text-blue-600">SCHOOL</a>
      <a href="#" class="hover:text-blue-600">COMPONENTS</a>
      <a href="#" class="hover:text-blue-600">SUPPORT</a>
    </nav>

    <div class="flex-1">
      <div class="relative">
        <input type="text" placeholder="Cari…"
          class="w-full h-9 rounded-md border border-gray-300 pl-9 pr-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"/>
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="currentColor">
          <path d="M10 4a6 6 0 1 1 0 12 6 6 0 0 1 0-12Zm8.71 13.29-3.4-3.4A8 8 0 1 0 12 20a8 8 0 0 0 5.89-2.6l3.4 3.4a1 1 0 1 0 1.42-1.42Z"/>
        </svg>
      </div>
    </div>


    <div class="flex items-center gap-2">
      <button class="w-9 h-9 grid place-items-center rounded-md border hover:bg-gray-50" title="Cart">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M7 4H5a1 1 0 0 0 0 2h1.28l1.8 9.02A3 3 0 0 0 11.01 18H17a1 1 0 1 0 0-2h-5.99a1 1 0 0 1-.98-.8L9.8 13H17a3 3 0 0 0 2.92-2.32l.84-3.78A1 1 0 0 0 19.79 6H8.27L7.9 4.2A1 1 0 0 0 7 4Z"/></svg>
      </button>
      <a href="#" class="px-3 py-1.5 rounded-md bg-blue-600 text-white text-xs font-semibold hover:opacity-90">Daftar</a>
      <a href="#" class="px-3 py-1.5 rounded-md border border-blue-600 text-blue-600 text-xs font-semibold hover:bg-blue-50">Masuk</a>
    </div>
  </div>
</header>
