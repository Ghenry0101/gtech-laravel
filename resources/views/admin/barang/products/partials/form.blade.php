@php
    $isEdit = isset($product);
    $existingImageUrl = $isEdit && data_get($product ?? null, 'image_product')
        ? asset('storage/' . data_get($product, 'image_product'))
        : '';
    $priceValue = old('price', data_get($product ?? null, 'price', 0));
    $initialDiscountType = data_get($product ?? null, 'discount_type');
    $discountEnabled = (bool) old('enable_discount', $initialDiscountType ? 1 : 0);
    $discountSource = old('discount_source', $initialDiscountType);
    $discountPercentage = old('discount_percentage', data_get($product ?? null, 'discount_percentage'));
    $discountAmount = old('discount_amount', data_get($product ?? null, 'discount_amount'));
@endphp

<div
    x-data="productDiscountState({
        price: @js($priceValue),
        enabled: @js($discountEnabled),
        source: @js($discountSource),
        percentage: @js($discountPercentage),
        amount: @js($discountAmount),
    })"
    x-init="syncDiscounts()"
    class="space-y-8"
>
    <div class="grid gap-6 md:grid-cols-2">
        <div class="space-y-1">
            <x-input-label for="name" :value="__('Nama Produk')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', data_get($product ?? null, 'name', ''))" required autocomplete="off" />
            <x-input-error class="mt-1" :messages="$errors->get('name')" />
        </div>

        <div class="space-y-1">
            <x-input-label for="category_id" :value="__('Kategori')" />
            <select id="category_id" name="category_id" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring focus:ring-slate-200">
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
            <textarea id="description" name="description" rows="5" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring focus:ring-slate-200" required>{{ old('description', data_get($product ?? null, 'description', '')) }}</textarea>
            <x-input-error class="mt-1" :messages="$errors->get('description')" />
        </div>

        <div class="space-y-1">
            <x-input-label for="price" :value="__('Harga (Rp)')" />
            <x-text-input
                id="price"
                name="price"
                type="number"
                min="0"
                step="1000"
                class="mt-1 block w-full"
                :value="$priceValue"
                required
                x-on:input="handlePriceInput($event)"
            />
            <x-input-error class="mt-1" :messages="$errors->get('price')" />
        </div>

        <div class="space-y-1">
            <x-input-label for="stock" :value="__('Stok')" />
            <x-text-input id="stock" name="stock" type="number" min="0" step="1" class="mt-1 block w-full" :value="old('stock', data_get($product ?? null, 'stock', 0))" required />
            <x-input-error class="mt-1" :messages="$errors->get('stock')" />
        </div>

        <input type="hidden" name="discount_source" x-model="discountSource">

        <div class="space-y-4 md:col-span-2">
            <label class="flex items-center gap-3 text-sm font-medium text-slate-700">
                <input
                    id="enable_discount"
                    type="checkbox"
                    name="enable_discount"
                    value="1"
                    class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-500"
                    x-model="discountEnabled"
                    @change="toggleDiscount()"
                >
                {{ __('Aktifkan Diskon Produk') }}
            </label>

            <div class="grid gap-4 md:grid-cols-3">
                <div class="space-y-1">
                    <x-input-label for="discount_percentage" :value="__('Diskon (%)')" />
                    <x-text-input
                        id="discount_percentage"
                        name="discount_percentage"
                        type="number"
                        min="0"
                        max="100"
                        step="0.01"
                        class="mt-1 block w-full"
                        :value="old('discount_percentage', data_get($product ?? null, 'discount_percentage'))"
                        x-model.number="discountPercentage"
                        x-on:input="handlePercentageInput($event)"
                        x-bind:disabled="!discountEnabled"
                    />
                    <x-input-error class="mt-1" :messages="$errors->get('discount_percentage')" />
                </div>

                <div class="space-y-1">
                    <x-input-label for="discount_amount" :value="__('Potongan (Rp)')" />
                    <x-text-input
                        id="discount_amount"
                        name="discount_amount"
                        type="number"
                        min="0"
                        step="100"
                        class="mt-1 block w-full"
                        :value="old('discount_amount', data_get($product ?? null, 'discount_amount'))"
                        x-model.number="discountAmount"
                        x-on:input="handleAmountInput($event)"
                        x-bind:disabled="!discountEnabled"
                    />
                    <x-input-error class="mt-1" :messages="$errors->get('discount_amount')" />
                </div>

                <div class="space-y-1">
                    <x-input-label :value="__('Ringkasan Diskon')" />
                    <div class="rounded-lg border border-slate-200 p-3 text-sm text-slate-600 min-h-[72px]">
                        <template x-if="discountEnabled && hasDiscount">
                            <div>
                                <p class="font-semibold text-slate-800">
                                    {{ __('Harga Setelah Diskon') }}:
                                    <span class="text-emerald-600">Rp <span x-text="formattedFinalPrice()"></span></span>
                                </p>
                                <p class="text-xs text-slate-500 mt-1">
                                    {{ __('Potongan Diterapkan') }}:
                                    <span class="font-medium text-slate-700" x-text="discountSummary()"></span>
                                </p>
                            </div>
                        </template>
                        <template x-if="!discountEnabled || !hasDiscount">
                            <p class="text-xs text-slate-400">
                                {{ __('Aktifkan diskon lalu isi persentase atau nominal untuk menghitung otomatis.') }}
                            </p>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-1">
            <x-input-label for="weight" :value="__('Berat (gram)')" />
            <x-text-input id="weight" name="weight" type="number" min="0" step="10" class="mt-1 block w-full" :value="old('weight', data_get($product ?? null, 'weight', 0))" required />
            <x-input-error class="mt-1" :messages="$errors->get('weight')" />
        </div>

        <div class="space-y-1">
            <x-input-label for="height" :value="__('Tinggi (cm)')" />
            <x-text-input id="height" name="height" type="number" min="0" step="1" class="mt-1 block w-full" :value="old('height', data_get($product ?? null, 'height', 0))" required />
            <x-input-error class="mt-1" :messages="$errors->get('height')" />
        </div>

        <div class="space-y-1">
            <x-input-label for="length" :value="__('Panjang (cm)')" />
            <x-text-input id="length" name="length" type="number" min="0" step="1" class="mt-1 block w-full" :value="old('length', data_get($product ?? null, 'length', 0))" required />
            <x-input-error class="mt-1" :messages="$errors->get('length')" />
        </div>

        <div class="space-y-1">
            <x-input-label for="width" :value="__('Lebar (cm)')" />
            <x-text-input id="width" name="width" type="number" min="0" step="1" class="mt-1 block w-full" :value="old('width', data_get($product ?? null, 'width', 0))" required />
            <x-input-error class="mt-1" :messages="$errors->get('width')" />
        </div>
    </div>

    <div class="grid gap-6 md:grid-cols-2" x-data="{ previewUrl: @js($existingImageUrl) }">
        <div class="space-y-1">
            <x-input-label for="image_product" :value="__('Foto Produk')" />
            <input
                id="image_product"
                name="image_product"
                type="file"
                accept="image/*"
                class="mt-1 block w-full text-sm text-slate-600 file:mr-3 file:rounded-md file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-slate-700"
                @change="
                    if ($event.target.files && $event.target.files[0]) {
                        previewUrl = URL.createObjectURL($event.target.files[0]);
                    } else {
                        previewUrl = '';
                    }
                "
                @if (! $isEdit) required @endif
            >
            <x-input-error class="mt-1" :messages="$errors->get('image_product')" />
            <p class="text-xs text-slate-500">{{ __('Format: jpg, jpeg, png, webp (maks 3MB)') }}</p>
        </div>

        <div class="space-y-2" x-show="previewUrl" x-transition>
            <x-input-label :value="__('Pratinjau Gambar')" />
            <img :src="previewUrl" alt="{{ data_get($product ?? null, 'name', __('Pratinjau Produk')) }}" class="h-40 w-40 rounded-lg border border-slate-200 object-cover">
            <p class="text-xs text-slate-500">{{ __('Gambar di atas akan tersimpan setelah formulir dikirim.') }}</p>
        </div>
    </div>

    <div class="flex items-center justify-between border-t border-slate-200 pt-4">
        <label for="is_active" class="flex items-center gap-3 text-sm font-medium text-slate-700">
            <input type="hidden" name="is_active" value="0">
            <input id="is_active" type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-500"
                @checked(old('is_active', isset($product) ? (bool) $product->is_active : true))>
            {{ __('Produk Aktif & Dapat Dijual') }}
        </label>

        <x-primary-button type="submit">
            {{ $isEdit ? __('Perbarui Produk') : __('Simpan Produk') }}
        </x-primary-button>
    </div>
