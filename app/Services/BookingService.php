<?php

namespace App\Services;

use App\Models\Appointments;
use App\Models\RedDay;
use App\Models\Services;
use App\Models\SiteSettings;
use App\Models\User;

class BookingService
{
    /**
     * Create a new class instance.
     */
    public function getFullyBooked($request) {
        $fixedDateTime = SiteSettings::where('group', 'hours')->where('key', 'fixed_booking_hours')->first();
        $fixedDateTime = json_decode($fixedDateTime['payload'])->payload;
        $redDays = RedDay::all();


        $appointments = Appointments::all();
        $users = User::all();
        $redDays = RedDay::all();
        $services = Services::with('users')->where('is_deleted', 0)->get();
        $deletedServices = Services::with('users')->where('is_deleted', 1)->get();

        $events = array();
        foreach($appointments as $appint)
        {
            $events[] = [
                'id' => $appint->id,
                'title' => $appint->service->name,
                'user_id' => $appint->user_id,
                'client_phone' => $appint->client_phone,
                'client_name' => $appint->client_name,
                'client_lastname' => $appint->client_lastname,
                'service_id' => $appint->service_id,
                'textColor' => $appint->service->eventColor,
                'description' => $appint->description,
                'price' => $appint->price,
                'start' => $appint->appointment_start,
                'end' => $appint->appointment_end,
            ];
        }
        return [
            'fixedDateTime' => $fixedDateTime,
            'redDays' => $redDays,
        ];
    }
}
