<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs uppercase tracking-widest text-slate-400">{{ __('Keranjang Saya') }}</p>
                <h2 class="text-2xl font-semibold text-slate-900">{{ __('Keranjang Anda') }}</h2>
            </div>
            <a href="{{ route('home') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">
                {{ __('Lanjut belanja') }}
            </a>
        </div>
    </x-slot>

    @php
        $formatCurrency = static function ($value): string {
            $number = (float) ($value ?? 0);
            return 'Rp '.number_format($number, 0, ',', '.');
        };
        $summaryItems = (int) ($summary['items'] ?? 0);
        $summarySubtotal = (float) ($summary['subtotal'] ?? 0);
        $summaryProducts = (int) ($summary['total_products'] ?? 0);
    @endphp

    <div class="py-10">
        <div class="mx-auto max-w-8xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if ($errors->has('cart'))
                <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 shadow-sm">
                    {{ $errors->first('cart') }}
                </div>
            @endif

            @if (session('status'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-sm">
                    {{ session('status') }}
                </div>
            @endif

            @if ($cartItems->isEmpty())
                <div class="rounded-3xl border border-dashed border-slate-200 bg-white p-12 text-center shadow-sm">
                    <p class="text-lg font-semibold text-slate-800">{{ __('Keranjang masih kosong.') }}</p>
                    <p class="mt-2 text-sm text-slate-500">{{ __('Cari produk menarik dan tambahkan ke keranjangmu.') }}</p>
                    <a href="{{ route('home') }}" class="mt-6 inline-flex items-center justify-center rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-slate-800">
                        {{ __('Cari Produk') }}
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <section id="cartList" class="space-y-4 lg:col-span-2">
                        @foreach ($cartItems as $item)
                            @php
                                $product = $item->product;
                                $price = (float) $item->price;
                                $lineTotal = $price * $item->quantity;
                                $imagePath = $product?->product_image ? asset('storage/'.$product->product_image) : asset('images/PC.png');
                                $productUrl = $product ? route('products.show', $product->slug) : null;
                                $maxQuantity = max($product?->stock ?? $item->quantity, 1);
                            @endphp
                            <article class="cart-row rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" data-price="{{ $price }}">
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-[96px_1fr_auto] sm:items-center">
                                    <div class="flex items-start gap-4 sm:block">
                                        <div class="h-24 w-24 overflow-hidden rounded-xl border border-slate-200 bg-slate-50">
                                            <img src="{{ $imagePath }}" alt="{{ $product?->name ?? __('Produk tidak tersedia') }}" class="h-full w-full object-cover">
                                        </div>
                                    </div>

                                    <div class="space-y-2">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <p class="text-[11px] uppercase tracking-wide text-slate-400">
                                                    {{ $product?->category?->name ?? __('Produk') }}
                                                </p>
                                                <h3 class="text-base font-semibold text-slate-900">
                                                    @if ($productUrl)
                                                        <a href="{{ $productUrl }}" class="hover:text-slate-700">{{ $product->name }}</a>
                                                    @else
                                                        {{ $product->name ?? __('Produk tidak tersedia') }}
                                                    @endif
                                                </h3>
                                            </div>
                                            <form method="POST" action="{{ route('cart.destroy', $item) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-slate-400 transition hover:text-rose-600" title="{{ __('Hapus') }}">
                                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                        <path d="M9 3h6a1 1 0 0 1 1 1v1h4a1 1 0 1 1 0 2h-1v12a3 3 0 0 1-3 3H8a3 3 0 0 1-3-3V7H4a1 1 0 1 1 0-2h4V4a1 1 0 0 1 1-1Zm1 2h4V4h-4v1ZM8 7v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V7H8Zm3 3a1 1 0 0 1 1 1v7a1 1 0 1 1-2 0v-7a1 1 0 0 1 1-1Zm4 0a1 1 0 0 1 1 1v7a1 1 0 1 1-2 0v-7a1 1 0 0 1 1-1Z"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>

                                        <p class="text-sm text-slate-500">
                                            {{ __('Harga satuan: :price', ['price' => $formatCurrency($price)]) }}
                                        </p>
                                        @if ($product && $product->hasDiscountActive())
                                            <p class="text-xs font-semibold text-emerald-600">
                                                {{ __('Diskon aktif hingga :date', ['date' => optional($product->discount_end)?->format('d M Y') ?? __('waktu tidak ditentukan')]) }}
                                            </p>
                                        @endif

                                        <div class="flex flex-wrap items-center gap-3">
                                            <form method="POST"
                                                  action="{{ route('cart.update', $item) }}"
                                                  class="qty-form inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-2 py-1.5"
                                                  data-auto-submit="true">
                                                @csrf
                                                @method('PATCH')

                                                <button type="button"
                                                        class="qty-step inline-flex h-8 w-8 items-center justify-center rounded-lg border border-transparent text-lg font-semibold text-slate-700 transition hover:border-slate-300"
                                                        data-step="-1"
                                                        aria-label="{{ __('Kurangi jumlah') }}">
                                                    &minus;
                                                </button>

                                                <input
                                                    type="number"
                                                    name="quantity"
                                                    min="1"
                                                    max="{{ $maxQuantity }}"
                                                    data-max="{{ $maxQuantity }}"
                                                    value="{{ (int) $item->quantity }}"
                                                    class="qty-input h-8 w-16 rounded-lg border border-transparent bg-white text-center text-sm font-semibold text-slate-800 focus:border-slate-300 focus:outline-none"
                                                >

                                                <button type="button"
                                                        class="qty-step inline-flex h-8 w-8 items-center justify-center rounded-lg border border-transparent text-lg font-semibold text-slate-700 transition hover:border-slate-300"
                                                        data-step="1"
                                                        aria-label="{{ __('Tambah jumlah') }}">
                                                    +
                                                </button>
                                            </form>

                                            @if (! is_null($product?->stock))
                                                <p class="text-xs text-slate-500">
                                                    {{ __('Stok tersisa: :count', ['count' => number_format(max($product->stock, 0))]) }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="text-right">
                                        <p class="text-xs uppercase tracking-wide text-slate-400">{{ __('Subtotal') }}</p>
                                        <p class="text-lg font-semibold text-slate-900 line-total">{{ $formatCurrency($lineTotal) }}</p>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </section>

                    <aside class="space-y-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-slate-400">{{ __('Ringkasan') }}</p>
                            <h3 class="text-xl font-semibold text-slate-900">{{ __('Total Belanja') }}</h3>
                            <p class="text-sm text-slate-500">
                                {{ number_format($summaryProducts) }} {{ __('produk') }}
                                <span aria-hidden="true">&middot;</span>
                                {{ number_format($summaryItems) }} {{ __('unit') }}
                            </p>
                        </div>
                        <dl class="space-y-2 text-sm text-slate-600">
                            <div class="flex items-center justify-between">
                                <dt>{{ __('Total item') }}</dt>
                                <dd id="summaryItems" class="font-semibold text-slate-900">{{ number_format($summaryItems) }}</dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt>{{ __('Subtotal') }}</dt>
                                <dd id="summarySubtotal" class="font-semibold text-slate-900">{{ $formatCurrency($summarySubtotal) }}</dd>
                            </div>
                            <hr class="my-3">
                            <div class="flex items-center justify-between text-base">
                                <dt class="font-semibold text-slate-900">{{ __('Total') }}</dt>
                                <dd id="summaryTotal" class="text-xl font-extrabold text-slate-900">{{ $formatCurrency($summarySubtotal) }}</dd>
                            </div>
                        </dl>

                        <div class="space-y-3">
                            <form method="GET" action="{{ route('checkout.index') }}">
                                <button
                                    id="checkoutButton"
                                    type="submit"
                                    class="inline-flex w-full items-center justify-center rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white shadow disabled:cursor-not-allowed disabled:opacity-40"
                                    @disabled($cartItems->isEmpty())
                                >
                                    {{ __('Lanjut ke Checkout') }}
                                </button>
                            </form>
                            <a
                                href="{{ route('orders.index') }}"
                                class="inline-flex w-full items-center justify-center rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                            >
                                {{ __('Lihat Riwayat Pesanan') }}
                            </a>
                        </div>
                    </aside>
                </div>
            @endif
        </div>
    </div>

    @if ($cartItems->isNotEmpty())
        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const cartList = document.getElementById('cartList');
                    if (!cartList) {
                        return;
                    }

                    const numberFormat = new Intl.NumberFormat('id-ID');

                    function money(value) {
                        const safe = Number(value) || 0;
                        return 'Rp ' + numberFormat.format(Math.max(0, Math.round(safe)));
                    }

                    function submitForm(form) {
                        if (!form) {
                            return;
                        }
                        if (typeof form.requestSubmit === 'function') {
                            form.requestSubmit();
                        } else {
                            form.submit();
                        }
                    }

                    function clamp(input) {
                        const raw = parseInt(input.value || '1', 10);
                        const maxAttr = Number(input.dataset.max || input.getAttribute('max'));
                        const hasMax = Number.isFinite(maxAttr) && maxAttr > 0;
                        let nextValue = Number.isNaN(raw) ? 1 : raw;
                        if (nextValue < 1) {
                            nextValue = 1;
                        }
                        if (hasMax && nextValue > maxAttr) {
                            nextValue = maxAttr;
                        }
                        input.value = nextValue;
                        return nextValue;
                    }

                    function recalc() {
                        let subtotal = 0;
                        let totalQty = 0;

                        cartList.querySelectorAll('.cart-row').forEach(row => {
                            const price = Number(row.dataset.price || 0);
                            const input = row.querySelector('.qty-input');
                            if (!input) {
                                return;
                            }
                            const qty = clamp(input);
                            const lineTotal = price * qty;
                            subtotal += lineTotal;
                            totalQty += qty;

                            const lineEl = row.querySelector('.line-total');
                            if (lineEl) {
                                lineEl.textContent = money(lineTotal);
                            }
                        });

                        const itemsEl = document.getElementById('summaryItems');
                        if (itemsEl) {
                            itemsEl.textContent = numberFormat.format(totalQty);
                        }

                        const subtotalEl = document.getElementById('summarySubtotal');
                        if (subtotalEl) {
                            subtotalEl.textContent = money(subtotal);
                        }

                        const totalEl = document.getElementById('summaryTotal');
                        if (totalEl) {
                            totalEl.textContent = money(subtotal);
                        }

                        const checkoutBtn = document.getElementById('checkoutButton');
                        if (checkoutBtn) {
                            checkoutBtn.disabled = totalQty === 0;
                        }
                    }

                    cartList.addEventListener('click', event => {
                        const button = event.target.closest('.qty-step');
                        if (!button) {
                            return;
                        }

                        event.preventDefault();
                        const form = button.closest('.qty-form');
                        const input = form?.querySelector('.qty-input');
                        if (!form || !input) {
                            return;
                        }

                        const step = Number(button.dataset.step || 0);
                        const current = parseInt(input.value || '1', 10) || 1;
                        const maxAttr = Number(input.dataset.max || input.getAttribute('max'));
                        const hasMax = Number.isFinite(maxAttr) && maxAttr > 0;

                        let nextValue = current + step;
                        if (nextValue < 1) {
                            nextValue = 1;
                        }
                        if (hasMax && nextValue > maxAttr) {
                            nextValue = maxAttr;
                        }

                        if (nextValue === current) {
                            return;
                        }

                        input.value = nextValue;
                        recalc();
                        submitForm(form);
                    });

                    cartList.addEventListener('change', event => {
                        if (!event.target.classList.contains('qty-input')) {
                            return;
                        }
                        clamp(event.target);
                        recalc();
                        submitForm(event.target.closest('.qty-form'));
                    });

                    recalc();
                });
            </script>
        @endpush
    @endif
</x-app-layout>
