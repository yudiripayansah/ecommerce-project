{{-- Reusable address form fields. Pass $prefill (CustomerAddress) to pre-fill values. --}}
@php $p = $prefill ?? null; @endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama penerima</label>
        <input name="name" type="text" value="{{ old('name', $p?->name) }}" required
            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 @error('name') border-red-400 @enderror"
            placeholder="Nama lengkap">
        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-1.5">No. telepon</label>
        <input name="phone" type="tel" value="{{ old('phone', $p?->phone) }}"
            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 @error('phone') border-red-400 @enderror"
            placeholder="08xx-xxxx-xxxx">
        @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Alamat lengkap</label>
        <textarea name="address" rows="2" required
            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 @error('address') border-red-400 @enderror"
            placeholder="Nama jalan, nomor, RT/RW, kelurahan">{{ old('address', $p?->address) }}</textarea>
        @error('address') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    @if ($hasRajaOngkir ?? false)
        {{-- Province dropdown --}}
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Provinsi</label>
            <select x-model="provinceId" @change="onProvinceChange()"
                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 bg-white @error('province') border-red-400 @enderror"
                required>
                <option value="">Pilih Provinsi</option>
                <template x-for="prov in provinces" :key="prov.province_id">
                    <option :value="prov.province_id" x-text="prov.province"></option>
                </template>
            </select>
            <input type="hidden" name="province" :value="provinceName">
            <input type="hidden" name="province_id" :value="provinceId">
            @error('province') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- City dropdown (dependent on province) --}}
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Kota / Kabupaten</label>
            <div class="relative">
                <select x-model="cityId"
                    :disabled="!provinceId || loadingCities"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 bg-white disabled:bg-gray-50 disabled:text-gray-400 @error('city') border-red-400 @enderror"
                    required>
                    <option value="" x-text="loadingCities ? 'Memuat kota...' : (provinceId ? 'Pilih Kota/Kabupaten' : 'Pilih provinsi dulu')"></option>
                    <template x-for="city in cities" :key="city.city_id">
                        <option :value="city.city_id" x-text="city.type + ' ' + city.city_name"></option>
                    </template>
                </select>
                <div x-show="loadingCities" class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                    <svg class="w-4 h-4 animate-spin text-gray-400" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                </div>
            </div>
            <input type="hidden" name="city" :value="cityName">
            <input type="hidden" name="city_id" :value="cityId">
            @error('city') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
    @else
        {{-- Fallback text inputs when Raja Ongkir is not configured --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Kota / Kabupaten</label>
            <input name="city" type="text" value="{{ old('city', $p?->city) }}" required
                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 @error('city') border-red-400 @enderror">
            @error('city') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Provinsi</label>
            <input name="province" type="text" value="{{ old('province', $p?->province) }}" required
                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 @error('province') border-red-400 @enderror">
            @error('province') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
    @endif

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Kode pos</label>
        <input name="postal_code" type="text" value="{{ old('postal_code', $p?->postal_code) }}" required
            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 @error('postal_code') border-red-400 @enderror"
            placeholder="12345" maxlength="10">
        @error('postal_code') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Negara</label>
        <input name="country" type="text" value="{{ old('country', $p?->country ?? 'Indonesia') }}"
            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900">
    </div>
</div>

<div class="flex items-center gap-2 mt-1">
    <input type="checkbox" id="is_default_{{ $p?->id ?? 'new' }}" name="is_default" value="1"
        {{ old('is_default', $p?->is_default) ? 'checked' : '' }}
        class="rounded border-gray-300 text-gray-900 focus:ring-gray-900">
    <label for="is_default_{{ $p?->id ?? 'new' }}" class="text-sm text-gray-600 select-none">Jadikan alamat utama</label>
</div>
