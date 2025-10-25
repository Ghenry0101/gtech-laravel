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
  <main class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 py-6">
    @yield('content')
  </main>
  @include('partials.footer')
</body>
</html>
