<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-slate-900">{{ __('Kategori Produk') }}</h2>
                <p class="text-sm text-slate-500">{{ __('Kelola kategori yang dapat dipilih oleh produk.') }}</p>
            </div>
            <a href="{{ route('admin.barang.categories.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-700">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 5v14M5 12h14" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                {{ __('Tambah Kategori') }}
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-5xl space-y-6 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-sm">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->has('category'))
                <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 shadow-sm">
                    {{ $errors->first('category') }}
                </div>
            @endif

            <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col gap-4 border-b border-slate-200 px-5 py-5 sm:flex-row sm:items-center sm:justify-between">
                    <form method="GET" class="w-full sm:w-auto">
                        <label for="search" class="sr-only">{{ __('Cari kategori') }}</label>
                        <div class="relative">
                            <input id="search" name="search" type="search" value="{{ $search }}" placeholder="{{ __('Cari kategori...') }}" class="w-full rounded-lg border border-slate-200 bg-white py-2 pl-10 pr-4 text-sm text-slate-700 shadow-sm placeholder:text-slate-400 focus:border-slate-500 focus:outline-none focus:ring focus:ring-slate-500/20 sm:w-64" />
                            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 20 20" fill="none">
                                <path d="M9 3a6 6 0 104 10.74l3.13 3.13a1 1 0 01-1.42 1.42L11.6 15.2A6 6 0 009 3z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                    </form>

                    <div class="text-xs font-medium uppercase tracking-wide text-slate-400">
                        {{ __('Total: :count kategori', ['count' => $categories->total()]) }}
                    </div>
                </div>

                <div class="divide-y divide-slate-200">
                    @forelse ($categories as $category)
                        <div class="flex flex-col gap-4 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ $category->name }}</p>
                                <p class="text-xs uppercase tracking-wide text-slate-400">{{ $category->slug }}</p>
                                @if ($category->description)
                                    <p class="mt-1 text-sm text-slate-500">{{ $category->description }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-3">
                                @if ($category->is_active)
                                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                                        {{ __('Aktif') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-slate-200 px-3 py-1 text-xs font-semibold text-slate-600">
                                        {{ __('Nonaktif') }}
                                    </span>
                                @endif
                                <a href="{{ route('admin.barang.categories.edit', $category->id) }}" class="inline-flex items-center rounded-md border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-slate-400 hover:text-slate-900">
                                    {{ __('Edit') }}
                                </a>
                                <form method="POST" action="{{ route('admin.barang.categories.destroy', $category->id) }}" onsubmit="return confirm('{{ __('Hapus kategori ini?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center rounded-md bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-rose-500">
                                        {{ __('Hapus') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-10 text-center text-sm text-slate-500">
                            {{ __('Belum ada kategori.') }}
                        </div>
                    @endforelse
                </div>

                <div class="flex items-center justify-between border-t border-slate-200 px-5 py-4 text-sm text-slate-500">
                    <div>{{ $categories->firstItem() }}-{{ $categories->lastItem() }} {{ __('dari') }} {{ $categories->total() }}</div>
                    {{ $categories->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
