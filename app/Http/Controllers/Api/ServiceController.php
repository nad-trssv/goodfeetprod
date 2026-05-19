<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Services;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function effective(Request $request)
    {
        $request->validate([
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'date'       => ['required', 'date'],
        ]);

        $service = Services::findOrFail($request->service_id);
        $date    = $request->date;

        $rule = $service->ruleForDate($date);

        $duration = $rule && $rule->duration_minutes
            ? (int) $rule->duration_minutes
            : (int) $service->duration_minutes;

        $price = $service->effectivePriceForDate($date);

        return response()->json([
            'duration_minutes' => $duration,
            'price'            => $price,
            'price_can_change' => (int) $service->price_can_change,
        ]);
    }
}
