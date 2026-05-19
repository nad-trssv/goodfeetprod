<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RedDayResource;
use App\Models\RedDay;

class RedDayController extends Controller
{
    public function getRedDays()
    {
        $data = RedDay::orderByDesc('id')->get();
        $redDays = RedDayResource::collection($data);
        $data_fullday = RedDay::orderByDesc('id')->where('full_day', 1)->get();
        $fullRedDays = RedDayResource::collection($data_fullday);

        return response()->json([
            'fullRedDays' => $fullRedDays,
            'redDays' => $redDays
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
