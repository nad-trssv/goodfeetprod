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
            'start_time' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'end_time' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'repeat' => 'required|boolean',
        ]);

        $startTime = $request->start_time ? substr($request->start_time, 0, 5) : null;
        $endTime = $request->end_time ? substr($request->end_time, 0, 5) : null;

        if ($startTime && $endTime) {
            if (strtotime($endTime) <= strtotime($startTime)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Время окончания должно быть позже времени начала'
                ], 422);
            }
        }

        $fullDay = !$startTime || !$endTime;

        $redDay = RedDay::create([
            'name' => $request->name,
            'date' => $request->date,
            'description' => $request->description,
            'start_time' => $fullDay ? null : $startTime,
            'end_time' => $fullDay ? null : $endTime,
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
            'start_time' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'end_time' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'repeat' => 'required|boolean',
        ]);

        $startTime = $request->start_time ? substr($request->start_time, 0, 5) : null;
        $endTime = $request->end_time ? substr($request->end_time, 0, 5) : null;

        if ($startTime && $endTime) {
            if (strtotime($endTime) <= strtotime($startTime)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Время окончания должно быть позже времени начала'
                ], 422);
            }
        }

        $fullDay = !$startTime || !$endTime;

        $redDay->update([
            'name' => $request->name,
            'date' => $request->date,
            'start_time' => $fullDay ? null : $startTime,
            'end_time' => $fullDay ? null : $endTime,
            'full_day' => $fullDay,
            'repeat' => $request->repeat,
        ]);

        return response()->json(['status' => 'success', 'redDay' => $redDay->fresh()]);
    }

    public function allRedDays(Request $request)
    {
        $redDays = RedDay::with('user')
            ->orderByDesc('date')
            ->get()
            ->map(function ($day) {
                return [
                    'id' => $day->id,
                    'name' => $day->name,
                    'date' => \Carbon\Carbon::parse($day->date)->format('Y-m-d'),
                    'start_time' => $day->start_time ? substr($day->start_time, 0, 5) : null,
                    'end_time' => $day->end_time ? substr($day->end_time, 0, 5) : null,
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
            'full_day' => 'nullable|boolean',
            'start_time' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'end_time' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'repeat' => 'nullable|boolean',
        ], [
            'name.required' => 'Заполните название',
            'date.required' => 'Заполните дату',
        ]);

        $fullDay = $request->has('full_day') && $request->full_day == '1';
        $startTime = $request->start_time ? substr($request->start_time, 0, 5) : null;
        $endTime = $request->end_time ? substr($request->end_time, 0, 5) : null;

        if (!$fullDay) {
            if (!$startTime || !$endTime) {
                return redirect()->route('admin.red-days.index')
                    ->with('error', 'Укажите время начала и конца');
            }
            if (strtotime($endTime) <= strtotime($startTime)) {
                return redirect()->route('admin.red-days.index')
                    ->with('error', 'Время окончания должно быть позже времени начала');
            }
        }

        RedDay::create([
            'name' => $request->name,
            'date' => $request->date,
            'user_id' => $request->user_id ?: null,
            'full_day' => $fullDay,
            'start_time' => $fullDay ? null : $startTime,
            'end_time' => $fullDay ? null : $endTime,
            'repeat' => $request->has('repeat') ? 1 : 0,
        ]);

        return redirect()->route('admin.red-days.index')
            ->with('success', 'Запись добавлена!');
    }
    public function timeOff(Request $request)
    {
        $user = Auth::user();
        $redDays = RedDay::where(function ($query) use ($user) {
            $query->where('user_id', $user->id)
                ->orWhereNull('user_id');
        })->orderByDesc('date')->get();

        $success = session('success');
        $error = session('error');
        $editId = $request->query('edit');
        $editDay = $editId ? RedDay::find($editId) : null;

        return view('admin.master.time-off', [
            'redDays' => $redDays,
            'user' => $user,
            'success' => $success,
            'error' => $error,
            'editDay' => $editDay,
        ]);
    }

    public function storeTimeOff(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string',
            'date' => 'required|date',
            'full_day' => 'nullable|boolean',
            'start_time' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'end_time' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'repeat' => 'nullable|boolean',
        ], [
            'name.required' => 'Заполните название',
            'date.required' => 'Заполните дату',
        ]);

        $fullDay = $request->has('full_day') && $request->full_day == '1';
        $startTime = $request->start_time ? substr($request->start_time, 0, 5) : null;
        $endTime = $request->end_time ? substr($request->end_time, 0, 5) : null;

        if (!$fullDay) {
            if (!$startTime || !$endTime) {
                return redirect()->route('master.time-off.index')
                    ->with('error', 'Укажите время начала и конца');
            }
            if (strtotime($endTime) <= strtotime($startTime)) {
                return redirect()->route('master.time-off.index')
                    ->with('error', 'Время окончания должно быть позже времени начала');
            }
        }

        RedDay::create([
            'name' => $request->name,
            'date' => $request->date,
            'user_id' => $user->id,
            'full_day' => $fullDay,
            'start_time' => $fullDay ? null : $startTime,
            'end_time' => $fullDay ? null : $endTime,
            'repeat' => $request->has('repeat') ? 1 : 0,
        ]);

        return redirect()->route('master.time-off.index')
            ->with('success', 'Запись добавлена!');
    }

    public function updateTimeOff(Request $request, string $id)
    {
        $user = Auth::user();
        if ($user->role_id == 1) {
            $redDay = RedDay::where('id', $id)->firstOrFail();
        } else {
            $redDay = RedDay::where('id', $id)->where('user_id', $user->id)->firstOrFail();
        }

        $request->validate([
            'name' => 'required|string',
            'date' => 'required|date',
            'full_day' => 'nullable|boolean',
            'start_time' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'end_time' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'repeat' => 'nullable|boolean',
        ], [
            'name.required' => 'Заполните название',
            'date.required' => 'Заполните дату',
        ]);

        $fullDay = $request->has('full_day') && $request->full_day == '1';
        $startTime = $request->start_time ? substr($request->start_time, 0, 5) : null;
        $endTime = $request->end_time ? substr($request->end_time, 0, 5) : null;

        if (!$fullDay) {
            if (!$startTime || !$endTime) {
                return redirect()->route('master.time-off.index')
                    ->with('error', 'Укажите время начала и конца');
            }
            if (strtotime($endTime) <= strtotime($startTime)) {
                return redirect()->route('master.time-off.index')
                    ->with('error', 'Время окончания должно быть позже времени начала');
            }
        }

        $redDay->update([
            'name' => $request->name,
            'date' => $request->date,
            'full_day' => $fullDay,
            'start_time' => $fullDay ? null : $startTime,
            'end_time' => $fullDay ? null : $endTime,
            'repeat' => $request->has('repeat') ? 1 : 0,
        ]);

        return redirect()->route('master.time-off.index')
            ->with('success', 'Запись обновлена!');
    }

    public function destroyTimeOff(string $id)
    {
        $user = Auth::user();
        if ($user->role_id == 1) {
            $redDay = RedDay::where('id', $id)->firstOrFail();
        } else {
            $redDay = RedDay::where('id', $id)->where('user_id', $user->id)->firstOrFail();
        }
        $redDay->delete();

        return redirect()->route('master.time-off.index')
            ->with('success', 'Запись удалена!');
    }
    public function updateRedDayForMaster(Request $request, string $id)
    {
        $redDay = RedDay::findOrFail($id);

        $request->validate([
            'name' => 'required|string',
            'date' => 'required|date',
            'full_day' => 'nullable|boolean',
            'start_time' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'end_time' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'repeat' => 'nullable|boolean',
        ], [
            'name.required' => 'Заполните название',
            'date.required' => 'Заполните дату',
        ]);

        $fullDay = $request->has('full_day') && $request->full_day == '1';
        $startTime = $request->start_time ? substr($request->start_time, 0, 5) : null;
        $endTime = $request->end_time ? substr($request->end_time, 0, 5) : null;

        if (!$fullDay) {
            if (!$startTime || !$endTime) {
                return redirect()->route('admin.red-days.index')
                    ->with('error', 'Укажите время начала и конца');
            }
            if (strtotime($endTime) <= strtotime($startTime)) {
                return redirect()->route('admin.red-days.index')
                    ->with('error', 'Время окончания должно быть позже времени начала');
            }
        }

        $redDay->update([
            'name' => $request->name,
            'date' => $request->date,
            'full_day' => $fullDay,
            'start_time' => $fullDay ? null : $startTime,
            'end_time' => $fullDay ? null : $endTime,
            'repeat' => $request->has('repeat') ? 1 : 0,
        ]);

        return redirect()->route('admin.red-days.index')
            ->with('success', 'Запись обновлена!');
    }

    public function destroyRedDayForMaster(string $id)
    {
        $redDay = RedDay::findOrFail($id);
        $redDay->delete();

        return redirect()->route('admin.red-days.index')
            ->with('success', 'Запись удалена!');
    }
}
