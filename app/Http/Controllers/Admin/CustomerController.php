<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminCustomerIndexRequest;
use App\Services\Customer\AdminCustomerDirectory;
use App\Services\Customer\AdminCustomerProfile;
use App\Models\Customer;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(AdminCustomerIndexRequest $request, AdminCustomerDirectory $directory): View
    {
        return view('admin.customers.index', $directory->paginate($request->user(), $request->validated()));
    }

    public function show(Customer $customer, AdminCustomerProfile $profile): View
    {
        abort_unless($profile->canView($customer, request()->user()), 403);

        return view('admin.customers.show', $profile->get($customer, request()->user()));
    }
}
