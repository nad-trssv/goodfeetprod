<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Resources\RedDayResource;
use App\Models\RedDay;
use Illuminate\Http\Request;

class RedDayController extends Controller
{
    public function getRedDays()
    {
        $data = RedDay::with('user')->orderByDesc('id')->get();
        $redDays = RedDayResource::collection($data);
        $data_fullday = RedDay::with('user')->where('full_day', 1)->orderByDesc('id')->get();
        $fullRedDays = RedDayResource::collection($data_fullday);
        return response()->json([
            'fullRedDays' => $fullRedDays,
            'redDays' => $redDays
        ]);
    }

    public function update(Request $request, RedDay $id)
    {
        $request->validate([
            'name' => 'required|string',
            'date' => 'required|date_format:Y-m-d',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'repeat' => 'required|boolean',
        ]);

        $fullDay = !$request->start_time || !$request->end_time;

        $id->update([
            'name' => $request->name,
            'date' => $request->date,
            'start_time' => $fullDay ? null : $request->start_time,
            'end_time' => $fullDay ? null : $request->end_time,
            'full_day' => $fullDay,
            'repeat' => $request->repeat,
            'description' => $request->description,
        ]);

        return response()->json([
            'status' => 'success',
            'redDay' => new RedDayResource($id->fresh()->load('user')),
        ]);
    }

    public function destroy(RedDay $id)
    {
        try {
            $id->delete();
            return response()->json(['status' => 'success', 'message' => 'Record deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}