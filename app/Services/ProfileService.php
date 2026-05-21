<?php

namespace App\Services;

use App\Models\Events;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileService
{
    public $profile;
    
    public function getProfile()
    {
        try {
            $user = User::where('id', auth()->user()->id)->first();
            $user->date_birthday = Carbon::parse($user->date_birthday)->format('Y-m-d');
            str_starts_with($user->username, '@') ? $user->username = ltrim($user->username, '@') : $user->username=$user->username;
            ($user->profile_photo_path === '') ? $user->profile_photo_fullpath = 'public/assets/img/team/avatar.webp' : $user->profile_photo_fullpath = 'public/storage/'.$user->profile_photo_path;
            ($user->profile_photo_path === '') ? $user->profile_photo_storage = 'public/assets/img/team/' : $user->profile_photo_storage = 'public/storage/';
            ($user->profile_photo_path === '') ? $user->profile_photo_storageFileName = '/avatar.webp' : $user->profile_photo_storageFileName = '/'.$user->profile_photo_path;
            $this->profile = $user;
            
            $user->load('services');

            return [
                'profile' => $this->profile,
                'services' => $user->services,
            ];

        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
    
    public function setProfile($request)
    {
        $this->profile = User::where('id', $request->user_id)->first();
        DB::transaction(function () use ($request) {
            if($request['oldPassword']){
                if (!Hash::check($request['oldPassword'], $this->profile['password'])) {
                    throw new Exception('The provided old password does not match our records.', 422);
                }else{
                    $this->profile->update([
                        'password' => $request['password'] ? Hash::make($request['password']) : $this->profile['password'],
                    ]);
                }
            }

            if ($request->hasFile('profile_photo_path')) {
                $path = $request['profile_photo_path'];
                $userphotoContent = file_get_contents($path);
                $userphotoName = 'users/' . Str::random(10) . '.jpg';
                Storage::disk('public')->put($userphotoName, $userphotoContent);
                $this->profile->update([
                    'profile_photo_path' => $userphotoName,
                ]);
            } 

            $this->profile = User::where('id', $request->user_id)->first();

            $this->profile->update([
                'username' => $request['username'],
                'name' => $request['name'],
                'phone' => $request['phone'],
                'date_birthday' => $request['date_birthday'],
                'email' => $request['email'],
            ]);
            if ($this->profile['date_birthday']) {
            Events::updateOrCreate(
                ['user_id' => $this->profile['id']], 
                [
                'name' => 'Sünnipäev',
                'date' => $request->date_birthday,
                'repeat' => 1,
                ]
            );
            }
        });
        return $this->profile;
    }
}
