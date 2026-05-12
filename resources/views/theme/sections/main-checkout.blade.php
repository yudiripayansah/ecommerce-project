<section class="bg-gray-50 min-h-screen">
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 md:py-14">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-sm text-gray-400 mb-6">
        <a href="{{ route('cart') }}" class="hover:text-gray-700 transition-colors">Keranjang</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
        </svg>
        <span class="text-gray-900 font-medium">Checkout</span>
    </nav>

    @php
        $savedAddresses  = $addresses ?? collect();
        $hasAddresses    = $savedAddresses->isNotEmpty();
        $addressesJson   = $savedAddresses->map(fn ($a) => [
            'id'          => $a->id,
            'name'        => $a->name,
            'phone'       => $a->phone,
            'address'     => $a->address,
            'city'        => $a->city,
            'province'    => $a->province,
            'postal_code' => $a->postal_code,
            'is_default'  => $a->is_default,
        ])->values()->toJson();
        $hasRajaOngkir   = ! empty($provinces);
        $provincesJson   = json_encode($provinces ?? []);
        $subtotalInt     = (int) $total;
    @endphp

    <div x-data="{
            payment: '{{ old('payment_method', 'midtrans') }}',
            addresses: {{ $addressesJson ?? '[]' }},
            loading: false,

            /* ── RajaOngkir ── */
            hasRajaOngkir: {{ $hasRajaOngkir ? 'true' : 'false' }},
            provinces: {{ $provincesJson }},
            cities: [],
            selectedProvinceId: '',
            selectedProvinceName: '',
            selectedCityId: '',
            selectedCityName: '',
            shippingOptions: [],
            selectedShippingIdx: null,
            shippingCost: 0,
            shippingService: '',
            loadingCities: false,
            loadingShipping: false,

            subtotal: {{ $subtotalInt }},
            get orderTotal() { return this.subtotal + this.shippingCost; },

            formatRupiah(n) {
                return 'Rp ' + Number(n).toLocaleString('id-ID');
            },

            async onProvinceChange() {
                this.cities = [];
                this.selectedCityId = '';
                this.selectedCityName = '';
                this.shippingOptions = [];
                this.shippingCost = 0;
                this.shippingService = '';
                this.selectedShippingIdx = null;

                const prov = this.provinces.find(p => p.province_id == this.selectedProvinceId);
                this.selectedProvinceName = prov ? prov.province : '';
                if (!this.selectedProvinceId) return;

                this.loadingCities = true;
                try {
                    const r = await fetch('/shipping/cities?province_id=' + this.selectedProvinceId);
                    this.cities = await r.json();
                } catch(e) {}
                this.loadingCities = false;
            },

            async onCityChange() {
                this.shippingOptions = [];
                this.shippingCost = 0;
                this.shippingService = '';
                this.selectedShippingIdx = null;

                const city = this.cities.find(c => c.city_id == this.selectedCityId);
                this.selectedCityName = city ? city.city_name : '';
                if (!this.selectedCityId) return;

                this.loadingShipping = true;
                try {
                    const r = await fetch('/shipping/cost', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        },
                        body: JSON.stringify({ destination_city_id: parseInt(this.selectedCityId) }),
                    });
                    this.shippingOptions = await r.json();
                } catch(e) {}
                this.loadingShipping = false;
            },

            selectShipping(idx) {
                this.selectedShippingIdx = idx;
                const opt = this.shippingOptions[idx];
                this.shippingCost = opt.cost;
                this.shippingService = opt.courier + ' ' + opt.service + ' (' + opt.etd + ' hari)';
            },

            fillAddress(addr) {
                this.$refs.shippingAddress.value = addr.address;
                this.$refs.shippingPostal.value  = addr.postal_code;

                if (this.hasRajaOngkir) {
                    const prov = this.provinces.find(p =>
                        p.province.toLowerCase() === addr.province.toLowerCase()
                    );
                    if (prov) {
                        this.selectedProvinceId = prov.province_id;
                        this.onProvinceChange().then(() => {
                            const city = this.cities.find(c =>
                                addr.city.toLowerCase().includes(c.city_name.toLowerCase()) ||
                                c.city_name.toLowerCase().includes(addr.city.toLowerCase())
                            );
                            if (city) {
                                this.selectedCityId = city.city_id;
                                this.onCityChange();
                            }
                        });
                    }
                } else {
                    this.$refs.shippingCity.value     = addr.city;
                    this.$refs.shippingProvince.value = addr.province;
                }
            },

            async submitOrder() {
                if (this.hasRajaOngkir && this.selectedShippingIdx === null) {
                    alert('Silakan pilih layanan pengiriman terlebih dahulu.');
                    return;
                }
                if (this.payment !== 'midtrans') {
                    this.$refs.checkoutForm.submit();
                    return;
                }
                this.loading = true;
                const form = this.$refs.checkoutForm;
                const data = new FormData(form);
                try {
                    const resp = await fetch(form.action, {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        body: data,
                    });
                    const json = await resp.json();
                    if (!resp.ok) {
                        alert(json.error || 'Terjadi kesalahan. Silakan coba lagi.');
                        this.loading = false;
                        return;
                    }
                    window.snap.pay(json.snap_token, {
                        onSuccess: () => { window.location.href = json.success_url; },
                        onPending: () => { window.location.href = json.success_url; },
                        onError:   () => { this.loading = false; alert('Pembayaran gagal. Silakan coba lagi.'); },
                        onClose:   () => { this.loading = false; },
                    });
                } catch (e) {
                    this.loading = false;
                    alert('Gagal menghubungi server. Silakan coba lagi.');
                }
            }
         }">

        <form method="POST" action="{{ route('checkout.process') }}"
              x-ref="checkoutForm"
              @submit.prevent="submitOrder()">
            @csrf

            {{-- Hidden shipping fields (driven by Alpine) --}}
            @if ($hasRajaOngkir)
                <input type="hidden" name="shipping_province" :value="selectedProvinceName">
                <input type="hidden" name="shipping_city"     :value="selectedCityName">
                <input type="hidden" name="shipping_cost"     :value="shippingCost">
                <input type="hidden" name="shipping_service"  :value="shippingService">
            @endif

            <div class="lg:grid lg:grid-cols-5 lg:gap-12">

                {{-- Kiri — Form --}}
                <div class="lg:col-span-3 flex flex-col gap-6">

                    {{-- Informasi Kontak --}}
                    <div class="bg-white border border-gray-200 rounded-2xl p-6">
                        <h2 class="text-base font-semibold text-gray-900 mb-5 pb-3 border-b border-gray-100">Informasi Kontak</h2>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama lengkap <span class="text-red-500">*</span></label>
                                <input type="text" name="customer_name"
                                       value="{{ old('customer_name', $customer?->name ?? '') }}"
                                       class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300 @error('customer_name') border-red-400 @enderror"
                                       placeholder="Budi Santoso" required>
                                @error('customer_name')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Alamat email <span class="text-red-500">*</span></label>
                                    <input type="email" name="customer_email"
                                           value="{{ old('customer_email', $customer?->email ?? '') }}"
                                           class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300 @error('customer_email') border-red-400 @enderror"
                                           placeholder="budi@email.com" required
                                           {{ $customer ? 'readonly' : '' }}>
                                    @error('customer_email')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Nomor telepon <span class="text-red-500">*</span></label>
                                    <input type="tel" name="customer_phone"
                                           value="{{ old('customer_phone', $customer?->phone ?? '') }}"
                                           class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300 @error('customer_phone') border-red-400 @enderror"
                                           placeholder="08123456789" required>
                                    @error('customer_phone')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Alamat Pengiriman --}}
                    <div class="bg-white border border-gray-200 rounded-2xl p-6">
                        <div class="flex items-center justify-between mb-5 pb-3 border-b border-gray-100">
                            <h2 class="text-base font-semibold text-gray-900">Alamat Pengiriman</h2>
                        </div>

                        @if ($customer && $hasAddresses)
                            <div class="mb-5">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Pilih alamat tersimpan</label>
                                <div class="space-y-2">
                                    @foreach ($savedAddresses as $addr)
                                        <button type="button"
                                                @click="fillAddress(addresses.find(a => a.id === {{ $addr->id }}))"
                                                class="w-full text-left px-4 py-3 border rounded-xl text-sm transition-colors
                                                       {{ $addr->is_default ? 'border-gray-900 bg-gray-50' : 'border-gray-200 hover:border-gray-400' }}">
                                            <div class="flex items-start justify-between gap-2">
                                                <div>
                                                    <p class="font-medium text-gray-900">{{ $addr->name }}
                                                        @if ($addr->is_default)
                                                            <span class="ml-1.5 text-xs font-medium text-white bg-gray-800 rounded-full px-2 py-0.5">Utama</span>
                                                        @endif
                                                    </p>
                                                    <p class="text-gray-500 mt-0.5">{{ $addr->address }}, {{ $addr->city }}, {{ $addr->province }} {{ $addr->postal_code }}</p>
                                                    <p class="text-gray-400 text-xs mt-0.5">{{ $addr->phone }}</p>
                                                </div>
                                                <svg class="w-4 h-4 text-gray-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/>
                                                </svg>
                                            </div>
                                        </button>
                                    @endforeach
                                </div>
                                <p class="mt-2 text-xs text-gray-400">Klik alamat di atas untuk mengisi otomatis, atau isi manual di bawah.</p>
                            </div>
                            <div class="border-t border-gray-100 mb-4"></div>
                        @endif

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Alamat lengkap <span class="text-red-500">*</span></label>
                                <input type="text" name="shipping_address" x-ref="shippingAddress"
                                       value="{{ old('shipping_address', $defaultAddress?->address ?? '') }}"
                                       class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300 @error('shipping_address') border-red-400 @enderror"
                                       placeholder="Jl. Sudirman No. 1, RT 01/RW 02" required>
                                @error('shipping_address')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            @if ($hasRajaOngkir)
                                {{-- Province + City dropdowns --}}
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Provinsi <span class="text-red-500">*</span></label>
                                        <select x-model="selectedProvinceId" @change="onProvinceChange()"
                                                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300 bg-white @error('shipping_province') border-red-400 @enderror"
                                                required>
                                            <option value="">-- Pilih Provinsi --</option>
                                            @foreach ($provinces as $prov)
                                                <option value="{{ $prov['province_id'] }}">{{ $prov['province'] }}</option>
                                            @endforeach
                                        </select>
                                        @error('shipping_province')
                                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Kota / Kabupaten <span class="text-red-500">*</span></label>
                                        <select x-model="selectedCityId" @change="onCityChange()"
                                                :disabled="!selectedProvinceId || loadingCities"
                                                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300 bg-white disabled:bg-gray-50 disabled:text-gray-400 @error('shipping_city') border-red-400 @enderror"
                                                required>
                                            <option value="" x-text="loadingCities ? 'Memuat kota...' : '-- Pilih Kota --'"></option>
                                            <template x-for="city in cities" :key="city.city_id">
                                                <option :value="city.city_id" x-text="city.type + ' ' + city.city_name"></option>
                                            </template>
                                        </select>
                                        @error('shipping_city')
                                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Postal Code --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Kode pos <span class="text-red-500">*</span></label>
                                    <input type="text" name="shipping_postal_code" x-ref="shippingPostal"
                                           value="{{ old('shipping_postal_code', $defaultAddress?->postal_code ?? '') }}"
                                           class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300 @error('shipping_postal_code') border-red-400 @enderror"
                                           placeholder="12190" required>
                                    @error('shipping_postal_code')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Shipping Options --}}
                                <div x-show="selectedCityId" x-transition>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Layanan Pengiriman <span class="text-red-500">*</span></label>

                                    <div x-show="loadingShipping" class="py-4 text-center text-sm text-gray-400">
                                        <svg class="animate-spin w-5 h-5 mx-auto mb-1" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                        Menghitung ongkos kirim…
                                    </div>

                                    <div x-show="!loadingShipping && shippingOptions.length === 0 && selectedCityId" class="py-3 text-sm text-amber-600 bg-amber-50 border border-amber-200 rounded-xl px-4">
                                        Layanan pengiriman tidak tersedia untuk kota ini.
                                    </div>

                                    <div x-show="!loadingShipping && shippingOptions.length > 0" class="space-y-2">
                                        <template x-for="(opt, idx) in shippingOptions" :key="idx">
                                            <label class="flex items-center justify-between px-4 py-3 border rounded-xl cursor-pointer transition-colors"
                                                   :class="selectedShippingIdx === idx ? 'border-gray-900 bg-gray-50' : 'border-gray-200 hover:border-gray-300'">
                                                <div class="flex items-center gap-3">
                                                    <input type="radio" name="_shipping_choice" :value="idx" x-model="selectedShippingIdx"
                                                           @change="selectShipping(idx)" class="accent-gray-900">
                                                    <div>
                                                        <p class="text-sm font-medium text-gray-900" x-text="opt.courier + ' ' + opt.service"></p>
                                                        <p class="text-xs text-gray-500" x-text="opt.description + ' · Estimasi ' + opt.etd + ' hari'"></p>
                                                    </div>
                                                </div>
                                                <span class="text-sm font-semibold text-gray-900 shrink-0" x-text="formatRupiah(opt.cost)"></span>
                                            </label>
                                        </template>
                                    </div>
                                </div>

                            @else
                                {{-- Fallback: text inputs when RajaOngkir not configured --}}
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Kota <span class="text-red-500">*</span></label>
                                        <input type="text" name="shipping_city" x-ref="shippingCity"
                                               value="{{ old('shipping_city', $defaultAddress?->city ?? '') }}"
                                               class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300 @error('shipping_city') border-red-400 @enderror"
                                               placeholder="Jakarta" required>
                                        @error('shipping_city')
                                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Provinsi <span class="text-red-500">*</span></label>
                                        <input type="text" name="shipping_province" x-ref="shippingProvince"
                                               value="{{ old('shipping_province', $defaultAddress?->province ?? '') }}"
                                               class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300 @error('shipping_province') border-red-400 @enderror"
                                               placeholder="DKI Jakarta" required>
                                        @error('shipping_province')
                                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Kode pos <span class="text-red-500">*</span></label>
                                        <input type="text" name="shipping_postal_code" x-ref="shippingPostal"
                                               value="{{ old('shipping_postal_code', $defaultAddress?->postal_code ?? '') }}"
                                               class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300 @error('shipping_postal_code') border-red-400 @enderror"
                                               placeholder="12190" required>
                                        @error('shipping_postal_code')
                                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            @endif

                            @error('stock')
                                <div class="p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    {{-- Metode Pembayaran --}}
                    <div class="bg-white border border-gray-200 rounded-2xl p-6">
                        <h2 class="text-base font-semibold text-gray-900 mb-5 pb-3 border-b border-gray-100">Metode Pembayaran</h2>
                        <div class="space-y-3">

                            <label class="flex items-start gap-4 p-4 border rounded-xl cursor-pointer transition-colors"
                                   :class="payment === 'midtrans' ? 'border-blue-600 bg-blue-50' : 'border-gray-200 hover:border-gray-300'">
                                <input type="radio" name="payment_method" value="midtrans" x-model="payment" class="mt-0.5 accent-blue-600">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <p class="text-sm font-semibold text-gray-900">Bayar Online</p>
                                        <span class="text-xs font-medium bg-blue-600 text-white px-2 py-0.5 rounded-full">Direkomendasikan</span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">QRIS, GoPay, OVO, Dana, ShopeePay, Virtual Account, Kartu Kredit/Debit</p>
                                    <div class="flex items-center gap-2 mt-2 flex-wrap">
                                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded font-medium">QRIS</span>
                                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded font-medium">GoPay</span>
                                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded font-medium">OVO</span>
                                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded font-medium">Dana</span>
                                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded font-medium">VA Bank</span>
                                    </div>
                                </div>
                            </label>

                            <label class="flex items-start gap-4 p-4 border rounded-xl cursor-pointer transition-colors"
                                   :class="payment === 'bank_transfer' ? 'border-gray-900 bg-gray-50' : 'border-gray-200 hover:border-gray-300'">
                                <input type="radio" name="payment_method" value="bank_transfer" x-model="payment" class="mt-0.5 accent-gray-900">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">Transfer Bank Manual</p>
                                    <p class="text-xs text-gray-500 mt-0.5">Transfer ke rekening kami. Pesanan diproses setelah pembayaran diverifikasi.</p>
                                </div>
                            </label>

                            <label class="flex items-start gap-4 p-4 border rounded-xl cursor-pointer transition-colors"
                                   :class="payment === 'cod' ? 'border-gray-900 bg-gray-50' : 'border-gray-200 hover:border-gray-300'">
                                <input type="radio" name="payment_method" value="cod" x-model="payment" class="mt-0.5 accent-gray-900">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">Bayar di Tempat (COD)</p>
                                    <p class="text-xs text-gray-500 mt-0.5">Bayar saat pesanan tiba di depan pintu Anda.</p>
                                </div>
                            </label>
                        </div>

                        <div x-show="payment === 'bank_transfer'" x-transition class="mt-3 p-4 bg-blue-50 border border-blue-100 rounded-xl text-sm text-blue-800">
                            <p class="font-semibold mb-2">Detail rekening bank</p>
                            @if (!empty($bankAccounts))
                                <div class="space-y-2">
                                    @foreach ($bankAccounts as $account)
                                        <div class="flex flex-wrap gap-x-4 gap-y-0.5">
                                            <span class="font-semibold">{{ $account['bank_name'] }}</span>
                                            <span class="font-mono font-bold">{{ $account['account_number'] }}</span>
                                            <span class="text-blue-700">a.n. {{ $account['account_holder'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-blue-600 italic">Detail rekening akan dikonfirmasi setelah Anda menempatkan pesanan.</p>
                            @endif
                            <p class="mt-2 text-xs text-blue-600">Mohon transfer jumlah yang tepat dan cantumkan nomor pesanan sebagai referensi.</p>
                        </div>

                        @error('payment_method')
                            <p class="mt-2 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Catatan --}}
                    <div class="bg-white border border-gray-200 rounded-2xl p-6">
                        <h2 class="text-base font-semibold text-gray-900 mb-5 pb-3 border-b border-gray-100">Catatan Pesanan <span class="text-xs font-normal text-gray-400">(opsional)</span></h2>
                        <textarea name="notes" rows="3"
                                  class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-gray-300 resize-none"
                                  placeholder="Instruksi khusus, catatan pengiriman…">{{ old('notes') }}</textarea>
                    </div>

                    {{-- Submit --}}
                    <button type="submit"
                            :disabled="loading"
                            class="w-full bg-gray-900 text-white font-semibold py-4 rounded-xl hover:bg-gray-700 transition-colors text-sm disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                        <svg x-show="loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span x-text="loading ? 'Memproses...' : 'Buat Pesanan'"></span>
                    </button>
                </div>

                {{-- Kanan — Ringkasan Pesanan --}}
                <div class="lg:col-span-2 mt-8 lg:mt-0">
                    <div class="sticky top-24 bg-gray-50 border border-gray-200 rounded-2xl p-6">
                        <h2 class="text-base font-semibold text-gray-900 pb-4 mb-4 border-b border-gray-200">Ringkasan Pesanan</h2>

                        <div class="divide-y divide-gray-200 max-h-72 overflow-y-auto -mx-1 px-1">
                            @foreach ($cart as $item)
                                <div class="flex items-center gap-3 py-3">
                                    @if ($item['image'])
                                        <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}"
                                             class="w-14 h-14 object-cover rounded-lg bg-white shrink-0">
                                    @else
                                        <div class="w-14 h-14 bg-white rounded-lg flex items-center justify-center text-gray-200 shrink-0">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                                            </svg>
                                        </div>
                                    @endif
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 line-clamp-1">{{ $item['title'] }}</p>
                                        @if ($item['variant_title'])
                                            <p class="text-xs text-gray-500">{{ $item['variant_title'] }}</p>
                                        @endif
                                        <p class="text-xs text-gray-500 mt-0.5">Qty {{ $item['quantity'] }}</p>
                                    </div>
                                    <span class="text-sm font-semibold text-gray-900 shrink-0">
                                        {{ rupiah($item['price'] * $item['quantity']) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>

                        <dl class="space-y-3 text-sm mt-5 pt-5 border-t border-gray-200">
                            <div class="flex justify-between">
                                <dt class="text-gray-600">Subtotal</dt>
                                <dd class="font-medium">{{ rupiah($total) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-600">Ongkos Kirim</dt>
                                @if ($hasRajaOngkir)
                                    <dd class="font-medium">
                                        <span x-show="selectedShippingIdx === null" class="text-gray-400">Pilih layanan</span>
                                        <span x-show="selectedShippingIdx !== null" x-text="formatRupiah(shippingCost)"></span>
                                    </dd>
                                @else
                                    <dd class="text-gray-400">Dihitung saat tiba</dd>
                                @endif
                            </div>
                        </dl>

                        <div class="mt-4 pt-4 border-t border-gray-200 flex justify-between text-base font-bold">
                            <span>Total</span>
                            @if ($hasRajaOngkir)
                                <span x-text="formatRupiah(orderTotal)"></span>
                            @else
                                <span>{{ rupiah($total) }}</span>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>
</section>

@push('scripts')
<script src="{{ $midtransSnapUrl }}" data-client-key="{{ $midtransClientKey }}"></script>
@endpush
