<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\AppointmentRescheduleRequest;
use App\Services\Booking\AppointmentRescheduleService;
use App\Services\Notifications\NotificationReadService;
use Illuminate\Support\Facades\Auth;

class RescheduleRequestController extends Controller
{
 public function index(NotificationReadService $notifications) {
    $notifications->event(Auth::user(), 'reschedule_requested');
    $visible = fn ($query) => ! Auth::user()->hasAllAppointmentsScope() ? $query->where('user_id', Auth::id()) : $query;
    $requests = $visible(AppointmentRescheduleRequest::with(['appointment','customer','service','user'])->where('status','pending'))->orderBy('requested_start')->paginate(20, ['*'], 'pending_page');
    $history = $visible(AppointmentRescheduleRequest::with(['appointment','customer','service','user','reviewer']))->latest('created_at')->paginate(20, ['*'], 'history_page');
    return view('admin.reschedule.index', compact('requests', 'history'));
 }
 public function approve(AppointmentRescheduleRequest $request, AppointmentRescheduleService $service) { $service->review($request,Auth::user(),true); return back()->with('success',__('customer.reschedule_approved')); }
 public function reject(AppointmentRescheduleRequest $request, AppointmentRescheduleService $service) { $service->review($request,Auth::user(),false); return back()->with('success',__('customer.reschedule_rejected')); }
}
