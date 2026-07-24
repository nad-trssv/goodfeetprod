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
use App\Http\Controllers\Api\BookingController as AjaxBookingController;
use App\Http\Controllers\Api\ServiceController as AjaxServiceController;
use App\Http\Controllers\Api\RedDayController as AjaxRedDayController;
use App\Http\Controllers\Api\MainSettingController as AjaxMainSettingController;
use App\Http\Controllers\Api\FixedBookingController as AjaxFixedBookingController;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\CustomerDashboardController;
use App\Http\Controllers\CustomerProfileController;
use App\Http\Controllers\CustomerAppointmentController;
use App\Http\Controllers\CustomerRescheduleController;
use App\Http\Controllers\Admin\RescheduleRequestController;
use App\Http\Controllers\Admin\ActivityLogController;

Route::get('/', [PageController::class, 'index'])->name('home');
Route::get('/sitemap.xml', [PageController::class, 'sitemap'])->name('sitemap');
Route::get('/localization/{lang}', [LanguageController::class, 'change'])->name('lang.change');

Route::get('/admin7f4k2qbackoffice', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/admin7f4k2qbackoffice', [AuthenticatedSessionController::class, 'store'])->name('login.store');

Route::get('/login', function () {
    abort(404);
});
Route::post('/login', function () {
    abort(404);
});

Route::get('/booking/create', [BookingController::class, 'create'])->name('booking.create');
Route::get('/booking/confirmation/{appointment:public_uuid}', [BookingController::class, 'show'])->name('booking.show');
Route::get('/services', [ServiceController::class, 'index'])->name('clientservice');
Route::get('/galerii', [PageController::class, 'gallery'])->name('gallery.index');
Route::get('/booking', [BookingController::class, 'serviceBooking'])->name('serviceBooking');
Route::get('/contacts', [PageController::class, 'contacts'])->name('contacts');
Route::get('/policy', [PageController::class, 'policy'])->name('policy');
Route::post('/booking/slots', [AjaxBookingController::class, 'getFullyBooked'])->name('booking.slots');
Route::post('/booking/busy-days', [AjaxBookingController::class, 'getBusyDays'])->name('booking.busy-days');
Route::post('/booking', [AjaxBookingController::class, 'storeBooking'])->name('booking.store');
Route::post('/booking/effective-service', [AjaxServiceController::class, 'effective'])->name('booking.service.effective');

Route::middleware('guest:customer')->group(function () {
    Route::get('/account/login', [CustomerAuthController::class, 'showLogin'])->name('customer.login');
    Route::post('/account/login', [CustomerAuthController::class, 'login'])->middleware('throttle:10,1')->name('customer.login.store');
    Route::get('/account/register', [CustomerAuthController::class, 'showRegister'])->name('customer.register');
    Route::post('/account/register', [CustomerAuthController::class, 'register'])->middleware('throttle:5,1')->name('customer.register.store');
});
Route::middleware('auth:customer')->prefix('account')->name('customer.')->group(function () {
    Route::get('/', CustomerDashboardController::class)->name('dashboard');
    Route::get('/profile', [CustomerProfileController::class, 'show'])->name('profile');
    Route::put('/profile', [CustomerProfileController::class, 'update'])->middleware('throttle:10,1')->name('profile.update');
    Route::delete('/appointments/{appointment:public_uuid}', [CustomerAppointmentController::class, 'destroy'])->middleware('throttle:5,1')->name('appointments.destroy');
    Route::get('/appointments/{appointment:public_uuid}/reschedule', [CustomerRescheduleController::class, 'show'])->name('appointments.reschedule');
    Route::get('/appointments/{appointment:public_uuid}/reschedule/days', [CustomerRescheduleController::class, 'days'])->middleware('throttle:20,1')->name('appointments.reschedule.days');
    Route::post('/appointments/{appointment:public_uuid}/reschedule/slots', [CustomerRescheduleController::class, 'slots'])->middleware('throttle:30,1')->name('appointments.reschedule.slots');
    Route::post('/appointments/{appointment:public_uuid}/reschedule', [CustomerRescheduleController::class, 'store'])->middleware('throttle:5,1')->name('appointments.reschedule.store');
    Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('logout');
    Route::delete('/', [CustomerAuthController::class, 'destroy'])->middleware('throttle:3,1')->name('destroy');
});

Route::middleware([
    'auth:sanctum',
    'admin',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('calendar', AppointmentController::class);
    Route::get('calendarList', [AppointmentController::class, 'calendarList'])->name('calendarList');
    Route::get('reschedule-requests', [RescheduleRequestController::class, 'index'])->name('reschedule.index');
    Route::get('notifications', [App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/status', [App\Http\Controllers\Admin\NotificationController::class, 'status'])->middleware('throttle:30,1')->name('notifications.status');
    Route::post('notifications/read-all', [App\Http\Controllers\Admin\NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::post('notifications/{notification}/read', [App\Http\Controllers\Admin\NotificationController::class, 'read'])->name('notifications.read');
    Route::post('reschedule-requests/{request:public_uuid}/approve', [RescheduleRequestController::class, 'approve'])->name('reschedule.approve');
    Route::post('reschedule-requests/{request:public_uuid}/reject', [RescheduleRequestController::class, 'reject'])->name('reschedule.reject');
    Route::get('calendar/create', [AppointmentController::class, 'createAppointment'])->name('calendar.create');
    Route::get('calendar/details/{appointment}', [AppointmentController::class, 'show'])->name('calendar.show');

    Route::resource('service', AdminServiceController::class);
    Route::get('service/{service}/languages', [AdminServiceController::class, 'editLanguages'])->name('languages.edit');
    Route::put('service/{service}/update-languages', [AdminServiceController::class, 'updateLanguages'])->name('languages.update');
    Route::get('service/{service}/fixedtime', [AdminServiceController::class, 'editFixedTime'])->name('fixedtime.edit');
    Route::put('service/{service}/update-fixedtime', [AdminServiceController::class, 'updateFixedTime'])->name('fixedtime.update');
    Route::patch('service/{service}/toggle-status', [AdminServiceController::class, 'toggleStatus']);
    Route::get('service/{service}/rules', [ServiceRuleController::class, 'edit'])->name('service.rules.edit');
    Route::post('service/{service}/rules', [ServiceRuleController::class, 'store'])->name('service.rules.store');
    Route::delete('service/rules/{rule}', [ServiceRuleController::class, 'destroy'])->name('service.rules.destroy');

    Route::get('member', [MemberController::class, 'index'])->name('member.index');

    Route::resource('profile', ProfileController::class);
    Route::post('profile/update', [ProfileController::class, 'update'])->name('profile.ajax.update');

    Route::get('master/schedule', [App\Http\Controllers\Admin\MasterScheduleController::class, 'index'])->name('master.schedule.index');
    Route::post('master/schedule/updateWorkHours', [App\Http\Controllers\Admin\MasterScheduleController::class, 'updateWorkHours'])->name('master.schedule.updateWorkHours');
    Route::post('master/schedule/updateLunchHours', [App\Http\Controllers\Admin\MasterScheduleController::class, 'updateLunchHours'])->name('master.schedule.updateLunchHours');
    Route::post('master/schedule/storeRedDay', [App\Http\Controllers\Admin\MasterScheduleController::class, 'storeRedDay'])->name('master.schedule.storeRedDay');
    Route::post('master/schedule/updateFixedBooking', [App\Http\Controllers\Admin\MasterScheduleController::class, 'updateFixedBooking'])->name('master.schedule.updateFixedBooking');
    Route::delete('master/schedule/redDay/{id}', [App\Http\Controllers\Admin\MasterScheduleController::class, 'destroyRedDay'])->name('master.schedule.destroyRedDay');
    Route::put('master/schedule/redDay/{id}', [App\Http\Controllers\Admin\MasterScheduleController::class, 'updateRedDay'])->name('master.schedule.updateRedDay');

    Route::get(
        'master/services',
        [App\Http\Controllers\Admin\MasterServiceController::class, 'index']
    )->name('master.services.index');

    Route::post(
        'master/services/{service}/toggle',
        [App\Http\Controllers\Admin\MasterServiceController::class, 'toggle']
    )->name('master.service.toggle');

    Route::put(
        'master/services/{service}',
        [App\Http\Controllers\Admin\MasterServiceController::class, 'update']
    )->name('master.service.update');
    
    Route::get('master/time-off', [App\Http\Controllers\Admin\MasterScheduleController::class, 'timeOff'])->name('master.time-off.index');
    Route::post('master/time-off/store', [App\Http\Controllers\Admin\MasterScheduleController::class, 'storeTimeOff'])->name('master.time-off.store');
    Route::post('master/time-off/{id}/update', [App\Http\Controllers\Admin\MasterScheduleController::class, 'updateTimeOff'])->name('master.time-off.update');
    Route::post('master/time-off/{id}/destroy', [App\Http\Controllers\Admin\MasterScheduleController::class, 'destroyTimeOff'])->name('master.time-off.destroy');
    Route::get(
        'activity-logs',
        [ActivityLogController::class, 'index']
    )->name('admin.activity-logs.index');
    // Только для администратора (super-admin)
    Route::middleware('super-admin')->group(function () {
        Route::resource('calendarAllMasters', AppointmentControllerAllMasters::class);
        Route::get('calendarListAllMasters', [AppointmentControllerAllMasters::class, 'calendarListAllMasters'])->name('calendarListAllMasters');
        Route::get('master/{id}/calendar', [AppointmentController::class, 'masterCalendar'])->name('master.calendar');
        Route::get('master/{id}/calendar/list', [AppointmentController::class, 'masterCalendarList'])->name('master.calendar.list');

        Route::get('member/create', [MemberController::class, 'create'])->name('member.create');
        Route::post('member/store/{step}', [MemberController::class, 'store'])->name('member.store');
        Route::get('member/{id}/edit', [MemberController::class, 'edit'])->name('member.edit');
        Route::put('member/{id}', [MemberController::class, 'update'])->name('member.update');
        Route::delete('member/{id}', [MemberController::class, 'destroy'])->name('member.destroy');
        Route::post('admin/red-days/store', [App\Http\Controllers\Admin\MasterScheduleController::class, 'storeRedDayForMaster'])->name('admin.red-days.store');

        Route::resource('settings', SettingsController::class)->only(['index']);
        Route::post('settings/updateWorkHours', [SettingsController::class, 'updateWorkHours']);
        Route::post('settings/updateLunchHours', [SettingsController::class, 'updateLunchHours']);
        Route::post('settings/storeRedDay', [SettingsController::class, 'storeRedDay']);
        Route::post('settings/updateFixedBooking', [SettingsController::class, 'updateFixedBooking']);
        Route::post('settings/updateMainSettings', [SettingsController::class, 'updateMainSettings'])->name('settings.main.update');
        Route::get('settings/red-days', [AjaxRedDayController::class, 'getRedDays'])->name('settings.red-days.index');
        Route::delete('settings/red-days/{id}', [AjaxRedDayController::class, 'destroy'])->name('settings.red-days.destroy');
        Route::put('settings/red-days/{id}', [AjaxRedDayController::class, 'update'])->name('settings.red-days.update');
        Route::get('settings/main', [AjaxMainSettingController::class, 'mainSettings'])->name('settings.main');
        Route::put('settings/booking-limit', [AjaxMainSettingController::class, 'updateLimitDays'])->name('settings.booking-limit.update');
        Route::get('settings/fixed-booking', [AjaxFixedBookingController::class, 'getFixedBooking'])->name('settings.fixed-booking');

        Route::get('admin/red-days', [App\Http\Controllers\Admin\MasterScheduleController::class, 'allRedDays'])->name('admin.red-days.index');
        Route::get('admin/masters/schedule', [MemberController::class, 'allSchedules'])->name('admin.masters.schedule');
        Route::post('admin/red-days/{id}/update', [App\Http\Controllers\Admin\MasterScheduleController::class, 'updateRedDayForMaster'])->name('admin.red-days.update');
        Route::post('admin/red-days/{id}/destroy', [App\Http\Controllers\Admin\MasterScheduleController::class, 'destroyRedDayForMaster'])->name('admin.red-days.destroy');

        Route::get('admin/masters/today', [App\Http\Controllers\Admin\MasterScheduleController::class, 'mastersToday'])->name('admin.masters.today');
        Route::get('admin/masters/day/{date}', [App\Http\Controllers\Admin\MasterScheduleController::class, 'mastersToday'])->name('admin.masters.day');

        Route::get('admin/appointments', [App\Http\Controllers\Admin\AppointmentController::class, 'allAppointments'])->name('admin.appointments.index');
        Route::post('member/{id}/notification-recipients', [App\Http\Controllers\Admin\MemberController::class, 'updateNotificationRecipients'])->name('member.notification-recipients.update');

        Route::get('admin/rooms', [App\Http\Controllers\Admin\RoomController::class, 'index'])->name('admin.rooms.index');
        Route::post('admin/rooms/store', [App\Http\Controllers\Admin\RoomController::class, 'store'])->name('admin.rooms.store');
        Route::post('admin/rooms/{id}/update', [App\Http\Controllers\Admin\RoomController::class, 'update'])->name('admin.rooms.update');
        Route::post('admin/rooms/{id}/destroy', [App\Http\Controllers\Admin\RoomController::class, 'destroy'])->name('admin.rooms.destroy');
        Route::post('admin/rooms/{id}/toggle', [App\Http\Controllers\Admin\RoomController::class, 'toggleActive'])->name('admin.rooms.toggle');
        Route::get('admin/rooms/today', [App\Http\Controllers\Admin\RoomController::class, 'today'])->name('admin.rooms.today');
        Route::get('admin/rooms/day/{date}', [App\Http\Controllers\Admin\RoomController::class, 'today'])->name('admin.rooms.day');
    });
});
