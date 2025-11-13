@extends('layouts.app')

@section('content')
@php
  $ordersCol      = collect($orders ?? []);
  $pendingCount   = $ordersCol->where('status', 'pending')->count();
  $completeCount  = $ordersCol->where('status', 'complete')->count();
  $totalProducts  = $ordersCol->flatMap(fn($o) => $o['items'] ?? [])->sum('qty');

  function money_fmt($n){ return '$'.number_format((float)$n, 2); }
@endphp

<div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 py-8 font-aerospace">

  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="rounded-2xl border bg-white shadow-[0_6px_20px_-6px_rgba(0,0,0,.15)] p-6 text-center">
      <p class="text-sm tracking-wider text-slate-500">PENDING</p>
      <p class="mt-2 text-2xl font-extrabold">{{ $pendingCount }}</p>
    </div>
    <div class="rounded-2xl border bg-white shadow-[0_6px_20px_-6px_rgba(0,0,0,.15)] p-6 text-center">
      <p class="text-sm tracking-wider text-slate-500">COMPLETE</p>
      <p class="mt-2 text-2xl font-extrabold">{{ $completeCount }}</p>
    </div>
    <div class="rounded-2xl border bg-white shadow-[0_6px_20px_-6px_rgba(0,0,0,.15)] p-6 text-center">
      <p class="text-sm tracking-wider text-slate-500">TOTAL PRODUCT</p>
      <p class="mt-2 text-2xl font-extrabold">{{ $totalProducts }}</p>
    </div>
  </div>

  @if($ordersCol->isEmpty())
    <div class="rounded-xl border bg-white p-6 text-slate-500">
      Belum ada pesanan. Silakan checkout terlebih dahulu.
    </div>
  @else
    <div class="space-y-4">
      @foreach($orders as $order)
        {{-- satu order --}}
        <article class="rounded-xl border bg-white shadow-[0_6px_20px_-6px_rgba(0,0,0,.15)]">
          @if(!empty($order['items']))
            @foreach($order['items'] as $it)
              @php
                $slug = $it['slug'] ?? \Illuminate\Support\Str::slug($it['title'] ?? '');
                $reviewed = in_array($slug, $reviewedSlugs ?? []);
              @endphp

              <div class="px-5 py-4 border-b last:border-0">
                <div class="grid grid-cols-[88px_1fr_auto] items-center gap-4">
                  {{-- gambar --}}
                  <img src="{{ asset($it['image'] ?? 'images/PC.png') }}"
                       alt="{{ $it['title'] ?? '' }}"
                       class="w-20 h-20 object-contain">

                  <div class="min-w-0">
                    <div class="flex items-center justify-between gap-2">
                      <h3 class="font-extrabold truncate">
                        {{ strtoupper($it['title'] ?? '-') }}
                      </h3>
                      <p class="text-sm font-extrabold ml-2">
                        {{ money_fmt($it['price'] ?? 0) }}
                      </p>
                    </div>

                    <div class="mt-2 flex items-center gap-3 text-slate-700">
                      <form action="#" method="POST" class="flex items-center gap-2">
                        @csrf
                        <button type="button"
                                class="w-7 h-7 grid place-items-center rounded-md border hover:bg-slate-50">−</button>
                        <input type="number" class="w-14 h-7 rounded-md border text-center"
                               value="{{ (int)($it['qty'] ?? 1) }}" min="1" readonly>
                        <button type="button"
                                class="w-7 h-7 grid place-items-center rounded-md border hover:bg-slate-50">+</button>
                      </form>

                      <button type="button" title="Hapus"
                              class="ml-2 text-slate-500 hover:text-red-600">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                          <path d="M9 3h6a1 1 0 0 1 1 1v1h4a1 1 0 1 1 0 2h-1v12a3 3 0 0 1-3 3H8a3 3 0 0 1-3-3V7H4a1 1 0 1 1 0-2h4V4a1 1 0 0 1 1-1Zm1 2h4V4h-4v1ZM8 7v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V7H8Zm3 3a1 1 0 0 1 1 1v7a1 1 0 1 1-2 0v-7a1 1 0 0 1 1-1Zm4 0a1 1 0 0 1 1 1v7a1 1 0 1 1-2 0v-7a1 1 0 0 1 1-1Z"/>
                        </svg>
                      </button>

                      @if(!empty($order['status']))
                        <span class="ml-auto text-xs px-2 py-1 rounded-md border
                                     {{ $order['status']=='complete' ? 'bg-green-50 border-green-200 text-green-700' : 'bg-yellow-50 border-yellow-200 text-yellow-700' }}">
                          {{ strtoupper($order['status']) }}
                        </span>
                      @endif
                    </div>
                  </div>

                  <div class="text-right">
                    @if(($order['status'] ?? '') === 'complete')
                      @if(!$reviewed)
                        <a href="{{ route('reviews.create', ['order' => $order['id'], 'product' => $slug]) }}"
                           class="inline-flex items-center h-9 px-3 rounded-lg border font-extrabold hover:bg-slate-50">
                          ULAS
                        </a>
                      @else
                        <span class="text-xs text-slate-500">Sudah diulas</span>
                      @endif
                    @endif
                  </div>
                </div>
              </div>
            @endforeach
          @endif
        </article>
      @endforeach
    </div>
  @endif
</div>
@endsection
