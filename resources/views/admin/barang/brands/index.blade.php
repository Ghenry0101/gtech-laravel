<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-slate-900">{{ __('Manajemen Brand') }}</h2>
                <p class="text-sm text-slate-500">{{ __('Gunakan brand untuk mengelompokkan dan menonjolkan identitas produk.') }}</p>
            </div>
            <a href="{{ route('admin.barang.brands.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-700">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 5v14M5 12h14" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                {{ __('Tambah Brand') }}
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-6xl space-y-8 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-sm">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->has('brand'))
                <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 shadow-sm">
                    {{ $errors->first('brand') }}
                </div>
            @endif

            <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col gap-4 border-b border-slate-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                    <form method="GET" class="w-full sm:w-auto">
                        <label for="search" class="sr-only">{{ __('Cari brand') }}</label>
                        <div class="relative">
                            <input id="search" name="search" type="search" value="{{ $search }}" placeholder="{{ __('Cari nama atau slug brand...') }}" class="w-full rounded-lg border border-slate-200 bg-white py-2 pl-10 pr-4 text-sm text-slate-700 shadow-sm placeholder:text-slate-400 focus:border-slate-500 focus:outline-none focus:ring focus:ring-slate-500/20 sm:w-72" />
                            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 20 20" fill="none">
                                <path d="M9 3a6 6 0 104 10.74l3.13 3.13a1 1 0 01-1.42 1.42L11.6 15.2A6 6 0 009 3z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                    </form>

                    <div class="text-xs font-medium uppercase tracking-wide text-slate-400">
                        {{ __('Total: :count brand', ['count' => $brands->total()]) }}
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
                            <tr>
                                <th class="px-6 py-3">{{ __('Brand') }}</th>
                                <th class="px-6 py-3">{{ __('Produk Terpasang') }}</th>
                                <th class="px-6 py-3">{{ __('Dibuat') }}</th>
                                <th class="px-6 py-3 text-right">{{ __('Aksi') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @forelse ($brands as $brand)
                                <tr class="text-slate-700">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="h-12 w-12 overflow-hidden rounded-xl border border-slate-200 bg-slate-50">
                                                @if ($brand->logo)
                                                    <img src="{{ asset('storage/'.$brand->logo) }}" alt="{{ $brand->name }}" class="h-full w-full object-contain p-2">
                                                @else
                                                    <div class="flex h-full w-full items-center justify-center text-[11px] uppercase tracking-wide text-slate-400">
                                                        {{ __('Logo') }}
                                                    </div>
                                                @endif
                                            </div>
                                            <div>
                                                <p class="font-semibold text-slate-900">{{ $brand->name }}</p>
                                                <p class="text-xs uppercase tracking-wide text-slate-400">{{ $brand->slug }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                                            {{ number_format($brand->products_count) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-500">
                                        {{ optional($brand->created_at)->format('d M Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('admin.barang.brands.edit', $brand) }}" class="inline-flex items-center rounded-md border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-slate-400 hover:text-slate-900">
                                                {{ __('Edit') }}
                                            </a>
                                            <form method="POST" action="{{ route('admin.barang.brands.destroy', $brand) }}" onsubmit="return confirm('{{ __('Hapus brand ini?') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center rounded-md bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-rose-500">
                                                    {{ __('Hapus') }}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-10 text-center text-sm text-slate-500">
                                        {{ __('Belum ada brand yang terdaftar.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="flex items-center justify-between border-t border-slate-200 px-6 py-4 text-sm text-slate-500">
                    <div>{{ $brands->firstItem() }}-{{ $brands->lastItem() }} {{ __('dari') }} {{ $brands->total() }}</div>
                    {{ $brands->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
