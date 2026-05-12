<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Mail\Admin\NewCustomerMail;
use App\Mail\Customer\WelcomeMail;
use App\Models\Customer;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('theme.templates.account.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('customer')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $intended = $request->session()->pull('url.intended', route('account.index'));

            return redirect($intended);
        }

        return back()->withErrors([
            'email' => 'Email atau password tidak sesuai.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        return view('theme.templates.account.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:customers,email'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $customer = Customer::create($validated);

        Mail::to($customer->email)->send(new WelcomeMail($customer));

        $adminEmail = Setting::get('contact_email', config('mail.from.address'));
        if ($adminEmail) {
            Mail::to($adminEmail)->send(new NewCustomerMail($customer));
        }

        Auth::guard('customer')->login($customer);

        $request->session()->regenerate();

        return redirect()->route('account.index')
            ->with('success', 'Akun berhasil dibuat. Selamat datang, ' . $customer->name . '!');
    }

    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('account.login');
    }
}
