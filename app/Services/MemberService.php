<?php

namespace App\Services;

use App\Http\Requests\Member\StoreRequest;
use App\Models\Roles;
use App\Models\Services;
use App\Models\User;
use App\Models\UserSchedule;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class MemberService
{
    public $member;
    public $token;

    public function list()
    {
        try {
            $members = User::where('role_id', 1)->orWhere('role_id', 2)->orderByDesc('id')->get();
            return ['members' => $members];
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function create()
    {
        try {
            return [
                'roles' => Roles::all(),
                'services' => Services::where('status', 1)->orderBy('name')->get(),
            ];
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function store(StoreRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $this->member = User::create([
                    'username' => $request['username'],
                    'name' => $request['name'],
                    'phone' => $request['phone'],
                    'date_birthday' => $request['date_birthday'],
                    'role_id' => $request['role_id'],
                    'email' => $request['email'],
                    'password' => Hash::make($request['password']),
                    'profile_photo_path' => $request['profile_photo_path'],
                ]);

                // Привязка услуг
                if ($request->has('services') && is_array($request['services'])) {
                    $this->member->services()->sync($request['services']);
                }

                // Сохранение расписания
                UserSchedule::create([
                    'user_id' => $this->member->id,
                    'monday_start' => $request['monday_start'] ?? null,
                    'monday_end' => $request['monday_end'] ?? null,
                    'tuesday_start' => $request['tuesday_start'] ?? null,
                    'tuesday_end' => $request['tuesday_end'] ?? null,
                    'wednesday_start' => $request['wednesday_start'] ?? null,
                    'wednesday_end' => $request['wednesday_end'] ?? null,
                    'thursday_start' => $request['thursday_start'] ?? null,
                    'thursday_end' => $request['thursday_end'] ?? null,
                    'friday_start' => $request['friday_start'] ?? null,
                    'friday_end' => $request['friday_end'] ?? null,
                    'saturday_start' => $request['saturday_start'] ?? null,
                    'saturday_end' => $request['saturday_end'] ?? null,
                    'sunday_start' => $request['sunday_start'] ?? null,
                    'sunday_end' => $request['sunday_end'] ?? null,
                    'lunch_start' => $request['lunch_start'] ?? null,
                    'lunch_end' => $request['lunch_end'] ?? null,
                ]);

                $this->token = $this->member->createToken('auth_token')->plainTextToken;
            });

            return [
                'member' => $this->member,
                'token' => $this->token,
            ];
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}