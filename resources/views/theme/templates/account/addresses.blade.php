@extends('theme.layouts.app')

@section('title', 'Alamat Saya — ' . config('app.name'))

@section('content')
<x-account-layout>

@php
    $hasRajaOngkir = ! empty($provinces);
    $provincesJson = json_encode($provinces ?? []);
@endphp

<div x-data="{
        addOpen: false,
        editId: null,

        hasRajaOngkir: {{ $hasRajaOngkir ? 'true' : 'false' }},
        provinces: {{ $provincesJson }},
        provinceId: '',
        cities: [],
        cityId: '',
        loadingCities: false,

        get provinceName() {
            const p = this.provinces.find(p => p.province_id == this.provinceId);
            return p ? p.province : '';
        },
        get cityName() {
            const c = this.cities.find(c => c.city_id == this.cityId);
            return c ? c.city_name : '';
        },

        async loadCities(pid) {
            if (!pid) { this.cities = []; return; }
            this.loadingCities = true;
            try {
                const r = await fetch('/shipping/cities?province_id=' + pid);
                this.cities = await r.json();
            } catch(e) { this.cities = []; }
            this.loadingCities = false;
        },

        async onProvinceChange() {
            this.cityId = '';
            await this.loadCities(this.provinceId);
        },

        openAdd() {
            this.provinceId = '';
            this.cityId = '';
            this.cities = [];
            this.addOpen = true;
        },

        async openEdit(id, pid, cid) {
            this.editId = id;
            this.provinceId = String(pid || '');
            this.cityId = String(cid || '');
            if (pid) await this.loadCities(pid);
            else this.cities = [];
        },
    }">

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-semibold text-gray-900">Alamat Saya</h2>
        <button @click="openAdd()"
            class="inline-flex items-center gap-2 bg-gray-900 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Tambah Alamat
        </button>
    </div>

    @if ($addresses->isEmpty())
        <div class="bg-white border border-gray-200 rounded-xl p-12 text-center">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
            </svg>
            <p class="text-gray-500 text-sm">Belum ada alamat tersimpan.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @foreach ($addresses as $address)
            <div class="bg-white border rounded-xl p-5 {{ $address->is_default ? 'border-gray-900' : 'border-gray-200' }}">
                <div class="flex items-start justify-between gap-3 mb-1">
                    <p class="font-semibold text-gray-900 text-sm leading-snug">{{ $address->name }}</p>
                    @if ($address->is_default)
                        <span class="shrink-0 text-[10px] font-semibold uppercase tracking-widest bg-gray-900 text-white px-2 py-0.5 rounded-full">Utama</span>
                    @endif
                </div>
                @if ($address->phone)
                    <p class="text-xs text-gray-500">{{ $address->phone }}</p>
                @endif
                <div class="mt-1.5 text-sm text-gray-600 space-y-0.5">
                    <p>{{ $address->address }}</p>
                    <p>{{ $address->city }}, {{ $address->province }} {{ $address->postal_code }}</p>
                    <p>{{ $address->country }}</p>
                </div>
                <div class="flex items-center gap-3 mt-4 pt-4 border-t border-gray-100">
                    <button @click="openEdit({{ $address->id }}, {{ $address->province_id ?? 'null' }}, {{ $address->city_id ?? 'null' }})"
                        class="text-xs font-medium text-gray-700 hover:text-gray-900 transition-colors">
                        Ubah
                    </button>
                    @if (! $address->is_default)
                    <form method="POST" action="{{ route('account.addresses.default', $address) }}">
                        @csrf @method('PUT')
                        <button type="submit" class="text-xs font-medium text-gray-700 hover:text-gray-900 transition-colors">
                            Jadikan Utama
                        </button>
                    </form>
                    <form method="POST" action="{{ route('account.addresses.destroy', $address) }}"
                          onsubmit="return confirm('Hapus alamat ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs font-medium text-red-500 hover:text-red-700 transition-colors">Hapus</button>
                    </form>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    @endif

    {{-- Add modal --}}
    <div x-show="addOpen" x-cloak style="display:none"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40"
         @click.self="addOpen = false">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto"
             @click.stop>
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Tambah Alamat Baru</h3>
                <button @click="addOpen = false" class="text-gray-400 hover:text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form method="POST" action="{{ route('account.addresses.store') }}" class="p-6 space-y-4">
                @csrf
                @include('theme.snippets.address-form', ['hasRajaOngkir' => $hasRajaOngkir])
                <div class="flex gap-3 pt-2">
                    <button type="button" @click="addOpen = false"
                        class="flex-1 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg px-4 py-2.5 hover:bg-gray-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 bg-gray-900 text-white text-sm font-medium rounded-lg px-4 py-2.5 hover:bg-gray-700 transition-colors">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit modals (one per address) --}}
    @foreach ($addresses as $address)
    <div x-show="editId === {{ $address->id }}" x-cloak style="display:none"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40"
         @click.self="editId = null">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto"
             @click.stop>
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Edit Alamat</h3>
                <button @click="editId = null" class="text-gray-400 hover:text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form method="POST" action="{{ route('account.addresses.update', $address) }}" class="p-6 space-y-4">
                @csrf @method('PUT')
                @include('theme.snippets.address-form', ['prefill' => $address, 'hasRajaOngkir' => $hasRajaOngkir])
                <div class="flex gap-3 pt-2">
                    <button type="button" @click="editId = null"
                        class="flex-1 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg px-4 py-2.5 hover:bg-gray-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 bg-gray-900 text-white text-sm font-medium rounded-lg px-4 py-2.5 hover:bg-gray-700 transition-colors">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endforeach

</div>

</x-account-layout>
@endsection
