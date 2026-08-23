<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Service\ServiceRequest;
use App\Models\Services;
use App\Services\ServiceService;
use Illuminate\Http\Request;
use App\Services\Localization\SiteLocaleRegistry;

class ServiceController extends Controller
{
    private ServiceService $serviceService;

    public function __construct(ServiceService $serviceService)
    {
        $this->serviceService = $serviceService;
    }

    public function index(): \Illuminate\View\View
    {
        $data = $this->serviceService->list();
         
        return view('admin.service.index', [
            'services' => $data['services'],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): \Illuminate\View\View
    {
        $data = $this->serviceService->create();

        return view('admin.service.create', [
            'masters' => $data['masters'],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ServiceRequest $request)
    {
        $this->serviceService->store($request);
        return redirect()->route('service.create')->with('success', __('admin_messages.service_added'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Services $service)
    {
        if ($service->is_deleted == 1) {
            abort(404);
        }

        $data = $this->serviceService->show($service);

        return view('admin.service.update.main', [
            'masters' => $data['masters'],
            'choosedMasters' => $data['choosedMasters'],
            'service' => $data['service'],
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
    public function update(ServiceRequest $request, Services $service)
    {
        try {
            $this->serviceService->update($request, $service);
            return redirect()->route('service.show', $service->id)->with('success', __('admin_messages.service_added'));
        } catch (\Exception $e) {
            return response()->json(['error' => __('admin_messages.service_update_failed')], 500);
        }
    }

    public function toggleStatus(Services $service)
    {
        $this->serviceService->toggleStatus($service);
        return redirect()->back()->with('success', __('admin_messages.status_updated'));
    }

    public function editLanguages(Services $service, SiteLocaleRegistry $locales)
    {
        $translations = $service->translations()->get()->keyBy('locale');
    
        return view('admin.service.update.languages', [
            'service' => $service,
            'translations' => $translations,
            'siteLocales' => $locales->installed(),
            'defaultSiteLocale' => $locales->defaultCode(),
        ]);
    }

    public function updateLanguages(Request $request, Services $service)
    {
        try {
            $this->serviceService->update($request, $service);
            return redirect()->route('languages.edit', $service->id)->with('success', __('admin_messages.translation_added'));
        } catch (\Exception $e) {
            return response()->json(['error' => __('admin_messages.service_update_failed')], 500);
        }
    }

    public function editFixedTime(Services $service)
    {
        return view('admin.service.update.fixedTime', [
            'service' => $service
        ]);
    }

    public function updateFixedTime(Request $request, Services $service)
    {
        try {
            $this->serviceService->updateFixedTime($request, $service);
            return redirect()->route('fixedtime.edit', $service)->with('success', __('admin_messages.fixed_time_updated'));
        } catch (\Exception $e) {
            return response()->json(['error' => __('admin_messages.service_update_failed')], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $this->serviceService->destroy($id);
            return redirect()->route('service.index')->with('success', __('admin_messages.service_deleted'));
        } catch (\Exception $e) {
            return response()->json(['error' => __('admin_messages.event_delete_failed')], 500);
        }
    }
}
