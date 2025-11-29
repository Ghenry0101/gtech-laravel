@extends('layouts.app')

@section('content')
  @php
    $formatCurrency = static function ($value): string {
        $number = (float) ($value ?? 0);
        return 'Rp '.number_format($number, 0, ',', '.');
    };
    $summaryItems = (int) ($summary['items'] ?? 0);
    $summarySubtotal = (float) ($summary['subtotal'] ?? 0);
    $summaryProducts = (int) ($summary['total_products'] ?? 0);
  @endphp

  <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 py-8 font-aerospace">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <p class="text-xs uppercase tracking-wider text-slate-400">{{ __('Keranjang Saya') }}</p>
        <h1 class="text-2xl font-medium text-slate-900">{{ __('Keranjang Anda') }}</h1>
      </div>
      <a href="{{ route('home') }}" class="text-sm text-slate-500 hover:text-slate-700">{{ __('Lanjut belanja') }}</a>
    </div>

    <div class="mt-6 space-y-4">
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
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
      <section class="lg:col-span-2 space-y-4">
        @if ($cartItems->isEmpty())
          <div class="rounded-2xl border border-dashed border-slate-200 bg-white p-10 text-center">
            <p class="text-base font-semibold text-slate-700">{{ __('Keranjang masih kosong.') }}</p>
            <p class="mt-1 text-sm text-slate-500">{{ __('Cari produk menarik dan tambahkan ke keranjangmu.') }}</p>
            <a href="{{ route('home') }}" class="mt-6 inline-flex items-center justify-center rounded-lg bg-slate-900 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">
              {{ __('Cari Produk') }}
            </a>
          </div>
        @else
          <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <label class="inline-flex items-center gap-2 text-sm text-slate-600">
              <input id="checkAll" type="checkbox" class="size-4 rounded border-slate-300" checked>
              <span>{{ __('Pilih semua') }}</span>
            </label>
            <p class="text-xs uppercase tracking-wide text-slate-400">
              {{ number_format($summaryProducts) }} {{ __('produk') }}
              <span aria-hidden="true">&middot;</span>
              {{ number_format($summaryItems) }} {{ __('unit') }}
            </p>
          </div>

          <div id="cartList" class="space-y-4">
            @foreach ($cartItems as $item)
              @php
                $product = $item->product;
                $price = (float) $item->price;
                $lineTotal = $price * $item->quantity;
                $productUrl = $product ? route('products.show', $product->slug) : null;
                $imagePath = $product?->product_image ? asset('storage/'.$product->product_image) : asset('images/PC.png');
              @endphp
              <article class="bg-white border rounded-xl shadow-[0_6px_20px_-6px_rgba(0,0,0,.15)] p-4" data-id="{{ $item->id }}">
                <div class="grid grid-cols-[24px_72px_1fr_auto] items-center gap-4">
                  <input type="checkbox" class="item-check size-4 rounded border-slate-300" checked>

                  <div class="h-[72px] w-[72px] overflow-hidden rounded-lg border border-slate-200 bg-slate-50">
                    <img src="{{ $imagePath }}" alt="{{ $product?->name ?? __('Produk tidak tersedia') }}" class="h-full w-full object-cover">
                  </div>

                  <div class="min-w-0">
                    <div class="flex items-start justify-between gap-2">
                      <div class="min-w-0">
                        <p class="text-[11px] uppercase tracking-wide text-slate-400">
                          {{ $product?->category?->name ?? __('Produk') }}
                        </p>
                        <h3 class="font-extrabold text-sm md:text-base text-slate-900 truncate">
                          @if ($productUrl)
                            <a href="{{ $productUrl }}" class="hover:text-slate-700">
                              {{ $product->name }}
                            </a>
                          @else
                            {{ $product->name ?? __('Produk tidak tersedia') }}
                          @endif
                        </h3>
                      </div>

                      <form action="{{ route('cart.destroy', $item) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-slate-400 hover:text-rose-600" title="{{ __('Hapus') }}">
                          <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M9 3h6a1 1 0 0 1 1 1v1h4a1 1 0 1 1 0 2h-1v12a3 3 0 0 1-3 3H8a3 3 0 0 1-3-3V7H4a1 1 0 1 1 0-2h4V4a1 1 0 0 1 1-1Zm1 2h4V4h-4v1ZM8 7v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V7H8Zm3 3a1 1 0 0 1 1 1v7a1 1 0 1 1-2 0v-7a1 1 0 0 1 1-1Zm4 0a1 1 0 0 1 1 1v7a1 1 0 1 1-2 0v-7a1 1 0 0 1 1-1Z"/>
                          </svg>
                        </button>
                      </form>
                    </div>

                    <div class="mt-1 space-y-1 text-xs text-slate-500">
                      <span class="item-price font-semibold text-slate-900" data-price="{{ $price }}">
                        {{ $formatCurrency($price) }}
                      </span>
                      @if ($product && $product->hasDiscountActive())
                        <span class="text-[11px] font-semibold text-emerald-600">
                          {{ __('Diskon aktif hingga :date', ['date' => optional($product->discount_end)?->format('d M Y') ?? __('waktu tidak ditentukan')]) }}
                        </span>
                      @endif
                    </div>

                      <form action="{{ route('cart.update', $item) }}" method="POST" class="mt-2 flex items-center gap-2 qty-form">
                      @csrf
                      @method('PATCH')
                      <button type="button" class="qty-minus w-7 h-7 grid place-items-center rounded-md border text-slate-600 hover:bg-slate-50">−</button>
                      <input
                        type="number"
                        min="1"
                        max="{{ max($product?->stock ?? $item->quantity, 1) }}"
                        name="quantity"
                        inputmode="numeric"
                        data-quantity-lock="true"
                        class="qty-input w-14 h-7 rounded-md border text-center text-sm"
                        value="{{ (int) $item->quantity }}"
                      >
                      <button type="button" class="qty-plus w-7 h-7 grid place-items-center rounded-md border text-slate-600 hover:bg-slate-50">+</button>
                      <span class="text-[11px] text-slate-500 ml-2">{{ __('Jumlah') }}</span>
                    </form>
                  </div>

                  <div class="text-right">
                    <p class="text-sm font-extrabold text-slate-900">
                      <span class="line-total">{{ $formatCurrency($lineTotal) }}</span>
                    </p>
                    <p class="text-[11px] uppercase tracking-wide text-slate-400">Subtotal</p>
                  </div>
                </div>
              </article>
            @endforeach
          </div>
        @endif
      </section>

      <aside class="bg-white border rounded-xl shadow-[0_6px_20px_-6px_rgba(0,0,0,.15)] p-5 h-fit sticky top-20 space-y-5">
        <div>
          <h2 class="font-medium text-slate-900">{{ __('Ringkasan Keranjang') }}</h2>
          <p class="text-xs text-slate-500">{{ __('Lihat total belanja dan lanjutkan ke pembayaran.') }}</p>
        </div>

        <div class="space-y-2 text-sm text-slate-600">
          <div class="flex justify-between">
            <span>{{ __('Item dipilih') }}</span>
            <span id="selCount" class="font-extrabold text-slate-900">{{ number_format($summaryItems) }}</span>
          </div>
          <div class="flex justify-between">
            <span>{{ __('Subtotal') }}</span>
            <span id="subtotal" class="font-extrabold text-slate-900">{{ $formatCurrency($summarySubtotal) }}</span>
          </div>
          <hr class="my-2">
          <div class="flex justify-between text-lg text-slate-900">
            <span class="font-medium">{{ __('Total') }}</span>
            <span id="grandTotal" class="font-extrabold">{{ $formatCurrency($summarySubtotal) }}</span>
          </div>
        </div>

        <button id="checkoutBtn"
                data-href="{{ route('checkout.index') }}"
                class="w-full h-11 rounded-lg bg-slate-900 text-white font-medium disabled:opacity-40"
                {{ $cartItems->isEmpty() ? 'disabled' : '' }}>
          {{ __('Lanjut ke Checkout') }}
        </button>

        <a href="{{ route('orders.index') }}"
           class="w-full inline-flex justify-center h-11 items-center rounded-lg border font-medium hover:bg-slate-50">
          {{ __('Riwayat Pesanan') }}
        </a>
      </aside>
    </div>
  </div>
