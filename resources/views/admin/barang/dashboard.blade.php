<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard Admin Barang') }}
            </h2>
            <a href="{{ route('admin.barang.products.index') }}" class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-700">
                <svg class="h-4 w-4" viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-width="2">
                    <path d="M12 5v14M5 12h14" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                {{ __('Kelola Produk') }}
            </a>
        </div>
    </x-slot>

    <div class="py-10 space-y-8 p-6">
        <section>
            <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                <div class="bg-white shadow-sm rounded-lg p-5 border border-gray-100">
                    <p class="text-sm text-gray-500">{{ __('Total Produk Aktif') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($stats['totalProducts']) }}</p>
                    <p class="text-xs text-emerald-600 mt-2">{{ __('+ :count produk non-aktif', ['count' => number_format($stats['inactiveProducts'])]) }}</p>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-5 border border-gray-100">
                    <p class="text-sm text-gray-500">{{ __('Total Stok Tersedia (pcs)') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($stats['totalStockUnits']) }}</p>
                    <p class="text-xs text-gray-400 mt-2">{{ __('Rata-rata :avg pcs/produk', ['avg' => number_format($stats['totalProducts'] ? $stats['totalStockUnits'] / max($stats['totalProducts'], 1) : 0, 1)]) }}</p>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-5 border border-gray-100">
                    <p class="text-sm text-gray-500">
                        {{ __('Produk Hampir Habis (<= :threshold)', ['threshold' => $lowStockThreshold]) }}
                    </p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($stats['lowStockCount']) }}</p>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-5 border border-gray-100">
                    <p class="text-sm text-gray-500">{{ __('Produk Tidak Aktif') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($stats['inactiveProducts']) }}</p>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-5 border border-gray-100">
                    <p class="text-sm text-gray-500">{{ __('Produk Diskon') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($stats['discountedProducts']) }}</p>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-5 border border-gray-100">
                    <p class="text-sm text-gray-500">{{ __('Diskon Aktif / Terjadwal') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($stats['activeDiscounts']) }} / {{ number_format($stats['upcomingDiscounts']) }}</p>
                    <p class="text-xs text-gray-400 mt-2">{{ __('Aktif sekarang / menunggu tanggal mulai') }}</p>
                </div>
            </div>
        </section>

        <div class="grid gap-8 lg:grid-cols-2">
            <section class="bg-white shadow-sm rounded-lg border border-gray-100">
                <header class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-800">{{ __('Produk Hampir Habis') }}</h3>
                </header>
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 text-gray-600">
                                <tr>
                                    <th scope="col" class="px-4 py-2 text-left font-medium uppercase tracking-wider">{{ __('Produk') }}</th>
                                    <th scope="col" class="px-4 py-2 text-left font-medium uppercase tracking-wider">{{ __('Stok') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse ($lowStockProducts as $product)
                                    <tr class="text-gray-700">
                                        <td class="px-4 py-2">
                                            <div class="font-medium">{{ $product->name }}</div>
                                            <div class="text-xs text-gray-400">{{ $product->slug }}</div>
                                        </td>
                                        <td class="px-4 py-2">{{ number_format($product->stock) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="px-4 py-6 text-center text-gray-400">
                                            {{ __('Semua stok aman untuk saat ini.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section class="bg-white shadow-sm rounded-lg border border-gray-100">
                <header class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-800">{{ __('Produk Terbaru') }}</h3>
                </header>
                <div class="p-6">
                    <ul class="space-y-4">
                        @forelse ($latestProducts as $product)
                            <li class="flex items-start justify-between text-sm text-gray-700">
                                @php
                                    $hasDiscount = $product->hasDiscountConfigured();
                                    $isDiscountActive = $product->hasDiscountActive();
                                    $displayPrice = $isDiscountActive ? $product->effective_price : $product->price;
                                    $plannedPrice = $product->discount_price;
                                    if ($plannedPrice === null && $product->discount_percent !== null) {
                                        $plannedPrice = round($product->price - ($product->price * $product->discount_percent / 100), 2);
                                    }
                                @endphp
                                <div>
                                    <p class="font-medium">{{ $product->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $product->created_at?->format('d M Y') ?? 'N/A' }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-gray-900">Rp {{ number_format($displayPrice, 0, ',', '.') }}</p>
                                    @if ($isDiscountActive)
                                        <p class="text-xs text-gray-400 line-through">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                                    @endif
                                    @if ($hasDiscount)
                                        <p class="text-[11px] font-semibold {{ $isDiscountActive ? 'text-emerald-600' : 'text-amber-600' }}">
                                            {{ $isDiscountActive ? __('Diskon aktif') : __('Diskon terjadwal') }}
                                        </p>
                                        <p class="text-[11px] text-gray-400">
                                            {{ __('Periode: :start - :end', [
                                                'start' => optional($product->discount_start)->format('d M Y H:i') ?? __('sekarang'),
                                                'end' => optional($product->discount_end)->format('d M Y H:i') ?? __('tanpa batas'),
                                            ]) }}
                                        </p>
                                    @endif
                                    <p class="text-xs text-gray-400">{{ __('Stok: :stock', ['stock' => number_format($product->stock)]) }}</p>
                                </div>
                            </li>
                        @empty
                            <li class="text-center text-gray-400 py-4">
                                {{ __('Belum ada produk baru.') }}
                            </li>
                        @endforelse
                    </ul>
                </div>
            </section>
        </div>

        <section class="bg-white shadow-sm rounded-lg border border-gray-100">
            <header class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">{{ __('Produk Terlaris') }}</h3>
                <span class="text-xs text-gray-400">{{ __('Berdasarkan total quantity terjual') }}</span>
            </header>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th scope="col" class="px-4 py-2 text-left font-medium uppercase tracking-wider">{{ __('Produk') }}</th>
                                <th scope="col" class="px-4 py-2 text-left font-medium uppercase tracking-wider">{{ __('Qty Terjual') }}</th>
                                <th scope="col" class="px-4 py-2 text-left font-medium uppercase tracking-wider">{{ __('Jumlah Pesanan') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($topSellingProducts as $item)
                                <tr class="text-gray-700">
                                    <td class="px-4 py-2">{{ $item['product_name'] }}</td>
                                    <td class="px-4 py-2">{{ number_format($item['total_qty']) }}</td>
                                    <td class="px-4 py-2">{{ number_format($item['total_orders']) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-6 text-center text-gray-400">
                                        {{ __('Belum ada data penjualan produk.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <div class="grid gap-8 lg:grid-cols-2">
            <section class="bg-white shadow-sm rounded-lg border border-gray-100">
                <header class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800">{{ __('Diskon Aktif & Terjadwal') }}</h3>
                    <span class="text-xs text-gray-400">{{ __('Top 5 produk dengan diskon berjalan') }}</span>
                </header>
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 text-gray-600">
                                <tr>
                                    <th class="px-4 py-2 text-left font-medium uppercase tracking-wider">{{ __('Produk') }}</th>
                                    <th class="px-4 py-2 text-left font-medium uppercase tracking-wider">{{ __('Periode') }}</th>
                                    <th class="px-4 py-2 text-left font-medium uppercase tracking-wider">{{ __('Diskon') }}</th>
                                    <th class="px-4 py-2 text-right font-medium uppercase tracking-wider">{{ __('Harga Akhir') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse ($activeDiscountProducts as $product)
                                    @php
                                        $priceAfterDiscount = $product->discount_price;
                                        if ($priceAfterDiscount === null && $product->discount_percent !== null) {
                                            $priceAfterDiscount = max($product->price - ($product->price * $product->discount_percent / 100), 0);
                                        }
                                    @endphp
                                    <tr class="text-gray-700">
                                        <td class="px-4 py-2">
                                            <div class="font-medium">{{ $product->name }}</div>
                                            <div class="text-xs text-gray-400">{{ __('Stok: :stock', ['stock' => number_format($product->stock)]) }}</div>
                                        </td>
                                        <td class="px-4 py-2">
                                            <div class="text-xs text-gray-500">
                                                {{ optional($product->discount_start)->format('d M Y H:i') ?? __('Segera') }} -
                                                {{ optional($product->discount_end)->format('d M Y H:i') ?? __('Tanpa batas') }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-2">
                                            {{ $product->discount_percent ? number_format($product->discount_percent, 2) .'%' : '—' }}
                                        </td>
                                        <td class="px-4 py-2 text-right">
                                            <div class="font-semibold {{ $product->discount_start && $product->discount_start->isFuture() ? 'text-amber-600' : 'text-emerald-600' }}">
                                                {{ $priceAfterDiscount !== null ? 'Rp '.number_format($priceAfterDiscount, 0, ',', '.') : '—' }}
                                            </div>
                                            <div class="text-xs text-gray-400 line-through">Rp {{ number_format($product->price, 0, ',', '.') }}</div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-6 text-center text-gray-400">
                                            {{ __('Belum ada produk dengan diskon.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section class="bg-white shadow-sm rounded-lg border border-gray-100">
                <header class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800">{{ __('Produk Tidak Aktif Terbaru') }}</h3>
                    <span class="text-xs text-gray-400">{{ __('Pantau produk yang perlu ditinjau kembali') }}</span>
                </header>
                <div class="p-6">
                    <ul class="space-y-4 text-sm">
                        @forelse ($inactiveProductsList as $product)
                            <li class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-gray-800">{{ $product->name }}</p>
                                    <p class="text-xs text-gray-400">{{ __('Diperbarui :date', ['date' => optional($product->updated_at)->format('d M Y H:i') ?? '—']) }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-gray-900">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                                    <a href="{{ route('admin.barang.products.edit', $product->id) }}" class="text-xs text-indigo-600 hover:text-indigo-800">{{ __('Kelola') }}</a>
                                </div>
                            </li>
                        @empty
                            <li class="text-center text-gray-400 py-4">
                                {{ __('Semua produk sedang aktif.') }}
                            </li>
                        @endforelse
                    </ul>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
