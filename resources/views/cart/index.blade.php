@extends('layouts.app')

@section('content')
  <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 py-8 font-aerospace">
    <h1 class="text-2xl font-medium mb-4">our cart</h1>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <section class="lg:col-span-2">
      @if (empty($items))
        <div class="rounded-xl border bg-white p-6 text-center text-slate-500">
          Keranjangmu kosong.
        </div>
      @else
        <div class="mb-3 flex items-center justify-between">
          <label class="inline-flex items-center gap-2 text-sm">
            <input id="checkAll" type="checkbox" class="size-4 rounded border-slate-300" checked>
            <span>Pilih semua</span>
          </label>
        </div>

        <div id="cartList" class="space-y-4">
          @foreach ($items as $item)
            <article class="bg-white border rounded-xl shadow-[0_6px_20px_-6px_rgba(0,0,0,.15)] p-3" data-id="{{ $item['id'] }}">
              <div class="grid grid-cols-[24px_72px_1fr_auto] items-center gap-3">
                
                <input type="checkbox" class="item-check size-4 rounded border-slate-300" checked>

                <img src="{{ $item['image'] ?? asset('images/PC.png') }}" alt="{{ $item['title'] }}"
                     class="w-[72px] h-[72px] object-contain">

                <div class="min-w-0">
                  <div class="flex items-start justify-between gap-2">
                    <h3 class="font-extrabold text-sm md:text-base truncate">
                      {{ $item['title'] }}
                    </h3>

                    <form action="{{ route('cart.remove', $item['id']) }}" method="POST">
                      @csrf
                      @method('DELETE')
                      <button class="text-slate-500 hover:text-red-600" title="Hapus">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                          <path d="M9 3h6a1 1 0 0 1 1 1v1h4a1 1 0 1 1 0 2h-1v12a3 3 0 0 1-3 3H8a3 3 0 0 1-3-3V7H4a1 1 0 1 1 0-2h4V4a1 1 0 0 1 1-1Zm1 2v0h4V4h-4v1ZM8 7v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V7H8Zm3 3a1 1 0 0 1 1 1v7a1 1 0 1 1-2 0v-7a1 1 0 0 1 1-1Zm4 0a1 1 0 0 1 1 1v7a1 1 0 1 1-2 0v-7a1 1 0 0 1 1-1Z"/>
                        </svg>
                      </button>
                    </form>
                  </div>

                  <div class="mt-1 text-xs text-slate-500">
                    <span class="item-price" data-price="{{ (float)$item['price'] }}">
                      ${{ number_format((float)$item['price'], 2) }}
                    </span>
                  </div>

                  <form action="{{ route('cart.update', $item['id']) }}" method="POST" class="mt-2 flex items-center gap-2 qty-form">
                    @csrf
                    <button type="button" class="qty-minus w-7 h-7 grid place-items-center rounded-md border hover:bg-slate-50">−</button>
                    <input type="number" min="1" name="qty"
                           class="qty-input w-14 h-7 rounded-md border text-center"
                           value="{{ (int)($item['qty'] ?? 1) }}">
                    <button type="button" class="qty-plus w-7 h-7 grid place-items-center rounded-md border hover:bg-slate-50">+</button>
                    <span class="text-[11px] text-slate-500 ml-2">QTY</span>
                  </form>
                </div>

                <div class="text-right">
                  <p class="text-sm font-extrabold">
                    <span class="line-total">$0.00</span>
                  </p>
                </div>
              </div>
            </article>
          @endforeach
        </div>
      @endif
    </section>

    <aside class="bg-white border rounded-xl shadow-[0_6px_20px_-6px_rgba(0,0,0,.15)] p-4 h-fit sticky top-20">
      <h2 class="font-medium mb-4">OUR CART</h2>

      <div class="space-y-2 text-sm">
        <div class="flex justify-between">
          <span>Item dipilih</span>
          <span id="selCount" class="font-extrabold">0</span>
        </div>
        <div class="flex justify-between">
          <span>Subtotal</span>
          <span id="subtotal" class="font-extrabold">$0.00</span>
        </div>
        <div class="flex justify-between text-slate-500">
          <span>Estimasi ongkir</span>
          <span id="shipping">$0.00</span>
        </div>
        <hr class="my-2">
        <div class="flex justify-between text-lg">
          <span class="font-medium">Total</span>
          <span id="grandTotal" class="font-extrabold">$0.00</span>
        </div>
      </div>

      <button id="checkoutBtn"
              class="w-full mt-5 h-11 rounded-lg bg-slate-900 text-white font-medium disabled:opacity-40"
              disabled>checkout</button>

      <a href="{{ route('orders.index') }}"
         class="w-full mt-2 inline-flex justify-center h-11 items-center rounded-lg border font-medium hover:bg-slate-50">
        Riwayat
      </a>
    </aside>
        </div>
    </main>
</div>
@endsection


@if (!empty($cart))
<script>
function money(n){ return '$' + (n).toFixed(2); }

function recalc() {
  const rows = document.querySelectorAll('#cartList article');
  let subtotal = 0, selectedQty = 0;

  rows.forEach(row => {
    const check = row.querySelector('.item-check');
    const price = parseFloat(row.querySelector('.item-price')?.dataset.price || '0');
    const qtyInp = row.querySelector('.qty-input');
    const qty = Math.max(1, parseInt(qtyInp.value || '1', 10));
    const lineTotal = price * qty;
    row.querySelector('.line-total').textContent = money(lineTotal);

    if (check && check.checked) {
      subtotal += lineTotal;
      selectedQty += qty;
    }
  });

  document.getElementById('selCount').textContent = selectedQty;
  document.getElementById('subtotal').textContent = money(subtotal);
  const shipping = selectedQty > 0 ? 15.00 : 0;
  document.getElementById('shipping').textContent = money(shipping);
  document.getElementById('grandTotal').textContent = money(subtotal + shipping);
  document.getElementById('checkoutBtn').disabled = selectedQty === 0;

  const allChecks = Array.from(document.querySelectorAll('.item-check'));
  const allChecked = allChecks.length && allChecks.every(c => c.checked);
  const checkAll = document.getElementById('checkAll');
  if (checkAll) checkAll.checked = allChecked;
}

(function attachEvents(){
  const list = document.getElementById('cartList');
  if (!list) return;

  const ca = document.getElementById('checkAll');
  if (ca) ca.addEventListener('change', (e)=>{
    document.querySelectorAll('.item-check').forEach(c => c.checked = e.target.checked);
    recalc();
  });

  list.addEventListener('change', (e)=>{
    if (e.target.classList.contains('item-check')) recalc();
  });

  list.addEventListener('click', (e)=>{
    const form = e.target.closest('.qty-form');
    if (!form) return;

    const input = form.querySelector('.qty-input');
    if (e.target.classList.contains('qty-minus')) {
      input.value = Math.max(1, parseInt(input.value || '1', 10) - 1);
      form.submit(); // kirim ke /cart/update/{id}
    }
    if (e.target.classList.contains('qty-plus')) {
      input.value = Math.max(1, parseInt(input.value || '1', 10) + 1);
      form.submit();
    }
  });

  list.addEventListener('change', (e)=>{
    if (e.target.classList.contains('qty-input')) {
      if (e.target.value === '' || parseInt(e.target.value,10) < 1) e.target.value = 1;
      e.target.closest('form')?.submit();
    }
  });

  recalc();
})();
</script>
@endif
