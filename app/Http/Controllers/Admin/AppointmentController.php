<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AppointmentRequest;
use App\Http\Requests\BusinessCancellationRequest;
use App\Http\Requests\AppointmentStatusRequest;
use App\Models\Appointments;
use App\Services\AppointmentService;
use App\Services\Booking\AppointmentScheduler;
use App\Services\Booking\BusinessCancellationService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Services\Booking\AppointmentStatusService;
use App\Http\Requests\AppointmentCustomerMessageRequest;
use App\Mail\AppointmentCustomerMessage;
use App\Models\AppointmentAudit;
use App\Services\Booking\AppointmentAuditService;
use Illuminate\Support\Facades\Mail;
use App\Services\Booking\TodayAppointments;
use App\Http\Requests\AdminAppointmentAvailabilityRequest;
use App\Http\Requests\AdminCustomerSearchRequest;
use App\Http\Requests\AdminCustomerDuplicateRequest;
use App\Models\Customer;
use App\Models\Services;
use App\Services\Booking\AdminAppointmentAvailability;
use App\Services\Customer\AdminCustomerDuplicateDetector;
use Illuminate\Database\Eloquent\Builder;
use App\Services\Notifications\NotificationReadService;

class AppointmentController extends Controller
{
    private AppointmentService $appointmentService;

    public function __construct(AppointmentService $appointmentService, private readonly AppointmentScheduler $scheduler)
    {
        $this->appointmentService = $appointmentService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = $this->appointmentService->list($this->calendarScope(), null, true);

        return view('admin.calendar.index', [
            'appointments' => $data['appointments'],
            'closedDays' => $data['closedDays'],
            'services' => $data['services'],
            'deletedServices' => $data['deletedServices'],
            'users' => $data['users'],
            'redDays' => $data['redDays'],
        ]);
    }

    public function calendarList(Request $request)
    {
        $data = $this->appointmentService->list($this->calendarScope(), null, true);
        if ($request->integer('customer_id')) {
            $customerId = $request->integer('customer_id');
            $data['appointments'] = array_values(array_filter(
                $data['appointments'],
                fn (array $appointment) => (int) ($appointment['customer_id'] ?? 0) === $customerId
            ));
        }
        return view('admin.calendar.list', [
            'appointments' => $data['appointments'],
            'title' => 'Мои записи',
        ]);
    }

    private function calendarScope(): string
    {
        return auth()->user()?->hasAllAppointmentsScope() ? 'superAdmin' : 'admin';
    }

    public function today(Request $request, TodayAppointments $todayAppointments)
    {
        $appointments = $todayAppointments->forUser($request->user());

        return view('admin.calendar.today', [
            'appointments' => $appointments,
            'unresolvedCount' => $appointments->filter(fn (Appointments $appointment) =>
                $appointment->appointment_end->isPast()
                && in_array($appointment->status, ['pending', 'confirmed', 'checked_in', 'in_progress'], true)
            )->count(),
        ]);
    }

