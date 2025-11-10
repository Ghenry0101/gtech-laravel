@php
    $isEdit = isset($product);
    $imagePath = $isEdit ? data_get($product, 'product_image') : null;
    $existingImageUrl = $imagePath ? asset('storage/' . $imagePath) : '';
    $priceValue = old('price', data_get($product ?? null, 'price', 0));
    $discountPercentValue = old('discount_percent', data_get($product ?? null, 'discount_percent'));
    $discountPriceValue = old('discount_price', data_get($product ?? null, 'discount_price'));
    $discountStartValue = old('discount_start');
    if ($discountStartValue === null) {
        $discountStartValue = optional(data_get($product ?? null, 'discount_start'))?->format('Y-m-d\TH:i');
    }
    $discountEndValue = old('discount_end');
    if ($discountEndValue === null) {
        $discountEndValue = optional(data_get($product ?? null, 'discount_end'))?->format('Y-m-d\TH:i');
    }
    $discountActiveOld = old('discount_active');
    $hasPreviousDiscount = filled($discountPercentValue) || filled($discountPriceValue);
    $discountActive = $discountActiveOld !== null ? (bool) (int) $discountActiveOld : $hasPreviousDiscount;
@endphp

<div
    x-data="{
        price: @js((string) $priceValue),
        imagePreview: @js($existingImageUrl),
        discountActive: @js($discountActive),
        discountPercent: @js($discountPercentValue),
        discountPrice: @js($discountPriceValue),
        normalizeNumber(value) {
            const number = Number.parseFloat(value);
            return Number.isFinite(number) ? number : 0;
        },
        handlePriceInput(event) {
            this.price = event.target.value;
            if (! this.discountActive) {
                return;
            }
            if (this.discountPercent !== null && this.discountPercent !== '') {
                this.syncDiscountFromPercent();
            } else if (this.discountPrice !== null && this.discountPrice !== '') {
                this.syncDiscountFromPrice();
            }
        },
        syncDiscountFromPercent() {
            if (! this.discountActive || this.discountPercent === null || this.discountPercent === '') {
                return;
            }
            const priceValue = this.normalizeNumber(this.price);
            if (priceValue === 0) {
                this.discountPrice = '0.00';
                return;
            }
            const percent = this.normalizeNumber(this.discountPercent);
            const value = priceValue - (priceValue * percent / 100);
            this.discountPrice = value >= 0 ? value.toFixed(2) : '0.00';
        },
        syncDiscountFromPrice() {
            if (! this.discountActive || this.discountPrice === null || this.discountPrice === '') {
                return;
            }
            const priceValue = this.normalizeNumber(this.price);
            const finalPrice = this.normalizeNumber(this.discountPrice);
            if (priceValue === 0) {
                this.discountPercent = null;
                return;
            }
            const percent = ((priceValue - finalPrice) / priceValue) * 100;
            this.discountPercent = percent.toFixed(2);
        },
        toggleDiscount(state) {
            this.discountActive = state;
            if (! state) {
                this.discountPercent = null;
                this.discountPrice = null;
            } else if (this.discountPercent) {
                this.syncDiscountFromPercent();
            } else if (this.discountPrice) {
                this.syncDiscountFromPrice();
            }
        },
        handleImageChange(event) {
            if (event.target.files && event.target.files[0]) {
                this.imagePreview = URL.createObjectURL(event.target.files[0]);
            } else {
                this.imagePreview = '';
            }
        }
    }"
    x-init="if (discountActive) { if (discountPercent) { syncDiscountFromPercent(); } else if (discountPrice) { syncDiscountFromPrice(); } }"
    class="space-y-8"
