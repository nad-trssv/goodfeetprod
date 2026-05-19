<?php

namespace App\Services\Api;

use App\Http\Requests\Member\StoreRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class MemberService
{
    public $user;
    /**
     * Create a new class instance.
     */
    public function store(StoreRequest $request): User
    {
        try {
            DB::transaction(function () use ($request) {
                $this->user = User::create([
                    'username' => '@'.$request['username'],
                    'name' => $request['name'],
                    'phone' => $request['phone'],
                    'date_birthday' => $request['date_birthday'],
                    'role_id' => $request['role_id'],
                    'email' => $request['email'],
                    'password' => Hash::make($request['password']),
                    'profile_photo_path' => $request['profile_photo_path'],
                ]);
            });
            return $this->user;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