    public function masterCalendar(string $id)
    {
        abort_unless(auth()->user()->hasAllAppointmentsScope() || (int) auth()->id() === (int) $id, 403, __('admin_roles.access_denied'));
        $master = \App\Models\User::findOrFail($id);
        $data = $this->appointmentService->list('byMaster', $id, true);

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
        abort_unless(auth()->user()->hasAllAppointmentsScope() || (int) auth()->id() === (int) $id, 403, __('admin_roles.access_denied'));
        $master = \App\Models\User::findOrFail($id);
        $data = $this->appointmentService->list('byMaster', $id, true);

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
            $events = $this->scheduler->createBatch($request);
            $event = $events->firstOrFail();

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
                'redirect_url' => route('calendar.show', $event),
                'created_count' => $events->count(),
                'series_uuid' => $event->series_uuid,
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
    public function show(Appointments $appointment, NotificationReadService $notifications)
    {
        $this->authorize('view', $appointment);
        $notifications->appointment(auth()->user(), $appointment);
        $event = $this->appointmentService->show($appointment);
        $event->load(['service', 'user', 'room', 'customer', 'media', 'auditTrail' => fn ($query) => $query->with('actor')->latest('id')]);

        $customerAppointments = Appointments::query()
            ->with(['service', 'user'])
            ->when(
                $event->customer_id,
                fn ($query) => $query->where('customer_id', $event->customer_id),
                fn ($query) => $query->where(function ($identity) use ($event) {
                    if ($event->client_email) {
                        $identity->where('client_email', $event->client_email);
                    } elseif ($event->client_phone) {
                        $identity->where('client_phone', $event->client_phone);
                    } else {
                        $identity->whereRaw('1 = 0');
                    }
                })
            )
            ->latest('appointment_start')
            ->limit(10)
            ->get();

        $customerActivity = AppointmentAudit::query()
            ->with(['appointment.service', 'actor'])
            ->whereIn('appointment_id', $customerAppointments->pluck('id'))
            ->latest('id')
            ->limit(15)
            ->get();

        return view('admin.calendar.details', [
            'appointment' => $event,
            'title' => $event['title'],
            'client' => $event['client_name'] . ' ' . $event['client_lastname'],
            'auditLookups' => [
                'service_id' => \App\Models\Services::pluck('name', 'id'),
                'user_id' => \App\Models\User::pluck('name', 'id'),
                'room_id' => \App\Models\Room::pluck('name', 'id'),
            ],
            'customerAppointments' => $customerAppointments,
            'customerActivity' => $customerActivity,
        ]);
    }

    public function sendCustomerMessage(
        AppointmentCustomerMessageRequest $request,
        Appointments $appointment,
        AppointmentAuditService $audit
    ): RedirectResponse {
        $this->authorize('message', $appointment);

        if (!$appointment->client_email) {
            return back()->withErrors(['message' => 'У записи не указан email клиента.']);
        }

        $data = $request->validated();
        Mail::to($appointment->client_email)->send(new AppointmentCustomerMessage(
            $appointment->loadMissing(['service', 'user']),
            $data['subject'],
            $data['message'],
            $request->user()->name
        ));

        $audit->event($appointment, 'customer_message_sent', $request->user(), null, [], [
            'subject' => $data['subject'],
            'recipient' => $appointment->client_email,
        ]);

        return back()->with('success', 'Сообщение клиенту отправлено.');
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
            $event = $this->scheduler->update($request);

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
    public function destroy(BusinessCancellationRequest $request, $id, BusinessCancellationService $cancellations)
    {
        $appointment = Appointments::findOrFail($id);
        $this->authorize('delete', $appointment);

        $cancellations->cancel($appointment, $request->user(), $request->validated('reason'));

        return response()->json(['message' => __('admin_appointments.cancelled')]);
    }

    public function updateStatus(AppointmentStatusRequest $request, Appointments $appointment, AppointmentStatusService $statuses)
    {
        $this->authorize('status', $appointment);

        if ($appointment->appointment_end->isFuture() && in_array($request->validated('status'), ['completed', 'no_show'], true)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'status' => 'Результат можно отметить после окончания записи.',
            ]);
        }

        $isCorrection = in_array($appointment->status, ['completed', 'no_show', 'cancelled_by_client', 'cancelled_by_business', 'rescheduled'], true)
            && $request->validated('status') !== $appointment->status;
        if ($isCorrection && mb_strlen(trim((string) $request->validated('reason'))) < 3) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'reason' => 'При исправлении результата укажите причину (минимум 3 символа).',
            ]);
        }

        $appointment = $statuses->transition(
            $appointment,
            $request->validated('status'),
            $request->user(),
            $request->validated('reason'),
            true
        );

        return response()->json([
            'status' => $appointment->status,
            'message' => 'Статус записи обновлён.',
        ]);
    }

    public function allAppointments(Request $request)
    {
        $query = \App\Models\Appointments::with(['service', 'user'])
            ->when(! $request->user()->hasAllAppointmentsScope(), fn ($query) => $query->where('user_id', $request->user()->id))
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

        if ($request->integer('customer_id')) {
            $query->where('customer_id', $request->integer('customer_id'));
        }

        if ($request->service_id) {
            $query->where('service_id', $request->service_id);
        }

        if ($request->status && in_array($request->status, Appointments::STATUSES, true)) {
            $query->where('status', $request->status);
        }

        if ($request->date_from) {
            $query->whereDate('appointment_start', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('appointment_start', '<=', $request->date_to);
        }

        $appointments = $query->paginate(100)->withQueryString();

        $masters = \App\Models\User::query()
            ->whereHas('role', fn (Builder $query) => $query->where('slug', 'master')->orWhere('name', 'Master'))
            ->when(! $request->user()->hasAllAppointmentsScope(), fn (Builder $query) => $query->whereKey($request->user()->id))
            ->orderBy('name')->get();
        $services = \App\Models\Services::where('is_deleted', 0)->orderBy('name')->get();

        return view('admin.appointments.index', [
            'appointments' => $appointments,
            'masters' => $masters,
            'services' => $services,
        ]);
    }
    public function createAppointment(Request $request)
    {
        $date = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $request->query('date'))
            ? (string) $request->query('date')
            : today()->toDateString();
        $locale = app()->getLocale();
        $services = Services::query()
            ->with(['translations', 'users' => fn ($query) => $query->orderBy('name')])
            ->when(! $request->user()->hasAllAppointmentsScope(), fn (Builder $query) => $query->whereHas('users', fn (Builder $users) => $users->whereKey($request->user()->id)))
            ->where('status', true)
            ->where('is_deleted', false)
            ->orderBy('name')
            ->get()
            ->map(function (Services $service) use ($date, $locale) {
                $translation = $service->translations->firstWhere('locale', $locale)
                    ?? $service->translations->firstWhere('locale', config('app.fallback_locale'));

                return [
                    'id' => $service->id,
                    'name' => $translation?->name ?: $service->name,
                    'image_url' => $service->image_url,
                    'price' => $service->effectivePriceForDate($date),
                    'duration_minutes' => app(\App\Services\Booking\BookingCalendarService::class)->serviceDuration($service->id, $date),
                    'users' => $service->users
                        ->when(! request()->user()->hasAllAppointmentsScope(), fn ($users) => $users->where('id', request()->user()->id))
                        ->map(fn ($user) => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'profile_photo_url' => $user->profile_photo_url,
                    ])->values(),
                ];
            })->values();

        $repeatAppointment = null;
        if ($request->filled('repeat')) {
            $source = Appointments::with('customer')->findOrFail($request->integer('repeat'));
            $this->authorize('view', $source);
            $repeatAppointment = [
                'id' => $source->id,
                'service_id' => $source->service_id,
                'user_id' => $source->user_id,
                'customer_id' => $source->customer_id,
                'client_name' => $source->client_name,
                'client_lastname' => $source->client_lastname,
                'client_phone' => $source->client_phone,
                'client_email' => $source->client_email,
                'description' => $source->description,
                'admin_notes' => $source->admin_notes,
                'customer_name' => trim($source->client_name.' '.$source->client_lastname),
                'appointments_count' => $source->customer?->appointments()->count() ?? 0,
                'has_account' => filled($source->customer?->getRawOriginal('password')),
            ];
        }

        return view('admin.calendar.create', [
            'services' => $services,
            'selectedDate' => $date,
            'currentUserId' => $request->user()->id,
            'canChooseMaster' => $request->user()->hasAllAppointmentsScope(),
            'repeatAppointment' => $repeatAppointment,
        ]);
    }

    public function availability(
        AdminAppointmentAvailabilityRequest $request,
        AdminAppointmentAvailability $availability,
    ): JsonResponse {
        $service = Services::findOrFail($request->integer('service_id'));

        return response()->json($availability->get(
            $service,
            $request->validated('date'),
            $request->validated('user_id'),
            $request->validated('hold_token'),
        ));
    }

    public function duplicateCustomers(
        AdminCustomerDuplicateRequest $request,
        AdminCustomerDuplicateDetector $duplicates,
    ): JsonResponse {
        return response()->json([
            'customers' => $duplicates->find($request->user(), $request->validated())->values(),
        ]);
    }

    public function searchCustomers(AdminCustomerSearchRequest $request): JsonResponse
    {
        $search = $request->validated('query');
        $like = '%'.addcslashes($search, '%_\\').'%';
        $viewer = $request->user();

        $customers = Customer::query()
            ->when(! $viewer->hasAllAppointmentsScope(), fn (Builder $query) => $query->whereHas(
                'appointments',
                fn (Builder $appointments) => $appointments->where('user_id', $viewer->id),
            ))
            ->where(function (Builder $query) use ($like) {
                $query->where('first_name', 'like', $like)
                    ->orWhere('last_name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('phone', 'like', $like);
            })
            ->withCount('appointments')
            ->orderByDesc('appointments_count')
            ->orderBy('first_name')
            ->limit(8)
            ->get()
            ->map(fn (Customer $customer) => [
                'id' => $customer->id,
                'name' => $customer->full_name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'has_account' => filled($customer->getRawOriginal('password')),
                'appointments_count' => $customer->appointments_count,
            ]);

        return response()->json(['customers' => $customers]);
    }
}