>
    <div class="grid gap-6 md:grid-cols-2">
        <div class="space-y-1">
            <x-input-label for="name" :value="__('Nama Produk')" />
            <x-text-input
                id="name"
                name="name"
                type="text"
                class="mt-1 block w-full"
                :value="old('name', data_get($product ?? null, 'name', ''))"
                required
                autocomplete="off"
            />
            <x-input-error class="mt-1" :messages="$errors->get('name')" />
        </div>

        <div class="space-y-1">
            <x-input-label for="category_id" :value="__('Kategori')" />
            <select
                id="category_id"
                name="category_id"
                class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring focus:ring-slate-200"
            >
                <option value="">{{ __('Tanpa Kategori') }}</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('category_id', data_get($product ?? null, 'category_id')) == $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            <x-input-error class="mt-1" :messages="$errors->get('category_id')" />
        </div>

        <div class="space-y-1 md:col-span-2">
            <x-input-label for="description" :value="__('Deskripsi')" />
            <textarea
                id="description"
                name="description"
                rows="5"
                class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring focus:ring-slate-200"
            >{{ old('description', data_get($product ?? null, 'description', '')) }}</textarea>
            <x-input-error class="mt-1" :messages="$errors->get('description')" />
        </div>

        <div class="space-y-1">
            <x-input-label for="price" :value="__('Harga (Rp)')" />
            <x-text-input
                id="price"
                name="price"
                type="number"
                min="0"
                step="0.01"
                class="mt-1 block w-full"
                :value="$priceValue"
                required
                x-model="price"
                @input="handlePriceInput($event)"
            />
            <x-input-error class="mt-1" :messages="$errors->get('price')" />
        </div>

        <div class="space-y-1">
            <x-input-label for="stock" :value="__('Stok')" />
            <x-text-input
                id="stock"
                name="stock"
                type="number"
                min="0"
                step="1"
                class="mt-1 block w-full"
                :value="old('stock', data_get($product ?? null, 'stock', 0))"
                required
            />
            <x-input-error class="mt-1" :messages="$errors->get('stock')" />
        </div>

        <div class="space-y-1">
            <x-input-label for="weight" :value="__('Berat (gram)')" />
            <x-text-input
                id="weight"
                name="weight"
                type="number"
                min="0"
                step="10"
                class="mt-1 block w-full"
                :value="old('weight', data_get($product ?? null, 'weight', 0))"
                required
            />
            <x-input-error class="mt-1" :messages="$errors->get('weight')" />
        </div>

        <div class="space-y-1">
            <x-input-label for="height" :value="__('Tinggi (cm)')" />
            <x-text-input
                id="height"
                name="height"
                type="number"
                min="0"
                step="1"
                class="mt-1 block w-full"
                :value="old('height', data_get($product ?? null, 'height', 0))"
                required
            />
            <x-input-error class="mt-1" :messages="$errors->get('height')" />
        </div>

        <div class="space-y-1">
            <x-input-label for="length" :value="__('Panjang (cm)')" />
            <x-text-input
                id="length"
                name="length"
                type="number"
                min="0"
                step="1"
                class="mt-1 block w-full"
                :value="old('length', data_get($product ?? null, 'length', 0))"
                required
            />
            <x-input-error class="mt-1" :messages="$errors->get('length')" />
        </div>

        <div class="space-y-1">
            <x-input-label for="width" :value="__('Lebar (cm)')" />
            <x-text-input
                id="width"
                name="width"
                type="number"
                min="0"
                step="1"
                class="mt-1 block w-full"
                :value="old('width', data_get($product ?? null, 'width', 0))"
                required
            />
            <x-input-error class="mt-1" :messages="$errors->get('width')" />
        </div>
    </div>

    <div class="grid gap-6 md:grid-cols-2">
        <div class="space-y-1">
            <x-input-label for="product_image" :value="__('Foto Produk')" />
            <input
                id="product_image"
                name="product_image"
                type="file"
                accept="image/*"
                class="mt-1 block w-full text-sm text-slate-600 file:mr-3 file:rounded-md file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-slate-700"
                @change="handleImageChange($event)"
                @if (! $isEdit) required @endif
            >
            <x-input-error class="mt-1" :messages="$errors->get('product_image')" />
            <p class="text-xs text-slate-500">{{ __('Format: jpg, jpeg, png, webp (maks 3MB)') }}</p>
        </div>

        <div class="space-y-2" x-show="imagePreview" x-transition>
            <x-input-label :value="__('Pratinjau Gambar')" />
            <img :src="imagePreview" alt="{{ data_get($product ?? null, 'name', __('Pratinjau Produk')) }}" class="h-40 w-40 rounded-lg border border-slate-200 object-cover">
            <p class="text-xs text-slate-500">{{ __('Gambar di atas akan tersimpan setelah formulir dikirim.') }}</p>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-slate-50 p-5">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-sm font-semibold text-slate-900">{{ __('Pengaturan Diskon') }}</p>
                <p class="text-xs text-slate-500">{{ __('Isi salah satu: persen atau harga setelah diskon.') }}</p>
            </div>
            <input type="hidden" name="discount_active" value="0">
            <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
                <input
                    id="discount_active"
                    type="checkbox"
                    name="discount_active"
                    value="1"
                    class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-500"
                    x-model="discountActive"
                    @change="toggleDiscount($event.target.checked)"
                >
                {{ __('Aktifkan Diskon') }}
            </label>
        </div>

        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <div class="space-y-1">
                <x-input-label for="discount_percent" :value="__('Diskon (%)')" />
                <x-text-input
                    id="discount_percent"
                    name="discount_percent"
                    type="number"
                    min="0"
                    max="100"
                    step="0.01"
                    class="mt-1 block w-full"
                    value="{{ $discountPercentValue }}"
                    x-model="discountPercent"
                    x-bind:disabled="!discountActive"
                    @input="syncDiscountFromPercent()"
                />
                <x-input-error class="mt-1" :messages="$errors->get('discount_percent')" />
            </div>
            <div class="space-y-1">
                <x-input-label for="discount_price" :value="__('Harga Setelah Diskon (Rp)')" />
                <x-text-input
                    id="discount_price"
                    name="discount_price"
                    type="number"
                    min="0"
                    step="0.01"
                    class="mt-1 block w-full"
                    value="{{ $discountPriceValue }}"
                    x-model="discountPrice"
                    x-bind:disabled="!discountActive"
                    @input="syncDiscountFromPrice()"
                />
                <x-input-error class="mt-1" :messages="$errors->get('discount_price')" />
            </div>
        </div>

        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <div class="space-y-1">
                <x-input-label for="discount_start" :value="__('Mulai Diskon')" />
                <x-text-input
                    id="discount_start"
                    name="discount_start"
                    type="datetime-local"
                    value="{{ $discountStartValue }}"
                    class="mt-1 block w-full"
                    x-bind:disabled="!discountActive"
                />
                <x-input-error class="mt-1" :messages="$errors->get('discount_start')" />
            </div>
            <div class="space-y-1">
                <x-input-label for="discount_end" :value="__('Selesai Diskon')" />
                <x-text-input
                    id="discount_end"
                    name="discount_end"
                    type="datetime-local"
                    value="{{ $discountEndValue }}"
                    class="mt-1 block w-full"
                    x-bind:disabled="!discountActive"
                />
                <x-input-error class="mt-1" :messages="$errors->get('discount_end')" />
            </div>
        </div>
    </div>

    <div class="flex items-center justify-between border-t border-slate-200 pt-4">
        <label for="is_active" class="flex items-center gap-3 text-sm font-medium text-slate-700">
            <input type="hidden" name="is_active" value="0">
            <input
                id="is_active"
                type="checkbox"
                name="is_active"
                value="1"
                class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-500"
                @checked(old('is_active', isset($product) ? (bool) $product->is_active : true))
            >
            {{ __('Produk Aktif & Dapat Dijual') }}
        </label>

        <x-primary-button type="submit">
            {{ $isEdit ? __('Perbarui Produk') : __('Simpan Produk') }}
        </x-primary-button>
    </div>
</div>
