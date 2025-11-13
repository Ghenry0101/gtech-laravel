<x-app-layout>
@php
    $categories = [
        ['title' => 'ALL PRODUCT', 'bg' => 'PcG1.png'],
        ['title' => 'COMPONENTS', 'bg' => 'bayangan-4.png'],
        ['title' => 'GAMING', 'bg' => 'PcBlack.png'],
        ['title' => 'OFFICE', 'bg' => 'PcOffice.png'],
        ['title' => 'SCHOOL', 'bg' => 'PcSchool.webp'],
    ];
@endphp

    <section class="font-aerospace space-y-10 p-6">
        <section class="bg-white rounded-lg shadow-lg border p-6 md:p-8 grid md:grid-cols-2 gap-6 items-center">
            <div class="space-y-4">
                <h1 class="text-3xl md:text-4xl tracking-wide font-extrabold">Easy To Use</h1>
                <p class="text-sm text-gray-600 max-w-md">
                    "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua."
                </p>
                <a href="#" class="inline-block px-5 py-2 rounded-sm bg-black text-white font-semibold hover:opacity-90">DISCOVER</a>
            </div>
            <div class="w-[35vw]">
                <img class="w-full rounded-md" src="{{ asset('images/Pcban.png') }}" alt="PC preview">
            </div>
        </section>

        <section>
            <h2 class="text-center font-extrabold tracking-wide text-lg md:text-xl mb-4">
                OUR CATEGORIES
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-1 gap-4">
                @foreach (array_slice($categories, 0, 1) as $cat)
                    <a href="#"
                       class="relative overflow-hidden rounded-sm border shadow-lg bg-white flex">
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <img src="{{ asset('images/'.$cat['bg']) }}"
                                 alt="{{ $cat['title'] }}"
                                 class="w-40 object-cover object-center opacity-40" />
                        </div>
                        <div class="relative z-10 h-40 md:h-44 w-full px-6 flex items-center justify-center text-center">
                            <p class="text-3xl md:text-4xl font-black tracking-wide text-slate-900">
                                {{ $cat['title'] }}
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                @foreach (array_slice($categories, 2) as $cat)
                    <a href="#"
                       class="relative overflow-hidden rounded-sm border shadow-lg bg-white">
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <img src="{{ asset('images/'.$cat['bg']) }}"
                                 alt="{{ $cat['title'] }}"
                                 class="w-40 object-cover object-center opacity-40" />
                        </div>
                        <div class="relative z-10 h-36 md:h-40 w-full px-6 flex items-center justify-center text-center">
                            <p class="text-3xl md:text-4xl font-black tracking-wide text-slate-900">
                                {{ $cat['title'] }}
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>

        <section>
            <div class="text-center">
                <h3 class="font-extrabold tracking-wide">OUR POPULAR PRODUCTS</h3>
                <a href="#" class="text-xs text-gray-500 font-semibold">DISCOVER +</a>
            </div>
            @include('components.product-grid', ['items' => $popular ?? []])
        </section>

        <section>
            <div class="text-center">
                <h3 class="font-extrabold tracking-wide">OUR LATEST PRODUCTS</h3>
                <a href="#" class="text-xs text-gray-500 font-semibold">DISCOVER +</a>
            </div>
            @include('components.product-grid', ['items' => $latest ?? []])
        </section>
    </section>
</x-app-layout>
