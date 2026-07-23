<?php

namespace App\Services\Customer;

use App\Models\AppointmentMedia;
use App\Models\AppointmentStatusHistory;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DeleteCustomerAccount
{
    public function handle(Customer $customer): void
    {
        $appointmentIds = $customer->appointments()->pluck('id');
        $mediaPaths = AppointmentMedia::whereIn('appointment_id', $appointmentIds)->pluck('photo_path');
        $actorType = $customer->getMorphClass();

        DB::transaction(function () use ($customer, $appointmentIds, $actorType) {
            AppointmentMedia::whereIn('appointment_id', $appointmentIds)->delete();
            AppointmentStatusHistory::where('changed_by_type', $actorType)
                ->where('changed_by_id', $customer->id)
                ->update(['changed_by_type' => null, 'changed_by_id' => null]);

            $customer->appointments()->update([
                'customer_id' => null,
                'client_name' => 'Deleted client',
                'client_lastname' => '',
                'client_phone' => null,
                'client_email' => null,
                'description' => null,
                'title' => null,
            ]);
            $customer->delete();
        });

        foreach ($mediaPaths as $path) {
            Storage::disk('public')->delete($path);
        }
    }
}
