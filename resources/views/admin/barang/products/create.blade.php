<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-slate-900">{{ __('Tambah Produk Baru') }}</h2>
            <p class="text-sm text-slate-500">{{ __('Lengkapi informasi produk untuk pelanggan, logistik, dan pembayaran.') }}</p>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                @if ($errors->any())
                    <div class="mb-6 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        <ul class="list-disc space-y-1 pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.barang.products.store') }}" enctype="multipart/form-data" class="space-y-8">
                    @csrf

                    @include('admin.barang.products.partials.form', ['product' => null, 'categories' => $categories])
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
