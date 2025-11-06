<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Admin Barang') }}
        </h2>
    </x-slot>

    <div class="py-10 space-y-8">
        <section>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div class="bg-white shadow-sm rounded-lg p-5 border border-gray-100">
                    <p class="text-sm text-gray-500">{{ __('Total Produk Aktif') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($stats['totalProducts']) }}</p>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-5 border border-gray-100">
                    <p class="text-sm text-gray-500">{{ __('Produk Tidak Aktif') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($stats['inactiveProducts']) }}</p>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-5 border border-gray-100">
                    <p class="text-sm text-gray-500">
                        {{ __('Produk Hampir Habis (<= :threshold)', ['threshold' => $lowStockThreshold]) }}
                    </p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ number_format($stats['lowStockCount']) }}</p>
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
                                <div>
                                    <p class="font-medium">{{ $product->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $product->created_at?->format('d M Y') ?? 'N/A' }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-gray-900">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
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
                <span class="text-xs text-gray-400">{{ __('Berdasarkan total penjualan') }}</span>
            </header>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th scope="col" class="px-4 py-2 text-left font-medium uppercase tracking-wider">{{ __('Produk') }}</th>
                                <th scope="col" class="px-4 py-2 text-left font-medium uppercase tracking-wider">{{ __('Qty Terjual') }}</th>
                                <th scope="col" class="px-4 py-2 text-left font-medium uppercase tracking-wider">{{ __('Pendapatan') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($topSellingProducts as $item)
                                <tr class="text-gray-700">
                                    <td class="px-4 py-2">{{ $item['product_name'] }}</td>
                                    <td class="px-4 py-2">{{ number_format($item['total_qty']) }}</td>
                                    <td class="px-4 py-2">Rp {{ number_format($item['total_revenue'], 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-6 text-center text-gray-400">
                                        {{ __('Belum ada data penjualan.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
