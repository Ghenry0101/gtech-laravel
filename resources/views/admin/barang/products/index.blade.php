<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-slate-900">{{ __('Manajemen Produk') }}</h2>
                <p class="text-sm text-slate-500">{{ __('Kelola data produk yang ditampilkan kepada pelanggan.') }}</p>
            </div>
            <a href="{{ route('admin.barang.products.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-700">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 5v14M5 12h14" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                {{ __('Tambah Produk') }}
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-8 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-sm">
                    {{ session('status') }}
                </div>
            @endif

            <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col gap-4 border-b border-slate-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                    <form method="GET" class="w-full sm:w-auto">
                        <label for="search" class="sr-only">{{ __('Cari produk') }}</label>
                        <div class="relative">
                            <input id="search" name="search" type="search" value="{{ $search }}" placeholder="{{ __('Cari produk, brand, kategori...') }}" class="w-full rounded-lg border border-slate-200 bg-white py-2 pl-10 pr-4 text-sm text-slate-700 shadow-sm placeholder:text-slate-400 focus:border-slate-500 focus:outline-none focus:ring focus:ring-slate-500/20 sm:w-72" />
                            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 20 20" fill="none">
                                <path d="M9 3a6 6 0 104 10.74l3.13 3.13a1 1 0 01-1.42 1.42L11.6 15.2A6 6 0 009 3z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                    </form>

                    <div class="text-xs font-medium uppercase tracking-wide text-slate-400">
                        {{ __('Total: :count produk', ['count' => $products->total()]) }}
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
                            <tr>
                                <th class="px-6 py-3">{{ __('Produk') }}</th>
                                <th class="px-6 py-3">{{ __('Kategori') }}</th>
                                <th class="px-6 py-3">{{ __('Brand') }}</th>
                                <th class="px-6 py-3">{{ __('Harga') }}</th>
                                <th class="px-6 py-3">{{ __('Stok') }}</th>
                                <th class="px-6 py-3">{{ __('Dimensi (cm)') }}</th>
                                <th class="px-6 py-3">{{ __('Berat (gr)') }}</th>
                                <th class="px-6 py-3">{{ __('Status') }}</th>
                                <th class="px-6 py-3 text-right">{{ __('Aksi') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @forelse ($products as $product)
                                <tr class="text-slate-700">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="h-16 w-16 overflow-hidden rounded-lg border border-slate-200 bg-slate-100">
                                                @php
                                                    $imageUrl = $product->product_image ? asset('storage/'.$product->product_image) : null;
                                                @endphp
                                                @if ($imageUrl)
                                                    <img src="{{ $imageUrl }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                                                @else
                                                    <div class="flex h-full w-full items-center justify-center text-xs text-slate-400">
                                                        {{ __('No Image') }}
                                                    </div>
                                                @endif
                                            </div>
                                            <div>
                                                <p class="font-semibold text-slate-900">{{ $product->name }}</p>
                                                <p class="text-xs uppercase tracking-wide text-slate-400">{{ $product->slug }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div>
                                            <p class="font-semibold text-slate-900">{{ $product->category?->name ?? __('Tanpa kategori') }}</p>
                                            <p class="text-xs text-slate-500">{{ $product->category?->slug }}</p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div>
                                            <p class="font-semibold text-slate-900">{{ $product->brand?->name ?? __('Tanpa Brand') }}</p>
                                            <p class="text-xs text-slate-500">{{ $product->brand?->slug }}</p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $formatCurrency = static fn ($value) => 'Rp '.number_format($value, 0, ',', '.');
                                            $formatPercent = static fn ($value) => rtrim(rtrim(number_format($value, 2, ',', '.'), '0'), ',');
                                            $hasDiscount = $product->hasDiscountConfigured();
                                            $discountActive = $product->hasDiscountActive();
                                            $rawDiscountPercent = $product->discount_percent;
                                            $computedPlannedDiscount = $hasDiscount
                                                ? ($product->discount_price ?? ($product->price - ($product->price * ($rawDiscountPercent ?? 0) / 100)))
                                                : null;
                                            if ($computedPlannedDiscount !== null) {
                                                $computedPlannedDiscount = max($computedPlannedDiscount, 0);
                                            }
                                            if ($rawDiscountPercent === null && $hasDiscount && $product->price > 0 && $computedPlannedDiscount !== null) {
                                                $rawDiscountPercent = (($product->price - $computedPlannedDiscount) / $product->price) * 100;
                                            }
                                            $activePrice = $discountActive ? $product->effective_price : $computedPlannedDiscount;
                                        @endphp
                                        <div class="flex flex-col text-sm">
                                            @if ($hasDiscount && $activePrice !== null)
                                                <span class="font-semibold {{ $discountActive ? 'text-emerald-600' : 'text-slate-900' }}">
                                                    {{ $formatCurrency($activePrice) }}
                                                </span>
                                                <span class="text-xs text-slate-400 line-through">
                                                    {{ $formatCurrency($product->price) }}
                                                </span>
                                                <span class="mt-1 inline-flex w-fit items-center gap-1 rounded-full {{ $discountActive ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }} px-2 py-0.5 text-[11px] font-semibold">
                                                    @if ($rawDiscountPercent !== null)
                                                        {{ __('Diskon :percent%', ['percent' => $formatPercent($rawDiscountPercent)]) }}
                                                    @else
                                                        {{ __('Diskon') }}
                                                    @endif
                                                </span>
                                                @php
                                                    $startLabel = $product->discount_start?->format('d M Y H:i');
                                                    $endLabel = $product->discount_end?->format('d M Y H:i');
                                                @endphp
                                                @if ($startLabel || $endLabel)
                                                    <span class="text-[11px] text-slate-500">
                                                        {{ __('Periode: :start - :end', [
                                                            'start' => $startLabel ?? __('sekarang'),
                                                            'end' => $endLabel ?? __('tanpa batas'),
                                                        ]) }}
                                                    </span>
                                                @endif
                                            @else
                                                <span class="font-semibold text-slate-900">{{ $formatCurrency($product->price) }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                                            {{ number_format($product->stock) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ $product->length }} x {{ $product->width }} x {{ $product->height }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ number_format($product->weight) }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($product->is_active)
                                            <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                                                {{ __('Aktif') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-slate-200 px-3 py-1 text-xs font-semibold text-slate-600">
                                                {{ __('Nonaktif') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('admin.barang.products.edit', $product->id) }}" class="inline-flex items-center rounded-md border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-slate-400 hover:text-slate-900">
                                                {{ __('Edit') }}
                                            </a>
                                            <form method="POST" action="{{ route('admin.barang.products.destroy', $product->id) }}" onsubmit="return confirm('{{ __('Hapus produk ini?') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center rounded-md bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-rose-500">
                                                    {{ __('Hapus') }}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-10 text-center text-sm text-slate-500">
                                        {{ __('Belum ada produk yang terdaftar.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="flex items-center justify-between border-t border-slate-200 px-6 py-4 text-sm text-slate-500">
                    <div>{{ $products->firstItem() }}-{{ $products->lastItem() }} {{ __('dari') }} {{ $products->total() }}</div>
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
