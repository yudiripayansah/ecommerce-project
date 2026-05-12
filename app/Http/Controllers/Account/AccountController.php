<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AccountController extends Controller
{
    public function index()
    {
        $customer = Auth::guard('customer')->user()->load('orders');

        return view('theme.templates.account.dashboard', compact('customer'));
    }

    public function updateProfile(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['required', 'email', 'unique:customers,email,' . $customer->id],
        ]);

        $customer->update($validated);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $request->validate([
            'current_password' => ['required', function ($attribute, $value, $fail) use ($customer) {
                if (! Hash::check($value, $customer->password)) {
                    $fail('Password saat ini tidak sesuai.');
                }
            }],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $customer->update(['password' => $request->password]);

        return back()->with('success', 'Password berhasil diperbarui.');
    }
}
