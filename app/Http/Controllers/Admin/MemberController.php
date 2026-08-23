<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Member\StoreRequest;
use App\Http\Resources\MemberResource;
use App\Models\Events;
use App\Models\Roles;
use App\Models\User;
use App\Services\MemberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Services\Media\OptimizedImageStorage;
use App\Services\EmployeeCardService;
use App\Services\MasterServiceCatalog;

class MemberController extends Controller
{
    private MemberService $memberService;

    public function __construct(MemberService $memberService, private readonly OptimizedImageStorage $images)
    {
        $this->memberService = $memberService;
    }

    public function index()
    {
        $data = $this->memberService->list();
        $members = MemberResource::collection($data['members'])->resolve(request());
        return view('admin.member.index', [
            'members' => $members,
            'summary' => $data['summary'],
        ]);
    }

    public function create(): \Illuminate\View\View
    {
        $data = $this->memberService->create();
        return view('admin.member.create', [
            'roles' => $data['roles'],
            'services' => $data['services'],
        ]);
    }

    public function store(StoreRequest $request, $step)
    {
        if ($step == 1) {
            if ($request->hasFile('photo')) {
                $path = $this->images->store($request->file('photo'), 'users', $request->name, 1000, 1000);
            } else {
                $path = null;
            }
            $data = [
                'name' => $request->name,
                'photo' => $path . '',
                'username' => $request->username,
                'phone' => $request->phone,
                'role_id' => $request->role_id,
                'date_birthday' => $request->date_birthday,
            ];
            return response()->json(['data' => $data], 200);

        } elseif ($step == 2) {
            return response()->json(['message' => 'Step 2 validated'], 200);

        } elseif ($step == 3) {
            return response()->json(['message' => 'Step 3 validated'], 200);

        } elseif ($step == 4) {
            $data = $this->memberService->store($request);
            if ($data['member']->date_birthday) {
                Events::create([
                    'name' => 'Sünnipäev',
                    'date' => $request->date_birthday,
                    'user_id' => $data['member']->id,
                    'repeat' => 1,
                ]);
            }
            return response()->json([
                'message' => __('admin_staff.employee_created'),
                'token' => $data['token']
            ], 200);
        }
    }

    public function edit(Request $request, string $id, EmployeeCardService $card, MasterServiceCatalog $catalog): \Illuminate\View\View
    {
        $member = User::with(['services', 'schedule', 'notificationRecipientsUsers', 'vacationClosures'])->findOrFail($id);
        $roles = $request->user()->hasPermission('roles.manage')
            ? Roles::query()->where(fn ($query) => $query->whereNull('slug')->orWhere('slug', '!=', 'customer'))->orderBy('name')->get()
            : Roles::whereKey($member->role_id)->get();
        $admins = User::with('role.permissions')->orderBy('name')->get()->filter(fn (User $user) => $user->hasPermission('notifications.view'));
        $search = trim((string) $request->query('search', ''));
        $catalogData = $catalog->get($member, $search, (string) $request->query('filter', 'all'));

        return view('admin.member.edit', [
            'member' => $member,
            'roles' => $roles,
            'admins' => $admins,
            'statistics' => $card->statistics($member),
            'workCalendar' => $card->calendar($member),
            'allServices' => $catalogData['services'],
            'masterServices' => $catalogData['settings'],
            'filter' => $catalogData['filter'],
            'search' => $search,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $member = User::findOrFail($id);

        $rules = [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:15|unique:users,phone,' . $id,
            'role_id' => [
                'required',
                'integer',
                \Illuminate\Validation\Rule::exists('roles', 'id')->where(fn ($query) => $query->whereNull('slug')->orWhere('slug', '!=', 'customer')),
                function (string $attribute, mixed $value, \Closure $fail) use ($request, $member) {
                    if (! $request->user()->hasPermission('roles.manage') && (int) $value !== (int) $member->role_id) {
                        $fail(__('admin_roles.access_denied'));
                    }
                },
            ],
            'username' => 'required|string|min:3|max:30|unique:users,username,' . $id,
            'email' => 'required|email|unique:users,email,' . $id,
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048|dimensions:min_width=400,min_height=400,max_width=4000,max_height=4000',
            'professional_titles' => 'nullable|array',
            'professional_titles.*' => 'nullable|string|max:120',
            'locale' => ['sometimes', 'required', 'string', \Illuminate\Validation\Rule::in(array_keys(config('supported_locales')))],
            'employment_started_at' => 'nullable|date|before_or_equal:today',
            'date_birthday' => 'nullable|date|before:today',
        ];

        if ($request->filled('password')) {
            $rules['password'] = 'string|min:8|confirmed';
        }

        $request->validate($rules);

        // Обновляем данные пользователя
        $member->update([
            'name' => $request->name,
            'professional_titles' => collect($request->input('professional_titles', []))
                ->only(array_keys(app(\App\Services\Localization\SiteLocaleRegistry::class)->installedLabels()))
                ->map(fn ($title) => trim(strip_tags((string) $title)))
                ->filter()
                ->all(),
            'username' => $request->username,
            'phone' => $request->phone,
            'email' => $request->email,
            'locale' => $request->input('locale', $member->locale ?? 'ru'),
            'role_id' => $request->role_id,
            'date_birthday' => $request->date_birthday,
            'employment_started_at' => $request->employment_started_at,
        ]);

        if ($request->filled('password')) {
            $member->update(['password' => Hash::make($request->password)]);
        }

        if ($request->hasFile('photo')) {
            $oldPhotoPath = $member->profile_photo_path;
            $path = $this->images->store($request->file('photo'), 'users', $member->name, 1000, 1000);
            $member->update(['profile_photo_path' => $path]);
            if ($oldPhotoPath && $oldPhotoPath !== $path) {
                Storage::disk('public')->delete($oldPhotoPath);
            }
        }

        // Обновляем услуги
        // Обновляем расписание
            return response()->json(['message' => __('admin_staff.employee_updated')], 200);
    }

    public function destroy(string $id)
    {
        $member = User::findOrFail($id);
        $member->delete();
        return response()->json(['message' => __('admin_staff.employee_deleted')], 200);
    }
    public function allSchedules()
    {
        $masters = \App\Models\User::with(['schedule', 'services', 'role'])
            ->whereHas('role', fn ($query) => $query->where('is_service_provider', true)->orWhereIn('id', [1, 2]))
            ->orderBy('name')
            ->get();

        return view('admin.member.schedules', [
            'masters' => $masters,
        ]);
    }
    public function show(string $id) {}
    public function updateNotificationRecipients(Request $request, string $id)
    {
        $master = User::findOrFail($id);
        $validated = $request->validate(['recipients' => ['nullable', 'array'], 'recipients.*' => ['integer', 'exists:users,id']]);
        $recipients = User::with('role.permissions')->whereIn('id', $validated['recipients'] ?? [])->get()
            ->filter(fn (User $user) => $user->hasPermission('notifications.view'))->pluck('id')->all();
        
        // Всегда включаем самого мастера
        if (!in_array($id, $recipients)) {
            $recipients[] = $id;
        }
        
        // Удаляем старые и сохраняем новые
        \App\Models\UserNotificationRecipient::where('master_id', $id)->delete();
        
        foreach ($recipients as $recipientId) {
            \App\Models\UserNotificationRecipient::create([
                'master_id' => $id,
                'recipient_id' => $recipientId,
            ]);
        }
        
        return redirect()->route('member.edit', $id)
            ->with('success', __('admin_staff.notification_recipients_updated'));
    }
}
