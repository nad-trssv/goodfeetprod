<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AppointmentRequest;
use App\Models\Appointments;
use App\Services\AppointmentService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
        $data = $this->appointmentService->list($this->calendarScope());

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
        $data = $this->appointmentService->list($this->calendarScope());
        return view('admin.calendar.list', [
            'appointments' => $data['appointments'],
            'title' => 'Мои записи',
        ]);
    }

    private function calendarScope(): string
    {
        return auth()->user()?->role_id === 1 ? 'superAdmin' : 'admin';
    }

    public function masterCalendar(string $id)
    {
        $master = \App\Models\User::findOrFail($id);
        $data = $this->appointmentService->list('byMaster', $id);

        return view('admin.calendar.index', [
            'appointments' => $data['appointments'],
            'closedDays' => $data['closedDays'],
            'services' => $data['services'],
            'deletedServices' => $data['deletedServices'],
            'users' => $data['users'],
            'redDays' => $data['redDays'],
            'master' => $master,
        ]);
    }

    public function masterCalendarList(string $id)
    {
        $master = \App\Models\User::findOrFail($id);
        $data = $this->appointmentService->list('byMaster', $id);

        return view('admin.calendar.list', [
            'appointments' => $data['appointments'],
            'title' => 'Записи мастера: ' . $master->name,
            'master' => $master,
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
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'errors' => ['appointment_start' => [$exception->getMessage()]]
                ], 422);
            }
            return redirect()->back()->withInput()->withErrors(['error' => $exception->getMessage()]);
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
            'client' => $event['client_name'] . ' ' . $event['client_lastname'],
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
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'errors' => ['appointment_start' => [$exception->getMessage()]]
                ], 422);
            }
            return redirect()->back()->withInput()->withErrors(['error' => $exception->getMessage()]);
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

    public function allAppointments(Request $request)
    {
        $query = \App\Models\Appointments::with(['service', 'user'])
            ->orderByDesc('appointment_start');

        // Фильтры
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('client_name', 'like', '%' . $request->search . '%')
                    ->orWhere('client_lastname', 'like', '%' . $request->search . '%')
                    ->orWhere('client_phone', 'like', '%' . $request->search . '%')
                    ->orWhere('client_email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->master_id) {
            $query->where('user_id', $request->master_id);
        }

        if ($request->service_id) {
            $query->where('service_id', $request->service_id);
        }

        if ($request->date_from) {
            $query->whereDate('appointment_start', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('appointment_start', '<=', $request->date_to);
        }

        $appointments = $query->paginate(100)->withQueryString();

        $masters = \App\Models\User::whereIn('role_id', [1, 2])->orderBy('name')->get();
        $services = \App\Models\Services::where('is_deleted', 0)->orderBy('name')->get();

        return view('admin.appointments.index', [
            'appointments' => $appointments,
            'masters' => $masters,
            'services' => $services,
        ]);
    }
    public function createAppointment()
    {
        $data = $this->appointmentService->list('admin');
        return view('admin.calendar.create', [
            'services' => $data['services'],
            'users' => $data['users'],
        ]);
    }
}
