<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminSlotHoldRequest;
use App\Services\Booking\AppointmentSlotHoldService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class AppointmentSlotHoldController extends Controller
{
    public function store(AdminSlotHoldRequest $request, AppointmentSlotHoldService $holds): JsonResponse
    {
        try {
            $hold = $holds->hold(
                $request->user(),
                $request->integer('service_id'),
                $request->integer('user_id'),
                $request->validated('appointment_start'),
                $request->validated('appointment_end'),
                $request->validated('token'),
            );

            return response()->json(['token' => $hold->token, 'expires_at' => $hold->expires_at->toIso8601String()]);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 409);
        }
    }

    public function renew(Request $request, string $token, AppointmentSlotHoldService $holds): JsonResponse
    {
        try {
            $hold = $holds->renew($request->user(), $token);

            return response()->json(['token' => $hold->token, 'expires_at' => $hold->expires_at->toIso8601String()]);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 409);
        }
    }

    public function destroy(Request $request, string $token, AppointmentSlotHoldService $holds): JsonResponse
    {
        $holds->release($request->user(), $token);

        return response()->json([], 204);
    }
}
