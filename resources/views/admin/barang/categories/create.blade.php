<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-slate-900">{{ __('Tambah Kategori') }}</h2>
            <p class="text-sm text-slate-500">{{ __('Kategori digunakan oleh Admin Barang untuk mengelola pengelompokan produk.') }}</p>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                @if ($errors->any())
                    <div class="mb-6 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 shadow-sm">
                        <ul class="list-disc space-y-1 pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.barang.categories.store') }}" class="space-y-6">
                    @csrf

                    <div class="space-y-1">
                        <x-input-label for="name" :value="__('Nama Kategori')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autocomplete="off" />
                        <x-input-error class="mt-1" :messages="$errors->get('name')" />
                    </div>

                    <div class="space-y-1">
                        <x-input-label for="description" :value="__('Deskripsi (opsional)')" />
                        <textarea id="description" name="description" rows="4" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring focus:ring-slate-200">{{ old('description') }}</textarea>
                        <x-input-error class="mt-1" :messages="$errors->get('description')" />
                    </div>

                    <div class="flex items-center justify-between border-t border-slate-200 pt-4">
                        <a href="{{ route('admin.barang.categories.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">
                            {{ __('Batal') }}
                        </a>
                        <x-primary-button type="submit">
                            {{ __('Simpan Kategori') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
