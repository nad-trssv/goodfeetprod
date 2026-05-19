<?php

namespace App\Services;

use App\Http\Requests\Member\StoreRequest;
use App\Models\Roles;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class MemberService
{
    public $member;
    public $token;
    /**
     * Create a new class instance.
     */
    public function list()
    {
        try {
            function memberList() {
                return User::where('role_id', 1)->orWhere('role_id', 2)->orderByDesc('id')->get();
            }
            
            return [
                'members' => memberList(),
            ];

        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function create()
    {
        try {
            function roles() {
                return Roles::all();
            }
            return [
                'roles' => roles(),
            ];

        } catch (Exception $exception) {
            Log::info($exception->getMessage());
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
