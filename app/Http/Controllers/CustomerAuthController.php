<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\Customer\CustomerIdentityService;
use App\Services\Customer\DeleteCustomerAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CustomerAuthController extends Controller
{
    public function __construct(private readonly CustomerIdentityService $identity)
    {
    }

    public function showLogin()
    {
        return view('pages.customer.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'identity' => ['required', 'string', 'max:190'],
            'password' => ['required', 'string'],
        ]);
        $identity = trim($credentials['identity']);
        $field = filter_var($identity, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        $value = $field === 'email' ? $this->identity->normalizeEmail($identity) : $this->identity->normalizePhone($identity);
        $customer = Customer::where($field, $value)->first();

        if (! $customer || ! $customer->password || ! Hash::check($credentials['password'], $customer->password)) {
            throw ValidationException::withMessages(['identity' => 'Неверный email, телефон или пароль.']);
        }

        Auth::guard('customer')->login($customer, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('customer.dashboard'));
    }

    public function showRegister()
    {
        return view('pages.customer.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:190'],
            'last_name' => ['nullable', 'string', 'max:190'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['required', 'string', 'min:8', 'max:32'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'terms' => ['accepted'],
        ]);

        $customer = DB::transaction(function () use ($data) {
            $customer = $this->identity->claimForRegistration(
                $data['first_name'],
                $data['last_name'] ?? null,
                $data['email'],
                $data['phone'],
            );
            $customer->update([
                'password' => $data['password'],
            ]);

            return $customer;
        });

        Auth::guard('customer')->login($customer);
        $request->session()->regenerate();

        return redirect()->route('customer.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    public function destroy(Request $request, DeleteCustomerAccount $deletion)
    {
        $data = $request->validate([
            'password' => ['required', 'string'],
            'confirm_delete' => ['accepted'],
        ]);
        $customer = Auth::guard('customer')->user();

        if (! Hash::check($data['password'], $customer->password)) {
            throw ValidationException::withMessages(['password' => 'Неверный пароль. Аккаунт не удалён.']);
        }

        Auth::guard('customer')->logout();
        $deletion->handle($customer);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('status', 'Клиентский аккаунт и персональные данные удалены.');
    }
}
