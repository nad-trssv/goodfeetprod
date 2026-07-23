<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\Customer\CustomerIdentityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CustomerProfileController extends Controller
{
    public function show()
    {
        return view('pages.customer.profile', ['customer' => Auth::guard('customer')->user()]);
    }

    public function update(Request $request, CustomerIdentityService $identity)
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:190'],
            'last_name' => ['nullable', 'string', 'max:190'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['required', 'string', 'min:8', 'max:32'],
            'password' => ['required', 'string'],
        ]);
        $customer = Auth::guard('customer')->user();
        if (! Hash::check($data['password'], $customer->password)) {
            throw ValidationException::withMessages(['password' => __('customer.wrong_password')]);
        }

        $email = $identity->normalizeEmail($data['email']);
        $phone = $identity->normalizePhone($data['phone']);
        DB::transaction(function () use ($customer, $data, $email, $phone) {
            Customer::whereKey($customer->id)->lockForUpdate()->firstOrFail();
            if (Customer::where('email', $email)->where('id', '!=', $customer->id)->exists()) {
                throw ValidationException::withMessages(['email' => 'Этот email уже используется другим клиентом.']);
            }
            if (Customer::where('phone', $phone)->where('id', '!=', $customer->id)->exists()) {
                throw ValidationException::withMessages(['phone' => 'Этот телефон уже используется другим клиентом.']);
            }
            $customer->update([
                'first_name' => trim($data['first_name']),
                'last_name' => trim((string) ($data['last_name'] ?? '')) ?: null,
                'email' => $email,
                'phone' => $phone,
            ]);
        });

        return back()->with('status', __('customer.profile_updated'));
    }
}
