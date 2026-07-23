<?php
namespace App\Http\Controllers;

use App\Models\Appointments;
use App\Services\Booking\AppointmentRescheduleService;
use App\Services\Booking\BookingCalendarService;
use App\Services\Booking\SlotAvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerRescheduleController extends Controller
{
    public function show(Appointments $appointment, SlotAvailabilityService $availability, BookingCalendarService $calendar)
    {
        $this->owned($appointment);
        abort_if($appointment->rescheduleRequests()->where('status', 'pending')->exists(), 409, __('customer.reschedule_already_pending'));
        $limitDate = today()->addDays(max(0, (int) ($calendar->bookingLimit()['days'] ?? 30)));
        $recommendations = collect();
        $day = $appointment->appointment_start->copy()->startOfDay()->max(today());
        while ($day->lte($limitDate) && $recommendations->count() < 6) {
            foreach ($availability->slots($day->toDateString(), $appointment->service_id, $appointment->user_id, $appointment->id) as $slot) {
                $dateTime = Carbon::parse($day->toDateString().' '.$slot['start']);
                if ($dateTime->gt($appointment->appointment_start)) {
                    $recommendations->push(['date'=>$day->toDateString(),'start'=>$slot['start'],'end'=>$slot['end']]);
                    if ($recommendations->count() === 6) break;
                }
            }
            $day->addDay();
        }
        return view('pages.customer.reschedule', compact('appointment','recommendations','limitDate'));
    }

    public function days(Request $request, Appointments $appointment, SlotAvailabilityService $availability, BookingCalendarService $calendar)
    {
        $this->owned($appointment);
        $data=$request->validate(['start'=>['required','date_format:Y-m-d'],'end'=>['required','date_format:Y-m-d','after_or_equal:start']]);
        $start=Carbon::parse($data['start'])->max(today()); $end=Carbon::parse($data['end']);
        abort_if($start->diffInDays($end) > 42, 422, 'Range is too large.');
        $limit=today()->addDays(max(0,(int)($calendar->bookingLimit()['days']??30))); $end=$end->min($limit);
        $days=[];
        for($day=$start->copy();$day->lte($end);$day->addDay()){
            $slots=$availability->slots($day->toDateString(),$appointment->service_id,$appointment->user_id,$appointment->id);
            if($slots!==[])$days[$day->toDateString()]=count($slots);
        }
        return response()->json(['days'=>$days,'limit_date'=>$limit->toDateString()]);
    }

    public function slots(Request $request, Appointments $appointment, SlotAvailabilityService $availability)
    {
        $this->owned($appointment); $data=$request->validate(['date'=>['required','date_format:Y-m-d','after_or_equal:today']]);
        return response()->json(['slots'=>$availability->slots($data['date'],$appointment->service_id,$appointment->user_id,$appointment->id)]);
    }

    public function store(Request $request, Appointments $appointment, AppointmentRescheduleService $service)
    {
        $data=$request->validate(['date'=>['required','date_format:Y-m-d','after_or_equal:today'],'start'=>['required','date_format:H:i'],'end'=>['required','date_format:H:i','after:start'],'reason'=>['nullable','string','max:500']]);
        $service->request($appointment,Auth::guard('customer')->user(),$data['date'],$data['start'],$data['end'],$data['reason']??null);
        return redirect()->route('customer.dashboard')->with('status',__('customer.reschedule_requested'));
    }

    private function owned(Appointments $appointment): void { if($appointment->customer_id!==Auth::guard('customer')->id())abort(404); }
}
