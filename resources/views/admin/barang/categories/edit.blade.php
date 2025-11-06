<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-slate-900">{{ __('Edit Kategori') }}</h2>
                <p class="text-sm text-slate-500">{{ $category->name }}</p>
            </div>
            <a href="{{ route('admin.barang.categories.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:text-slate-900">
                <svg class="h-4 w-4" viewBox="0 0 24 24" stroke="currentColor" fill="none" stroke-width="2">
                    <path d="M15 18l-6-6 6-6" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                {{ __('Kembali') }}
            </a>
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

                <form method="POST" action="{{ route('admin.barang.categories.update', $category->id) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="space-y-1">
                        <x-input-label for="name" :value="__('Nama Kategori')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $category->name)" required autocomplete="off" />
                        <x-input-error class="mt-1" :messages="$errors->get('name')" />
                    </div>

                    <div class="space-y-1">
                        <x-input-label for="description" :value="__('Deskripsi (opsional)')" />
                        <textarea id="description" name="description" rows="4" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring focus:ring-slate-200">{{ old('description', $category->description) }}</textarea>
                        <x-input-error class="mt-1" :messages="$errors->get('description')" />
                    </div>

                    <label class="flex items-center gap-3 text-sm font-medium text-slate-700">
                        <input type="hidden" name="is_active" value="0">
                        <input id="is_active" type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-500" @checked(old('is_active', (bool) $category->is_active))>
                        {{ __('Kategori Aktif') }}
                    </label>

                    <div class="flex items-center justify-between border-t border-slate-200 pt-4">
                        <a href="{{ route('admin.barang.categories.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">
                            {{ __('Batal') }}
                        </a>
                        <x-primary-button type="submit">
                            {{ __('Perbarui Kategori') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
