<?php

namespace App\Services;

use App\Models\Events;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Services\Media\OptimizedImageStorage;
use Throwable;

class ProfileService
{
    public $profile;

    public function __construct(private readonly OptimizedImageStorage $images)
    {
    }
    
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
        $this->profile = User::findOrFail(auth()->id());
        $oldPhotoPath = $this->profile->profile_photo_path;
        $newPhotoPath = $request->hasFile('profile_photo_path')
            ? $this->images->store($request->file('profile_photo_path'), 'users', $this->profile->name, 800, 800)
            : null;

        try {
            DB::transaction(function () use ($request, $newPhotoPath) {
            if($request['oldPassword']){
                if (!Hash::check($request['oldPassword'], $this->profile['password'])) {
                    throw new Exception('The provided old password does not match our records.', 422);
                }else{
                    $this->profile->update([
                        'password' => $request['password'] ? Hash::make($request['password']) : $this->profile['password'],
                    ]);
                }
            }

            $this->profile->update([
                'username' => $request['username'],
                'name' => $request['name'],
                'professional_titles' => $this->validatedTitles($request->input('professional_titles', [])),
                'phone' => $request['phone'],
                'date_birthday' => $request['date_birthday'],
                'email' => $request['email'],
                'locale' => $request->input('locale', $this->profile->locale ?? config('app.locale')),
                'profile_photo_path' => $newPhotoPath ?? $this->profile->profile_photo_path,
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
        } catch (Throwable $exception) {
            if ($newPhotoPath) {
                Storage::disk('public')->delete($newPhotoPath);
            }
            throw $exception;
        }

        if ($newPhotoPath && $oldPhotoPath && $oldPhotoPath !== $newPhotoPath) {
            Storage::disk('public')->delete($oldPhotoPath);
        }

        return $this->profile->refresh();
    }

    private function validatedTitles(array $titles): array
    {
        return collect($titles)
            ->only(array_keys(config('supported_locales')))
            ->map(fn ($title) => trim(strip_tags((string) $title)))
            ->filter()
            ->all();
    }
}