@endsection

@if ($cartItems->isNotEmpty())
  <script>
    function money(n) {
      const value = Number(n) || 0;
      return 'Rp ' + value.toLocaleString('id-ID');
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

    function recalc() {
      const rows = document.querySelectorAll('#cartList article');
      let subtotal = 0;
      let selectedQty = 0;

      rows.forEach(row => {
        const check = row.querySelector('.item-check');
        const price = Number(row.querySelector('.item-price')?.dataset.price || 0);
        const qtyInp = row.querySelector('.qty-input');
        const qty = Math.max(1, parseInt(qtyInp.value || '1', 10));
        const lineTotal = price * qty;
        row.querySelector('.line-total').textContent = money(lineTotal);

        if (check && check.checked) {
          subtotal += lineTotal;
          selectedQty += qty;
        }
      });

      document.getElementById('selCount').textContent = selectedQty.toLocaleString('id-ID');
      document.getElementById('subtotal').textContent = money(subtotal);
      document.getElementById('grandTotal').textContent = money(subtotal);

      const checkoutBtn = document.getElementById('checkoutBtn');
      if (checkoutBtn) {
        checkoutBtn.disabled = selectedQty === 0;
      }

      const allChecks = Array.from(document.querySelectorAll('.item-check'));
      const allChecked = allChecks.length > 0 && allChecks.every(box => box.checked);
      const checkAll = document.getElementById('checkAll');
      if (checkAll) {
        checkAll.checked = allChecked;
      }
    }

    (function attachEvents() {
      const list = document.getElementById('cartList');
      if (!list) {
        return;
      }

      const checkoutBtn = document.getElementById('checkoutBtn');
      if (checkoutBtn) {
        checkoutBtn.addEventListener('click', () => {
          if (checkoutBtn.disabled) {
            return;
          }
          const href = checkoutBtn.dataset.href;
          if (href) {
            window.location.href = href;
          }
        });
      }

      const checkAll = document.getElementById('checkAll');
      if (checkAll) {
        checkAll.addEventListener('change', event => {
          document.querySelectorAll('.item-check').forEach(box => {
            box.checked = event.target.checked;
          });
          recalc();
        });
      }

      list.addEventListener('change', event => {
        if (event.target.classList.contains('item-check')) {
          recalc();
        }
      });

      list.addEventListener('click', event => {
        const form = event.target.closest('.qty-form');
        if (!form) {
          return;
        }

        const input = form.querySelector('.qty-input');
        if (event.target.classList.contains('qty-minus')) {
          input.value = Math.max(1, parseInt(input.value || '1', 10) - 1);
          submitForm(form);
        }
        if (event.target.classList.contains('qty-plus')) {
          input.value = Math.max(1, parseInt(input.value || '1', 10) + 1);
          submitForm(form);
        }
      });

      list.addEventListener('change', event => {
        if (event.target.classList.contains('qty-input')) {
          if (event.target.value === '' || parseInt(event.target.value, 10) < 1) {
            event.target.value = 1;
          }
          submitForm(event.target.closest('form'));
        }
      });

      recalc();
    })();
  </script>
@endif
