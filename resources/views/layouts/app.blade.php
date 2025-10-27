<!doctype html>
<html lang="id" class="scroll-smooth">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title','G-TECH')</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="bg-[#F3F4F6] text-gray-900 antialiased">
  @include('partials.header')
<div id="searchBar" class="hidden border-b bg-white shadow-sm">
  <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 py-3">
    <form action="{{ url('/search') }}" method="GET" class="flex items-center gap-2">
      <input id="searchInput" name="q" type="text" placeholder="Cari produk, kategori, dll…"
        class="flex-1 h-10 rounded-md border border-gray-300 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
      <button class="h-10 px-4 rounded-md bg-blue-600 text-white text-sm font-semibold hover:opacity-90"
        type="submit">Cari</button>
    </form>
  </div>
</div>
  <main class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 py-6">
    @yield('content')
  </main>
  @include('partials.footer')
</body>
</html>
