<?php

namespace App\Http\Controllers;

use App\Http\Resources\ServiceResource;
use App\Mail\BookingMail;
use App\Models\Appointments;
use App\Models\RedDay;
use App\Models\Services;
use App\Models\SiteSettings;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $redDays = RedDay::where('full_day', 1)->get();
        $redDaysTime = RedDay::where('full_day', 0)->get();
        $locale = app()->getLocale();
        $today = Carbon::now()->toDateString();

        $services = Services::with(['users', 'rules', 'futureRules', 'translations'])
            ->where('status', 1)
            ->where('is_deleted', 0)
            ->get()
            ->map(function ($service) use ($locale, $today) {
                $service->translation = $service->translations
                    ->where('locale', $locale)
                    ->first();

                $ruleToday = $service->ruleForDate($today);

                $service->effective_price = $service->effectivePriceForDate($today);
                $service->effective_duration_minutes = $ruleToday?->duration_minutes ?? $service->duration_minutes;

                $service->next_rule = $service->futureRules->sortBy('valid_from')->first();

                return $service;
            });

        $days = (int) $this->bookLimit();
        $today = Carbon::now()->format('Y-m-d');
        $limitDate = Carbon::now()->addDays($days)->format('Y-m-d');

        $formattedServices = ServiceResource::collection($services)->toArray(request());
        
        return view('pages.booking.index', [
            'chooseService' => null,
            'services' => $formattedServices,
            'redDays' => $redDays,
            'redDaysTime' => $redDaysTime,
            'today' => $today,
            'limitDate' => $limitDate,
            'bookLimit' => $days,
            'workHours' => $this->getWorkHours(),
            'settings' => $this->getSettings(),
        ]);
    }

    protected function bookLimit(): int
    {
        $siteSettings = SiteSettings::where('group', 'hours')
            ->where('key', 'booking_date_limit')
            ->first();

        if ($siteSettings) {
            return (int) json_decode($siteSettings->payload, true)['days'];
        }

        return 30; 
    }


    public function serviceBooking(Request $request)
    {
        $chooseServiceId = $request->query('service');
        $locale = app()->getLocale();
        $today = Carbon::now()->toDateString();

        $chooseService = null;
        if ($chooseServiceId) {
            $chooseService = Services::with(['users', 'rules', 'futureRules', 'translations'])
                ->find($chooseServiceId);

            if ($chooseService) {
                $chooseService->translation = $chooseService->translations
                    ->where('locale', $locale)
                    ->first();

                $ruleToday = $chooseService->ruleForDate($today);
                $chooseService->effective_price = $chooseService->effectivePriceForDate($today);
                $chooseService->effective_duration_minutes = $ruleToday?->duration_minutes ?? $chooseService->duration_minutes;
                $chooseService->next_rule = $chooseService->futureRules->sortBy('valid_from')->first();
            }
        }

        $redDays = RedDay::where('full_day', 1)->get();
        $redDaysTime = RedDay::where('full_day', 0)->get();

        $services = Services::with(['users', 'rules', 'futureRules', 'translations'])
            ->where('status', 1)
            ->where('is_deleted', 0)
            ->get()
            ->map(function ($service) use ($locale, $today) {
                $service->translation = $service->translations
                    ->where('locale', $locale)
                    ->first();

                $ruleToday = $service->ruleForDate($today);
                $service->effective_price = $service->effectivePriceForDate($today);
                $service->effective_duration_minutes = $ruleToday?->duration_minutes ?? $service->duration_minutes;
                $service->next_rule = $service->futureRules->sortBy('valid_from')->first();

                return $service;
            });

        $days = (int) $this->bookLimit();
        $today = Carbon::now()->format('Y-m-d');
        $limitDate = Carbon::now()->addDays($days)->format('Y-m-d');
        $formattedServices = ServiceResource::collection($services)->toArray(request());

        return view('pages.booking.index', [
            'chooseService' => $chooseService,
            'services' => $formattedServices,
            'redDays' => $redDays,
            'redDaysTime' => $redDaysTime,
            'today' => $today,
            'limitDate' => $limitDate,
            'bookLimit' => $days,
            'workHours' => $this->getWorkHours(),
            'settings' => $this->getSettings(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        function getService($id, $chooseDate){
            $locale = app()->getLocale();
            $service = Services::with(['users', 'rules', 'futureRules', 'translations'])
                ->where('id', $id)
                ->firstOrFail();

            $service->translation = $service->translations
                ->where('locale', $locale)
                ->first();

            $rule = $service->ruleForDate($chooseDate);
            $service->effective_price = $service->effectivePriceForDate($chooseDate);
            $service->effective_duration_minutes = $rule?->duration_minutes ?? $service->duration_minutes;

            return $service;
        }

        function getDay($date){
            $formattedDate = Carbon::parse($date)->isoFormat('dddd D, MMMM');
            return $formattedDate;
        }
        
        $data = $request->all();
        $service = getService($request->service_id, $request->choose_date);

        return view('pages.booking.create', [
            'bookingData' => $data,
            'settings' => $this->getSettings(),
            'service' => $service,
            'choose_date' => getDay($request->choose_date),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $booking = Appointments::with(['service', 'user'])->find($id);
        $translation = $booking->service ? $booking->service->getTranslation(app()->getLocale(), 'name') : null;

        if (!$booking) {
            return redirect()->back()->with('error', 'Бронирование не найдено.');
        }
        $dateTimeStart = Carbon::parse($booking->appointment_start);
        $dateTimeEnd = Carbon::parse($booking->appointment_end);

        $booking->booking_date = $dateTimeStart->format('Y-m-d'); 
        $booking->booking_start = $dateTimeStart->format('H:i');
        $booking->booking_end = $dateTimeEnd->format('H:i');

        $locale = app()->getLocale();
        $services = Services::where('status', 1)->where('is_deleted', 0)->orderBy('id', 'asc')->get()->map(function ($service) use ($locale) {
            $service->translation = $service->translations()->where('locale', $locale)->first();
            return $service;
        });
        
        return view('pages.booking.success', [
            'locale' => $locale,
            'services' => $services,
            'appointment' => $booking,
            'translation' => $translation,
            'settings' => $this->getSettings(),
        ]);
        
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
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
    
    function getWorkHours(){
        // Возвращаем пустой массив — выходные дни определяются
        // индивидуально для каждого мастера через getBusyDays
        return [];
    }
}