</div>

@once
    <script>
        function productDiscountState(config) {
            return {
                price: Number(config.price) || 0,
                discountEnabled: Boolean(config.enabled),
                discountSource: config.source ?? null,
                discountPercentage: config.percentage !== null ? Number(config.percentage) : null,
                discountAmount: config.amount !== null ? Number(config.amount) : null,
                get hasDiscount() {
                    return (this.discountPercentage ?? 0) > 0 || (this.discountAmount ?? 0) > 0;
                },
                handlePriceInput(event) {
                    this.price = Number(event.target.value) || 0;
                    this.syncDiscounts();
                },
                handlePercentageInput(event) {
                    this.discountSource = 'percentage';
                    this.discountPercentage = Number(event.target.value) || 0;
                    if (!this.discountEnabled) {
                        this.discountEnabled = true;
                    }
                    this.syncDiscounts();
                },
                handleAmountInput(event) {
                    this.discountSource = 'amount';
                    this.discountAmount = Number(event.target.value) || 0;
                    if (!this.discountEnabled) {
                        this.discountEnabled = true;
                    }
                    this.syncDiscounts();
                },
                toggleDiscount() {
                    if (!this.discountEnabled) {
                        this.discountSource = null;
                        this.discountPercentage = null;
                        this.discountAmount = null;
                    } else {
                        this.discountSource = this.discountSource ?? 'percentage';
                        this.syncDiscounts();
                    }
                },
                syncDiscounts() {
                    if (!this.discountEnabled) {
                        this.discountPercentage = null;
                        this.discountAmount = null;
                        return;
                    }

                    if (this.discountSource === 'amount') {
                        const amount = Math.min(Math.max(Number(this.discountAmount) || 0, 0), this.price);
                        this.discountAmount = amount;
                        this.discountPercentage = this.price > 0 ? (amount / this.price) * 100 : 0;
                    } else {
                        this.discountSource = 'percentage';
                        const pct = Math.min(Math.max(Number(this.discountPercentage) || 0, 0), 100);
                        this.discountPercentage = pct;
                        this.discountAmount = Math.round(this.price * (pct / 100));
                    }

                    if (this.price <= 0) {
                        this.discountEnabled = false;
                        this.discountSource = null;
                        this.discountPercentage = null;
                        this.discountAmount = null;
                    }
                },
                formattedFinalPrice() {
                    const finalPrice = Math.max(this.price - (this.discountAmount || 0), 0);
                    return new Intl.NumberFormat('id-ID').format(finalPrice);
                },
                discountSummary() {
                    if (!this.discountEnabled || !this.hasDiscount) {
                        return '-';
                    }

                    const amount = new Intl.NumberFormat('id-ID').format(this.discountAmount || 0);
                    const percentage = (this.discountPercentage || 0).toFixed(2);

                    return `Rp ${amount} (${percentage}%)`;
                },
            };
        }
    </script>
@endonce
