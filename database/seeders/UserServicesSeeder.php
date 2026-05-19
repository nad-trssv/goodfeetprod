<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserServicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $serviceIds = DB::table('services')->pluck('id');

        foreach ($serviceIds as $serviceId) {
            DB::table('user_services')->insert([
                'user_id'    => 1,
                'service_id' => $serviceId,
            ]);
        }
    }
}
