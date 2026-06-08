<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Member\StoreRequest;
use App\Http\Resources\MemberResource;
use App\Models\Events;
use App\Models\Roles;
use App\Models\Services;
use App\Models\User;
use App\Models\UserSchedule;
use App\Services\MemberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MemberController extends Controller
{
    private MemberService $memberService;

    public function __construct(MemberService $memberService)
    {
        $this->memberService = $memberService;
    }

    public function index()
    {
        $data = $this->memberService->list();
        $members = MemberResource::collection($data['members']);
        return view('admin.member.index', [
            'members' => $members,
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
                $path = $request['photo']->store('users', 'public');
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
                'message' => 'Мастер успешно создан',
                'token' => $data['token']
            ], 200);
        }
    }

    public function edit(string $id): \Illuminate\View\View
    {
        $member = User::with(['services', 'schedule', 'notificationRecipientsUsers'])->findOrFail($id);
        $roles = Roles::all();
        $services = Services::where('status', 1)->where('is_deleted', 0)->orderBy('name')->get();
        $admins = User::whereIn('role_id', [1, 2])->orderBy('name')->get();

        return view('admin.member.edit', [
            'member' => $member,
            'roles' => $roles,
            'services' => $services,
            'admins' => $admins,
        ]);
    }

    public function update(Request $request, string $id)
    {
        $member = User::findOrFail($id);

        $rules = [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:15|unique:users,phone,' . $id,
            'role_id' => 'required|exists:roles,id',
            'username' => 'required|string|min:3|max:30|unique:users,username,' . $id,
            'email' => 'required|email|unique:users,email,' . $id,
            'services' => 'nullable|array',
            'services.*' => 'exists:services,id',
        ];

        if ($request->filled('password')) {
            $rules['password'] = 'string|min:8|confirmed';
        }

        $request->validate($rules);

        // Обновляем данные пользователя
        $member->update([
            'name' => $request->name,
            'username' => $request->username,
            'phone' => $request->phone,
            'email' => $request->email,
            'role_id' => $request->role_id,
            'date_birthday' => $request->date_birthday,
        ]);

        if ($request->filled('password')) {
            $member->update(['password' => Hash::make($request->password)]);
        }

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('users', 'public');
            $member->update(['profile_photo_path' => $path]);
        }

        // Обновляем услуги
        $member->services()->sync($request->services ?? []);

        // Обновляем расписание
        UserSchedule::updateOrCreate(
            ['user_id' => $member->id],
            [
                'monday_start' => $request->monday_start,
                'monday_end' => $request->monday_end,
                'tuesday_start' => $request->tuesday_start,
                'tuesday_end' => $request->tuesday_end,
                'wednesday_start' => $request->wednesday_start,
                'wednesday_end' => $request->wednesday_end,
                'thursday_start' => $request->thursday_start,
                'thursday_end' => $request->thursday_end,
                'friday_start' => $request->friday_start,
                'friday_end' => $request->friday_end,
                'saturday_start' => $request->saturday_start,
                'saturday_end' => $request->saturday_end,
                'sunday_start' => $request->sunday_start,
                'sunday_end' => $request->sunday_end,
                'lunch_start' => $request->lunch_start,
                'lunch_end' => $request->lunch_end,
            ]
        );

        return response()->json(['message' => 'Данные мастера обновлены'], 200);
    }

    public function destroy(string $id)
    {
        $member = User::findOrFail($id);
        $member->delete();
        return response()->json(['message' => 'Мастер удалён'], 200);
    }
    public function allSchedules()
    {
        $masters = \App\Models\User::with(['schedule', 'services', 'role'])
            ->whereIn('role_id', [1, 2])
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
        
        $recipients = $request->recipients ?? [];
        
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
            ->with('success', 'Настройки уведомлений обновлены!');
    }
}