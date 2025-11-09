<section x-data="{ showForm: false }">
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Alamat Pengiriman') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Tambahkan minimal satu alamat agar proses checkout lebih cepat. Tandai salah satu alamat sebagai default.') }}
        </p>
    </header>

    <div class="mt-4">
        <button type="button" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:border-gray-400 hover:text-gray-900"
            @click="showForm = !showForm">
            <span x-text="showForm ? '{{ __('Batalkan Tambah Data') }}' : '{{ __('Tambah Data Alamat') }}'"></span>
        </button>
    </div>

    <form method="POST" action="{{ route('profile.addresses.store') }}" class="mt-6 grid gap-4 rounded-lg border border-gray-200 p-4" x-show="showForm" x-transition>
        @csrf

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="label" :value="__('Label Alamat (mis. Rumah)')" />
                <x-text-input id="label" name="label" type="text" class="mt-1 block w-full" :value="old('label')" />
                <x-input-error class="mt-1" :messages="$errors->get('label')" />
            </div>
            <div>
                <x-input-label for="recipient_name" :value="__('Nama Penerima')" />
                <x-text-input id="recipient_name" name="recipient_name" type="text" class="mt-1 block w-full" :value="old('recipient_name')" required />
                <x-input-error class="mt-1" :messages="$errors->get('recipient_name')" />
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="address_phone" :value="__('No. Telepon Penerima')" />
                <x-text-input id="address_phone" name="recipient_phone" type="text" class="mt-1 block w-full" :value="old('recipient_phone')" required />
                <x-input-error class="mt-1" :messages="$errors->get('recipient_phone')" />
            </div>
            <div>
                <x-input-label for="postal_code" :value="__('Kode Pos')" />
                <x-text-input id="postal_code" name="postal_code" type="text" class="mt-1 block w-full" :value="old('postal_code')" />
                <x-input-error class="mt-1" :messages="$errors->get('postal_code')" />
            </div>
        </div>

        <div>
            <x-input-label for="detail" :value="__('Alamat Lengkap')" />
            <textarea id="detail" name="detail" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ old('detail') }}</textarea>
            <x-input-error class="mt-1" :messages="$errors->get('detail')" />
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <x-input-label for="province" :value="__('Provinsi')" />
                <x-text-input id="province" name="province" type="text" class="mt-1 block w-full" :value="old('province')" required />
                <x-input-error class="mt-1" :messages="$errors->get('province')" />
            </div>
            <div>
                <x-input-label for="city" :value="__('Kota')" />
                <x-text-input id="city" name="city" type="text" class="mt-1 block w-full" :value="old('city')" required />
                <x-input-error class="mt-1" :messages="$errors->get('city')" />
            </div>
            <div>
                <x-input-label for="district" :value="__('Kecamatan')" />
                <x-text-input id="district" name="district" type="text" class="mt-1 block w-full" :value="old('district')" required />
                <x-input-error class="mt-1" :messages="$errors->get('district')" />
            </div>
        </div>

        <label class="flex items-center gap-2 text-sm font-medium text-gray-700">
            <input type="checkbox" name="is_default" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
            {{ __('Jadikan alamat default') }}
        </label>

        <div class="flex items-center gap-3">
            <x-primary-button>{{ __('Simpan Alamat') }}</x-primary-button>
            @if (session('status') === 'address-added')
                <p class="text-sm text-green-600">{{ __('Alamat berhasil ditambahkan.') }}</p>
            @endif
        </div>
    </form>

    <div class="mt-8 space-y-4">
        @forelse ($addresses as $address)
            <div x-data="{ open: false }" class="rounded-lg border border-gray-200 p-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-900">
                            {{ $address->label }}
                            @if ($address->is_default)
                                <span class="ml-2 rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-semibold text-indigo-700">{{ __('Default') }}</span>
                            @endif
                        </p>
                        <p class="text-sm text-gray-600">
                            {{ $address->recipient_name }} • {{ $address->phone }}
                        </p>
                        <p class="text-sm text-gray-500">
                            {{ $address->detail }},
                            {{ $address->district }},
                            {{ $address->city }},
                            {{ $address->province }}
                            @if ($address->postal_code)
                                , {{ $address->postal_code }}
                            @endif
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:border-gray-400" @click="open = !open">
                            {{ __('Edit') }}
                        </button>
                        <form method="POST" action="{{ route('profile.addresses.destroy', $address) }}" onsubmit="return confirm('{{ __('Hapus alamat ini?') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-md bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-500">
                                {{ __('Hapus') }}
                            </button>
                        </form>
                    </div>
                </div>

                <form x-show="open" x-transition method="POST" action="{{ route('profile.addresses.update', $address) }}" class="mt-4 grid gap-4 rounded-lg border border-gray-100 p-4 bg-gray-50">
                    @csrf
                    @method('PUT')

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <x-input-label :for="'label_'.$address->id" :value="__('Label Alamat')" />
                            <x-text-input :id="'label_'.$address->id" name="label" type="text" class="mt-1 block w-full" :value="$address->label" />
                        </div>
                        <div>
                            <x-input-label :for="'recipient_'.$address->id" :value="__('Nama Penerima')" />
                            <x-text-input :id="'recipient_'.$address->id" name="recipient_name" type="text" class="mt-1 block w-full" :value="$address->recipient_name" required />
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <x-input-label :for="'phone_'.$address->id" :value="__('No. Telepon')" />
                            <x-text-input :id="'phone_'.$address->id" name="recipient_phone" type="text" class="mt-1 block w-full" :value="$address->phone" required />
                        </div>
                        <div>
                            <x-input-label :for="'postal_'.$address->id" :value="__('Kode Pos')" />
                            <x-text-input :id="'postal_'.$address->id" name="postal_code" type="text" class="mt-1 block w-full" :value="$address->postal_code" />
                        </div>
                    </div>

                    <div>
                        <x-input-label :for="'detail_'.$address->id" :value="__('Alamat Lengkap')" />
                        <textarea :id="'detail_'.$address->id" name="detail" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ $address->detail }}</textarea>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-3">
                        <div>
                            <x-input-label :for="'city_'.$address->id" :value="__('Kota')" />
                            <x-text-input :id="'city_'.$address->id" name="city" type="text" class="mt-1 block w-full" :value="$address->city" required />
                        </div>
                        <div>
                            <x-input-label :for="'province_'.$address->id" :value="__('Provinsi')" />
                            <x-text-input :id="'province_'.$address->id" name="province" type="text" class="mt-1 block w-full" :value="$address->province" required />
                        </div>
                        <div>
                            <x-input-label :for="'district_'.$address->id" :value="__('Kecamatan')" />
                            <x-text-input :id="'district_'.$address->id" name="district" type="text" class="mt-1 block w-full" :value="$address->district" required />
                        </div>
                    </div>

                    <label class="flex items-center gap-2 text-sm font-medium text-gray-700">
                        <input type="hidden" name="is_default" value="0">
                        <input type="checkbox" name="is_default" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked($address->is_default)>
                        {{ __('Jadikan default') }}
                    </label>

                    <div class="flex items-center gap-2">
                        <x-primary-button>{{ __('Simpan Perubahan') }}</x-primary-button>
                        @if (session('status') === 'address-updated')
                            <p class="text-sm text-green-600">{{ __('Alamat diperbarui.') }}</p>
                        @endif
                    </div>
                </form>
            </div>
        @empty
            <p class="text-sm text-gray-500">{{ __('Belum ada alamat tersimpan.') }}</p>
        @endforelse
    </div>
</section>
