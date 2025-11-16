<x-app-layout>
    <div class="bg-gray-50 py-10">
        <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex flex-col gap-1">
                    <h1 class="text-2xl font-semibold text-gray-900">{{ __('Cari Produk') }}</h1>
                    <p class="text-sm text-gray-500">{{ __('Temukan produk berdasarkan nama, deskripsi, atau kategori.') }}</p>
                </div>
                
                <form method="GET" action="{{ route('search') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <label class="sr-only" for="query">{{ __('Kata kunci') }}</label>
                    <div class="relative flex-1">
                        <input
                            id="query"
                            name="query"
                            type="search"
                            value="{{ $query }}"
                            placeholder="{{ __('Cari produk...') }}"
                            class="w-full rounded-lg border border-gray-300 py-2 pl-10 pr-4 text-sm text-gray-800 placeholder:text-gray-400 focus:border-gray-500 focus:outline-none focus:ring focus:ring-gray-200"
                        />
                        <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" viewBox="0 0 20 20" fill="none">
                            <path d="M9 3a6 6 0 104 10.74l3.13 3.13a1 1 0 01-1.42 1.42L11.6 15.2A6 6 0 009 3z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-gray-900 px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-gray-700">
                        {{ __('Cari') }}
                    </button>
                </form>
            </div>

            @if (! $hasQuery)
                <div class="rounded-xl border border-dashed border-gray-300 bg-white px-6 py-10 text-center text-sm text-gray-500 shadow-sm">
                    {{ __('Masukkan kata kunci di atas untuk mulai mencari produk.') }}
                </div>
            @else
                <div class="rounded-xl border border-gray-200 bg-white px-6 py-4 shadow-sm">
                    <div class="flex flex-col gap-2 text-sm text-gray-600 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            {{ __('Menampilkan :count hasil', ['count' => number_format($products?->total() ?? 0)]) }}
                        </div>
                        <div class="text-gray-500">
                            {{ __('Kata kunci: ":query"', ['query' => $query]) }}
                        </div>
                    </div>
                </div>

                @if (count($results))
                    @include('components.product-grid', ['items' => $results])
                    @if ($products && $products->hasPages())
                        <div class="mt-6">
                            {{ $products->links() }}
                        </div>
                    @endif
                @else
                    <div class="rounded-xl border border-amber-200 bg-amber-50 px-6 py-10 text-center text-sm text-amber-700 shadow-sm">
                        {{ __('Tidak ditemukan produk yang cocok dengan kata kunci tersebut.') }}
                        <div class="mt-2 text-xs text-amber-700/80">
                            {{ __('Coba gunakan kata kunci lain atau periksa ejaan Anda.') }}
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
