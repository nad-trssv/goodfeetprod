<?php

namespace App\Services;

use App\Models\RedDay;
use App\Models\SiteSettings;

class SettingService
{
    public $setting;

    public function list(){
        function getWorkHours(){
            $siteSettings = SiteSettings::where('group', 'hours')
            ->where('key', 'work_hours')
            ->first();
    
            if ($siteSettings) {
                return json_decode($siteSettings->payload, true);
            }
        }
        function getLunchHours(){
            $siteSettings = SiteSettings::where('group', 'hours')
            ->where('key', 'lunch_hours')
            ->first();
    
            if ($siteSettings) {
                return json_decode($siteSettings->payload, true);
            }
        }
        function getBookingLimit() {
            $siteSettings = SiteSettings::where('group', 'hours')
            ->where('key', 'booking_date_limit')
            ->first();
    
            if ($siteSettings) {
                return json_decode($siteSettings->payload, true);
            }
        }

        return[
            'workHours' => getWorkHours(),
            'lunchHours' => getLunchHours(),
            'bookingLimit' => getBookingLimit(),
        ];
    }
    public function updateHours($request)
    {
        $siteSettings = SiteSettings::where('group', 'hours')
                            ->where('key', 'work_hours')
                            ->first();
                            
        if ($siteSettings) {             
            $workHours = json_decode($siteSettings->payload, true);       
            $workHours = [
                'monday' => ['start' => $request->monday_start, 'end' => $request->monday_end],
                'tuesday' => ['start' => $request->tuesday_start, 'end' => $request->tuesday_end],
                'wednesday' => ['start' => $request->wednesday_start, 'end' => $request->wednesday_end],
                'thursday' => ['start' => $request->thursday_start, 'end' => $request->thursday_end],
                'friday' => ['start' => $request->friday_start, 'end' => $request->friday_end],
                'saturday' => ['start' => $request->saturday_start, 'end' => $request->saturday_end],
                'sunday' => ['start' => $request->sunday_start, 'end' => $request->sunday_end],
            ];      
            $siteSettings->payload = json_encode($workHours);
            $this->setting = $siteSettings->save();
            return $this->setting;
        }
    }

    public function updateLunchHours($request)
    {
        $lunchTimeSettings = SiteSettings::where('group', 'hours')->where('key', 'lunch_hours')->first();
        if ($lunchTimeSettings) { 
            $lunchHours = json_decode($lunchTimeSettings->payload, true); 
            $lunchHours = ['start' => $request->lunch_start, 'end' => $request->lunch_end];
            $lunchTimeSettings->payload = json_encode($lunchHours);
            $this->setting = $lunchTimeSettings->save();
            return $this->setting;
        }  
    }
    
    public function storeRedDay($request)
    {
        $full_day = false;
        if ($request['start_time'] == null || $request['end_time'] == null) {
            $full_day = true;
        }

        $this->setting = RedDay::create([
            'name' => $request['name'],
            'description' => $request['description'],
            'date' => $request['date'],
            'start_time' => $request['start_time'],
            'end_time' => $request['end_time'],
            'full_day' => $full_day,
            'repeat' => $request['repeat'],
            'user_id' => $request['user_id'] ?? null,
        ]);

        return $this->setting;
    }

    public function updateFixedBooking($request)
    {
        $fixedBookingSettings = SiteSettings::where('group', 'hours')->where('key', 'fixed_booking_hours')->first();
        if ($fixedBookingSettings) { 
            $hours = $request['fixedBooking'];
            $status = $request['fixedBookingStatus'];

            $data = [
                "value" => $status,
                "payload" => $hours
            ];
            $fixedBookingSettings->payload = $data;
            $this->setting = $fixedBookingSettings->save();
    
            return $this->setting;
        }  
    }

    public function updateMainSettings($request)
    {
        foreach ($request->all() as $key => $value) {
            SiteSettings::where('key', $key)
                ->update(['payload' => json_encode($value, JSON_UNESCAPED_UNICODE)]);
        }
        return $this->setting;
    }
}