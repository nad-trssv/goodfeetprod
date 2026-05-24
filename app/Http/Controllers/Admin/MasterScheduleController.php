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

        $bookingLimit = SiteSettings::where('group', 'hours')
            ->where('key', 'booking_date_limit')
            ->first();
        $bookingLimit = $bookingLimit ? json_decode($bookingLimit->payload, true) : ['days' => 30, 'active' => false];

        $fixedBooking = SiteSettings::where('group', 'hours')
            ->where('key', 'fixed_booking_hours')
            ->first();
        $fixedBooking = $fixedBooking ? json_decode($fixedBooking->payload, true) : ['value' => false, 'payload' => []];

        $redDays = RedDay::where('user_id', $user->id)
            ->orderByDesc('date')
            ->get();

        return view('admin.master.schedule', [
            'schedule' => $schedule,
            'bookingLimit' => $bookingLimit,
            'fixedBooking' => $fixedBooking,
            'redDays' => $redDays,
            'user' => $user,
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

    public function updateRedDay(Request $request, string $id)
    {
        $user = Auth::user();
        $redDay = RedDay::where('id', $id)->where('user_id', $user->id)->firstOrFail();

        $request->validate([
            'name' => 'required|string',
            'date' => 'required|date_format:Y-m-d',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'repeat' => 'required|boolean',
        ]);

        $fullDay = !$request->start_time || !$request->end_time;

        $redDay->update([
            'name' => $request->name,
            'date' => $request->date,
            'start_time' => $fullDay ? null : $request->start_time,
            'end_time' => $fullDay ? null : $request->end_time,
            'full_day' => $fullDay,
            'repeat' => $request->repeat,
        ]);

        return response()->json(['status' => 'success', 'redDay' => $redDay->fresh()]);
    }

    public function allRedDays()
    {
        $redDays = RedDay::with('user')
            ->orderByDesc('date')
            ->get()
            ->map(function ($day) {
                return [
                    'id' => $day->id,
                    'name' => $day->name,
                    'date' => \Carbon\Carbon::parse($day->date)->format('Y-m-d'),
                    'start_time' => $day->start_time,
                    'end_time' => $day->end_time,
                    'full_day' => $day->full_day,
                    'repeat' => $day->repeat,
                    'master_name' => $day->user ? $day->user->name : 'Общий',
                    'user_id' => $day->user_id,
                ];
            });

        return view('admin.red-days.index', [
            'redDays' => $redDays,
        ]);
    }
    public function storeRedDayForMaster(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'date' => 'required|date',
            'user_id' => 'nullable|exists:users,id',
            'full_day' => 'required|boolean',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'repeat' => 'required|boolean',
        ]);

        $redDay = RedDay::create([
            'name' => $request->name,
            'date' => $request->date,
            'user_id' => $request->user_id ?: null,
            'full_day' => $request->full_day,
            'start_time' => $request->full_day ? null : $request->start_time,
            'end_time' => $request->full_day ? null : $request->end_time,
            'repeat' => $request->repeat,
        ]);

        $redDay->load('user');

        return response()->json([
            'status' => 'success',
            'redDay' => [
                'id' => $redDay->id,
                'name' => $redDay->name,
                'date' => \Carbon\Carbon::parse($redDay->date)->format('Y-m-d'),
                'full_day' => $redDay->full_day,
                'start_time' => $redDay->start_time,
                'end_time' => $redDay->end_time,
                'repeat' => $redDay->repeat,
                'user_id' => $redDay->user_id,
                'master_name' => $redDay->user ? $redDay->user->name : 'Общий',
            ]
        ]);
    }
}
