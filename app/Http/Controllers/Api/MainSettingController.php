<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MainSettingResource;
use App\Http\Requests\Settings\LimitDaysRequest;
use App\Models\SiteSettings;

class MainSettingController extends Controller
{
    public function mainSettings()
    {
        $data = SiteSettings::orderByDesc('id')
            ->whereIn('group', ['company', 'map', 'social_media', 'license'])
            ->get();
            
        $collection = MainSettingResource::collection($data);

        return response()->json([
            'mainSettings' => $collection
        ]);
    }

    public function updateLimitDays(LimitDaysRequest $request) 
    {
        $data = $request->validated();
        $response = SiteSettings::where('key', 'booking_date_limit')
            ->update([
                'payload' => json_encode([
                    'days' => $data['days'],
                    'active' => filter_var($data['active'], FILTER_VALIDATE_BOOLEAN)
                ])
            ]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Лимит дней успешно обновлен'
        ]);
    }
}
