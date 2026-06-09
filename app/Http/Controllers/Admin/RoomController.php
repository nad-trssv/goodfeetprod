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
        $masters = User::whereIn('role_id', [1, 2])->orderBy('name')->get();
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
}