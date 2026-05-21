<?php

use App\Http\Controllers\Admin\AppointmentController;
use App\Http\Controllers\Admin\AppointmentControllerAllMasters;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\Admin\ServiceRuleController;


Route::get('/', [PageController::class, 'index'])->name('home');
Route::get('/localization/{lang}', [LanguageController::class, 'change'])->name('lang.change');

Route::get('/gf-administraator', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/gf-administraator', [AuthenticatedSessionController::class, 'store'])->name('login.store');

Route::get('/login', function () {
    abort(404);
});
Route::post('/login', function () {
    abort(404);
});

Route::resource('/booking', BookingController::class);
Route::get('/services', [ServiceController::class, 'index'])->name('clientservice');
Route::get('/galerii', [PageController::class, 'gallery'])->name('gallery.index');
Route::get('/booking', [BookingController::class, 'serviceBooking'])->name('serviceBooking');
Route::get('/contacts', [PageController::class, 'contacts'])->name('contacts');

Route::middleware([
    'auth:sanctum',
    'admin',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('calendar', AppointmentController::class);
    Route::resource('calendarAllMasters', AppointmentControllerAllMasters::class)->middleware('super-admin');
    Route::get('calendarList', [AppointmentController::class, 'calendarList'])->name('calendarList');
    Route::get('calendar/details/{appointment}', [AppointmentController::class, 'show'])->name('calendar.show');
    Route::get('calendarListAllMasters', [AppointmentControllerAllMasters::class, 'calendarListAllMasters'])->middleware('super-admin')->name('calendarListAllMasters');
    Route::get('master/{id}/calendar', [AppointmentController::class, 'masterCalendar'])->middleware('super-admin')->name('master.calendar');
    Route::get('master/{id}/calendar/list', [AppointmentController::class, 'masterCalendarList'])->middleware('super-admin')->name('master.calendar.list');

    Route::resource('service', AdminServiceController::class);
    Route::get('service/{service}/languages', [AdminServiceController::class, 'editLanguages'])->name('languages.edit');
    Route::put('service/{service}/update-languages', [AdminServiceController::class, 'updateLanguages'])->name('languages.update');
    Route::get('service/{service}/fixedtime', [AdminServiceController::class, 'editFixedTime'])->name('fixedtime.edit');
    Route::put('service/{service}/update-fixedtime', [AdminServiceController::class, 'updateFixedTime'])->name('fixedtime.update');
    Route::patch('service/{service}/toggle-status', [AdminServiceController::class, 'toggleStatus']);

    Route::get('service/{service}/rules', [ServiceRuleController::class, 'edit'])
        ->name('service.rules.edit');

    Route::post('service/{service}/rules', [ServiceRuleController::class, 'store'])
        ->name('service.rules.store');

    Route::delete('service/rules/{rule}', [ServiceRuleController::class, 'destroy'])
        ->name('service.rules.destroy');
        
    Route::get('member', [MemberController::class, 'index'])->name('member.index');
    Route::get('member/create', [MemberController::class, 'create'])->name('member.create');
    Route::post('member/store/{step}', [MemberController::class, 'store'])->name('member.store');
    Route::get('member/{id}/edit', [MemberController::class, 'edit'])->name('member.edit');
    Route::put('member/{id}', [MemberController::class, 'update'])->name('member.update');
    Route::delete('member/{id}', [MemberController::class, 'destroy'])->name('member.destroy');

    Route::resource('settings', SettingsController::class)->middleware('super-admin');
    Route::post('settings/updateWorkHours', [SettingsController::class, 'updateWorkHours'])->middleware('super-admin');
    Route::post('settings/updateLunchHours', [SettingsController::class, 'updateLunchHours'])->middleware('super-admin');
    Route::post('settings/storeRedDay', [SettingsController::class, 'storeRedDay'])->middleware('super-admin');
    Route::post('settings/updateFixedBooking', [SettingsController::class, 'updateFixedBooking'])->middleware('super-admin');
    Route::post('settings/updateMainSettings', [SettingsController::class, 'updateMainSettings'])->middleware('super-admin');

    Route::resource('profile', ProfileController::class);
    Route::get('master/schedule', [App\Http\Controllers\Admin\MasterScheduleController::class, 'index'])->name('master.schedule.index');
    Route::post('master/services/{service}/toggle', [App\Http\Controllers\Admin\MasterServiceController::class, 'toggle'])->name('master.service.toggle');
    Route::get('master/services', [App\Http\Controllers\Admin\MasterServiceController::class, 'index'])->name('master.services.index');
    Route::post('master/schedule/updateWorkHours', [App\Http\Controllers\Admin\MasterScheduleController::class, 'updateWorkHours'])->name('master.schedule.updateWorkHours');
    Route::post('master/schedule/updateLunchHours', [App\Http\Controllers\Admin\MasterScheduleController::class, 'updateLunchHours'])->name('master.schedule.updateLunchHours');
    Route::post('master/schedule/storeRedDay', [App\Http\Controllers\Admin\MasterScheduleController::class, 'storeRedDay'])->name('master.schedule.storeRedDay');
    Route::post('master/schedule/updateFixedBooking', [App\Http\Controllers\Admin\MasterScheduleController::class, 'updateFixedBooking'])->name('master.schedule.updateFixedBooking');
    Route::delete('master/schedule/redDay/{id}', [App\Http\Controllers\Admin\MasterScheduleController::class, 'destroyRedDay'])->name('master.schedule.destroyRedDay');
});


Route::get('/policy', [PageController::class, 'policy'])->name('policy');