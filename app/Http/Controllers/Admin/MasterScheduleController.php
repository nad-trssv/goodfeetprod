<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RedDay;
use App\Models\SiteSettings;
use App\Models\UserSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MasterScheduleController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $schedule = UserSchedule::where('user_id', $user->id)->first();

        // Глобальный лимит дней
        $bookingLimit = SiteSettings::where('group', 'hours')
            ->where('key', 'booking_date_limit')
            ->first();
        $bookingLimit = $bookingLimit ? json_decode($bookingLimit->payload, true) : ['days' => 30, 'active' => false];

        // Глобальное фиксированное время
        $fixedBooking = SiteSettings::where('group', 'hours')
            ->where('key', 'fixed_booking_hours')
            ->first();
        $fixedBooking = $fixedBooking ? json_decode($fixedBooking->payload, true) : ['value' => false, 'payload' => []];

        // Индивидуальные нерабочие дни мастера
        $redDays = RedDay::where('user_id', $user->id)->orderByDesc('date')->get();

        return view('admin.master.schedule', [
            'schedule' => $schedule,
            'bookingLimit' => $bookingLimit,
            'fixedBooking' => $fixedBooking,
            'redDays' => $redDays,
        ]);
    }

    public function updateWorkHours(Request $request)
    {
        $user = Auth::user();

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $data = ['user_id' => $user->id];

        foreach ($days as $day) {
            $isOff = $request->input($day . '_off') == '1';
            $data[$day . '_start'] = $isOff ? null : $request->input($day . '_start');
            $data[$day . '_end'] = $isOff ? null : $request->input($day . '_end');
        }

        UserSchedule::updateOrCreate(
            ['user_id' => $user->id],
            $data
        );

        return response()->json(['status' => 'success']);
    }

    public function updateLunchHours(Request $request)
    {
        $user = Auth::user();

        UserSchedule::updateOrCreate(
            ['user_id' => $user->id],
            [
                'lunch_start' => $request->lunch_start ?: null,
                'lunch_end' => $request->lunch_end ?: null,
            ]
        );

        return response()->json(['status' => 'success']);
    }

    public function storeRedDay(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string',
            'date' => 'required|date_format:Y-m-d',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|required_with:start_time|after:start_time',
            'repeat' => 'required|boolean',
        ]);

        $fullDay = !$request->start_time || !$request->end_time;

        $redDay = RedDay::create([
            'name' => $request->name,
            'date' => $request->date,
            'description' => $request->description,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'full_day' => $fullDay,
            'repeat' => $request->repeat,
            'user_id' => $user->id,
        ]);

        return response()->json([
            'status' => 'success',
            'redDay' => $redDay,
        ]);
    }

    public function updateFixedBooking(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'fixed_booking_enabled' => 'required|boolean',
            'fixed_booking_slots' => 'nullable|array',
            'fixed_booking_slots.*' => 'date_format:H:i',
        ]);

        UserSchedule::updateOrCreate(
            ['user_id' => $user->id],
            [
                'fixed_booking_enabled' => $request->fixed_booking_enabled,
                'fixed_booking_slots' => $request->fixed_booking_slots ?? [],
            ]
        );

        return response()->json(['status' => 'success']);
    }
    
    public function destroyRedDay(string $id)
    {
        $user = Auth::user();
        $redDay = RedDay::where('id', $id)->where('user_id', $user->id)->firstOrFail();
        $redDay->delete();

        return response()->json(['status' => 'success']);
    }
}