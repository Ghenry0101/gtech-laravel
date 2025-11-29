<x-app-layout>
@php
    $categoryMap = collect($categories ?? [])->filter(fn ($cat) => !empty($cat['slug']))->keyBy('slug');
    $slotDefinitions = collect([
        [
            'label' => __('All Categories'),
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

    $categoryCards = $slots->map(function ($slot) {
        $key = strtolower($slot['label']);

        $icon = match ($key) {
            'gaming' => new Illuminate\Support\HtmlString(
                '<svg class="h-7 w-7 text-slate-900 transition duration-300 group-hover:text-slate-950" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M6.75 9h10.5a2.25 2.25 0 012.22 1.92l.53 3.7a1.88 1.88 0 01-1.86 2.15 1.88 1.88 0 01-1.82-1.59l-.17-1.23a.75.75 0 00-.74-.65H8.29a.75.75 0 00-.74.65l-.17 1.23a1.88 1.88 0 01-1.82 1.59 1.88 1.88 0 01-1.86-2.15l.53-3.7A2.25 2.25 0 016.75 9z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M9 12v3M7.5 13.5h3M16.875 12h.007M14.625 13.5h.007" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>'
            ),
            'office' => new Illuminate\Support\HtmlString(
                '<svg class="h-7 w-7 text-slate-900 transition duration-300 group-hover:text-slate-950" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M4.5 21V7.125A2.625 2.625 0 017.125 4.5h9.75A2.625 2.625 0 0119.5 7.125V21M3 21h18M9 21v-4.5h6V21M9.75 9h4.5M9.75 12.75h4.5M9.75 16.5h4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>'
            ),
            'school' => new Illuminate\Support\HtmlString(
                '<svg class="h-7 w-7 text-slate-900 transition duration-300 group-hover:text-slate-950" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 14.25v6m0-6L3.75 9 12 3.75 20.25 9 12 14.25z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M6.75 12v6.75A2.25 2.25 0 009 21h6a2.25 2.25 0 002.25-2.25V12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>'
            ),
            default => new Illuminate\Support\HtmlString(
                '<svg class="h-7 w-7 text-slate-900 transition duration-300 group-hover:text-slate-950" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M4.5 5.25A2.25 2.25 0 016.75 3h3a2.25 2.25 0 012.25 2.25v3A2.25 2.25 0 019.75 10.5h-3A2.25 2.25 0 014.5 8.25v-3zM4.5 15.75A2.25 2.25 0 016.75 18h3a2.25 2.25 0 002.25-2.25v-3a2.25 2.25 0 00-2.25-2.25h-3a2.25 2.25 0 00-2.25 2.25v3zM13.5 5.25A2.25 2.25 0 0115.75 3h3a2.25 2.25 0 012.25 2.25v3a2.25 2.25 0 01-2.25 2.25h-3a2.25 2.25 0 01-2.25-2.25v-3zM13.5 15.75A2.25 2.25 0 0115.75 13.5h3a2.25 2.25 0 012.25 2.25v3A2.25 2.25 0 0118.75 21h-3a2.25 2.25 0 01-2.25-2.25v-3z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>'
            ),
        };

        return $slot + ['icon' => $icon];
    });
@endphp

    <section class="font-aerospace space-y-8 px-4 py-8 sm:px-6 lg:px-8">
        <section class="grid items-center gap-5 rounded-2xl border border-slate-100 bg-white/95 p-5 shadow-sm sm:p-6 md:grid-cols-2 md:gap-8 md:p-8">
            <div class="space-y-3">
                <p class="text-xs font-semibold uppercase tracking-[0.4em] text-slate-400">New experience</p>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 md:text-4xl">Easy To Use</h1>
                <p class="text-sm text-slate-600 max-w-md">
                    Platform yang ringkas untuk membeli komponen PC dan rakitan siap pakai sesuai kebutuhan gaming, kantor, maupun sekolah.
                </p>
            </div>
            <div class="max-w-md justify-self-center md:justify-self-end">
                <img class="w-full rounded-lg object-cover" src="{{ asset('images/Pcban.png') }}" alt="PC preview">
            </div>
        </section>

        <section class="rounded-3xl border border-slate-100 bg-[#f5f7fb] px-5 py-8 shadow-inner sm:px-7 sm:py-10">
            <div class="mx-auto max-w-6xl space-y-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.5em] text-slate-400">Our categories</p>
                        <h2 class="text-2xl font-black text-slate-900 sm:text-3xl">Pilih kebutuhanmu lebih cepat</h2>
                        <p class="text-sm text-slate-500">Grid rapat dengan ikon seragam bikin tiap kategori sama-sama menonjol tanpa banyak ruang kosong.</p>
                    </div>
                    <a href="{{ route('products.index') }}"
                       class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 transition hover:text-slate-900">
                        {{ __('Lihat semua produk') }}
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14m0 0-4-4m4 4-4 4" />
                        </svg>
                    </a>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($categoryCards as $card)
                        @if ($card['available'])
                            @php
                                $url = $card['slug'] ? route('products.index', $card['slug']) : route('products.index');
                            @endphp
                            <a href="{{ $url }}"
                               class="group relative overflow-hidden rounded-2xl border border-slate-100 bg-white/90 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-300 sm:p-6">
                                <div class="absolute inset-0 bg-white/80 backdrop-blur-[3px] transition duration-300 group-hover:bg-white/55"></div>
                                <div class="absolute -right-8 -bottom-8 h-32 w-32 rounded-full bg-slate-900/5 blur-3xl"></div>
                                <div class="relative flex h-full flex-col gap-4">
                                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-900/5 text-slate-900 transition duration-300 group-hover:bg-slate-900/10">
                                        {!! $card['icon'] !!}
                                    </span>
                                    <div class="space-y-1">
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.4em] text-slate-400">Category</p>
                                        <p class="text-xl font-black text-slate-900 transition duration-300 group-hover:text-slate-950">
                                            {{ strtoupper($card['label']) }}
                                        </p>
                                    </div>
                                    <p class="text-sm text-slate-500">
                                        {{ $card['slug'] ? __('Fokus pada kebutuhan :name.', ['name' => strtolower($card['label'])]) : __('Telusuri seluruh katalog produk kami.') }}
                                    </p>
                                </div>
                            </a>
                        @else
                            <div class="relative rounded-2xl border border-dashed border-slate-300 bg-white/70 p-5 text-center text-slate-400 sm:p-6">
                                <p class="text-xl font-black tracking-wide">{{ strtoupper($card['label']) }}</p>
                                <p class="text-sm font-semibold text-slate-500">{{ __('Tidak ada produk.') }}</p>
                            </div>
                        @endif
                    @endforeach
                </div>
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
