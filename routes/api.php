<?php

use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\FixedBookingController;
use App\Http\Controllers\Api\MainSettingController;
use App\Http\Controllers\Api\RedDayController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ServiceController as ApiServiceController;

Route::middleware([
    'api',
])->group(function () {
    Route::get('settings/getRedDays', [RedDayController::class, 'getRedDays']);
    Route::get('settings/getFixedBooking', [FixedBookingController::class, 'getFixedBooking']);
    Route::get('settings/mainSettings', [MainSettingController::class, 'mainSettings']);
    Route::put('settings/updateLimitDays', [MainSettingController::class, 'updateLimitDays'])->name('updateLimitDays');
    Route::delete('settings/deleteRedDay/{id}', [RedDayController::class, 'destroy']);

    Route::post('booking/getFullyBooked', [BookingController::class, 'getFullyBooked'])->name('api.booking.getFullyBooked');
    Route::post('booking/getBusyDays', [BookingController::class, 'getBusyDays'])->name('api.booking.getBusyDays');
    Route::post('booking/store', [BookingController::class, 'storeBooking'])->name('api.booking.store');
    Route::post('profile/setProfile', [ProfileController::class, 'update'])->name('api.profile.setProfile');

    Route::post('service/effective', [ApiServiceController::class, 'effective'])
    ->name('api.service.effective');
});