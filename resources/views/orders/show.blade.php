
@php
    $formatCurrency = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
    $timelineStatuses = ['pending', 'processing', 'shipped', 'completed'];
    $timelineCurrentIndex = array_search($order->order_status, $timelineStatuses, true);
    $statusLabel = $currentStatus['label'] ?? \Illuminate\Support\Str::headline($order->order_status);
    $statusBadgeClass = $currentStatus['badge_class'] ?? 'bg-slate-100 text-slate-700';
    $latestComplaint = $order->complaints->sortByDesc('created_at')->first();
    $canSubmitComplaint = in_array($order->order_status, ['shipped', 'completed'], true)
        && (! $latestComplaint || $latestComplaint->status !== 'pending');
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
        <div class="mx-auto max-w-8xl px-4 sm:px-6 lg:px-8">
            @if ($cameFromCheckout)
                <div class="mb-6 rounded-md border border-emerald-200 bg-emerald-50 px-6 py-5 text-sm text-emerald-800">
                    <p class="font-semibold">{{ __('Terima kasih! Pesanan Anda berhasil dibuat.') }}</p>
                    <p class="mt-1">{{ __('Status pembayaran akan diperbarui otomatis begitu Midtrans mengkonfirmasi transaksi Anda.') }}</p>
                </div>
            @endif

            @if (session('status_message'))
                <div class="mb-6 rounded-md border border-emerald-200 bg-emerald-50 px-6 py-4 text-sm text-emerald-800">
                    {{ session('status_message') }}
                </div>
            @endif

            @if ($order->order_status === 'canceled')
                <div class="mb-6 rounded-md border border-rose-200 bg-rose-50 px-6 py-5 text-sm text-rose-800">
                    <p class="font-semibold">{{ __('Pesanan dibatalkan') }}</p>
                    <p class="mt-1">{{ $currentStatus['description'] ?? __('Silakan hubungi tim kami jika ini tidak sesuai.') }}</p>
                </div>
            @endif

            <div class="grid gap-6 lg:grid-cols-[2fr,1fr]">
                <div class="space-y-6">
                    <section class="rounded-md border border-slate-200 bg-white p-6 shadow-sm">
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

                    <section class="rounded-md border border-slate-200 bg-white p-6 shadow-sm">
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
                                            {{ __('Qty: :qty', ['qty' => $item->quantity]) }} � {{ $formatCurrency($item->price) }}
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

                    <section class="rounded-md border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ __('Alamat Pengiriman') }}</p>
                                <p class="text-xs text-slate-500">{{ __('Alamat tujuan paket dikirim.') }}</p>
                            </div>
                        </div>
                        <div class="mt-4 text-sm text-slate-600">
                            @if ($order->recipient_name || $order->full_address)
                                <p class="font-semibold text-slate-900">{{ $order->recipient_name ?? __('Tanpa nama') }}</p>
                                @if ($order->phone)
                                    <p class="text-xs text-slate-500">{{ $order->phone }}</p>
                                @endif
                                <p class="mt-2">{{ $order->full_address ?? __('Alamat tidak tersedia.') }}</p>
                            @else
                                <p>{{ __('Alamat tidak tersedia.') }}</p>
                            @endif
                        </div>
                    </section>
                    <section class="rounded-md border border-slate-200 bg-white p-6 shadow-sm">
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
                                @php
                                    $trackingId = $order->shipment?->tracking_id;
                                    $waybillId = $order->shipment?->waybill_id;
                                    $hasTracking = filled($trackingId);
                                    $hasWaybill = filled($waybillId);
                                @endphp
                                @if ($hasTracking || $hasWaybill)
                                    <p class="text-xs text-slate-500">
                                        {{ __('Nomor Resi: :resi', ['resi' => $trackingId ?? $waybillId]) }}
                                    </p>
                                    @if ($hasWaybill && $trackingId !== $waybillId)
                                        <p class="text-xs text-slate-500">
                                            {{ __('Waybill Biteship: :waybill', ['waybill' => $waybillId]) }}
                                        </p>
                                    @endif
                                @else
                                    <p class="text-xs text-slate-500">{{ __('Resi belum tersedia') }}</p>
                                @endif
                            </div>
                        </dl>
                    </section>

                    <section class="rounded-md border border-slate-200 bg-white p-6 shadow-sm">
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
                        <section class="rounded-md border border-slate-200 bg-white p-6 shadow-sm">
                            <p class="text-sm font-semibold text-slate-900">{{ __('Catatan Pembeli') }}</p>
                            <p class="mt-2 text-sm text-slate-600">{{ $order->notes }}</p>
                        </section>
                    @endif
                </div>

                <aside class="space-y-6">
                    <section class="rounded-md border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-sm font-semibold text-slate-900">{{ __('Ringkasan Pembayaran') }}</p>
                        <dl class="mt-4 space-y-3 text-sm text-slate-600">
                            <div class="flex items-center justify-between">
                                <dt>{{ __('Subtotal') }}</dt>
                                <dd class="font-semibold text-slate-900">{{ $formatCurrency($order->subtotal_amount) }}</dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt>{{ __('Ongkos Kirim') }}</dt>
                                <dd class="font-semibold text-slate-900">{{ $formatCurrency($order->shipping_cost) }}</dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt>{{ __('Total Dibayar') }}</dt>
                                <dd class="text-lg font-semibold text-slate-900">{{ $formatCurrency($order->total_amount) }}</dd>
                            </div>
                        </dl>
                        <p class="mt-4 text-xs text-slate-500">
                            {{ __('Nomor pesanan: :number', ['number' => $order->order_number]) }}
                        </p>
                    </section>
                    <section class="rounded-md border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ __('Komplain Pengiriman') }}</p>
                                <p class="text-xs text-slate-500">{{ __('Laporkan jika barang kurang, salah, atau rusak.') }}</p>
                            </div>
                            @if ($latestComplaint)
                                <span class="inline-flex items-center justify-center rounded-sm px-3 py-1 text-xs font-semibold {{ $latestComplaint->status === 'resolved' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $latestComplaint->status === 'resolved' ? __('Selesai') : __('Menunggu') }}
                                </span>
                            @endif
                        </div>
                        @if ($latestComplaint)
                            <article class="mt-5 rounded-md border border-slate-100 bg-slate-50 p-4">
                                <p class="text-sm font-semibold text-slate-900">{{ $latestComplaint->reason }}</p>
                                <p class="text-xs text-slate-500">{{ __('Diajukan :date', ['date' => optional($latestComplaint->created_at)->format('d M Y H:i')]) }}</p>
                                <p class="mt-3 whitespace-pre-line text-sm text-slate-600">{{ $latestComplaint->issue_detail }}</p>
                                @if ($latestComplaint->images->isNotEmpty())
                                    <div class="mt-4 flex flex-wrap gap-2">
                                        @foreach ($latestComplaint->images as $image)
                                            <img
                                                src="{{ asset('storage/'.$image->path) }}"
                                                alt="{{ __('Foto bukti komplain') }}"
                                                class="h-16 w-16 rounded-md object-cover"
                                            >
                                        @endforeach
                                    </div>
                                @endif
                            </article>
                        @else
                            <p class="mt-4 text-sm text-slate-500">{{ __('Belum ada komplain untuk pesanan ini.') }}</p>
                        @endif
                        @if ($canSubmitComplaint)
                            <form method="POST" action="{{ route('orders.complaints.store', $order) }}" class="mt-5 space-y-4" enctype="multipart/form-data">
                                @csrf
                                <div>
                                    <label class="text-xs uppercase text-slate-400" for="complaint-reason">{{ __('Judul Komplain') }}</label>
                                    <input type="text" id="complaint-reason" name="reason" value="{{ old('reason') }}" class="mt-1 w-full rounded-md border border-slate-200 px-3 py-2 text-sm focus:border-slate-400 focus:outline-none focus:ring-0" placeholder="{{ __('Contoh: Barang rusak saat tiba') }}">
                                    @error('reason')
                                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="text-xs uppercase text-slate-400" for="complaint-detail">{{ __('Jelaskan keluhannya') }}</label>
                                    <textarea id="complaint-detail" name="issue_detail" rows="3" class="mt-1 w-full rounded-md border border-slate-200 px-3 py-2 text-sm focus:border-slate-400 focus:outline-none focus:ring-0" placeholder="{{ __('Tuliskan detail kendala yang dialami...') }}">{{ old('issue_detail') }}</textarea>
                                    @error('issue_detail')
                                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div x-data="{
                                    rows: [Date.now()],
                                    previews: {},
                                    modalOpen: false,
                                    modalSrc: null,
                                    addRow() {
                                        if (this.rows.length >= 5) return;
                                        this.rows.push(Date.now() + this.rows.length);
                                    },
                                    removeRow(index) {
                                        const rowId = this.rows[index];
                                        this.clearFile(rowId);
                                        if (this.rows.length > 1) {
                                            this.rows.splice(index, 1);
                                        }
                                    },
                                    handleFileChange(event, row) {
                                        const [file] = event.target.files;
                                        if (!file) {
                                            delete this.previews[row];
                                            return;
                                        }
                                        const reader = new FileReader();
                                        reader.onload = e => {
                                            this.$nextTick(() => {
                                                this.previews[row] = e.target.result;
                                            });
                                        };
                                        reader.readAsDataURL(file);
                                    },
                                    clearFile(row) {
                                        const refKey = 'file-' + row;
                                        if (this.$refs[refKey]) {
                                            this.$refs[refKey].value = '';
                                        }
                                        delete this.previews[row];
                                    },
                                    showPreview(src) {
                                        if (!src) return;
                                        this.modalSrc = src;
                                        this.modalOpen = true;
                                    },
                                    closePreview() {
                                        this.modalOpen = false;
                                        this.modalSrc = null;
                                    },
                                }">
                                    <label class="text-xs uppercase text-slate-400">
                                        {{ __('Foto Bukti (opsional, maks 5)') }}
                                    </label>
                                    <template x-for="(row, index) in rows" :key="row">
                                        <div class="mt-2 space-y-2">
                                            <div class="flex items-center gap-2">
                                                <input
                                                    type="file"
                                                    name="images[]"
                                                    accept="image/*"
                                                    class="flex-1 rounded-md border border-dashed border-slate-300 px-3 py-2 text-sm focus:border-slate-400 focus:outline-none focus:ring-0 file:mr-4 file:cursor-pointer file:rounded-md file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white"
                                                    x-ref="'file-' + row"
                                                    @change="handleFileChange($event, row)"
                                                >
                                                <button
                                                    type="button"
                                                    class="text-xs text-rose-500 hover:text-rose-600"
                                                    @click="removeRow(index)"
                                                >
                                                    {{ __('Hapus') }}
                                                </button>
                                            </div>
                                            <template x-if="previews[row]">
                                                <div class="relative inline-block">
                                                    <img
                                                        :src="previews[row]"
                                                        alt="{{ __('Pratinjau foto komplain') }}"
                                                        class="h-16 w-16 cursor-pointer rounded-md object-cover"
                                                        @click="showPreview(previews[row])"
                                                    >
                                                    <button
                                                        type="button"
                                                        class="absolute -right-2 -top-2 inline-flex h-5 w-5 items-center justify-center rounded-md bg-white text-xs font-semibold text-rose-600 shadow"
                                                        @click="clearFile(row)"
                                                    >
                                                        &times;
                                                    </button>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        <button
                                            type="button"
                                            class="inline-flex items-center rounded-md border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-600 hover:border-slate-300 hover:text-slate-900 disabled:pointer-events-none disabled:opacity-50"
                                            @click="addRow"
                                            :disabled="rows.length >= 5"
                                        >
                                            {{ __('Tambah Foto') }}
                                        </button>

                                        <p class="text-xs text-slate-400">
                                            {{ __('Format JPG, PNG, atau WEBP. Ukuran maksimal 10MB per foto.') }}
                                        </p>
                                    </div>
                                    @error('images')
                                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                                    @enderror
                                    @error('images.*')
                                        <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                                    @enderror

                                    <div
                                        x-cloak
                                        x-show="modalOpen"
                                        x-transition
                                        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 p-4"
                                    >
                                        <div class="relative max-h-[90vh] max-w-3xl rounded-md bg-white p-4 shadow-2xl">
                                            <button
                                                type="button"
                                                class="absolute -right-3 -top-3 inline-flex h-8 w-8 items-center justify-center rounded-md bg-white text-slate-700 shadow focus:outline-none focus-visible:ring focus-visible:ring-slate-500/50"
                                                @click="closePreview()"
                                            >
                                                &times;
                                            </button>
                                            <img
                                                :src="modalSrc"
                                                alt="{{ __('Foto Komplain') }}"
                                                class="max-h-[80vh] w-full rounded-md object-contain"
                                            >
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                    <p class="text-xs text-slate-500">{{ __('Komplain akan diteruskan ke admin pengiriman.') }}</p>
                                    <button type="submit" class="inline-flex items-center justify-center rounded-md bg-rose-500 px-4 py-2 text-xs font-semibold text-white hover:bg-rose-600">
                                        {{ __('Kirim Komplain') }}
                                    </button>
                                </div>
                            </form>
                        @elseif ($latestComplaint && $latestComplaint->status === 'pending')
                            <div class="mt-5 rounded-md border border-amber-100 bg-amber-50 px-4 py-3 text-xs text-amber-700">
                                {{ __('Komplain Anda sedang ditinjau oleh admin pengiriman.') }}
                            </div>
                        @elseif (! in_array($order->order_status, ['shipped', 'completed'], true))
                            <div class="mt-5 rounded-md border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-slate-500">
                                {{ __('Komplain dapat diajukan setelah pesanan dikirim.') }}
                            </div>
                        @endif
                    </section>
                    @if ($order->order_status === 'completed')
                        <section id="order-review-section" class="rounded-md border border-slate-200 bg-white p-6 shadow-sm">
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
                                    <article class="rounded-md border border-slate-100 bg-slate-50 p-4">
                                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                            <div>
                                                <p class="text-sm font-semibold text-slate-900">{{ $item->product_name }}</p>
                                                <p class="text-xs text-slate-500">{{ __('Jumlah: :qty', ['qty' => $item->quantity]) }}</p>
                                            </div>
                                            @if ($review)
                                                <span class="rounded-md bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-600">
                                                    {{ __('Sudah diulas') }}
                                                </span>
                                            @endif
                                        </div>
                                        <form method="POST" action="{{ $review ? route('order-items.review.update', $item) : route('order-items.review.store', $item) }}" class="mt-4 space-y-3" enctype="multipart/form-data">
                                            @csrf
                                            @if ($review)
                                                @method('PUT')
                                            @endif
                                            <input type="hidden" name="context_item" value="{{ $item->id }}">
                                            <div class="grid gap-3 sm:grid-cols-2">
                                                <div>
                                                    <label class="text-xs uppercase text-slate-400" for="rating-{{ $item->id }}">{{ __('Penilaian') }}</label>
                                                    <select name="rating" id="rating-{{ $item->id }}" class="mt-1 w-full rounded-md border border-slate-200 px-3 py-2 text-sm focus:border-slate-400 focus:outline-none focus:ring-0">
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
                                                    <input type="text" name="title" id="title-{{ $item->id }}" value="{{ $titleValue }}" class="mt-1 w-full rounded-md border border-slate-200 px-3 py-2 text-sm focus:border-slate-400 focus:outline-none focus:ring-0" placeholder="{{ __('Contoh: Pengiriman cepat') }}">
                                                    @if ($errors->has('title') && $isCurrentForm)
                                                        <p class="mt-1 text-xs text-rose-500">{{ $errors->first('title') }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                            <div>
                                                <label class="text-xs uppercase text-slate-400" for="comment-{{ $item->id }}">{{ __('Komentar') }}</label>
                                                <textarea name="comment" id="comment-{{ $item->id }}" rows="3" class="mt-1 w-full rounded-md border border-slate-200 px-3 py-2 text-sm focus:border-slate-400 focus:outline-none focus:ring-0" placeholder="{{ __('Tuliskan kesan Anda...') }}">{{ $commentValue }}</textarea>
                                                @if ($errors->has('comment') && $isCurrentForm)
                                                    <p class="mt-1 text-xs text-rose-500">{{ $errors->first('comment') }}</p>
                                                @endif
                                            </div>
                                            <div x-data="{
                                                rows: [Date.now()],
                                                previews: {},
                                                modalOpen: false,
                                                modalSrc: null,
                                                removeList: [],
                                                addRow() {
                                                    if (this.rows.length >= 5) return;
                                                    this.rows.push(Date.now() + this.rows.length);
                                                },
                                                removeRow(index) {
                                                    const rowId = this.rows[index];
                                                    this.clearFile(rowId);
                                                    if (this.rows.length > 1) {
                                                        this.rows.splice(index, 1);
                                                    }
                                                },
                                                handleFileChange(event, row) {
                                                    const [file] = event.target.files;
                                                    if (!file) {
                                                        delete this.previews[row];
                                                        return;
                                                    }
                                                    const reader = new FileReader();
                                                    reader.onload = e => {
                                                        this.$nextTick(() => {
                                                            this.previews[row] = e.target.result;
                                                        });
                                                    };
                                                    reader.readAsDataURL(file);
                                                },
                                                clearFile(row) {
                                                    const refKey = 'file-' + row;
                                                    if (this.$refs[refKey]) {
                                                        this.$refs[refKey].value = '';
                                                    }
                                                    delete this.previews[row];
                                                },
                                                showPreview(src) {
                                                    if (!src) return;
                                                    this.modalSrc = src;
                                                    this.modalOpen = true;
                                                },
                                                closePreview() {
                                                    this.modalOpen = false;
                                                    this.modalSrc = null;
                                                },
                                                toggleRemove(id) {
                                                    if (this.removeList.includes(id)) {
                                                        this.removeList = this.removeList.filter(item => item !== id);
                                                    } else {
                                                        this.removeList.push(id);
                                                    }
                                                },
                                                isRemoved(id) {
                                                    return this.removeList.includes(id);
                                                }
                                            }">
                                                <label class="text-xs uppercase text-slate-400">{{ __('Unggah Foto (maks 5)') }}</label>
                                                <template x-for="id in removeList" :key="'remove-'+id">
                                                    <input type="hidden" name="remove_images[]" :value="id">
                                                </template>
                                                <template x-for="(row, index) in rows" :key="row">
                                                    <div class="mt-2 space-y-2">
                                                        <div class="flex items-center gap-2">
                                                            <input type="file" name="images[]" accept="image/*" class="flex-1 rounded-md border border-dashed border-slate-300 px-3 py-2 text-sm focus:border-slate-400 focus:outline-none focus:ring-0 file:mr-4 file:cursor-pointer file:rounded-md file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white" x-ref="'file-' + row" @change="handleFileChange($event, row)">
                                                            <button type="button" class="text-xs text-rose-500 hover:text-rose-600" @click="removeRow(index)">
                                                                {{ __('Hapus') }}
                                                            </button>
                                                        </div>
                                                        <template x-if="previews[row]">
                                                            <div class="relative inline-block">
                                                                <img :src="previews[row]" alt="{{ __('Pratinjau foto ulasan') }}" class="h-16 w-16 cursor-pointer rounded-md object-cover" @click="showPreview(previews[row])">
                                                                <button type="button" class="absolute -right-2 -top-2 inline-flex h-5 w-5 items-center justify-center rounded-md bg-white text-xs font-semibold text-rose-600 shadow" @click="clearFile(row)">&times;</button>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </template>
                                                <div class="mt-2 flex flex-wrap gap-2">
                                                    <button type="button" class="inline-flex items-center rounded-md border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-600 hover:border-slate-300 hover:text-slate-900 disabled:pointer-events-none disabled:opacity-50" @click="addRow" :disabled="rows.length >= 5">
                                                        {{ __('Tambah Foto') }}
                                                    </button>

                                                    <p class="text-xs text-slate-400">{{ __('Format JPG, PNG, atau WEBP. Ukuran maksimal 10MB per foto.') }}</p>
                                                </div>
                                                @if ($errors->has('images') && $isCurrentForm)
                                                    <p class="mt-1 text-xs text-rose-500">{{ $errors->first('images') }}</p>
                                                @endif
                                                @if ($errors->has('images.*') && $isCurrentForm)
                                                    <p class="mt-1 text-xs text-rose-500">{{ $errors->first('images.*') }}</p>
                                                @endif
                                                @if ($review && $review->images->isNotEmpty())
                                                    <div class="mt-3 flex flex-wrap gap-2">
                                                        @foreach ($review->images as $image)
                                                            @php($imageUrl = asset('storage/'.$image->path))
                                                            <div class="relative">
                                                                <button type="button" class="block rounded-md border border-transparent focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-500" @click="showPreview('{{ $imageUrl }}')" :class="{ 'opacity-40': isRemoved({{ $image->id }}) }">
                                                                    <img src="{{ $imageUrl }}" alt="{{ $image->original_name ?: $item->product_name }}" class="h-16 w-16 rounded-lg object-cover">
                                                                </button>
                                                                <button type="button" class="absolute -right-2 -top-2 inline-flex h-5 w-5 items-center justify-center rounded-full bg-white text-xs font-semibold text-rose-600 shadow" @click="toggleRemove({{ $image->id }})">
                                                                    &times;
                                                                </button>
                                                                <p class="absolute inset-x-0 bottom-0 rounded-b-md bg-rose-600/80 px-1 text-center text-[10px] font-semibold text-white" x-show="isRemoved({{ $image->id }})">{{ __('Dihapus') }}</p>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif

                                                <div x-cloak x-show="modalOpen" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 p-4">
                                                    <div class="relative max-h-[90vh] max-w-3xl rounded-md bg-white p-4 shadow-2xl">
                                                        <button type="button" class="absolute -right-3 -top-3 inline-flex h-8 w-8 items-center justify-center rounded-md bg-white text-slate-700 shadow focus:outline-none focus-visible:ring focus-visible:ring-slate-500/50" @click="closePreview()">
                                                            &times;
                                                        </button>
                                                        <img :src="modalSrc" alt="{{ __('Foto Ulasan') }}" class="max-h-[80vh] w-full rounded-md object-contain">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                                <p class="text-xs text-slate-500">{{ __('Ulasan dipublikasikan dengan nama akun Anda.') }}</p>
                                                <button type="submit" class="inline-flex items-center justify-center rounded-md {{ $review ? 'bg-amber-500 hover:bg-amber-600' : 'bg-emerald-500 hover:bg-emerald-600' }} px-4 py-2 text-xs font-semibold text-white">
                                                    {{ $review ? __('Perbarui Ulasan') : __('Kirim Ulasan') }}
                                                </button>
                                            </div>
                                        </form>
                                    </article>
                                @endforeach
                            </div>
                        </section>
                    @endif
                    <section class="rounded-md border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-sm font-semibold text-slate-900">{{ __('Butuh Bantuan?') }}</p>
                        <p class="mt-2 text-sm text-slate-600">{{ __('Jika Anda mengalami kendala pembayaran atau pengiriman, hubungi tim kami via email support atau WhatsApp customer service.') }}</p>
                        <div class="mt-4 space-y-2">
                            <a href="mailto:{{ config('mail.from.address', 'support@example.com') }}" class="inline-flex w-full items-center justify-center rounded-md border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:border-slate-300">
                                {{ __('Email Support') }}
                            </a>
                            <a href="https://wa.me/{{ preg_replace('/\D+/', '', config('app.support_whatsapp', '628123456789')) }}" target="_blank" class="inline-flex w-full items-center justify-center rounded-md bg-emerald-500 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-600">
                                {{ __('Chat WhatsApp') }}
                            </a>
                        </div>
                    </section>
                </aside>
            </div>
        </div>
    </div>
</x-app-layout>
