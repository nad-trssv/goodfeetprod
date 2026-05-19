<?php

namespace App\Services;

use App\Http\Requests\Service\ServiceRequest;
use App\Http\Resources\ServiceResource;
use App\Models\Services;
use App\Models\User;
use App\Models\UserServices;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ServiceService
{
    public $service;
    
    function getMasters() {
        return User::where('role_id', '1')->orWhere('role_id', '2')->get();
    }
    /**
     * Create a new class instance.
     */

    public function list()
    {
        try {
            $today = Carbon::now()->toDateString();

            $services = Services::with(['users', 'rules', 'futureRules'])
                ->where('is_deleted', 0)
                ->orderByDesc('status')
                ->orderByDesc('id')
                ->get()
                ->map(function ($service) use ($today) {
                    $ruleToday = $service->ruleForDate($today);

                    $service->effective_price = $service->effectivePriceForDate($today);
                    $service->effective_duration_minutes = $ruleToday?->duration_minutes ?? $service->duration_minutes;

                    $service->next_rule = $service->futureRules
                        ? $service->futureRules->sortBy('valid_from')->first()
                        : null;

                    return $service;
                });
            
            return [
                'services' => $services,
            ];

        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function create()
    {
        try {
            return [
                'masters' => $this->getMasters(),
            ];

        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function store(ServiceRequest $request): Services
    {
        try {
            DB::transaction(function () use ($request) {
                $price_can_change = $request->has('price_can_change') ? 1 : 0; 
                $this->service = Services::create([
                    'name' => $request['name'],
                    'price' => $request['price'],
                    'price_can_change' => $price_can_change,
                    'duration_minutes' => $request['duration_minutes'],
                    'short_description' => $request['short_description'],
                    'full_description' => $request['full_description'],
                    'eventColor' => $request['eventColor'],
                    'status' => 1,
                ]);
    
                foreach ($request['masters'] as $masterId) {
                    UserServices::create([
                        'user_id' => $masterId,
                        'service_id' => $this->service->id,
                    ]);
                }
                $this->service->translations()->updateOrCreate(
                    ['locale' => 'ru'],
                    [
                        'name' => $request['name'],
                        'short_description' => $request['short_description'],
                        'full_description' => $request['full_description'],
                    ]
                );
            });
            return $this->service;

        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function show(Services $service)
    {
        try {
            return [
                'masters' => $this->getMasters(),
                'choosedMasters' => $service->users()->pluck('user_id')->toArray(),
                'service' => $service,
            ];

        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function update($request, $service): Services
    {
        try {
            DB::transaction(function () use ($request, $service) {
                $status = $request->has('status') ? 1 : 0; 
                $price_can_change = $request->has('price_can_change') ? 1 : 0; 
                $hasTranslations = $request->has('translations') && !empty($request->translations);

                $name = $hasTranslations && isset($request->translations['ru']['name']) 
                    ? $request->translations['ru']['name'] 
                    : $request['name'];
    
                $shortDescription = $hasTranslations && isset($request->translations['ru']['short_description']) 
                    ? $request->translations['ru']['short_description'] 
                    : $request['short_description'] ?? null;
    
                $fullDescription = $hasTranslations && isset($request->translations['ru']['full_description']) 
                    ? $request->translations['ru']['full_description'] 
                    : $request['full_description'] ?? null;

                if ($hasTranslations) {
                    foreach ($request->translations as $locale => $translation) {
                        $service->translations()->updateOrCreate(
                            ['locale' => $locale],
                            [
                                'name' => $translation['name'],
                                'short_description' => $translation['short_description'] ?? null,
                                'full_description' => $translation['full_description'] ?? null,
                            ]
                        );
                    }
                    $service->update([
                        'name' => $name,
                        'short_description' => $shortDescription,
                        'full_description' => $fullDescription,
                    ]);
                } else {
                    $service->update([
                        'name' => $name,
                        'price' => $request['price'],
                        'price_can_change' => $price_can_change,
                        'duration_minutes' => $request['duration_minutes'],
                        'short_description' => $shortDescription,
                        'full_description' => $fullDescription,
                        'eventColor' => $request['eventColor'],
                        'status' => $status,
                    ]);
                    if ($request->has('masters')) {
                        $service->users()->sync($request->masters);
                    }
                    $service->translations()->updateOrCreate(
                        ['locale' => 'ru'],
                        [
                            'name' => $name,
                            'short_description' => $shortDescription,
                            'full_description' => $fullDescription,
                        ]
                    );
                }
    
                $this->service = $service;
            });
            return $this->service;

        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function updateFixedTime($request, $service): Services
    {
        try {
            DB::transaction(function () use ($request, $service) {
                $hasFixedTime = $request->has('has_fixed_time') && !empty($request->has_fixed_time);
                $status = $hasFixedTime ? 1 : 0;

                $service->update([
                    'time_from' => $request['time_from'],
                    'time_to' => $request['time_to'],
                    'has_fixed_time' => $status,
                ]);
                $this->service = $service;
            });
            return $this->service;

        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    
    public function toggleStatus(Services $service): Services
    {
        $service->update(['status' => !$service->status]);

        return $service->refresh();
    }

    public function destroy($id)
    {
        try {
            $removeService = Services::findOrFail($id); 
            DB::transaction(function () use ($removeService) { 
                $removeService->is_deleted = true;
                $removeService->save();
                return $removeService;
            });
            return $removeService;

        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
