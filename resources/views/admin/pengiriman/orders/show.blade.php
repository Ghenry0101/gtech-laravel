@php
    $formatCurrency = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
    $statusMeta = [
        'pending' => ['label' => __('Menunggu Pembayaran'), 'badge' => 'bg-amber-100 text-amber-800'],
        'processing' => ['label' => __('Sedang Diproses'), 'badge' => 'bg-sky-100 text-sky-800'],
        'shipped' => ['label' => __('Sedang Dikirim'), 'badge' => 'bg-indigo-100 text-indigo-800'],
        'completed' => ['label' => __('Selesai'), 'badge' => 'bg-emerald-100 text-emerald-800'],
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs uppercase tracking-wide text-slate-400">{{ __('Admin Pengiriman') }}</p>
                <h1 class="text-2xl font-semibold text-slate-900">{{ __('Pesanan :number', ['number' => $order->order_number]) }}</h1>
                <p class="text-xs text-slate-500">
                    {{ __('Dipesan pada :date', ['date' => optional($order->order_time ?? $order->created_at)->format('d M Y H:i')]) }}
                </p>
            </div>
            <a href="{{ route('admin.shipping.dashboard') }}" class="inline-flex items-center rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:border-slate-300 hover:text-slate-900">
                {{ __('Kembali ke daftar') }}
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-6xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status_message'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-6 py-4 text-sm text-emerald-800">
                    {{ session('status_message') }}
                </div>
            @endif

            <div class="grid gap-6 lg:grid-cols-[2fr,1fr]">
                <div class="space-y-6">
                    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ __('Status Pesanan') }}</p>
                                <p class="text-xs text-slate-500">{{ __('Update status ini akan terlihat oleh pelanggan.') }}</p>
                            </div>
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $statusMeta[$order->order_status]['badge'] ?? 'bg-slate-100 text-slate-600' }}">
                                {{ $statusMeta[$order->order_status]['label'] ?? \Illuminate\Support\Str::headline($order->order_status) }}
                            </span>
                        </div>
                        <div class="mt-5 grid gap-4 sm:grid-cols-3">
                            <div class="rounded-xl border border-slate-100 p-4">
                                <p class="text-xs uppercase text-slate-400">{{ __('Kurir') }}</p>
                                <p class="mt-1 text-sm font-semibold text-slate-900">{{ $order->shipment?->courier_name ?? __('Belum ditentukan') }}</p>
                                <p class="text-xs text-slate-500">{{ $order->shipment?->courier_service }}</p>
                            </div>
                            <div class="rounded-xl border border-slate-100 p-4">
                                <p class="text-xs uppercase text-slate-400">{{ __('Nomor Resi') }}</p>
                                <p class="mt-1 text-sm font-semibold text-slate-900">{{ $order->shipment?->tracking_id ?? __('-') }}</p>
                                <p class="text-xs text-slate-500">{{ __('Perbarui jika ada perubahan') }}</p>
                            </div>
                            <div class="rounded-xl border border-slate-100 p-4">
                                <p class="text-xs uppercase text-slate-400">{{ __('Estimasi') }}</p>
                                <p class="mt-1 text-sm font-semibold text-slate-900">
                                    {{ $order->shipment?->estimation_days ? __(':day hari', ['day' => $order->shipment->estimation_days]) : __('Tidak tersedia') }}
                                </p>
                                <p class="text-xs text-slate-500">{{ __('Ongkir: :amount', ['amount' => $formatCurrency($order->shipment?->shipping_cost ?? 0)]) }}</p>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-sm font-semibold text-slate-900">{{ __('Daftar Produk') }}</p>
                        <div class="mt-4 divide-y divide-slate-100">
                            @foreach ($order->items as $item)
                                <div class="flex flex-col gap-2 py-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">{{ $item->product_name }}</p>
                                        <p class="text-xs text-slate-500">{{ __('Qty: :qty', ['qty' => $item->quantity]) }}</p>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-900">
                                        {{ $formatCurrency($item->price * $item->quantity) }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </section>

                    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm grid gap-6 md:grid-cols-2">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">{{ __('Alamat Pengiriman') }}</p>
                            <div class="mt-2 text-sm text-slate-600">
                                @if ($order->address)
                                    <p class="font-semibold text-slate-900">{{ $order->address->recipient_name }}</p>
                                    <p class="text-xs text-slate-500">{{ $order->address->phone }}</p>
                                    <p class="mt-2">{{ $order->address->detail }}</p>
                                    <p>{{ $order->address->district }}, {{ $order->address->city }}, {{ $order->address->province }} {{ $order->address->postal_code }}</p>
                                @else
                                    <p>{{ __('Alamat belum tersedia.') }}</p>
                                @endif
                            </div>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-900">{{ __('Ringkasan Pembayaran') }}</p>
                            <dl class="mt-2 space-y-1 text-sm text-slate-600">
                                <div class="flex items-center justify-between">
                                    <dt>{{ __('Subtotal') }}</dt>
                                    <dd class="font-semibold text-slate-900">{{ $formatCurrency($order->total_amount) }}</dd>
                                </div>
                                <div class="flex items-center justify-between">
                                    <dt>{{ __('Ongkos Kirim') }}</dt>
                                    <dd class="font-semibold text-slate-900">{{ $formatCurrency($order->shipping_cost) }}</dd>
                                </div>
                                <div class="flex items-center justify-between border-t border-dashed border-slate-200 pt-2">
                                    <dt>{{ __('Total') }}</dt>
                                    <dd class="text-lg font-semibold text-slate-900">{{ $formatCurrency($order->grand_total) }}</dd>
                                </div>
                            </dl>
                            <p class="mt-2 text-xs text-slate-500">
                                {{ __('Metode: :method', ['method' => \Illuminate\Support\Str::headline($order->payment_method ?? '-')]) }}
                            </p>
                        </div>
                    </section>
                </div>

                <aside class="space-y-6">
                    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-sm font-semibold text-slate-900">{{ __('Perbarui Detail Pengiriman') }}</p>
                        <form method="POST" action="{{ route('admin.shipping.orders.shipment.update', $order) }}" class="mt-4 space-y-4">
                            @csrf
                            @method('PATCH')

                            <div>
                                <label class="text-xs uppercase text-slate-400" for="courier_name">{{ __('Nama Kurir') }}</label>
                                <input type="text" name="courier_name" id="courier_name" value="{{ old('courier_name', $order->shipment?->courier_name) }}" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-slate-400 focus:outline-none focus:ring-0" />
                                @error('courier_name')
                                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="text-xs uppercase text-slate-400" for="courier_service">{{ __('Layanan') }}</label>
                                <input type="text" name="courier_service" id="courier_service" value="{{ old('courier_service', $order->shipment?->courier_service) }}" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-slate-400 focus:outline-none focus:ring-0" />
                                @error('courier_service')
                                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="text-xs uppercase text-slate-400" for="tracking_id">{{ __('Nomor Resi') }}</label>
                                <input type="text" name="tracking_id" id="tracking_id" value="{{ old('tracking_id', $order->shipment?->tracking_id) }}" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-slate-400 focus:outline-none focus:ring-0" />
                                @error('tracking_id')
                                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="text-xs uppercase text-slate-400" for="estimation_days">{{ __('Estimasi (hari)') }}</label>
                                    <input type="number" min="1" name="estimation_days" id="estimation_days" value="{{ old('estimation_days', $order->shipment?->estimation_days) }}" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-slate-400 focus:outline-none focus:ring-0" />
                                    @error('estimation_days')
                                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="text-xs uppercase text-slate-400" for="shipping_cost">{{ __('Ongkir (Rp)') }}</label>
                                    <input type="number" min="0" step="1000" name="shipping_cost" id="shipping_cost" value="{{ old('shipping_cost', $order->shipment?->shipping_cost) }}" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-slate-400 focus:outline-none focus:ring-0" />
                                    @error('shipping_cost')
                                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <button type="submit" class="w-full rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                                {{ __('Simpan Detail') }}
                            </button>
                        </form>
                    </section>

                    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-sm font-semibold text-slate-900">{{ __('Perbarui Status Pengiriman') }}</p>
                        <form method="POST" action="{{ route('admin.shipping.orders.status.update', $order) }}" class="mt-4 space-y-4">
                            @csrf
                            @method('PATCH')
                            <div>
                                <label class="text-xs uppercase text-slate-400" for="status">{{ __('Status') }}</label>
                                <select id="status" name="status" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-slate-400 focus:outline-none focus:ring-0">
                                    <option value="processing" @selected(old('status', $order->shipment?->status) === 'processing')>{{ __('Sedang diproses gudang') }}</option>
                                    <option value="shipped" @selected(old('status', $order->shipment?->status) === 'shipped')>{{ __('Sedang dikirim / courier pick up') }}</option>
                                    <option value="delivered" @selected(old('status', $order->shipment?->status) === 'delivered')>{{ __('Sudah diterima pelanggan') }}</option>
                                </select>
                                @error('status')
                                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="text-xs uppercase text-slate-400" for="status_tracking_id">{{ __('Nomor Resi (opsional)') }}</label>
                                <input type="text" name="tracking_id" id="status_tracking_id" value="{{ old('tracking_id') }}" placeholder="{{ $order->shipment?->tracking_id }}" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-slate-400 focus:outline-none focus:ring-0" />
                                <p class="mt-1 text-xs text-slate-400">{{ __('Kosongkan untuk mempertahankan nilai sebelumnya.') }}</p>
                            </div>
                            <button type="submit" class="w-full rounded-full bg-indigo-500 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-600">
                                {{ __('Perbarui Status') }}
                            </button>
                        </form>
                        <p class="mt-3 text-xs text-slate-500">
                            {{ __('Status \"Sudah diterima\" akan otomatis mengubah pesanan menjadi selesai sehingga pelanggan bisa mengulas.') }}
                        </p>
                    </section>
                </aside>
            </div>
        </div>
    </div>
</x-app-layout>
