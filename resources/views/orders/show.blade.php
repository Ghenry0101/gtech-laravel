@php
    $formatCurrency = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
    $timelineStatuses = ['pending', 'processing', 'shipped', 'completed'];
    $timelineCurrentIndex = array_search($order->order_status, $timelineStatuses, true);
    $statusLabel = $currentStatus['label'] ?? \Illuminate\Support\Str::headline($order->order_status);
    $statusBadgeClass = $currentStatus['badge_class'] ?? 'bg-slate-100 text-slate-700';
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs uppercase text-slate-400">{{ __('Pesanan #') }}{{ $order->order_number }}</p>
                <h1 class="text-2xl font-semibold text-slate-900">{{ __('Detail Pesanan') }}</h1>
                <p class="text-xs text-slate-500">
                    {{ __('Dibuat pada :date', ['date' => optional($order->order_time ?? $order->created_at)->format('d M Y H:i')]) }}
                </p>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-3">
                <span class="inline-flex items-center justify-center rounded-full px-3 py-1 text-xs font-semibold {{ $statusBadgeClass }}">
                    {{ $statusLabel }}
                </span>
                @if ($order->order_status === 'shipped')
                    <form method="POST" action="{{ route('orders.complete', $order) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="inline-flex items-center justify-center rounded-full bg-emerald-500 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-600">
                            {{ __('Tandai Pesanan Selesai') }}
                        </button>
                    </form>
                @endif
                <a href="{{ route('orders.index') }}" class="inline-flex items-center justify-center rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:border-slate-300 hover:text-slate-900">
                    {{ __('Kembali ke Riwayat') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            @if ($cameFromCheckout)
                <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-6 py-5 text-sm text-emerald-800">
                    <p class="font-semibold">{{ __('Terima kasih! Pesanan Anda berhasil dibuat.') }}</p>
                    <p class="mt-1">{{ __('Status pembayaran akan diperbarui otomatis begitu Midtrans mengkonfirmasi transaksi Anda.') }}</p>
                </div>
            @endif

            @if (session('status_message'))
                <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-6 py-4 text-sm text-emerald-800">
                    {{ session('status_message') }}
                </div>
            @endif

            @if ($order->order_status === 'canceled')
                <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-6 py-5 text-sm text-rose-800">
                    <p class="font-semibold">{{ __('Pesanan dibatalkan') }}</p>
                    <p class="mt-1">{{ $currentStatus['description'] ?? __('Silakan hubungi tim kami jika ini tidak sesuai.') }}</p>
                </div>
            @endif

            <div class="grid gap-6 lg:grid-cols-[2fr,1fr]">
                <div class="space-y-6">
                    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ __('Status Pesanan') }}</p>
                                <p class="text-xs text-slate-500">{{ __('Perkembangan setiap tahapan pesanan Anda.') }}</p>
                            </div>
                        </div>
                        <ol class="mt-6 space-y-5">
                            @foreach ($timelineStatuses as $index => $statusKey)
                                @php
                                    $isCompleted = $order->order_status !== 'canceled' && $timelineCurrentIndex !== false && $index <= $timelineCurrentIndex;
                                    $statusInfo = $statusMeta[$statusKey] ?? null;
                                @endphp
                                <li class="flex gap-3">
                                    <div class="flex flex-col items-center">
                                        <span class="flex h-6 w-6 items-center justify-center rounded-full text-xs font-semibold {{ $isCompleted ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-500' }}">
                                            {{ $index + 1 }}
                                        </span>
                                        @if ($index < count($timelineStatuses) - 1)
                                            <span class="mt-1 h-full w-px flex-1 bg-gradient-to-b from-transparent via-slate-200 to-transparent"></span>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">
                                            {{ $statusInfo['label'] ?? \Illuminate\Support\Str::headline($statusKey) }}
                                        </p>
                                        <p class="text-xs text-slate-500">
                                            {{ $statusInfo['description'] ?? '' }}
                                        </p>
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    </section>

                    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ __('Daftar Produk') }}</p>
                                <p class="text-xs text-slate-500">{{ __('Rincian barang yang Anda pesan.') }}</p>
                            </div>
                        </div>
                        <div class="mt-4 divide-y divide-slate-100">
                            @forelse ($order->items as $item)
                                <div class="flex flex-col gap-2 py-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">{{ $item->product_name }}</p>
                                        <p class="text-xs text-slate-500">
                                            {{ __('Qty: :qty', ['qty' => $item->quantity]) }} • {{ $formatCurrency($item->price) }}
                                        </p>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-900">
                                        {{ $formatCurrency($item->price * $item->quantity) }}
                                    </p>
                                </div>
                            @empty
                                <p class="py-4 text-sm text-slate-500">{{ __('Tidak ada item pada pesanan ini.') }}</p>
                            @endforelse
                        </div>
                    </section>

                    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ __('Alamat Pengiriman') }}</p>
                                <p class="text-xs text-slate-500">{{ __('Alamat tujuan paket dikirim.') }}</p>
                            </div>
                        </div>
                        <div class="mt-4 text-sm text-slate-600">
                            @if ($order->address)
                                <p class="font-semibold text-slate-900">{{ $order->address->recipient_name }}</p>
                                <p class="text-xs text-slate-500">{{ $order->address->phone }}</p>
                                <p class="mt-2">{{ $order->address->detail }}</p>
                                <p>{{ $order->address->district }}, {{ $order->address->city }}, {{ $order->address->province }} {{ $order->address->postal_code }}</p>
                            @else
                                <p>{{ __('Alamat tidak tersedia.') }}</p>
                            @endif
                        </div>
                    </section>

                    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ __('Informasi Pengiriman') }}</p>
                                <p class="text-xs text-slate-500">{{ __('Detail kurir dan tracking paket.') }}</p>
                            </div>
                        </div>
                        <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-xs uppercase text-slate-400">{{ __('Kurir') }}</dt>
                                <dd class="text-sm font-semibold text-slate-900">{{ $order->shipment?->courier_name ?? __('Belum ditentukan') }}</dd>
                                <p class="text-xs text-slate-500">{{ $order->shipment?->courier_service }}</p>
                            </div>
                            <div>
                                <dt class="text-xs uppercase text-slate-400">{{ __('Status Pengiriman') }}</dt>
                                <dd class="text-sm font-semibold text-slate-900">{{ \Illuminate\Support\Str::headline($order->shipment?->status ?? '-') }}</dd>
                                <p class="text-xs text-slate-500">{{ $order->shipment?->tracking_id ? __('Resi: :resi', ['resi' => $order->shipment->tracking_id]) : __('Resi belum tersedia') }}</p>
                            </div>
                        </dl>
                    </section>

                    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ __('Informasi Pembayaran') }}</p>
                                <p class="text-xs text-slate-500">{{ __('Status transaksi Midtrans Anda.') }}</p>
                            </div>
                        </div>
                        <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-xs uppercase text-slate-400">{{ __('Metode') }}</dt>
                                <dd class="text-sm font-semibold text-slate-900">
                                    {{ $paymentMethods[$order->payment_method]['label'] ?? __('-') }}
                                </dd>
                                <p class="text-xs text-slate-500">{{ $paymentMethods[$order->payment_method]['description'] ?? '' }}</p>
                            </div>
                            <div>
                                <dt class="text-xs uppercase text-slate-400">{{ __('Status Pembayaran') }}</dt>
                                <dd class="text-sm font-semibold text-slate-900">
                                    {{ \Illuminate\Support\Str::headline($order->payment?->payment_status ?? 'pending') }}
                                </dd>
                                <p class="text-xs text-slate-500">
                                    @if ($order->payment?->paid_at)
                                        {{ __('Dibayar pada :date', ['date' => $order->payment->paid_at->format('d M Y H:i')]) }}
                                    @else
                                        {{ __('Menunggu konfirmasi Midtrans') }}
                                    @endif
                                </p>
                            </div>
                            @if ($order->payment?->va_number)
                                <div>
                                    <dt class="text-xs uppercase text-slate-400">{{ __('Virtual Account') }}</dt>
                                    <dd class="text-sm font-semibold text-slate-900">{{ strtoupper($order->payment->bank) }} - {{ $order->payment->va_number }}</dd>
                                </div>
                            @endif
                            @if ($order->payment?->transaction_id)
                                <div>
                                    <dt class="text-xs uppercase text-slate-400">{{ __('ID Transaksi') }}</dt>
                                    <dd class="text-sm font-semibold text-slate-900">{{ $order->payment->transaction_id }}</dd>
                                </div>
                            @endif
                        </dl>
                    </section>

                    @if ($order->notes)
                        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                            <p class="text-sm font-semibold text-slate-900">{{ __('Catatan Pembeli') }}</p>
                            <p class="mt-2 text-sm text-slate-600">{{ $order->notes }}</p>
                        </section>
                    @endif
                </div>

                <aside class="space-y-6">
                    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-sm font-semibold text-slate-900">{{ __('Ringkasan Pembayaran') }}</p>
                        <dl class="mt-4 space-y-3 text-sm text-slate-600">
                            <div class="flex items-center justify-between">
                                <dt>{{ __('Subtotal') }}</dt>
                                <dd class="font-semibold text-slate-900">{{ $formatCurrency($order->total_amount) }}</dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt>{{ __('Ongkos Kirim') }}</dt>
                                <dd class="font-semibold text-slate-900">{{ $formatCurrency($order->shipping_cost) }}</dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt>{{ __('Total Dibayar') }}</dt>
                                <dd class="text-lg font-semibold text-slate-900">{{ $formatCurrency($order->grand_total) }}</dd>
                            </div>
                        </dl>
                        <p class="mt-4 text-xs text-slate-500">
                            {{ __('Nomor pesanan: :number', ['number' => $order->order_number]) }}
                        </p>
                    </section>

                    @if ($order->order_status === 'completed')
                        <section id="order-review-section" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">{{ __('Bagikan Pengalaman Anda') }}</p>
                                    <p class="text-xs text-slate-500">{{ __('Ulas setiap produk untuk membantu pelanggan lain.') }}</p>
                                </div>
                            </div>
                            <div class="mt-5 space-y-4">
                                @foreach ($order->items as $item)
                                    @php
                                        $review = $item->review;
                                        $activeOld = old('context_item');
                                        $isCurrentForm = $activeOld && (int) $activeOld === $item->id;
                                        $ratingValue = $isCurrentForm ? old('rating') : ($review->rating ?? null);
                                        $titleValue = $isCurrentForm ? old('title') : ($review->title ?? '');
                                        $commentValue = $isCurrentForm ? old('comment') : ($review->comment ?? '');
                                    @endphp
                                    <article class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                            <div>
                                                <p class="text-sm font-semibold text-slate-900">{{ $item->product_name }}</p>
                                                <p class="text-xs text-slate-500">{{ __('Jumlah: :qty', ['qty' => $item->quantity]) }}</p>
                                            </div>
                                            @if ($review)
                                                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-600">
                                                    {{ __('Sudah diulas') }}
                                                </span>
                                            @endif
                                        </div>
                                        <form method="POST" action="{{ $review ? route('order-items.review.update', $item) : route('order-items.review.store', $item) }}" class="mt-4 space-y-3">
                                            @csrf
                                            @if ($review)
                                                @method('PUT')
                                            @endif
                                            <input type="hidden" name="context_item" value="{{ $item->id }}">
                                            <div class="grid gap-3 sm:grid-cols-2">
                                                <div>
                                                    <label class="text-xs uppercase text-slate-400" for="rating-{{ $item->id }}">{{ __('Penilaian') }}</label>
                                                    <select name="rating" id="rating-{{ $item->id }}" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-slate-400 focus:outline-none focus:ring-0">
                                                        <option value="">{{ __('Pilih rating') }}</option>
                                                        @for ($i = 5; $i >= 1; $i--)
                                                            <option value="{{ $i }}" @selected((int) $ratingValue === $i)>{{ $i }} / 5</option>
                                                        @endfor
                                                    </select>
                                                    @if ($errors->has('rating') && $isCurrentForm)
                                                        <p class="mt-1 text-xs text-rose-500">{{ $errors->first('rating') }}</p>
                                                    @endif
                                                </div>
                                                <div>
                                                    <label class="text-xs uppercase text-slate-400" for="title-{{ $item->id }}">{{ __('Judul (opsional)') }}</label>
                                                    <input type="text" name="title" id="title-{{ $item->id }}" value="{{ $titleValue }}" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-slate-400 focus:outline-none focus:ring-0" placeholder="{{ __('Contoh: Pengiriman cepat') }}">
                                                    @if ($errors->has('title') && $isCurrentForm)
                                                        <p class="mt-1 text-xs text-rose-500">{{ $errors->first('title') }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                            <div>
                                                <label class="text-xs uppercase text-slate-400" for="comment-{{ $item->id }}">{{ __('Komentar') }}</label>
                                                <textarea name="comment" id="comment-{{ $item->id }}" rows="3" class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm focus:border-slate-400 focus:outline-none focus:ring-0" placeholder="{{ __('Tuliskan kesan Anda...') }}">{{ $commentValue }}</textarea>
                                                @if ($errors->has('comment') && $isCurrentForm)
                                                    <p class="mt-1 text-xs text-rose-500">{{ $errors->first('comment') }}</p>
                                                @endif
                                            </div>
                                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                                <p class="text-xs text-slate-500">{{ __('Ulasan dipublikasikan dengan nama akun Anda.') }}</p>
                                                <button type="submit" class="inline-flex items-center justify-center rounded-full {{ $review ? 'bg-amber-500 hover:bg-amber-600' : 'bg-emerald-500 hover:bg-emerald-600' }} px-4 py-2 text-xs font-semibold text-white">
                                                    {{ $review ? __('Perbarui Ulasan') : __('Kirim Ulasan') }}
                                                </button>
                                            </div>
                                        </form>
                                    </article>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-sm font-semibold text-slate-900">{{ __('Butuh Bantuan?') }}</p>
                        <p class="mt-2 text-sm text-slate-600">{{ __('Jika Anda mengalami kendala pembayaran atau pengiriman, hubungi tim kami via email support atau WhatsApp customer service.') }}</p>
                        <div class="mt-4 space-y-2">
                            <a href="mailto:{{ config('mail.from.address', 'support@example.com') }}" class="inline-flex w-full items-center justify-center rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:border-slate-300">
                                {{ __('Email Support') }}
                            </a>
                            <a href="https://wa.me/{{ preg_replace('/\D+/', '', config('app.support_whatsapp', '628123456789')) }}" target="_blank" class="inline-flex w-full items-center justify-center rounded-full bg-emerald-500 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-600">
                                {{ __('Chat WhatsApp') }}
                            </a>
                        </div>
                    </section>
                </aside>
            </div>
        </div>
    </div>
</x-app-layout>
