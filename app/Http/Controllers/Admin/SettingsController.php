<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RedDays\StoreRequest;
use App\Http\Requests\Settings\FixedBookingRequest;
use App\Http\Requests\Settings\HoursRequest;
use App\Http\Requests\Settings\LunchHoursRequest;
use App\Http\Requests\Settings\MainSettingsRequest;
use App\Http\Resources\RedDayResource;
use App\Http\Resources\SettingWorkhoursResource;
use App\Models\RedDay;
use App\Models\MessagingIntegration;
use App\Models\MessagingTriggerSetting;
use App\Services\SettingService;
use App\Services\Localization\FrontendTranslationCatalog;
use App\Services\Localization\SiteLocaleRegistry;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    private SettingService $settingService;

    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
    }
    
    public function index(SiteLocaleRegistry $siteLocales, FrontendTranslationCatalog $translationCatalog): View
    {
        $this->authorize('is-superadmin', Auth::user());
        $data = $this->settingService->list();
        $sortedWorkHours = [];
        
        if ($data['workHours']) {
            $daysOrder = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
            $workHours =  new SettingWorkhoursResource($data['workHours']);
            $workHoursArray = $workHours->resource;
            foreach ($daysOrder as $day) {
                if (isset($workHoursArray[$day])) {
                    $sortedWorkHours[$day] = $workHoursArray[$day];
                }
            }
        }
        
        $installedSiteLocales = $siteLocales->installed();

        return view('admin.settings.index',[
            'workHours'     => $sortedWorkHours,
            'lunchHours'    => $data['lunchHours'] ?? ['start' => null, 'end' => null],
            'bookingLimit'  => $data['bookingLimit'] ?? ['days' => 180, 'active' => false],
            'supportedLocales' => $siteLocales->installedLabels(),
            'messagingIntegrations' => MessagingIntegration::query()->get()->keyBy('provider'),
            'messagingTriggerSettings' => MessagingTriggerSetting::query()->get()->keyBy('trigger'),
            'siteLocales' => $installedSiteLocales,
            'availableSiteLocales' => $siteLocales->available(),
            'defaultSiteLocale' => $siteLocales->defaultCode(),
            'siteLocaleStatistics' => $siteLocales->statisticsFor($installedSiteLocales, $translationCatalog),
        ]);
    }

    public function updateWorkHours(HoursRequest $request)
    {
        $data = $this->settingService->updateHours($request);
        return response()->json([
            'status'    => 'success',
            'data'      => $data,
        ]);
    }

    public function updateLunchHours(LunchHoursRequest $request)
    {
        $data = $this->settingService->updateLunchHours($request);
        return response()->json([
            'status'    => 'success',
            'data'      => $data,
        ]);
    }

    public function storeRedDay(StoreRequest $request)
    {
        $data = $this->settingService->storeRedDay($request);

        $redDayData = RedDay::with('user')->orderByDesc('id')->get();
        $redDays = RedDayResource::collection($redDayData);

        $data_fullday = RedDay::with('user')->where('full_day', 1)->orderByDesc('id')->get();
        $fullRedDays = RedDayResource::collection($data_fullday);

        return response()->json([
            'status' => 'success',
            'isFullDay' => $data['full_day'],
            'data' => $data,
            'redDays' => $redDays,
            'fullRedDays' => $fullRedDays,
        ]);
    }

    public function updateFixedBooking(FixedBookingRequest $request)
    {
        $data = $this->settingService->updateFixedBooking($request);
        
        return response()->json([
            'status'    => 'success',
            'data'      => $data,
        ]);
    }

    public function updateMainSettings(MainSettingsRequest $request){
        $data = $this->settingService->updateMainSettings($request);
        
        return response()->json([
            'status'    => 'success',
            'data'      => $data,
        ]);
    }
}
