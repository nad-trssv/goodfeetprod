<?php

namespace Database\Seeders;

use App\Models\Appointments;
use App\Models\Services;
use App\Models\User;
use App\Services\Booking\RoomAllocationService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use RuntimeException;

class AugustFifthAppointmentSeeder extends Seeder
{
    private const DATE = '2026-08-05';
    private const MARKER = '[DEMO-2026-08-05]';

    public function run(): void
    {
        if (app()->environment('production')) throw new RuntimeException('Demo appointment seeder cannot run in production.');

        $masters = User::with(['schedule','services','rooms'])->whereIn('role_id',[1,2])->orderBy('id')->get();
        $rooms = app(RoomAllocationService::class);
        $created = 0;
        Appointments::where('description','like',self::MARKER.'%')->delete();

        foreach ($masters as $master) {
            if (! $master->schedule || $master->services->isEmpty()) continue;
            $weekday = strtolower(Carbon::parse(self::DATE)->format('l'));
            $workStart = $master->schedule->{$weekday.'_start'};
            $workEnd = $master->schedule->{$weekday.'_end'};
            if (! $workStart || ! $workEnd) continue;

            foreach (['completed','no_show','cancelled_by_client'] as $index => $status) {
                $service = $master->services[$index % $master->services->count()];
                $slot = $this->findSlot($master, $service, $workStart, $workEnd, $index);
                if (! $slot) continue;
                [$start,$end] = $slot;
                $roomId = $rooms->assign($master->id,self::DATE,$start->format('H:i'),$end->format('H:i'));
                if ($master->rooms->isNotEmpty() && ! $roomId) continue;

                Appointments::updateOrCreate(
                    ['description'=>self::MARKER.' '.$master->id.' '.$status],
                    ['title'=>$service->name,'client_name'=>['Kadri','Laura','Maria'][$index],'client_lastname'=>['Tamm','Saar','Kask'][$index],'client_phone'=>'+372 5550 '.str_pad((string)($master->id*10+$index),4,'0',STR_PAD_LEFT),'client_email'=>'demo-'.$master->id.'-'.$index.'@example.test','service_id'=>$service->id,'user_id'=>$master->id,'room_id'=>$roomId,'price'=>$service->effectivePriceForDate(self::DATE),'appointment_start'=>$start,'appointment_end'=>$end,'status'=>$status,'status_changed_at'=>$end]
                );
                $created++;
            }
        }

        $this->command?->info("Prepared {$created} demo appointment outcomes for ".self::DATE.'.');
    }

    private function findSlot(User $master, Services $service, string $workStart, string $workEnd, int $offset): ?array
    {
        $cursor = Carbon::parse(self::DATE.' '.$workStart)->addMinutes($offset * 90);
        $limit = Carbon::parse(self::DATE.' '.$workEnd);
        $duration = max(15,(int)$service->duration_minutes);
        while ($cursor->copy()->addMinutes($duration)->lte($limit)) {
            $end = $cursor->copy()->addMinutes($duration);
            if ($master->schedule->lunch_start && $master->schedule->lunch_end) {
                $lunchStart=Carbon::parse(self::DATE.' '.$master->schedule->lunch_start);$lunchEnd=Carbon::parse(self::DATE.' '.$master->schedule->lunch_end);
                if ($cursor->lt($lunchEnd) && $end->gt($lunchStart)) {$cursor=$lunchEnd->copy();continue;}
            }
            $busy=Appointments::where('user_id',$master->id)->where('appointment_start','<',$end)->where('appointment_end','>',$cursor)->exists();
            if (! $busy) return [$cursor,$end];
            $cursor->addMinutes(30);
        }
        return null;
    }
}
