<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs uppercase text-slate-400">{{ __('Checkout') }}</p>
                <h1 class="text-2xl font-semibold text-slate-900">{{ __('Ringkasan Pesanan') }}</h1>
            </div>
            <a href="{{ route('cart.index') }}" class="text-sm text-slate-500 hover:text-slate-700">{{ __('Kembali ke keranjang') }}</a>
        </div>
    </x-slot>

    <div class="py-10">
        <div
            class="mx-auto grid max-w-6xl gap-6 px-4 sm:px-6 lg:grid-cols-[2fr,1fr] lg:px-8"
            data-checkout-root="true"
            data-shipping-endpoint="{{ route('checkout.shipping-rates') }}"
            data-submit-endpoint="{{ route('checkout.store') }}"
            data-summary='@json($summary)'
            data-initial-shipping='@json($shippingOptions)'
            data-payment-methods='@json($paymentMethods)'
            data-success-redirect="{{ route('orders.show', ['order' => '__ORDER_NUMBER__']) }}?from_checkout=1"
        >
            <div class="space-y-6">
                <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <p class="text-sm font-semibold text-slate-900">{{ __('Alamat Pengiriman') }}</p>
                        <p class="text-xs text-slate-500">{{ __('Pilih alamat tujuan paket dikirim.') }}</p>
                    </div>
                    <div class="divide-y divide-slate-100" data-address-list>
                        @forelse ($addresses as $address)
                            <label class="flex cursor-pointer flex-col gap-1 px-6 py-4 hover:bg-slate-50">
                                <div class="flex items-center gap-3">
                                    <input
                                        type="radio"
                                        name="address_id"
                                        value="{{ $address->id }}"
                                        class="h-4 w-4 text-slate-900"
                                        {{ $loop->first ? 'checked' : '' }}
                                        data-address-option="true"
                                    >
                                    <div class="flex flex-col">
                                        <span class="text-sm font-semibold text-slate-900">{{ $address->recipient_name }}</span>
                                        <span class="text-xs text-slate-500">{{ $address->phone }}</span>
                                    </div>
                                    @if($address->is_default)
                                        <span class="ml-auto rounded-full bg-emerald-100 px-3 py-0.5 text-xs font-semibold text-emerald-700">{{ __('Utama') }}</span>
                                    @endif
                                </div>
                                <p class="text-sm text-slate-600">
                                    {{ $address->detail }}, {{ $address->district }}, {{ $address->city }}, {{ $address->province }} {{ $address->postal_code }}
                                </p>
                            </label>
                        @empty
                            <div class="p-6 text-sm text-slate-500">
                                {{ __('Tambahkan alamat terlebih dahulu di halaman profil Anda.') }}
                            </div>
                        @endforelse
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white shadow-sm" data-shipping-section>
                    <div class="border-b border-slate-100 px-6 py-4 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">{{ __('Ekspedisi Pengiriman') }}</p>
                            <p class="text-xs text-slate-500">{{ __('Biaya ongkir dihitung otomatis dari Biteship.') }}</p>
                        </div>
                        <button type="button" class="text-xs font-semibold text-slate-900 hover:text-slate-600" data-refresh-shipping>
                            {{ __('Refresh Ongkir') }}
                        </button>
                    </div>
                    <div class="divide-y divide-slate-100" data-shipping-options>
                        <div class="p-6 text-sm text-slate-500" data-empty-shipping>
                            {{ __('Memuat opsi pengiriman...') }}
                        </div>
                    </div>
                    <div class="hidden p-4 text-sm text-rose-600" data-shipping-error></div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <p class="text-sm font-semibold text-slate-900">{{ __('Metode Pembayaran') }}</p>
                        <p class="text-xs text-slate-500">{{ __('Pilih cara bayar favorit Anda, transaksi diproses melalui Midtrans.') }}</p>
                    </div>
                    <div class="divide-y divide-slate-100" data-payment-options>
                        @foreach($paymentMethods as $key => $method)
                            <label class="flex cursor-pointer items-start gap-3 px-6 py-4 hover:bg-slate-50">
                                <input
                                    type="radio"
                                    name="payment_method"
                                    value="{{ $key }}"
                                    class="mt-1 h-4 w-4 text-slate-900"
                                    {{ $loop->first ? 'checked' : '' }}
                                >
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">{{ $method['label'] }}</p>
                                    <p class="text-xs text-slate-500">{{ $method['description'] }}</p>
                                </div>
                                @if(!empty($method['icon']))
                                    <span class="ml-auto rounded bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">{{ $method['icon'] }}</span>
                                @endif
                            </label>
                        @endforeach
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <p class="text-sm font-semibold text-slate-900">{{ __('Catatan Pesanan') }}</p>
                    </div>
                    <div class="p-6">
                        <textarea
                            rows="3"
                            class="w-full rounded-xl border-slate-200 text-sm text-slate-700 focus:border-slate-400 focus:ring-slate-400"
                            placeholder="{{ __('Contoh: titip di satpam, warna bebas, dll') }}"
                            data-field-notes
                        ></textarea>
                    </div>
                </section>
            </div>

            <aside class="space-y-6">
                <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <p class="text-sm font-semibold text-slate-900">{{ __('Ringkasan Biaya') }}</p>
                    </div>
                    <div class="space-y-4 px-6 py-5 text-sm text-slate-600">
                        <div class="flex items-center justify-between">
                            <span>{{ __('Subtotal Produk') }}</span>
                            <span class="font-semibold text-slate-900" data-summary-subtotal>
                                Rp {{ number_format($summary['subtotal'], 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>{{ __('Ongkos Kirim') }}</span>
                            <span class="font-semibold text-slate-900" data-summary-shipping>Rp 0</span>
                        </div>
                        <div class="flex items-center justify-between border-t border-dashed border-slate-200 pt-4 text-base font-semibold text-slate-900">
                            <span>{{ __('Total Bayar') }}</span>
                            <span data-summary-total>Rp {{ number_format($summary['subtotal'], 0, ',', '.') }}</span>
                        </div>
                        <p class="text-xs text-slate-500">{{ __('Total sudah termasuk seluruh produk dan ongkir pilihan Anda.') }}</p>
                        <div class="hidden rounded border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700" data-checkout-error></div>
                        <button
                            type="button"
                            class="mt-2 inline-flex w-full items-center justify-center rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 disabled:opacity-60"
                            data-checkout-submit
                        >
                            {{ __('Bayar Sekarang') }}
                        </button>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <p class="text-sm font-semibold text-slate-900">{{ __('Detail Produk') }}</p>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @foreach($cartItems as $item)
                            <div class="flex items-center gap-3 px-6 py-4">
                                <div class="h-16 w-16 overflow-hidden rounded-xl border border-slate-200 bg-slate-50">
                                    <img
                                        src="{{ $item->product?->product_image ? asset('storage/'.$item->product->product_image) : asset('images/PC.png') }}"
                                        alt="{{ $item->product?->name }}"
                                        class="h-full w-full object-cover"
                                    >
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-semibold text-slate-900">{{ $item->product?->name }}</p>
                                    <p class="text-xs text-slate-500">{{ __('Qty: :qty', ['qty' => $item->quantity]) }}</p>
                                </div>
                                <span class="text-sm font-semibold text-slate-900">
                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </section>
            </aside>

            <input type="hidden" data-field-shipping-code>
            <input type="hidden" data-field-shipping-service>
        </div>
    </div>

    @push('scripts')
        @if($snapScriptUrl && $midtransClientKey)
            <script src="{{ $snapScriptUrl }}" data-client-key="{{ $midtransClientKey }}"></script>
        @endif
    @endpush
</x-app-layout>
