<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold text-gray-800 leading-tight">
                {{ __('Keranjang Saya') }}
            </h2>
            <a href="{{ route('home') }}" class="text-sm text-slate-500 hover:text-slate-700">{{ __('Lanjut belanja') }}</a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-5xl space-y-6 sm:px-6 lg:px-8">
            @if ($errors->has('cart'))
                <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 shadow-sm">
                    {{ $errors->first('cart') }}
                </div>
            @endif

            @if (session('status'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-sm">
                    {{ session('status') }}
                </div>
            @endif

            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                @if ($cartItems->isEmpty())
                    <div class="p-8 text-center text-slate-500">
                        <p>{{ __('Keranjang masih kosong.') }}</p>
                        <a href="{{ route('home') }}" class="mt-4 inline-flex items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">
                            {{ __('Cari Produk') }}
                        </a>
                    </div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach ($cartItems as $item)
                            <div class="flex flex-col gap-4 p-6 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex flex-1 items-start gap-4">
                                        <div class="h-24 w-24 overflow-hidden rounded-lg border border-slate-200 bg-slate-50">
                                            <img
                                                src="{{ $item->product?->product_image ? asset('storage/'.$item->product->product_image) : asset('images/PC.png') }}"
                                                alt="{{ $item->product?->name }}"
                                                class="h-full w-full object-cover"
                                            >
                                        </div>
                                        <div>
                                            <p class="text-xs uppercase text-slate-400">{{ $item->product?->category?->name ?? __('Produk') }}</p>
                                            <a href="{{ $item->product ? route('products.show', $item->product->slug) : '#' }}" class="text-base font-semibold text-slate-900 hover:text-slate-700">
                                                {{ $item->product->name ?? __('Produk tidak tersedia') }}
                                            </a>
                                            <p class="text-sm text-slate-500">
                                                {{ __('Harga satuan: Rp :price', ['price' => number_format($item->price, 0, ',', '.')]) }}
                                            </p>
                                            @if ($item->product && $item->product->hasDiscountActive())
                                                <p class="text-xs text-emerald-600 font-semibold">
                                                    {{ __('Diskon aktif: :percent%', ['percent' => number_format($item->product->discount_percent ?? (($item->product->price - $item->product->effective_price) / $item->product->price * 100), 2)]) }}
                                                </p>
                                            @endif
                                        </div>
                                </div>

                                <div class="flex flex-col gap-3 text-sm text-slate-600">
                                    <form method="POST" action="{{ route('cart.update', $item) }}" class="flex items-center gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <label for="quantity-{{ $item->id }}" class="text-xs text-slate-500">{{ __('Jumlah') }}</label>
                                        <input
                                            id="quantity-{{ $item->id }}"
                                            name="quantity"
                                            type="number"
                                            min="1"
                                            max="{{ max($item->product?->stock ?? $item->quantity, 1) }}"
                                            value="{{ $item->quantity }}"
                                            class="w-20 rounded-lg border-slate-300 text-center text-sm"
                                        >
                                        <button type="submit" class="text-xs font-semibold text-slate-900 hover:text-slate-600">
                                            {{ __('Update') }}
                                        </button>
                                    </form>
                                    <p class="text-sm font-semibold text-slate-900">
                                        {{ __('Subtotal: Rp :price', ['price' => number_format($item->subtotal, 0, ',', '.')]) }}
                                    </p>
                                    <form method="POST" action="{{ route('cart.destroy', $item) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-1 text-xs text-rose-600 hover:text-rose-800">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M6 3a1 1 0 00-1 1v1H3.5a.5.5 0 000 1H4v9a2 2 0 002 2h8a2 2 0 002-2V6h.5a.5.5 0 000-1H15V4a1 1 0 00-1-1H6zm2 4a.5.5 0 011 0v7a.5.5 0 01-1 0V7zm4 0a.5.5 0 011 0v7a.5.5 0 01-1 0V7z" clip-rule="evenodd" /></svg>
                                            {{ __('Hapus') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex flex-col gap-4 border-t border-slate-200 p-6 lg:flex-row lg:items-center lg:justify-between">
                        <div class="space-y-1">
                            <p class="text-sm text-slate-500">{{ __('Ringkasan Keranjang') }}</p>
                            <p class="text-2xl font-semibold text-slate-900">Rp {{ number_format($summary['subtotal'], 0, ',', '.') }}</p>
                            <p class="text-xs text-slate-400">
                                {{ __(':items produk • :qty unit', ['items' => number_format($summary['total_products']), 'qty' => number_format($summary['items'])]) }}
                            </p>
                        </div>
                        <a
                            href="{{ route('checkout.index') }}"
                            class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-slate-800"
                        >
                            {{ __('Lanjut ke Checkout') }}
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
