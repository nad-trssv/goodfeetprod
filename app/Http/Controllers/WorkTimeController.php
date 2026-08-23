<?php

namespace App\Http\Controllers;

use App\Services\WorkTimeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkTimeController extends Controller
{
    public function status(Request $request, WorkTimeService $service): JsonResponse
    {
        return response()->json($service->state($request->user()));
    }

    public function start(Request $request, WorkTimeService $service): JsonResponse
    {
        return response()->json($service->start($request->user()), 201);
    }

    public function pause(Request $request, WorkTimeService $service): JsonResponse
    {
        return response()->json($service->pause($request->user()));
    }

    public function resume(Request $request, WorkTimeService $service): JsonResponse
    {
        return response()->json($service->resume($request->user()));
    }

    public function stop(Request $request, WorkTimeService $service): JsonResponse
    {
        return response()->json($service->stop($request->user()));
    }
}
