<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-slate-900">{{ __('Tambah Brand Baru') }}</h2>
            <p class="text-sm text-slate-500">{{ __('Brand membantu pelanggan mengenal produk Anda.') }}</p>
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

                <form method="POST" action="{{ route('admin.barang.brands.store') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    @include('admin.barang.brands.partials.form', ['brand' => null])

                    <div class="flex items-center justify-between border-t border-slate-200 pt-4">
                        <a href="{{ route('admin.barang.brands.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">
                            {{ __('Batal') }}
                        </a>
                        <x-primary-button type="submit">
                            {{ __('Simpan Brand') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
