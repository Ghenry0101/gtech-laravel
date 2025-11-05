<x-app-layout>
@php
    $categories = [
        ['title' => 'PREBUILT PC', 'bg' => 'bayangan-pc.png'],
        ['title' => 'COMPONENTS', 'bg' => 'bayangan-4.png'],
        ['title' => 'GAMING', 'bg' => 'bayangan-1.png'],
        ['title' => 'OFFICE', 'bg' => 'bayangan-2.png'],
        ['title' => 'SCHOOL', 'bg' => 'bayangan-3.png'],
    ];
@endphp

    <section class="font-aerospace space-y-10">
        <section class="bg-white rounded-xl shadow-sm border p-6 md:p-8 grid md:grid-cols-2 gap-6 items-center">
            <div class="space-y-4">
                <h1 class="text-3xl md:text-4xl font-black tracking-wide">easy to use</h1>
                <p class="text-sm text-gray-600 max-w-md">
                    "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua."
                </p>
                <a href="#" class="inline-block px-5 py-2 rounded-md bg-black text-white font-semibold hover:opacity-90">DISCOVER</a>
            </div>
            <div class="w-full">
                <img class="w-full rounded-lg" src="{{ asset('images/PC.png') }}" alt="PC preview">
            </div>
        </section>

        <section>
            <h2 class="text-center font-extrabold tracking-wide text-lg md:text-xl mb-4">
                OUR CATEGORIES
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach (array_slice($categories, 0, 2) as $cat)
                    <a href="#"
                       class="relative overflow-hidden rounded-2xl ring-1 ring-black/10 shadow-[0_6px_20px_-6px_rgba(0,0,0,0.15)] bg-white">
                        <img src="{{ asset('images/'.$cat['bg']) }}"
                             alt="{{ $cat['title'] }}"
                             class="absolute inset-0 h-full w-full object-cover pointer-events-none" />
                        <div class="relative z-10 h-40 md:h-44 grid place-items-center px-6">
                            <p class="text-3xl md:text-4xl font-black tracking-wide text-slate-900 text-center">
                                {{ $cat['title'] }}
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                @foreach (array_slice($categories, 2) as $cat)
                    <a href="#"
                       class="relative overflow-hidden rounded-2xl ring-1 ring-black/10 shadow-[0_6px_20px_-6px_rgba(0,0,0,0.15)] bg-white">
                        <img src="{{ asset('images/'.$cat['bg']) }}"
                             alt="{{ $cat['title'] }}"
                             class="absolute inset-0 h-full w-full object-cover pointer-events-none" />
                        <div class="relative z-10 h-36 md:h-40 grid place-items-center px-6">
                            <p class="text-3xl md:text-4xl font-black tracking-wide text-slate-900 text-center">
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
