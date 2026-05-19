<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SiteSettings;
use Illuminate\Http\Request;

class FixedBookingController extends Controller
{
    public function getFixedBooking()
    {
        $data = SiteSettings::where('group', 'hours')->where('key', 'fixed_booking_hours')->value('payload');
        $dataArray = json_decode($data, true);
        $payload = $dataArray['payload'];
        $status = $dataArray['value'];

        return response()->json([
            'fixedBooking' => $payload,
            'fixedBookingStatus' => $status,
        ]);
    }
}
