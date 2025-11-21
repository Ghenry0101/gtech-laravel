<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

   
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600&family=Russo+One&display=swap" rel="stylesheet">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

      
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            :root {
                font-family: 'Montserrat', 'Figtree', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            }

            .brand-font {
                font-family: 'Russo One', 'Montserrat', 'Figtree', system-ui, sans-serif;
            }
        </style>
    </head>
    <body class="antialiased bg-neutral-100 text-gray-900">
        <div class="min-h-screen flex items-center justify-center px-4 py-12 bg-neutral-100">
            <div class="w-full max-w-md bg-white rounded-[26px] shadow-[0_15px_35px_rgba(0,0,0,0.12)] border border-neutral-100">
                <div class="text-center border-b border-gray-200 px-10 pt-10 pb-8">
                    <a href="/" class="inline-block">
                        <span class="brand-font text-3xl  text-gray-900">G-TECH</span>
                    </a>
                </div>
                <div class="px-10 py-10">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
