<x-app-layout>
@php
    $categoryMap = collect($categories ?? [])->filter(fn ($cat) => !empty($cat['slug']))->keyBy('slug');
    $slotDefinitions = collect([
        [
            'label' => __('All Products'),
            'slugs' => [null],
            'background' => config('storefront.category_backgrounds.default', 'images/PcBan.png'),
        ],
        [
            'label' => __('Gaming'),
            'slugs' => ['gaming', 'pc-gaming'],
            'background' => config('storefront.category_backgrounds.gaming', 'images/PcBlack.png'),
        ],
        [
            'label' => __('Office'),
            'slugs' => ['office', 'pc-office'],
            'background' => config('storefront.category_backgrounds.office', 'images/PcOffice.png'),
        ],
        [
            'label' => __('School'),
            'slugs' => ['school', 'pc-school'],
            'background' => config('storefront.category_backgrounds.school', 'images/PcSchool.webp'),
        ],
    ]);

    $slots = $slotDefinitions->map(function ($slot) use ($categoryMap) {
        $resolvedSlug = null;
        $slugData = null;

        foreach ($slot['slugs'] as $slugOption) {
            if ($slugOption === null) {
                $resolvedSlug = null;
                break;
            }

            if ($categoryMap->has($slugOption)) {
                $resolvedSlug = $slugOption;
                $slugData = $categoryMap->get($slugOption);
                break;
            }
        }

        return [
            'label' => $slot['label'],
            'slug' => $resolvedSlug,
            'background' => $slugData['background'] ?? $slot['background'],
            'available' => $resolvedSlug === null ? true : ($slugData !== null),
        ];
    })->values();

    $heroCategory = $slots->first();
    $gridCategories = $slots->slice(1);
@endphp

    <section class="font-aerospace space-y-10 p-6">
        <section class="bg-white rounded-lg shadow-lg border p-6 md:p-8 grid md:grid-cols-2 gap-6 items-center">
            <div class="space-y-4">
                <h1 class="text-3xl md:text-4xl tracking-wide font-extrabold">Easy To Use</h1>
                <p class="text-sm text-gray-600 max-w-md">
                    "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua."
                </p>
                <a href="{{ route('products.index') }}" class="inline-block px-5 py-2 rounded-sm bg-black text-white font-semibold hover:opacity-90">DISCOVER</a>
            </div>
            <div class="w-[35vw]">
                <img class="w-full rounded-md" src="{{ asset('images/Pcban.png') }}" alt="PC preview">
            </div>
        </section>

        <section>
            <h2 class="text-center font-extrabold tracking-wide text-lg md:text-xl mb-4">
                OUR CATEGORIES
            </h2>

            <div class="grid grid-cols-1 gap-4">
                @if ($heroCategory)
                    <a href="{{ route('products.index') }}"
                       class="relative overflow-hidden rounded-sm border shadow-lg bg-white flex">
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <img src="{{ asset($heroCategory['background']) }}"
                                 alt="{{ $heroCategory['label'] }}"
                                 class="w-40 object-cover object-center opacity-40" />
                        </div>
                        <div class="relative z-10 h-40 md:h-44 w-full px-6 flex items-center justify-center text-center">
                            <p class="text-3xl md:text-4xl font-black tracking-wide text-slate-900">
                                {{ strtoupper($heroCategory['label']) }}
                            </p>
                        </div>
                    </a>
                @else
                    <div class="relative overflow-hidden rounded-sm border shadow-lg bg-white flex items-center justify-center h-40 md:h-44">
                        <p class="text-sm font-semibold text-gray-500">Tidak ada produk.</p>
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                @foreach ($gridCategories as $cat)
                    @if ($cat['available'])
                        <a href="{{ route('products.index', $cat['slug']) }}"
                           class="relative overflow-hidden rounded-sm border shadow-lg bg-white">
                            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                <img src="{{ asset($cat['background']) }}"
                                     alt="{{ $cat['label'] }}"
                                     class="w-40 object-cover object-center opacity-40" />
                            </div>
                            <div class="relative z-10 h-36 md:h-40 w-full px-6 flex items-center justify-center text-center">
                                <p class="text-3xl md:text-4xl font-black tracking-wide text-slate-900">
                                    {{ strtoupper($cat['label']) }}
                                </p>
                            </div>
                        </a>
                    @else
                        <div class="relative overflow-hidden rounded-sm border shadow-lg bg-white flex flex-col items-center justify-center h-36 md:h-40 gap-1">
                            <p class="text-xl font-black tracking-wide text-slate-400">
                                {{ strtoupper($cat['label']) }}
                            </p>
                            <p class="text-sm font-semibold text-slate-500">Tidak ada produk.</p>
                        </div>
                    @endif
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
