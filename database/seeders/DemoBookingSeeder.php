<?php

namespace Database\Seeders;

use App\Models\Appointments;
use App\Models\RedDay;
use App\Models\Room;
use App\Models\Roles;
use App\Models\Services;
use App\Models\User;
use App\Models\UserSchedule;
use App\Services\Api\GetFullyBookedService;
use App\Services\Booking\RoomAllocationService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class DemoBookingSeeder extends Seeder
{
    private const MARKER = '[DEMO-SEED]';

    private array $clients = [
        ['Mari', 'Tamm', '+372 5551 0101', 'mari.tamm@example.test'],
        ['Laura', 'Saar', '+372 5551 0102', 'laura.saar@example.test'],
        ['Anna', 'Ivanova', '+372 5551 0103', 'anna.ivanova@example.test'],
        ['Kristi', 'Kask', '+372 5551 0104', 'kristi.kask@example.test'],
        ['Natalia', 'Petrova', '+372 5551 0105', 'natalia.petrova@example.test'],
        ['Kadri', 'Mets', '+372 5551 0106', 'kadri.mets@example.test'],
        ['Olga', 'Smirnova', '+372 5551 0107', 'olga.smirnova@example.test'],
        ['Liis', 'Pärn', '+372 5551 0108', 'liis.parn@example.test'],
        ['Diana', 'Kozlova', '+372 5551 0109', 'diana.kozlova@example.test'],
        ['Katrin', 'Oja', '+372 5551 0110', 'katrin.oja@example.test'],
        ['Irina', 'Sokolova', '+372 5551 0111', 'irina.sokolova@example.test'],
        ['Helen', 'Kuusk', '+372 5551 0112', 'helen.kuusk@example.test'],
    ];

    public function run(): void
    {
        if (app()->environment('production')) {
            throw new RuntimeException('DemoBookingSeeder must not run in production.');
        }

        $services = Services::where('status', true)
            ->where('is_deleted', false)
            ->where('has_fixed_time', false)
            ->orderBy('id')
            ->get();
        if ($services->count() < 3) {
            throw new RuntimeException('At least three active services are required for demo booking data.');
        }

        DB::transaction(function () use ($services) {
            Appointments::where('description', 'like', self::MARKER.'%')->delete();
            RedDay::where('description', self::MARKER)->delete();

            $masters = $this->seedMasters($services);
            $this->seedRooms($masters);
            $this->seedClosures($masters);
        });

        $masters = User::whereIn('email', array_column($this->masterDefinitions(), 'email'))
            ->with('services')
            ->get();

        $availability = app(GetFullyBookedService::class);
        $rooms = app(RoomAllocationService::class);
        $periodStart = today()->addDay()->startOfDay();
        $targets = [90, 55, 30];

        foreach ($targets as $period => $target) {
            $from = $periodStart->copy()->addDays($period * 30);
            $to = $from->copy()->addDays(29);
            $created = $this->seedPeriod($availability, $rooms, $masters, $from, $to, $target, $period);

            if ($created !== $target) {
                throw new RuntimeException("Could create only {$created} of {$target} demo appointments for period ".($period + 1).'.');
            }
        }

        $this->backfillRooms($rooms, $masters);
    }

    private function backfillRooms(RoomAllocationService $rooms, Collection $masters): void
    {
        Appointments::whereIn('user_id', $masters->pluck('id'))
            ->whereNull('room_id')
            ->orderBy('appointment_start')
            ->each(function (Appointments $appointment) use ($rooms) {
                $start = Carbon::parse($appointment->appointment_start);
                $end = Carbon::parse($appointment->appointment_end);
                $roomId = $rooms->assign(
                    $appointment->user_id,
                    $start->toDateString(),
                    $start->format('H:i'),
                    $end->format('H:i'),
                    $appointment->id,
                );

                if ($roomId) {
                    $appointment->update(['room_id' => $roomId]);
                }
            });
    }

    private function seedRooms(Collection $masters): void
    {
        $first = Room::updateOrCreate(
            ['name' => 'Demo Cabinet A'],
            ['capacity' => 1, 'description' => self::MARKER, 'is_active' => true],
        );
        $second = Room::updateOrCreate(
            ['name' => 'Demo Cabinet B'],
            ['capacity' => 1, 'description' => self::MARKER, 'is_active' => true],
        );

        foreach ($masters as $master) {
            $master->rooms()->detach();
        }
        $masters->values()->get(0)?->rooms()->attach($first);
        $masters->values()->get(1)?->rooms()->attach($second);
        $masters->values()->get(2)?->rooms()->attach([$first->id, $second->id]);
    }

    private function seedMasters(Collection $services): Collection
    {
        $role = Roles::firstOrCreate(['name' => 'Master']);
        $masters = collect();

        foreach ($this->masterDefinitions() as $index => $definition) {
            $master = User::updateOrCreate(
                ['email' => $definition['email']],
                [
                    'username' => $definition['username'],
                    'name' => $definition['name'],
                    'phone' => $definition['phone'],
                    'role_id' => $role->id,
                    'date_birthday' => $definition['birthday'],
                    'email_verified_at' => now(),
                    'password' => Hash::make('Master123!'),
                ],
            );

            $serviceIds = $services->values()
                ->filter(fn (Services $service, int $serviceIndex) => ($serviceIndex + $index) % 3 !== 2)
                ->pluck('id');
            $master->services()->sync($serviceIds);
            UserSchedule::updateOrCreate(['user_id' => $master->id], $definition['schedule']);
            $masters->push($master->fresh('services'));
        }

        return $masters;
    }

    private function seedClosures(Collection $masters): void
    {
        foreach ($masters->values() as $index => $master) {
            foreach ([12 + $index * 3, 47 + $index * 2] as $offset) {
                RedDay::create([
                    'user_id' => $master->id,
                    'name' => 'Demo day off',
                    'date' => today()->addDays($offset)->toDateString(),
                    'full_day' => true,
                    'repeat' => false,
                    'description' => self::MARKER,
                ]);
            }

            RedDay::create([
                'user_id' => $master->id,
                'name' => 'Demo personal time',
                'date' => today()->addDays(25 + $index * 4)->toDateString(),
                'start_time' => '13:00',
                'end_time' => '15:00',
                'full_day' => false,
                'repeat' => false,
                'description' => self::MARKER,
            ]);
        }
    }

    private function seedPeriod(GetFullyBookedService $availability, RoomAllocationService $rooms, Collection $masters, Carbon $from, Carbon $to, int $target, int $period): int
    {
        $created = 0;
        $attempt = 0;
        $maxAttempts = $target * 30;

        while ($created < $target && $attempt++ < $maxAttempts) {
            $day = $from->copy()->addDays(($attempt * 7 + $created * 3 + $period) % 30);
            if ($day->gt($to)) {
                continue;
            }

            $master = $masters[($attempt + $created + $period) % $masters->count()];
            $service = $master->services[($attempt * 2 + $created) % $master->services->count()];
            $request = (object) [
                'user_id' => $master->id,
                'service_id' => $service->id,
                'choose_date' => $day->toDateString(),
            ];
            $slots = collect($availability->getList($request)['slots'] ?? []);
            if ($slots->isEmpty()) {
                continue;
            }

            $slot = $slots[($attempt + $period) % $slots->count()];
            if (! $this->slotFitsMasterSchedule($master, $day, $slot)) {
                continue;
            }
            $client = $this->clients[($created + $period * 4) % count($this->clients)];
            $roomId = $rooms->assign($master->id, $day->toDateString(), $slot['start'], $slot['end']);
            if ($master->rooms()->exists() && $roomId === null) {
                continue;
            }

            Appointments::create([
                'title' => $service->name,
                'client_name' => $client[0],
                'client_lastname' => $client[1],
                'client_phone' => $client[2],
                'client_email' => $client[3],
                'service_id' => $service->id,
                'user_id' => $master->id,
                'room_id' => $roomId,
                'price' => $service->effectivePriceForDate($day),
                'appointment_start' => $day->toDateString().' '.$slot['start'].':00',
                'appointment_end' => $day->toDateString().' '.$slot['end'].':00',
                'description' => self::MARKER.' period '.($period + 1),
            ]);
            $created++;
        }

        return $created;
    }

    private function slotFitsMasterSchedule(User $master, Carbon $day, array $slot): bool
    {
        $schedule = $master->schedule()->firstOrFail();
        $weekday = strtolower($day->format('l'));
        $workStart = $schedule->{$weekday.'_start'};
        $workEnd = $schedule->{$weekday.'_end'};

        if (! $workStart || ! $workEnd || $slot['start'] < $workStart || $slot['end'] > $workEnd) {
            return false;
        }

        return ! ($schedule->lunch_start && $schedule->lunch_end
            && $slot['start'] < $schedule->lunch_end
            && $slot['end'] > $schedule->lunch_start);
    }

    private function masterDefinitions(): array
    {
        return [
            [
                'name' => 'Anna Tamm', 'username' => '@demo_anna', 'email' => 'anna.master@example.test',
                'phone' => '+372 5600 1001', 'birthday' => '1990-03-14',
                'schedule' => $this->schedule('09:00', '17:00', ['saturday', 'sunday'], '12:00', '13:00'),
            ],
            [
                'name' => 'Maria Saar', 'username' => '@demo_maria', 'email' => 'maria.master@example.test',
                'phone' => '+372 5600 1002', 'birthday' => '1987-08-22',
                'schedule' => $this->schedule('10:00', '18:00', ['monday', 'sunday'], '13:30', '14:30'),
            ],
            [
                'name' => 'Elena Kask', 'username' => '@demo_elena', 'email' => 'elena.master@example.test',
                'phone' => '+372 5600 1003', 'birthday' => '1993-11-05',
                'schedule' => array_merge(
                    $this->schedule('08:30', '16:30', ['wednesday', 'sunday'], '12:30', '13:00'),
                    ['saturday_start' => '09:00', 'saturday_end' => '14:00'],
                ),
            ],
        ];
    }

    private function schedule(string $start, string $end, array $daysOff, string $lunchStart, string $lunchEnd): array
    {
        $schedule = [
            'lunch_start' => $lunchStart,
            'lunch_end' => $lunchEnd,
            'fixed_booking_enabled' => false,
            'fixed_booking_slots' => [],
        ];

        foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day) {
            $schedule[$day.'_start'] = in_array($day, $daysOff, true) ? null : $start;
            $schedule[$day.'_end'] = in_array($day, $daysOff, true) ? null : $end;
        }

        return $schedule;
    }
}
