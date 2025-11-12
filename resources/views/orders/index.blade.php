@php
    $formatCurrency = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs uppercase text-slate-400">{{ __('Pesanan') }}</p>
                <h1 class="text-2xl font-semibold text-slate-900">{{ __('Riwayat Pemesanan') }}</h1>
            </div>
            <a href="{{ route('home') }}" class="text-sm text-slate-500 hover:text-slate-700">
                {{ __('Belanja Produk Lainnya') }}
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            @if ($orders->isEmpty())
                <div class="rounded-2xl border border-dashed border-slate-200 bg-white px-6 py-16 text-center shadow-sm">
                    <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-600">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                            <path d="M3 9h18M3 9l2-4h14l2 4M3 9l1.2 10.2a1 1 0 0 0 1 .8h13.6a1 1 0 0 0 1-.8L21 9M7 13h10M9 17h6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <h2 class="text-lg font-semibold text-slate-900">{{ __('Belum ada pesanan') }}</h2>
                    <p class="mt-2 text-sm text-slate-500">
                        {{ __('Setelah checkout berhasil, pesanan Anda akan tampil di halaman ini.') }}
                    </p>
                    <a href="{{ route('home') }}" class="mt-6 inline-flex items-center rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                        {{ __('Mulai Belanja') }}
                    </a>
                </div>
            @else
                <div class="space-y-4">
                    @foreach ($orders as $order)
                        @php
                            $status = $statusMeta[$order->order_status] ?? null;
                            $itemsCount = $order->items->sum('quantity');
                            $firstItem = $order->items->first();
                            $needsReview = $order->order_status === 'completed'
                                && $order->items->contains(fn ($item) => $item->review === null);
                        @endphp
                        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-xs uppercase text-slate-400">{{ __('Nomor Pesanan') }}</p>
                                    <p class="text-lg font-semibold text-slate-900">{{ $order->order_number }}</p>
                                    <p class="text-xs text-slate-500">
                                        {{ __('Dibuat pada :date', ['date' => optional($order->order_time ?? $order->created_at)->format('d M Y H:i')]) }}
                                    </p>
                                </div>
                                @if ($status)
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $status['badge_class'] }}">
                                        {{ $status['label'] }}
                                    </span>
                                @endif
                            </div>
                            <div class="mt-4 grid gap-4 sm:grid-cols-3">
                                <div>
                                    <p class="text-xs uppercase text-slate-400">{{ __('Ringkasan Barang') }}</p>
                                    <p class="text-sm font-semibold text-slate-900">
                                        {{ $itemsCount }} {{ __('item') }}
                                    </p>
                                    @if ($firstItem)
                                        <p class="text-xs text-slate-500">
                                            {{ $firstItem->product_name }}
                                            @if ($order->items->count() > 1)
                                                {{ __('+ :count produk lainnya', ['count' => $order->items->count() - 1]) }}
                                            @endif
                                        </p>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-xs uppercase text-slate-400">{{ __('Kurir') }}</p>
                                    <p class="text-sm font-semibold text-slate-900">
                                        {{ $order->shipment?->courier_name ?? __('-') }}
                                    </p>
                                    <p class="text-xs text-slate-500">{{ $order->shipment?->courier_service }}</p>
                                </div>
                                <div>
                                    <p class="text-xs uppercase text-slate-400">{{ __('Total Pembayaran') }}</p>
                                    <p class="text-lg font-semibold text-slate-900">
                                        {{ $formatCurrency($order->grand_total) }}
                                    </p>
                                    <p class="text-xs text-slate-500">{{ __('Status Pembayaran: :status', ['status' => __(\Illuminate\Support\Str::headline($order->payment?->payment_status ?? 'pending'))]) }}</p>
                                </div>
                            </div>
                            <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <p class="text-xs text-slate-500">
                                    {{ __('Terakhir diperbarui :time', ['time' => optional($order->updated_at)->diffForHumans()]) }}
                                </p>
                                <div class="flex flex-wrap gap-3">
                                    @if ($needsReview)
                                        <a href="{{ route('orders.show', $order).'#order-review-section' }}" class="inline-flex items-center rounded-full bg-emerald-500 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-600">
                                            {{ __('Tulis Ulasan') }}
                                        </a>
                                    @endif
                                    <a href="{{ route('orders.show', $order) }}" class="inline-flex items-center rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:border-slate-300 hover:text-slate-900">
                                        {{ __('Lihat Detail') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
