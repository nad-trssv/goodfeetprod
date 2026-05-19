<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AppointmentRequest;
use App\Models\Appointments;
use App\Services\AppointmentService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class AppointmentController extends Controller
{
    private AppointmentService $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = $this->appointmentService->list('admin');
        
        return view('admin.calendar.index', [
            'appointments' => $data['appointments'], 
            'closedDays' => $data['closedDays'], 
            'services' => $data['services'],
            'deletedServices' => $data['deletedServices'],
            'users' => $data['users'],
            'redDays' => $data['redDays'],
        ]);
    }

    public function calendarList()
    {
        $data = $this->appointmentService->list('admin');
        return view('admin.calendar.list', [
            'appointments' => $data['appointments'],
            'title' => 'Мои записи',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AppointmentRequest $request): JsonResponse|RedirectResponse
    {
        try {
            $event = $this->appointmentService->store($request);
            
                return response()->json([
                    'id' => $event->id,
                    'title' => $event->service->name,
                    'client_phone' => $event->client_phone,
                    'client_name' => $event->client_name,
                    'client_lastname' => $event->client_lastname,
                    'description' => $event->description,
                    'price' => $event->price,
                    'service_id' => $event->service_id,
                    'user_id' => $event->user_id,
                    'appointment_start' => $event->appointment_start,
                    'appointment_end' => $event->appointment_end,
                    'backgroundColor' => $event->service->eventColor,
                    'message' => 'Запись добавлена успешно!'
                ], 200);
        } catch (Exception $exception) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => $exception->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Appointments $appointment)
    {
        $event = $this->appointmentService->show($appointment);
        
        return view('admin.calendar.details', [
            'appointment' => $event,
            'title' => $event['title'],
            'client' => $event['client_name'].' '.$event['client_lastname'],
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
    public function update(AppointmentRequest $request)
    {   
        try {
            $event = $this->appointmentService->update($request);
            
            return response()->json([
                'appointment' => $event,
                'backgroundColor' => $event->service->eventColor,
                'title' => $event->service->name,
                'message' => 'Запись успешно обновлена!'
            ], 200);
        } catch (Exception $exception) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error' => $exception->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $event = Appointments::findOrFail($id); 
            $event->delete(); 
            return response()->json(['message' => 'Событие успешно удалено'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Ошибка при удалении события'], 500);
        }
    }
}
