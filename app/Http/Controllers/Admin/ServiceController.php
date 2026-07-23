<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Service\ServiceRequest;
use App\Models\Services;
use App\Services\ServiceService;
use Illuminate\Http\Request;

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
        return redirect()->route('service.create')->with('success', 'Сервис успешно добавлен!');
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
            return redirect()->route('service.show', $service->id)->with('success', 'Сервис успешно добавлен!');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Ошибка при обновлении'], 500);
        }
    }

    public function toggleStatus(Services $service)
    {
        $this->serviceService->toggleStatus($service);
        return redirect()->back()->with('success', 'Статус обновлен!');
    }

    public function editLanguages(Services $service)
    {
        $translations = $service->translations()->get()->keyBy('locale');
    
        return view('admin.service.update.languages', [
            'service' => $service,
            'translations' => $translations,
        ]);
    }

    public function updateLanguages(Request $request, Services $service)
    {
        try {
            $this->serviceService->update($request, $service);
            return redirect()->route('languages.edit', $service->id)->with('success', 'Перевод успешно добавлен!');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Ошибка при обновлении'], 500);
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
            return redirect()->route('fixedtime.edit', $service)->with('success', 'Фиксированное время успешно обновлено!');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Ошибка при обновлении'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $this->serviceService->destroy($id);
            return redirect()->route('service.index')->with('success', 'Услуга удалена!');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Ошибка при удалении события'], 500);
        }
    }
}
