@php
    $items = collect($items ?? [])->map(fn ($item) => is_array($item) ? $item : (array) $item)->filter();
@endphp

@if ($items->isEmpty())
    <div class="text-center text-sm text-gray-400 mt-4">Belum ada produk.</div>
@else
    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach ($items as $p)
            @php
                $title = \Illuminate\Support\Arr::get($p, 'title', '-');
                $status = strtolower(\Illuminate\Support\Arr::get($p, 'status', 'available'));
                $stock = \Illuminate\Support\Arr::get($p, 'stock', 0);
                $price = (float) \Illuminate\Support\Arr::get($p, 'price', 0);
                $originalPrice = \Illuminate\Support\Arr::get($p, 'original_price');
                $plannedPrice = \Illuminate\Support\Arr::get($p, 'planned_price', $originalPrice);
                $hasDiscount = (bool) \Illuminate\Support\Arr::get($p, 'has_discount', false);
                $isDiscountActive = (bool) \Illuminate\Support\Arr::get($p, 'is_discount_active', $hasDiscount);
                $discountPercent = \Illuminate\Support\Arr::get($p, 'discount_percent');
                $discountStart = \Illuminate\Support\Arr::get($p, 'discount_start');
                $discountEnd = \Illuminate\Support\Arr::get($p, 'discount_end');
                $primaryPrice = $hasDiscount && $plannedPrice ? (float) $plannedPrice : $price;
                $slashPrice = $hasDiscount ? ($originalPrice ?: ($isDiscountActive ? $price : null)) : null;
                $imagePath = \Illuminate\Support\Arr::get($p, 'image_path') ?? \Illuminate\Support\Arr::get($p, 'image') ?? 'images/PC.png';
                $slug = \Illuminate\Support\Arr::get($p, 'slug');

                $isAbsolute = str_starts_with($imagePath, 'http://') || str_starts_with($imagePath, 'https://');
                $imageUrl = $isAbsolute ? $imagePath : asset($imagePath);
                $detailUrl = $slug ? route('products.show', $slug) : '#';
            @endphp

            <div class="rounded-2xl ring-1 ring-black/10 shadow-[0_6px_20px_-6px_rgba(0,0,0,.15)] bg-white overflow-hidden flex flex-col">
                <div class="relative h-48 bg-slate-100">
                    <img
                        src="{{ $imageUrl }}"
                        alt="{{ $title }}"
                        class="w-full h-full object-cover"
                        onerror="this.onerror=null;this.src='{{ asset('images/PC.png') }}'">
                    @if ($hasDiscount)
                        <span class="absolute left-3 top-3 rounded-full bg-emerald-500/90 px-3 py-1 text-[11px] font-semibold uppercase tracking-wide text-white">
                            {{ __('Diskon') }}
                        </span>
                    @endif
                </div>

                <div class="p-4 flex-1 flex flex-col gap-3">
                    <div>
                        <h4 class="font-extrabold text-slate-900 line-clamp-2">{{ $title }}</h4>
                        <p class="mt-1 text-xs font-bold {{ $status === 'available' ? 'text-green-600' : 'text-red-600' }}">
                            {{ $status === 'available' ? __('Tersedia') : __('Stok kosong') }}
                            @if ($status === 'available')
                                <span class="text-[11px] text-gray-400 font-normal">({{ __('Stok: :stock', ['stock' => number_format($stock)]) }})</span>
                            @endif
                        </p>
                    </div>

                    <div>
                        <p class="text-lg font-black text-slate-900">
                            Rp {{ number_format($primaryPrice, 0, ',', '.') }}
                            @if ($hasDiscount)
                                <span class="ml-2 text-[11px] font-semibold {{ $isDiscountActive ? 'text-emerald-600' : 'text-amber-600' }}">
                                    {{ $isDiscountActive ? __('Promo aktif') : __('Promo segera') }}
                                </span>
                            @endif
                        </p>
                        @if ($slashPrice && $slashPrice > $primaryPrice)
                            <p class="text-xs text-slate-400 line-through">
                                Rp {{ number_format($slashPrice, 0, ',', '.') }}
                            </p>
                        @endif
                        @if ($hasDiscount)
                            <div class="space-y-1 text-[11px]">
                                <p class="text-[11px] font-semibold text-emerald-600">
                                    {{ __('Diskon :percent%', ['percent' => number_format($discountPercent ?? (($originalPrice ?: $price) && $plannedPrice ? (($originalPrice ?: $price) - $plannedPrice) / max($originalPrice ?: $price, 1) * 100 : 0), 2)]) }}
                                </p>
                                @if (! $isDiscountActive && $plannedPrice)
                                    <p class="text-emerald-700">
                                        {{ __('Harga promo: Rp :price', ['price' => number_format($plannedPrice, 0, ',', '.')]) }}
                                    </p>
                                @endif
                                <p class="text-[11px] text-slate-400">
                                    {{ __('Periode: :start - :end', [
                                        'start' => $discountStart ?? __('sekarang'),
                                        'end' => $discountEnd ?? __('tanpa batas')
                                    ]) }}
                                </p>
                            </div>
                        @endif
                    </div>

                    <a
                        href="{{ $detailUrl }}"
                        class="mt-auto inline-flex w-full items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-xs font-extrabold uppercase tracking-wide text-white hover:bg-slate-800 {{ $slug ? '' : 'opacity-50 pointer-events-none' }}"
                    >
                        {{ __('Lihat Produk') }}
                    </a>
                </div>
            </div>
        @endforeach
    </div>
@endif
