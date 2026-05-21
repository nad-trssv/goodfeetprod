<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Services;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class MasterServiceController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $myServiceIds = $user->services->pluck('id')->toArray();

        $allServices = Services::where('status', 1)
            ->where('is_deleted', 0)
            ->orderBy('name')
            ->get();

        return view('admin.master.services', [
            'allServices' => $allServices,
            'myServiceIds' => $myServiceIds,
        ]);
    }

    public function toggle(Services $service)
    {
        $user = Auth::user();

        if ($user->services->contains($service->id)) {
            $user->services()->detach($service->id);
            $attached = false;
        } else {
            $user->services()->attach($service->id);
            $attached = true;
        }

        return response()->json([
            'status' => 'success',
            'attached' => $attached,
        ]);
    }
}