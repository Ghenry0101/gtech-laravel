@php
    $statusBadges = [
        'pending' => 'bg-amber-100 text-amber-700',
        'processing' => 'bg-sky-100 text-sky-800',
        'shipped' => 'bg-indigo-100 text-indigo-800',
        'completed' => 'bg-emerald-100 text-emerald-800',
    ];

    $filterOptions = [
        'processing' => __('Perlu Diproses'),
        'shipped' => __('Sedang Dikirim'),
        'completed' => __('Selesai'),
        'all' => __('Semua'),
    ];

    $paymentBadges = [
        'settlement' => 'bg-emerald-100 text-emerald-700',
        'capture' => 'bg-emerald-100 text-emerald-700',
        'pending' => 'bg-amber-100 text-amber-700',
        'expire' => 'bg-rose-100 text-rose-700',
        'cancel' => 'bg-rose-100 text-rose-700',
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs uppercase tracking-wide text-slate-400">{{ __('Admin Pengiriman') }}</p>
                <h1 class="text-2xl font-semibold text-slate-900">{{ __('Dasbor Pengiriman Pesanan') }}</h1>
            </div>
            <p class="text-sm text-slate-500">
                {{ __('Pantau antrian, update status, dan catat resi secara cepat.') }}
            </p>
        </div>
    </x-slot>

    <div class="py-10 space-y-8">
        <section>
            <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-6">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs uppercase text-slate-400">{{ __('Menunggu Diproses') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-900">{{ number_format($stats['queue']) }}</p>
                    <p class="text-xs text-slate-500">{{ __('Pesanan siap dikirim') }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs uppercase text-slate-400">{{ __('Sedang Dikirim') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-900">{{ number_format($stats['in_transit']) }}</p>
                    <p class="text-xs text-slate-500">{{ __('Dalam perjalanan ke pelanggan') }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs uppercase text-slate-400">{{ __('Selesai Bulan Ini') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-900">{{ number_format($stats['completed']) }}</p>
                    <p class="text-xs text-slate-500">{{ __('Telah diterima pelanggan') }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs uppercase text-slate-400">{{ __('Butuh Perhatian') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-rose-600">{{ number_format($stats['delayed']) }}</p>
                    <p class="text-xs text-rose-500">{{ __('Lewat 3 hari, cek statusnya') }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs uppercase text-slate-400">{{ __('Sudah Bayar') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-emerald-600">{{ number_format($stats['paid']) }}</p>
                    <p class="text-xs text-slate-500">{{ __('Siap diproses gudang') }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs uppercase text-slate-400">{{ __('Belum Bayar') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-amber-600">{{ number_format($stats['unpaid']) }}</p>
                    <p class="text-xs text-slate-500">{{ __('Menunggu konfirmasi pelanggan') }}</p>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <header class="border-b border-slate-100 px-6 py-4">
                <h3 class="text-lg font-semibold text-slate-900">{{ __('Segmentasi Pembayaran') }}</h3>
                <p class="text-sm text-slate-500">{{ __('Monitor prioritas pengiriman berdasarkan status pembayaran.') }}</p>
            </header>
            <div class="grid gap-6 border-t border-slate-100 px-6 py-6 lg:grid-cols-2">
                <div>
                    <p class="text-sm font-semibold text-slate-900">{{ __('Sudah dibayar') }}</p>
                    <p class="text-xs text-slate-500">{{ __('Prioritaskan pengiriman pesanan berikut.') }}</p>
                    <div class="mt-4 space-y-3">
                        @forelse ($paidSample as $paidOrder)
                            <article class="rounded-2xl border border-slate-100 p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">{{ $paidOrder->order_number }}</p>
                                        <p class="text-xs text-slate-500">{{ $paidOrder->user?->name }}</p>
                                    </div>
                                    @php
                                        $badge = $paymentBadges[$paidOrder->payment?->payment_status ?? 'settlement'] ?? 'bg-slate-100 text-slate-600';
                                    @endphp
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $badge }}">
                                        {{ \Illuminate\Support\Str::headline($paidOrder->payment?->payment_status ?? 'settlement') }}
                                    </span>
                                </div>
                                <div class="mt-3 flex items-center justify-between text-xs text-slate-500">
                                    <span>{{ __('Total') }}: <strong class="text-slate-900">Rp {{ number_format($paidOrder->total_amount, 0, ',', '.') }}</strong></span>
                                    <span>{{ __('Kurir') }}: {{ $paidOrder->shipment?->courier_name ?? '-' }}</span>
                                </div>
                            </article>
                        @empty
                            <p class="text-sm text-slate-500">{{ __('Belum ada pesanan berbayar.') }}</p>
                        @endforelse
                    </div>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-900">{{ __('Belum dibayar') }}</p>
                    <p class="text-xs text-slate-500">{{ __('Pantau pesanan yang masih menunggu pembayaran.') }}</p>
                    <div class="mt-4 space-y-3">
                        @forelse ($unpaidSample as $unpaidOrder)
                            <article class="rounded-2xl border border-slate-100 p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">{{ $unpaidOrder->order_number }}</p>
                                        <p class="text-xs text-slate-500">{{ $unpaidOrder->user?->name }}</p>
                                    </div>
                                    @php
                                        $status = $unpaidOrder->payment?->payment_status ?? 'pending';
                                        $badge = $paymentBadges[$status] ?? 'bg-slate-100 text-slate-600';
                                    @endphp
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $badge }}">
                                        {{ \Illuminate\Support\Str::headline($status) }}
                                    </span>
                                </div>
                                <div class="mt-3 flex items-center justify-between text-xs text-slate-500">
                                    <span>{{ __('Total') }}: <strong class="text-slate-900">Rp {{ number_format($unpaidOrder->total_amount, 0, ',', '.') }}</strong></span>
                                    <span>{{ __('Dibuat') }}: {{ optional($unpaidOrder->order_time ?? $unpaidOrder->created_at)->format('d M H:i') }}</span>
                                </div>
                            </article>
                        @empty
                            <p class="text-sm text-slate-500">{{ __('Semua pesanan sudah dibayar.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-5 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex flex-wrap gap-2">
                    @foreach ($filterOptions as $key => $label)
                        @php
                            $url = route('admin.shipping.dashboard', array_filter([
                                'status' => $key,
                                'q' => $search !== '' ? $search : null,
                            ]));
                            $active = $filter === $key;
                        @endphp
                        <a href="{{ $url }}" class="inline-flex items-center rounded-full px-4 py-2 text-sm font-semibold {{ $active ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
                <form method="GET" action="{{ route('admin.shipping.dashboard') }}" class="flex w-full max-w-md items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-1.5 text-sm shadow-sm">
                    <input type="hidden" name="status" value="{{ $filter }}">
                    <input type="search" name="q" value="{{ $search }}" placeholder="{{ __('Cari nomor pesanan atau nama pelanggan') }}" class="w-full bg-transparent text-slate-700 placeholder:text-slate-400 focus:outline-none" />
                    <button type="submit" class="text-slate-500 hover:text-slate-900">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m21 21-5.8-5.8M10 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16Z" />
                        </svg>
                    </button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left font-semibold uppercase tracking-wide text-xs">{{ __('Pesanan') }}</th>
                            <th scope="col" class="px-6 py-3 text-left font-semibold uppercase tracking-wide text-xs">{{ __('Pelanggan') }}</th>
                            <th scope="col" class="px-6 py-3 text-left font-semibold uppercase tracking-wide text-xs">{{ __('Kurir') }}</th>
                            <th scope="col" class="px-6 py-3 text-left font-semibold uppercase tracking-wide text-xs">{{ __('Status') }}</th>
                            <th scope="col" class="px-6 py-3 text-left font-semibold uppercase tracking-wide text-xs">{{ __('Pembayaran') }}</th>
                            <th scope="col" class="px-6 py-3 text-left font-semibold uppercase tracking-wide text-xs">{{ __('Total') }}</th>
                            <th scope="col" class="px-6 py-3 text-left font-semibold uppercase tracking-wide text-xs"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white text-slate-700">
                        @forelse ($orders as $order)
                            <tr>
                                <td class="px-6 py-4">
                                    <p class="text-sm font-semibold text-slate-900">{{ $order->order_number }}</p>
                                    <p class="text-xs text-slate-500">{{ __('Dibuat: :date', ['date' => optional($order->order_time ?? $order->created_at)->format('d M Y H:i')]) }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-semibold">{{ $order->user?->name ?? __('Tanpa nama') }}</p>
                                    <p class="text-xs text-slate-500">{{ $order->user?->email }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-slate-900">{{ $order->shipment?->courier_name ?? __('-') }}</p>
                                    @php
                                        $trackingId = $order->shipment?->tracking_id;
                                        $waybillId = $order->shipment?->waybill_id;
                                    @endphp
                                    @if ($trackingId || $waybillId)
                                        <p class="text-xs text-slate-500">{{ __('Resi: :resi', ['resi' => $trackingId ?? $waybillId]) }}</p>
                                        @if ($waybillId && $trackingId !== $waybillId)
                                            <p class="text-xs text-slate-400">{{ __('Waybill: :waybill', ['waybill' => $waybillId]) }}</p>
                                        @endif
                                    @else
                                        <p class="text-xs text-slate-500">{{ __('Resi belum ada') }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4 space-y-1">
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $statusBadges[$order->order_status] ?? 'bg-slate-100 text-slate-600' }}">
                                        {{ \Illuminate\Support\Str::headline($order->order_status) }}
                                    </span>
                                    <p class="text-xs text-slate-500">
                                        {{ __('Pengiriman: :status', ['status' => \Illuminate\Support\Str::headline($order->shipment?->status ?? '-')]) }}
                                    </p>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $paymentStatus = $order->payment?->payment_status ?? 'pending';
                                        $paymentBadge = $paymentBadges[$paymentStatus] ?? 'bg-slate-100 text-slate-600';
                                    @endphp
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $paymentBadge }}">
                                        {{ \Illuminate\Support\Str::headline($paymentStatus) }}
                                    </span>
                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $order->payment?->paid_at ? __('Dibayar: :time', ['time' => optional($order->payment->paid_at)->format('d M H:i')]) : __('Belum dibayar') }}
                                    </p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-slate-900">
                                        Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                    </p>
                                    <p class="text-xs text-slate-500">
                                        {{ __('+ :count item', ['count' => $order->items?->sum('quantity') ?? 0]) }}
                                    </p>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-2">
                                        <a href="{{ route('admin.shipping.orders.show', $order) }}" class="inline-flex items-center justify-center rounded-full border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:border-slate-300 hover:text-slate-900">
                                            {{ __('Detail') }}
                                        </a>
                                        @if (in_array($order->order_status, ['pending', 'processing'], true))
                                            <form method="POST" action="{{ route('admin.shipping.orders.status.update', $order) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="shipped">
                                                <button type="submit" class="inline-flex w-full items-center justify-center rounded-full bg-indigo-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-600">
                                                    {{ __('Tandai Sedang Dikirim') }}
                                                </button>
                                            </form>
                                        @elseif ($order->order_status === 'shipped')
                                            <form method="POST" action="{{ route('admin.shipping.orders.status.update', $order) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="delivered">
                                                <button type="submit" class="inline-flex w-full items-center justify-center rounded-full bg-emerald-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-600">
                                                    {{ __('Tandai Selesai') }}
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-center text-xs text-emerald-600">{{ __('Selesai') }}</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-sm text-slate-500">
                                    {{ __('Tidak ada pesanan pada filter ini.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4">
                {{ $orders->links() }}
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <header class="border-b border-slate-100 px-6 py-4">
                <h3 class="text-lg font-semibold text-slate-900">{{ __('Aktivitas Terkini') }}</h3>
            </header>
            <div class="divide-y divide-slate-100">
                @forelse ($recentActivities as $activity)
                    <article class="flex items-center justify-between px-6 py-4 text-sm">
                        <div>
                            <p class="font-semibold text-slate-900">{{ $activity->order_number }}</p>
                            <p class="text-xs text-slate-500">
                                {{ $activity->user?->name }} &middot;
                                {{ __('Status :status', ['status' => \Illuminate\Support\Str::headline($activity->shipment?->status ?? '-')]) }}
                            </p>
                        </div>
                        <p class="text-xs text-slate-400">
                            {{ optional($activity->shipment?->updated_at ?? $activity->updated_at)->diffForHumans() }}
                        </p>
                    </article>
                @empty
                    <p class="px-6 py-10 text-center text-sm text-slate-500">{{ __('Belum ada aktivitas pengiriman.') }}</p>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
