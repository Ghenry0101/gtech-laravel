@php
    $isEdit = isset($brand);
    $logoPath = $isEdit ? $brand->logo : null;
    $logoUrl = $logoPath ? asset('storage/'.$logoPath) : null;
@endphp

<div class="space-y-6">
    <div class="space-y-1">
        <x-input-label for="name" :value="__('Nama Brand')" />
        <x-text-input
            id="name"
            name="name"
            type="text"
            class="mt-1 block w-full"
            :value="old('name', $brand->name ?? '')"
            required
            autocomplete="off"
        />
        <x-input-error class="mt-1" :messages="$errors->get('name')" />
    </div>

    <div class="space-y-2">
        <div class="flex items-center justify-between">
            <x-input-label for="logo" :value="__('Logo Brand (opsional)')" />
            <p class="text-xs text-slate-500">{{ __('PNG/JPG maksimal 2MB') }}</p>
        </div>
        <input
            id="logo"
            name="logo"
            type="file"
            accept="image/*"
            class="block w-full rounded-md border border-dashed border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-600 file:mr-4 file:rounded-md file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white focus:border-slate-500 focus:outline-none focus:ring focus:ring-slate-200"
        >
        <x-input-error class="mt-1" :messages="$errors->get('logo')" />
    </div>

    @if ($logoUrl)
        <div class="flex items-center gap-4 rounded-lg border border-slate-200 bg-slate-50 p-3">
            <img src="{{ $logoUrl }}" alt="{{ $brand->name }}" class="h-16 w-16 rounded-lg object-contain">
            <div>
                <p class="text-sm font-semibold text-slate-900">{{ __('Logo Saat Ini') }}</p>
                <p class="text-xs text-slate-500">{{ __('Unggah file baru untuk mengganti logo.') }}</p>
            </div>
        </div>
    @endif
</div>
