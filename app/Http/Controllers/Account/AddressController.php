<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use App\Services\RajaOngkirService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    public function index()
    {
        $customer  = Auth::guard('customer')->user();
        $addresses = $customer->addresses()->orderByDesc('is_default')->latest()->get();
        $provinces = (new RajaOngkirService)->getProvinces();

        return view('theme.templates.account.addresses', compact('addresses', 'provinces'));
    }

    public function store(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'phone'       => ['nullable', 'string', 'max:20'],
            'address'     => ['required', 'string', 'max:500'],
            'city'        => ['required', 'string', 'max:100'],
            'province'    => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:10'],
            'country'     => ['nullable', 'string', 'max:100'],
            'is_default'  => ['boolean'],
            'province_id' => ['nullable', 'integer'],
            'city_id'     => ['nullable', 'integer'],
        ]);

        $validated['customer_id'] = $customer->id;
        $validated['country']     = $validated['country'] ?? 'Indonesia';

        if (! empty($validated['is_default'])) {
            $customer->addresses()->update(['is_default' => false]);
        }

        // First address is always default
        if ($customer->addresses()->count() === 0) {
            $validated['is_default'] = true;
        }

        CustomerAddress::create($validated);

        return back()->with('success', 'Alamat berhasil ditambahkan.');
    }

    public function update(Request $request, CustomerAddress $address)
    {
        $this->authorizeAddress($address);

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'phone'       => ['nullable', 'string', 'max:20'],
            'address'     => ['required', 'string', 'max:500'],
            'city'        => ['required', 'string', 'max:100'],
            'province'    => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:10'],
            'country'     => ['nullable', 'string', 'max:100'],
            'is_default'  => ['boolean'],
            'province_id' => ['nullable', 'integer'],
            'city_id'     => ['nullable', 'integer'],
        ]);

        $validated['country'] = $validated['country'] ?? 'Indonesia';

        if (! empty($validated['is_default'])) {
            Auth::guard('customer')->user()->addresses()->update(['is_default' => false]);
        }

        $address->update($validated);

        return back()->with('success', 'Alamat berhasil diperbarui.');
    }

    public function destroy(CustomerAddress $address)
    {
        $this->authorizeAddress($address);

        $wasDefault = $address->is_default;
        $address->delete();

        if ($wasDefault) {
            $customer = Auth::guard('customer')->user();
            $customer->addresses()->latest()->first()?->update(['is_default' => true]);
        }

        return back()->with('success', 'Alamat berhasil dihapus.');
    }

    public function setDefault(CustomerAddress $address)
    {
        $this->authorizeAddress($address);

        $address->setAsDefault();

        return back()->with('success', 'Alamat utama berhasil diperbarui.');
    }

    private function authorizeAddress(CustomerAddress $address): void
    {
        if ($address->customer_id !== Auth::guard('customer')->id()) {
            abort(403);
        }
    }
}
