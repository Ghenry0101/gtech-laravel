@include('partials.header')

<main class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 py-10 font-aerospace">
  <h1 class="text-2xl font-extrabold mb-6">Riwayat Pesanan</h1>

  @if(empty($orders))
    <div class="rounded-xl border bg-white p-6 text-slate-500">
      Belum ada pesanan. Silakan checkout terlebih dahulu.
    </div>
  @else
    <div class="space-y-4">
      @foreach($orders as $order)
        <article class="rounded-xl border bg-white p-4">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="font-extrabold">Order #{{ $order['id'] }}</h3>
              <p class="text-sm text-slate-500">{{ $order['date'] ?? '' }}</p>
            </div>
            <div class="text-right">
              <p class="font-extrabold">${{ number_format($order['total'] ?? 0, 2) }}</p>
            </div>
          </div>
          @if(!empty($order['items']))
            <ul class="mt-3 text-sm text-slate-700 list-disc pl-5">
              @foreach($order['items'] as $it)
                <li>{{ $it['title'] }} × {{ $it['qty'] }} — ${{ number_format($it['price'], 2) }}</li>
              @endforeach
            </ul>
          @endif
        </article>
      @endforeach
    </div>
  @endif
</main>

@include('partials.footer')
