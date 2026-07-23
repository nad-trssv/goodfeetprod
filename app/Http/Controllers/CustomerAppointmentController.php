<?php

namespace App\Http\Controllers;

use App\Models\Appointments;
use App\Services\Booking\CustomerCancellationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerAppointmentController extends Controller
{
    public function destroy(Request $request, Appointments $appointment, CustomerCancellationService $cancellations)
    {
        $data = $request->validate(['reason' => ['nullable', 'string', 'max:500']]);
        $cancellations->cancel($appointment, Auth::guard('customer')->user(), $data['reason'] ?? null);

        return back()->with('status', __('customer.cancelled_successfully'));
    }
}
