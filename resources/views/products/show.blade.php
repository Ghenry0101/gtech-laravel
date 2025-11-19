@php
    $imageUrl = $product->product_image ? asset('storage/'.$product->product_image) : asset('images/PC.png');
    $discountActive = $product->hasDiscountActive();
    $hasDiscountConfigured = $product->hasDiscountConfigured();
    $discountPercent = $product->discount_percent ?? ($product->price > 0 ? ($product->price - $product->effective_price) / $product->price * 100 : null);
    $discountStart = optional($product->discount_start)?->format('d M Y H:i') ?? __('sekarang');
    $discountEnd = optional($product->discount_end)?->format('d M Y H:i') ?? __('tanpa batas');
    $specs = [
        ['label' => __('Stok'), 'value' => number_format($product->stock), 'helper' => __('tersedia untuk dipesan')],
        ['label' => __('Berat'), 'value' => number_format($product->weight).' gr', 'helper' => __('dalam gram')],
        ['label' => __('Dimensi'), 'value' => $product->length.' x '.$product->width.' x '.$product->height.' cm', 'helper' => __('(P x L x T)')],
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-500">{{ $product->category?->name ?? __('Tanpa kategori') }}</p>
                <h1 class="text-2xl font-bold text-gray-900">{{ $product->name }}</h1>
            </div>
            <a href="{{ route('home') }}" class="text-sm text-slate-500 hover:text-slate-700">{{ __('Kembali ke beranda') }}</a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-8xl space-y-10 sm:px-6 lg:px-8">
            <div class="grid gap-8 rounded-md border border-slate-200 bg-white p-6 shadow-sm lg:grid-cols-3">
                <div class="lg:col-span-2 space-y-6">
                    <div class="overflow-hidden rounded-md border border-slate-200 bg-slate-50 ">
                        <img src="{{ $imageUrl }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                    </div>

                    <div class="space-y-4 text-sm text-slate-600">
                        <div>
                            <p class="font-semibold text-slate-800">{{ __('Deskripsi Produk') }}</p>
                            <p class="mt-1 leading-relaxed">{{ $product->description ?: __('Belum ada deskripsi.') }}</p>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-3">
                            @foreach ($specs as $spec)
                                <div class="rounded-md border border-slate-200 p-4">
                                    <p class="text-xs uppercase text-slate-400">{{ $spec['label'] }}</p>
                                    <p class="text-lg font-semibold text-slate-900">{{ $spec['value'] }}</p>
                                    <p class="text-xs text-slate-400">{{ $spec['helper'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-md border border-slate-200 bg-gray-900 p-6 text-white shadow-md">
                        <p class="text-xs uppercase tracking-wide text-slate-300">{{ __('Harga Promo') }}</p>
                        <p class="mt-2 text-4xl font-bold">
                            Rp {{ number_format($product->effective_price, 0, ',', '.') }}
                        </p>
                        @if ($discountActive)
                            <p class="text-sm text-slate-300 line-through">
                                Rp {{ number_format($product->price, 0, ',', '.') }}
                            </p>
                            <p class="mt-2 inline-flex items-center rounded-full bg-emerald-500/20 px-3 py-1 text-xs font-semibold text-emerald-200">
                                {{ __('Diskon :percent%', ['percent' => number_format($discountPercent, 2)]) }}
                            </p>
                        @elseif ($hasDiscountConfigured)
                            <p class="mt-2 text-sm text-amber-200">
                                {{ __('Promo akan dimulai pada :date', ['date' => $discountStart]) }}
                            </p>
                            <p class="text-xs text-slate-300">{{ __('Harga normal: Rp :price', ['price' => number_format($product->price, 0, ',', '.')]) }}</p>
                        @else
                            <p class="text-sm text-slate-300">{{ __('Harga normal tanpa promo aktif.') }}</p>
                        @endif

                        @auth
                            <form method="POST" action="{{ route('cart.store') }}" class="mt-6 space-y-4">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <div>
                                    <label for="quantity" class="text-sm font-medium text-slate-200">{{ __('Jumlah') }}</label>
                                    <div class="mt-1 flex items-center gap-2">
                                        <input
                                            type="number"
                                            min="1"
                                            max="{{ max($product->stock, 1) }}"
                                            id="quantity"
                                            name="quantity"
                                            value="1"
                                            class="w-24 rounded-sm border-slate-300 text-center text-sm text-slate-900"
                                        >
                                        <span class="text-xs text-slate-200">{{ __('Tersisa :stock', ['stock' => number_format($product->stock)]) }}</span>
                                    </div>
                                </div>
                                <div class="flex flex-col gap-3">
                                    <button
                                        type="submit"
                                        name="action"
                                        value="buy"
                                        class="inline-flex w-full items-center justify-center rounded-sm bg-white px-4 py-2.5 text-sm font-semibold text-slate-900 shadow-sm hover:bg-slate-100"
                                    >
                                        {{ __('Beli Sekarang') }}
                                    </button>
                                    <button
                                        type="submit"
                                        name="action"
                                        value="add"
                                        class="inline-flex w-full items-center justify-center rounded-sm border border-white/40 px-4 py-2.5 text-sm font-semibold text-white hover:bg-white/10"
                                    >
                                        {{ __('Tambah ke Keranjang') }}
                                    </button>
                                </div>
                            </form>
                        @else
                            <div class="mt-6 rounded-md border border-white/30 bg-white/10 p-4 text-sm">
                                {{ __('Silakan masuk untuk membeli atau menambahkan produk ke keranjang.') }}
                            </div>
                        @endauth
                    </div>

                    <div class="rounded-md border border-slate-200 bg-white p-5 text-sm text-slate-600 shadow-sm">
                        <p class="text-sm font-semibold text-slate-900">{{ __('Informasi Diskon') }}</p>
                        <p class="mt-1">
                            {{ __('Mulai: :start', ['start' => $discountStart]) }}<br>
                            {{ __('Berakhir: :end', ['end' => $discountEnd]) }}
                        </p>
                        <p class="mt-4 text-sm font-semibold text-slate-900">{{ __('Pengiriman & Garansi') }}</p>
                        <ul class="mt-1 list-disc space-y-1 pl-4 text-xs">
                            <li>{{ __('Pengiriman nasional melalui partner logistik terpercaya.') }}</li>
                            <li>{{ __('Garansi toko 7 hari untuk kerusakan produksi.') }}</li>
                            <li>{{ __('Layanan pelanggan siap membantu setiap hari kerja.') }}</li>
                        </ul>
                    </div>
                </div>
            </div>

            <section class="rounded-md border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">{{ __('Ulasan Pelanggan') }}</h3>
                        <p class="text-sm text-slate-500">
                            @if ($selectedRating)
                                {{ __('Menampilkan :count ulasan dengan rating :rating bintang.', ['count' => $reviews->total(), 'rating' => $selectedRating]) }}
                            @else
                                {{ __('Menampilkan :count ulasan terbaru.', ['count' => $reviews->total()]) }}
                            @endif
                        </p>
                    </div>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:gap-6">
                        @if ($reviewStats['count'])
                            <div class="flex items-center gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-2 text-amber-700">
                                <strong class="text-2xl">{{ number_format($reviewStats['average'], 1) }}</strong>
                                <span class="text-xs uppercase tracking-wide">{{ __(':count ulasan', ['count' => $reviewStats['count']]) }}</span>
                            </div>
                        @endif
                        <form method="GET" action="{{ route('products.show', $product) }}" class="flex flex-col text-sm">
                            <label for="rating-filter" class="text-xs uppercase text-slate-400">{{ __('Filter Rating') }}</label>
                            <select id="rating-filter" name="rating" class="mt-1 w-48 rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none focus:ring-0" onchange="this.form.submit()">
                                <option value="">{{ __('Semua rating') }}</option>
                                @foreach ($reviewStats['distribution'] as $rating => $count)
                                    <option value="{{ $rating }}" @selected($selectedRating === $rating)>
                                        {{ $rating }} ★ ({{ $count }})
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>

                @if ($reviewStats['count'])
                    <div class="mt-4 flex flex-wrap gap-2 text-xs text-slate-600">
                        @foreach ($reviewStats['distribution'] as $rating => $count)
                            <span class="inline-flex items-center rounded-full border border-slate-200 px-3 py-1">
                                {{ $rating }} ★ &middot; {{ $count }}
                            </span>
                        @endforeach
                    </div>
                @endif

                <div class="mt-6 space-y-4">
                    @forelse ($reviews as $review)
                        @php
                            $customer = optional(optional($review->orderItem)->order)->user;
                            $customerName = $customer?->name ?? __('Pelanggan');
                        @endphp
                        <article class="rounded-md border border-slate-100 bg-slate-50 p-4">
                            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                                <p class="font-semibold text-slate-900">{{ $customerName }}</p>
                                <p class="text-xs text-slate-500">{{ optional($review->reviewed_at ?? $review->created_at)->format('d M Y H:i') }}</p>
                            </div>
                            <div class="mt-2 flex items-center gap-1 text-amber-400">
                                @for ($i = 1; $i <= 5; $i++)
                                    <svg class="h-4 w-4" fill="{{ $review->rating >= $i ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.401c.5.036.703.665.322.995l-4.208 3.63a.563.563 0 00-.182.557l1.257 5.29a.563.563 0 01-.84.61l-4.725-2.77a.563.563 0 00-.57 0l-4.725 2.77a.563.563 0 01-.84-.61l1.257-5.29a.563.563 0 00-.182-.556l-4.208-3.63a.563.563 0 01.322-.996l5.518-.4a.563.563 0 00.475-.345l2.125-5.112z" />
                                    </svg>
                                @endfor
                                <span class="ml-2 text-xs text-slate-500">{{ number_format($review->rating, 1) }}</span>
                            </div>
                            <p class="mt-3 text-sm text-slate-600">{{ $review->comment ?: __('Pengguna tidak memberikan komentar tambahan.') }}</p>
                            @if ($review->images->isNotEmpty())
                                <div class="mt-3 flex flex-wrap gap-3">
                                    @foreach ($review->images as $image)
                                        <div class="relative">
                                            <button type="button" class="block h-20 w-20 overflow-hidden rounded-xl border border-slate-200 bg-white" data-image-preview="{{ asset('storage/' . $image->path) }}">
                                                <img src="{{ asset('storage/' . $image->path) }}" alt="{{ __('Foto ulasan pelanggan') }}" class="h-full w-full object-cover">
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </article>
                    @empty
                        <p class="text-sm text-slate-500">
                            @if ($selectedRating)
                                {{ __('Belum ada ulasan dengan rating :rating bintang.', ['rating' => $selectedRating]) }}
                            @else
                                {{ __('Belum ada ulasan untuk produk ini.') }}
                            @endif
                        </p>
                    @endforelse
                </div>

                @if (method_exists($reviews, 'hasPages') && $reviews->hasPages())
                    <div class="mt-6">
                        {{ $reviews->links() }}
                    </div>
                @endif
            </section>

            <section>
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-900">{{ __('Produk Terkait') }}</h3>
                </div>
                @include('components.product-grid', [
                    'items' => $relatedProducts->map(fn ($item) => [
                        'title' => $item->name,
                        'slug' => $item->slug,
                        'status' => $item->stock > 0 ? 'available' : 'unavailable',
                        'price' => $item->effective_price,
                        'original_price' => $item->price,
                        'planned_price' => $item->discount_price,
                        'has_discount' => $item->hasDiscountConfigured(),
                        'is_discount_active' => $item->hasDiscountActive(),
                        'discount_percent' => $item->discount_percent,
                        'discount_start' => optional($item->discount_start)?->format('d M Y H:i'),
                        'discount_end' => optional($item->discount_end)?->format('d M Y H:i'),
                        'image_path' => $item->product_image ? 'storage/' . $item->product_image : null,
                        'stock' => $item->stock,
                    ]),
                ])
            </section>
        </div>
    </div>
</x-app-layout>

@push('scripts')
    @include('components.review-image-scripts')
@endpush
