@php
    $formatCurrency = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
    $statusMeta = [
        'pending' => ['label' => __('Menunggu Pembayaran'), 'badge' => 'bg-amber-100 text-amber-800'],
        'processing' => ['label' => __('Sedang Diproses'), 'badge' => 'bg-sky-100 text-sky-800'],
        'shipped' => ['label' => __('Sedang Dikirim'), 'badge' => 'bg-indigo-100 text-indigo-800'],
        'completed' => ['label' => __('Selesai'), 'badge' => 'bg-emerald-100 text-emerald-800'],
    ];
    $paymentMethods = config('midtrans.payment_methods', []);
    $shipment = $order->shipment;
    $estimationLabel = \App\Support\ShipmentFormatter::formatEstimation($shipment) ?? __('5 hari');
    $trackingEvents = $shipment?->trackings?->sortBy('recorded_at')->values() ?? collect();
    $trackingStatusLabels = [
        'courier_allocated' => __('Kurir ditemukan'),
        'picking_up' => __('Kurir menuju lokasi pickup'),
        'picked' => __('Barang dijemput kurir'),
        'on_the_way' => __('Sedang dikirim / dalam perjalanan'),
        'delivering' => __('Sedang dikirim / dalam perjalanan'),
        'on_delivery' => __('Sedang dikirim / dalam perjalanan'),
        'in_transit' => __('Sedang dikirim / dalam perjalanan'),
        'shipped' => __('Sedang dikirim / dalam perjalanan'),
        'dropping_off' => __('Sedang dikirim / dalam perjalanan'),
        'dropping_off_item' => __('Sedang dikirim / dalam perjalanan'),
        'out_for_delivery' => __('Sedang dikirim / dalam perjalanan'),
        'delivered' => __('Berhasil dikirim'),
    ];
    $latestTrackingStatus = $trackingEvents->last()?->status;
    if (! $latestTrackingStatus && $shipment && array_key_exists($shipment->status, $trackingStatusLabels)) {
        $latestTrackingStatus = $shipment->status;
    }
    $trackingStatusLabel = $latestTrackingStatus
        ? ($trackingStatusLabels[$latestTrackingStatus] ?? \Illuminate\Support\Str::headline($latestTrackingStatus))
        : __('Belum ada update');
    $trackingNumber = $shipment?->tracking_id ?: $shipment?->waybill_id;
    $orderDate = $order->order_time ?? $order->created_at;
    $orderDateLabel = $orderDate ? $orderDate->format('d M Y - H.i').' WIB' : '-';
    $totalWeightGram = (int) $order->items->sum(fn ($item) => (int) ($item->product?->weight ?? 0) * max(1, $item->quantity));
    $totalWeightKg = $totalWeightGram > 0 ? number_format($totalWeightGram / 1000, 2) : '0.00';
    $shippingCostValue = $order->shipping_cost ?? $shipment?->shipping_cost ?? 0;
    $driverVisibleStatuses = ['on_the_way', 'delivering', 'delivered'];
    $shouldShowDriver = $shipment && in_array($shipment->status, $driverVisibleStatuses, true);
    $driverDefaults = [
        'name' => 'John Doe',
        'phone' => '08123456789',
        'plate' => 'B 12345 ABC',
    ];
    $driverName = $shipment?->driver_name ?? ($shouldShowDriver ? $driverDefaults['name'] : null);
    $driverPhone = $shipment?->driver_phone ?? ($shouldShowDriver ? $driverDefaults['phone'] : null);
    $driverPlate = $shipment?->driver_plate_number ?? ($shouldShowDriver ? $driverDefaults['plate'] : null);
    $courierLabel = trim(($shipment?->courier_name ?? '').' '.($shipment?->courier_service ?? ''));
    $payment = $order->payment;
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
                                @php
                                    $trackingId = $order->shipment?->tracking_id;
                                    $waybillId = $order->shipment?->waybill_id;
                                @endphp
                                @if ($trackingId || $waybillId)
                                    <p class="mt-1 text-sm font-semibold text-slate-900">{{ $trackingId ?? $waybillId }}</p>
                                    @if ($waybillId && $trackingId !== $waybillId)
                                        <p class="text-xs text-slate-500">{{ __('Waybill: :waybill', ['waybill' => $waybillId]) }}</p>
                                    @endif
                                @else
                                    <p class="mt-1 text-sm font-semibold text-slate-900">{{ __('-') }}</p>
                                @endif
                                <p class="text-xs text-slate-500">{{ __('Perbarui jika ada perubahan') }}</p>
                            </div>
                            <div class="rounded-xl border border-slate-100 p-4">
                                <p class="text-xs uppercase text-slate-400">{{ __('Estimasi Sampai') }}</p>
                                <p class="mt-1 text-sm font-semibold text-slate-900">
                                    {{ $estimationLabel ? __(":range sampai", ['range' => $estimationLabel]) : __('Tidak tersedia') }}
                                </p>
                                <p class="text-xs text-slate-500">{{ __('Ongkir: :amount', ['amount' => $formatCurrency($order->shipment?->shipping_cost ?? 0)]) }}</p>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ __('Detail Pengiriman') }}</p>
                                <p class="text-xs text-slate-500">{{ __('Status real-time dan informasi resi Anda.') }}</p>
                            </div>
                        </div>
                        <div class="mt-4 grid gap-6 lg:grid-cols-[1.2fr,1fr]">
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                <dl class="grid gap-3 sm:grid-cols-2">
                                    <div>
                                        <dt class="text-[11px] uppercase text-slate-500">{{ __('ID Pesanan') }}</dt>
                                        <dd class="text-sm font-semibold text-slate-900">{{ $order->order_number }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-[11px] uppercase text-slate-500">{{ __('No. Resi') }}</dt>
                                        <dd class="text-sm font-semibold text-slate-900">{{ $trackingNumber ?? __('Belum tersedia') }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-[11px] uppercase text-slate-500">{{ __('Status') }}</dt>
                                        <dd class="text-sm font-semibold text-slate-900">{{ $trackingStatusLabel }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-[11px] uppercase text-slate-500">{{ __('Estimasi Sampai') }}</dt>
                                        <dd class="text-sm font-semibold text-slate-900">
                                            {{ $estimationLabel ? __(":range sampai", ['range' => $estimationLabel]) : __('Belum tersedia') }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-[11px] uppercase text-slate-500">{{ __('Tanggal Order') }}</dt>
                                        <dd class="text-sm font-semibold text-slate-900">{{ $orderDateLabel }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-[11px] uppercase text-slate-500">{{ __('Kurir') }}</dt>
                                        <dd class="text-sm font-semibold text-slate-900">{{ $courierLabel !== '' ? $courierLabel : __('Belum ditentukan') }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-[11px] uppercase text-slate-500">{{ __('Berat') }}</dt>
                                        <dd class="text-sm font-semibold text-slate-900">{{ $totalWeightKg }} {{ __('kg') }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-[11px] uppercase text-slate-500">{{ __('Ongkos Kirim') }}</dt>
                                        <dd class="text-sm font-semibold text-slate-900">{{ $formatCurrency($shippingCostValue) }}</dd>
                                    </div>
                                </dl>
                            </div>
                            <div class="rounded-xl border border-slate-100 bg-white p-4">
                                <p class="text-sm font-semibold text-slate-900">{{ __('Kontak Kurir / Driver') }}</p>
                                <dl class="mt-3 space-y-2 text-sm text-slate-700">
                                    <div class="flex items-center justify-between">
                                        <dt class="text-xs uppercase text-slate-400">{{ __('Nama Driver') }}</dt>
                                        <dd class="font-semibold text-slate-900">{{ $driverName ?? __('Belum tersedia') }}</dd>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <dt class="text-xs uppercase text-slate-400">{{ __('Nomor HP Driver') }}</dt>
                                        <dd class="font-semibold text-slate-900">{{ $driverPhone ?? __('Belum tersedia') }}</dd>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <dt class="text-xs uppercase text-slate-400">{{ __('Plat Nomor') }}</dt>
                                        <dd class="font-semibold text-slate-900">{{ $driverPlate ?? __('Belum tersedia') }}</dd>
                                    </div>
                                </dl>
                                <p class="mt-3 text-[11px] text-slate-500">{{ __('Data driver muncul otomatis ketika status sudah dalam perjalanan.') }}</p>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ __('Informasi Pembayaran') }}</p>
                                <p class="text-xs text-slate-500">{{ __('Status transaksi Midtrans pelanggan.') }}</p>
                            </div>
                        </div>
                        <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-xs uppercase text-slate-400">{{ __('Metode') }}</dt>
                                <dd class="text-sm font-semibold text-slate-900">
                                    {{ $paymentMethods[$order->payment_method]['label'] ?? \Illuminate\Support\Str::headline($order->payment_method ?? '-') }}
                                </dd>
                                <p class="text-xs text-slate-500">{{ $paymentMethods[$order->payment_method]['description'] ?? '' }}</p>
                            </div>
                            <div>
                                <dt class="text-xs uppercase text-slate-400">{{ __('Status Pembayaran') }}</dt>
                                <dd class="text-sm font-semibold text-slate-900">
                                    {{ \Illuminate\Support\Str::headline($payment?->payment_status ?? 'pending') }}
                                </dd>
                                <p class="text-xs text-slate-500">
                                    @if ($payment?->paid_at)
                                        {{ __('Dibayar pada :date', ['date' => $payment->paid_at->format('d M Y H:i')]) }}
                                    @else
                                        {{ __('Menunggu konfirmasi Midtrans') }}
                                    @endif
                                </p>
                            </div>
                            @if ($payment?->va_number)
                                <div>
                                    <dt class="text-xs uppercase text-slate-400">{{ __('Virtual Account') }}</dt>
                                    <dd class="text-sm font-semibold text-slate-900">
                                        {{ strtoupper($payment->bank ?? '') }} - {{ $payment->va_number }}
                                    </dd>
                                </div>
                            @endif
                            @if ($payment?->payment_link)
                                <div>
                                    <dt class="text-xs uppercase text-slate-400">{{ __('Link Pembayaran') }}</dt>
                                    <dd class="text-sm font-semibold text-slate-900">
                                        <a href="{{ $payment->payment_link }}" target="_blank" rel="noreferrer" class="text-amber-700 hover:text-amber-800">
                                            {{ __('Buka di tab baru') }}
                                        </a>
                                    </dd>
                                </div>
                            @endif
                            @if ($payment?->qr_string)
                                <div>
                                    <dt class="text-xs uppercase text-slate-400">{{ __('QR String') }}</dt>
                                    <dd class="text-sm font-semibold text-slate-900 break-words">{{ \Illuminate\Support\Str::limit($payment->qr_string, 80) }}</dd>
                                </div>
                            @endif
                            @if ($payment?->transaction_id)
                                <div>
                                    <dt class="text-xs uppercase text-slate-400">{{ __('ID Transaksi') }}</dt>
                                    <dd class="text-sm font-semibold text-slate-900">{{ $payment->transaction_id }}</dd>
                                </div>
                            @endif
                            <div>
                                <dt class="text-xs uppercase text-slate-400">{{ __('Total Bayar') }}</dt>
                                <dd class="text-sm font-semibold text-slate-900">{{ $formatCurrency($order->total_amount) }}</dd>
                            </div>
                        </dl>
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

                    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold text-slate-900">{{ __('Tracking Resi') }}</p>
                            <p class="text-xs text-slate-500">
                                {{ $order->shipment?->tracking_id ?: $order->shipment?->waybill_id ?: __('Belum ada resi') }}
                            </p>
                        </div>
                        <div class="mt-4 space-y-4">
                            @if ($trackingEvents->isEmpty())
                                <p class="text-sm text-slate-500">{{ __('Belum ada update tracking dari Biteship.') }}</p>
                            @else
                                @foreach ($trackingEvents as $event)
                                    @php
                                        $recordedAt = $event->recorded_at ? $event->recorded_at->format('d-m-Y H:i:s') : null;
                                        $status = $event->status ?? '-';
                                        $eventLabel = $trackingStatusLabels[$status] ?? \Illuminate\Support\Str::headline($status);
                                        $description = $event->description ?: $eventLabel;
                                    @endphp
                                    <div class="flex flex-col gap-1 rounded-xl border border-slate-100 bg-slate-50 p-3">
                                        <div class="flex items-center justify-between">
                                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                {{ $eventLabel }}
                                            </p>
                                            <p class="text-xs text-slate-500">
                                                {{ $recordedAt ?? '-' }}
                                            </p>
                                        </div>
                                        <p class="text-sm text-slate-800">{{ $description }}</p>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </section>

                    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm grid gap-6 md:grid-cols-2">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">{{ __('Alamat Pengiriman') }}</p>
                            <div class="mt-2 text-sm text-slate-600">
                                @if ($order->recipient_name || $order->full_address)
                                    <p class="font-semibold text-slate-900">{{ $order->recipient_name ?? __('Tanpa nama') }}</p>
                                    @if ($order->phone)
                                        <p class="text-xs text-slate-500">{{ $order->phone }}</p>
                                    @endif
                                    <p class="mt-2">{{ $order->full_address ?? __('Alamat belum tersedia.') }}</p>
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
                                    <dd class="font-semibold text-slate-900">{{ $formatCurrency($order->subtotal_amount) }}</dd>
                                </div>
                                <div class="flex items-center justify-between">
                                    <dt>{{ __('Ongkos Kirim') }}</dt>
                                    <dd class="font-semibold text-slate-900">{{ $formatCurrency($order->shipping_cost) }}</dd>
                                </div>
                                <div class="flex items-center justify-between border-t border-dashed border-slate-200 pt-2">
                                    <dt>{{ __('Total') }}</dt>
                                    <dd class="text-lg font-semibold text-slate-900">{{ $formatCurrency($order->total_amount) }}</dd>
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
                        <p class="text-sm font-semibold text-slate-900">{{ __('Estimasi Pengiriman') }}</p>
                        <p class="mt-1 text-xs text-slate-500">{{ __('Estimasi mengikuti layanan kurir yang dipilih saat checkout. Admin pengiriman tidak dapat mengubah estimasi.') }}</p>
                        <div class="mt-4 rounded-xl border border-dashed border-slate-200 bg-slate-50 px-3 py-3 text-sm text-slate-700">
                            <p class="font-semibold text-slate-900">{{ __('Estimasi saat ini') }}</p>
                            <p class="mt-1 text-base font-semibold text-slate-900">{{ $estimationLabel }}</p>
                            <p class="text-xs text-slate-500">{{ __('Jika estimasi kurang sesuai, sesuaikan pilihan kurir saat pesanan dibuat.') }}</p>
                        </div>
                    </section>
                </aside>
            </div>
        </div>
    </div>
</x-app-layout>
