<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::with('users')->orderBy('name')->get();
        $masters = User::query()->whereHas('role', fn ($query) => $query->where('is_service_provider', true)->orWhereIn('id', [1, 2]))->orderBy('name')->get();
        return view('admin.rooms.index', compact('rooms', 'masters'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        $room = Room::create([
            'name' => $request->name,
            'capacity' => $request->capacity,
            'description' => $request->description,
            'is_active' => 1,
        ]);

        if ($request->user_ids) {
            $room->users()->sync($request->user_ids);
        }

        return redirect()->route('admin.rooms.index')->with('success', 'Кабинет добавлен!');
    }

    public function update(Request $request, string $id)
    {
        $room = Room::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        $room->update([
            'name' => $request->name,
            'capacity' => $request->capacity,
            'description' => $request->description,
        ]);

        $room->users()->sync($request->user_ids ?? []);

        return redirect()->route('admin.rooms.index')->with('success', 'Кабинет обновлён!');
    }

    public function destroy(string $id)
    {
        Room::findOrFail($id)->delete();
        return redirect()->route('admin.rooms.index')->with('success', 'Кабинет удалён!');
    }

    public function toggleActive(string $id)
    {
        $room = Room::findOrFail($id);
        $room->update(['is_active' => !$room->is_active]);
        return redirect()->route('admin.rooms.index')->with('success', 'Статус обновлён!');
    }
    
    public function today(Request $request, $date = null)
    {
        $today = $date ? \Carbon\Carbon::parse($date) : \Carbon\Carbon::now();
        $todayStr = $today->format('Y-m-d');

        $rooms = Room::with(['users.schedule'])->where('is_active', 1)->orderBy('name')->get()->map(function ($room) use ($today, $todayStr) {

            // Записи в этом кабинете сегодня
            $appointments = \App\Models\Appointments::with(['service', 'user'])
                ->where(function($q) use ($room) {
                    $q->where('room_id', $room->id)
                    ->orWhere(function($q2) use ($room) {
                        $q2->whereNull('room_id')
                            ->whereHas('user.rooms', function($q3) use ($room) {
                                $q3->where('rooms.id', $room->id);
                            });
                    });
                })
                ->whereDate('appointment_start', $todayStr)
                ->orderBy('appointment_start')
                ->get();

            // Считаем загрузку по слотам
            $slotGroups = [];
            foreach ($appointments as $apt) {
                $start = \Carbon\Carbon::parse($apt->appointment_start)->format('H:i');
                $end = \Carbon\Carbon::parse($apt->appointment_end)->format('H:i');
                $key = $start . '-' . $end;
                if (!isset($slotGroups[$key])) {
                    $slotGroups[$key] = [
                        'start' => $start,
                        'end' => $end,
                        'appointments' => [],
                        'is_full' => false,
                    ];
                }
                $slotGroups[$key]['appointments'][] = $apt;
                if (count($slotGroups[$key]['appointments']) >= $room->capacity) {
                    $slotGroups[$key]['is_full'] = true;
                }
            }

            ksort($slotGroups);

            return [
                'id' => $room->id,
                'name' => $room->name,
                'capacity' => $room->capacity,
                'description' => $room->description,
                'masters_count' => $room->users->count(),
                'appointments_count' => $appointments->count(),
                'slot_groups' => array_values($slotGroups),
            ];
        });

        return view('admin.rooms.today', [
            'rooms' => $rooms,
            'today' => $today,
        ]);
    }
}
