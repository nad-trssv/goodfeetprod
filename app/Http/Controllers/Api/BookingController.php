<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookingRequest;
use App\Http\Requests\getFullyBookedRequest;
use App\Mail\BookingAdminMail;
use App\Services\Api\GetFullyBookedService;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingMail;
use App\Models\Appointments;
use App\Models\Services;
use App\Models\SiteSettings;
use GuzzleHttp\Psr7\Request;

class BookingController extends Controller
{
    private GetFullyBookedService $bookedService;

    public function __construct(GetFullyBookedService $bookedService)
    {
        $this->bookedService = $bookedService;
    }

    public function getFullyBooked(getFullyBookedRequest $request) 
    {
        $data = $this->bookedService->getList($request);
        return $data['slots'];
    }
    
    public function getBusyDays(getFullyBookedRequest $request) 
    {
        $data = $this->bookedService->getBusyDays($request);
        return $data;
    }

    public function sendEmail($email, $service_name, $client_name, $client_email, $client_phone, $mastername, $booking_date, $booking_start, $booking_end, $company_address, $price_can_change, $company_phone, $company_email, $price){
        Mail::to($email)->send(new BookingMail($email, $service_name, $mastername, $booking_date, $booking_start, $booking_end, $company_address, $price_can_change, $company_phone, $company_email, $price));
        Mail::to('goodfeet.ee@gmail.com')->send(new BookingAdminMail($email, $service_name, $client_name, $client_email, $client_phone, $mastername, $booking_date, $booking_start, $booking_end, $company_address, $price_can_change, $company_phone, $company_email, $price));
        return true;
    }

    public function storeBooking(BookingRequest $request) 
    {
        $response = $this->bookedService->store($request);
        $data = $response->getData(true);

        if (isset($data['appointmentId'])) {
            $appointmentId = $data['appointmentId'];
            $settings = $this->getSettings();
            $locale = app()->getLocale();

            $service = Services::with(['translations', 'rules'])->find($request->service_id);
            $service->translation = $service->translations()->where('locale', $locale)->first();

            $booking_date = $request->choose_date;
            $effectivePrice = $service->effectivePriceForDate($booking_date);

            $email = $request->client_email;
            $mastername = Appointments::find($appointmentId)->user->name;
            $client_name = $request->client_name . ' ' . $request->client_lastname;
            $client_email = $request->client_email;
            $client_phone = $request->client_phone;
            $booking_start = $request->appointment_start;
            $booking_end = $request->appointment_end;
            $company_address = $settings['company_address'];
            $price_can_change = $service['price_can_change'];
            $company_phone = $settings['company_phone'];
            $company_email = $settings['company_email'];
            $price = $effectivePrice;
            $service_name = $service->translation ? $service->translation['name'] : $service->name;
            
            $this->sendEmail(
                $email,
                $service_name,
                $client_name,
                $client_email,
                $client_phone,
                $mastername,
                $booking_date,
                $booking_start,
                $booking_end,
                $company_address,
                $price_can_change,
                $company_phone,
                $company_email,
                $price
            );

            $redirectUrl = route('booking.show', [$data['appointmentId']]);

            return response()->json([
                'redirectUrl' => $redirectUrl,
            ]);
        } else {
            return response()->json([
                'error' => $data['error'],
            ], 404);
        }
    }

    function getSettings(){
        $siteSettings = SiteSettings::where('group', 'company')->get();
        if ($siteSettings) {
            $formattedSettings = $siteSettings->pluck('payload', 'key')->map(function ($value) {
                return trim($value, '"');
            })->toArray();
    
            return $formattedSettings;
        }
    
        return [];
    }
}
