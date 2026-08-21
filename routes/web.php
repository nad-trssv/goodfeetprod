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
use App\Http\Controllers\Admin\AdminMasterServiceController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\AdminGlobalSearchController;
use App\Http\Controllers\Admin\AppointmentSlotHoldController;
use App\Http\Controllers\PublicChatController;
use App\Http\Controllers\Admin\CrmChatController;
use App\Http\Controllers\Admin\CrmSettingsController;

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
Route::post('/booking/promo-code/preview', [AjaxBookingController::class, 'previewPromo'])->middleware('throttle:20,1')->name('booking.promo.preview');
Route::get('/chat/state', [PublicChatController::class, 'state'])->middleware('throttle:60,1')->name('chat.state');
Route::post('/chat/conversations', [PublicChatController::class, 'store'])->middleware('throttle:10,1')->name('chat.store');
Route::post('/chat/conversations/{conversation}/poll', [PublicChatController::class, 'show'])->middleware('throttle:120,1')->name('chat.show');
Route::post('/chat/conversations/{conversation}/messages', [PublicChatController::class, 'message'])->middleware('throttle:30,1')->name('chat.messages.store');

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
    Route::get('dashboard', [DashboardController::class, 'index'])->middleware('permission:dashboard.view')->name('dashboard');
    Route::get('admin/search', AdminGlobalSearchController::class)->middleware('throttle:60,1')->name('admin.search');

    Route::get('calendar/create', [AppointmentController::class, 'createAppointment'])->middleware('permission:appointments.create')->name('calendar.create');
    Route::post('calendar/availability', [AppointmentController::class, 'availability'])->middleware(['permission:appointments.create', 'throttle:60,1'])->name('calendar.availability');
    Route::get('calendar/customers/search', [AppointmentController::class, 'searchCustomers'])->middleware(['permission:appointments.create', 'permission:customers.view', 'throttle:60,1'])->name('calendar.customers.search');
    Route::post('calendar/customers/duplicates', [AppointmentController::class, 'duplicateCustomers'])->middleware(['permission:appointments.create', 'permission:customers.view', 'throttle:60,1'])->name('calendar.customers.duplicates');
    Route::post('calendar/slot-holds', [AppointmentSlotHoldController::class, 'store'])->middleware(['permission:appointments.create', 'throttle:120,1'])->name('calendar.slot-holds.store');
    Route::patch('calendar/slot-holds/{token}', [AppointmentSlotHoldController::class, 'renew'])->middleware(['permission:appointments.create', 'throttle:120,1'])->name('calendar.slot-holds.renew');
    Route::delete('calendar/slot-holds/{token}', [AppointmentSlotHoldController::class, 'destroy'])->middleware(['permission:appointments.create', 'throttle:120,1'])->name('calendar.slot-holds.destroy');
    Route::get('calendar', [AppointmentController::class, 'index'])->middleware('permission:appointments.view')->name('calendar.index');
    Route::post('calendar', [AppointmentController::class, 'store'])->middleware('permission:appointments.create')->name('calendar.store');
    Route::match(['put', 'patch'], 'calendar/{calendar}', [AppointmentController::class, 'update'])->middleware('permission:appointments.update')->name('calendar.update');
    Route::delete('calendar/{calendar}', [AppointmentController::class, 'destroy'])->middleware('permission:appointments.delete')->name('calendar.destroy');
    Route::get('calendarList', [AppointmentController::class, 'calendarList'])->middleware('permission:appointments.view')->name('calendarList');
    Route::get('appointments/today', [AppointmentController::class, 'today'])->middleware('permission:appointments.view')->name('appointments.today');
    Route::get('reschedule-requests', [RescheduleRequestController::class, 'index'])->middleware('permission:reschedule_requests.view')->name('reschedule.index');
    Route::get('notifications', [App\Http\Controllers\Admin\NotificationController::class, 'index'])->middleware('permission:notifications.view')->name('notifications.index');
    Route::get('notifications/status', [App\Http\Controllers\Admin\NotificationController::class, 'status'])->middleware(['permission:notifications.view', 'throttle:30,1'])->name('notifications.status');
    Route::post('notifications/read-all', [App\Http\Controllers\Admin\NotificationController::class, 'readAll'])->middleware('permission:notifications.update')->name('notifications.read-all');
    Route::post('notifications/{notification}/read', [App\Http\Controllers\Admin\NotificationController::class, 'read'])->middleware('permission:notifications.update')->name('notifications.read');
    Route::post('reschedule-requests/{request:public_uuid}/approve', [RescheduleRequestController::class, 'approve'])->middleware('permission:reschedule_requests.update')->name('reschedule.approve');
    Route::post('reschedule-requests/{request:public_uuid}/reject', [RescheduleRequestController::class, 'reject'])->middleware('permission:reschedule_requests.update')->name('reschedule.reject');
    Route::get('calendar/details/{appointment}', [AppointmentController::class, 'show'])->middleware('permission:appointments.view')->name('calendar.show');
    Route::patch('appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])->middleware('permission:appointments.status')->name('appointments.status.update');
    Route::post('appointments/{appointment}/message', [AppointmentController::class, 'sendCustomerMessage'])->middleware(['permission:appointments.message', 'throttle:10,1'])->name('appointments.message.store');

    Route::resource('service', AdminServiceController::class)->only(['index', 'show'])->middleware('permission:services.view');
    Route::resource('service', AdminServiceController::class)->only(['create', 'store'])->middleware('permission:services.create');
    Route::resource('service', AdminServiceController::class)->only(['edit', 'update'])->middleware('permission:services.update');
    Route::resource('service', AdminServiceController::class)->only(['destroy'])->middleware('permission:services.delete');
    Route::get('service/{service}/languages', [AdminServiceController::class, 'editLanguages'])->middleware('permission:services.view')->name('languages.edit');
    Route::put('service/{service}/update-languages', [AdminServiceController::class, 'updateLanguages'])->middleware('permission:services.update')->name('languages.update');
    Route::get('service/{service}/fixedtime', [AdminServiceController::class, 'editFixedTime'])->middleware('permission:services.view')->name('fixedtime.edit');
    Route::put('service/{service}/update-fixedtime', [AdminServiceController::class, 'updateFixedTime'])->middleware('permission:services.update')->name('fixedtime.update');
    Route::patch('service/{service}/toggle-status', [AdminServiceController::class, 'toggleStatus'])->middleware('permission:services.update');
    Route::get('service/{service}/rules', [ServiceRuleController::class, 'edit'])->middleware('permission:services.view')->name('service.rules.edit');
    Route::post('service/{service}/rules', [ServiceRuleController::class, 'store'])->middleware('permission:services.update')->name('service.rules.store');
    Route::delete('service/rules/{rule}', [ServiceRuleController::class, 'destroy'])->middleware('permission:services.delete')->name('service.rules.destroy');

    Route::get('member', [MemberController::class, 'index'])->middleware('permission:staff.view')->name('member.index');
    Route::get('admin/customers', [App\Http\Controllers\Admin\CustomerController::class, 'index'])->middleware('permission:customers.view')->name('admin.customers.index');
    Route::get('admin/customers/{customer}', [App\Http\Controllers\Admin\CustomerController::class, 'show'])->middleware('permission:customers.view')->name('admin.customers.show');
    Route::get('crm/customers', [App\Http\Controllers\Admin\CustomerController::class, 'index'])->middleware('permission:crm.view')->name('crm.customers.index');
    Route::get('crm/customers/{customer}', [App\Http\Controllers\Admin\CustomerController::class, 'show'])->middleware('permission:crm.view')->name('crm.customers.show');
    Route::put('crm/customers/{customer}', [App\Http\Controllers\Admin\CustomerController::class, 'updateCrm'])->middleware('permission:crm.update')->name('crm.customers.update');
    Route::post('crm/customers/{customer}/notes', [App\Http\Controllers\Admin\CustomerController::class, 'storeNote'])->middleware('permission:crm.update')->name('crm.customers.notes.store');
    Route::delete('crm/customers/{customer}/notes/{note}', [App\Http\Controllers\Admin\CustomerController::class, 'destroyNote'])->middleware('permission:crm.update')->name('crm.customers.notes.destroy');
    Route::post('crm/customers/{customer}/consents', [App\Http\Controllers\Admin\CustomerController::class, 'storeConsent'])->middleware('permission:crm.update')->name('crm.customers.consents.store');
    Route::post('crm/customers/{customer}/documents', [App\Http\Controllers\Admin\CustomerController::class, 'storeDocument'])->middleware('permission:crm.documents')->name('crm.customers.documents.store');
    Route::get('crm/documents/{document}', [App\Http\Controllers\Admin\CustomerController::class, 'downloadDocument'])->middleware('permission:crm.documents')->name('crm.documents.download');
    Route::get('crm/documents/{document}/preview', [App\Http\Controllers\Admin\CustomerController::class, 'previewDocument'])->middleware('permission:crm.documents')->name('crm.documents.preview');
    Route::delete('crm/documents/{document}', [App\Http\Controllers\Admin\CustomerController::class, 'destroyDocument'])->middleware('permission:crm.documents')->name('crm.documents.destroy');
    Route::get('crm/chat', [CrmChatController::class, 'index'])->middleware('permission:crm.chat.view')->name('crm.chat.index');
    Route::get('crm/chat/status', [CrmChatController::class, 'status'])->middleware(['permission:crm.chat.view','throttle:60,1'])->name('crm.chat.status');
    Route::get('crm/chat/{conversation}', [CrmChatController::class, 'show'])->middleware('permission:crm.chat.view')->name('crm.chat.show');
    Route::get('crm/chat/{conversation}/messages', [CrmChatController::class, 'messages'])->middleware(['permission:crm.chat.view','throttle:120,1'])->name('crm.chat.messages');
    Route::post('crm/chat/{conversation}/reply', [CrmChatController::class, 'reply'])->middleware(['permission:crm.chat.reply','throttle:60,1'])->name('crm.chat.reply');
    Route::post('crm/chat/{conversation}/transfer', [CrmChatController::class, 'transfer'])->middleware('permission:crm.chat.reply')->name('crm.chat.transfer');
    Route::post('crm/chat/{conversation}/close', [CrmChatController::class, 'close'])->middleware('permission:crm.chat.reply')->name('crm.chat.close');
    Route::get('crm/settings', [CrmSettingsController::class, 'index'])->middleware('permission:crm.settings')->name('crm.settings.index');
    Route::put('crm/settings', [CrmSettingsController::class, 'update'])->middleware('permission:crm.settings')->name('crm.settings.update');
    Route::post('crm/settings/tags', [CrmSettingsController::class, 'storeTag'])->middleware('permission:crm.settings')->name('crm.tags.store');
    Route::put('crm/settings/tags/{tag}', [CrmSettingsController::class, 'updateTag'])->middleware('permission:crm.settings')->name('crm.tags.update');
    Route::delete('crm/settings/tags/{tag}', [CrmSettingsController::class, 'destroyTag'])->middleware('permission:crm.settings')->name('crm.tags.destroy');

    Route::resource('profile', ProfileController::class)->only(['index', 'show'])->middleware('permission:profile.view');
    Route::resource('profile', ProfileController::class)->only(['create', 'store', 'edit', 'update', 'destroy'])->middleware('permission:profile.update');
    Route::post('profile/update', [ProfileController::class, 'update'])->middleware('permission:profile.update')->name('profile.ajax.update');

    Route::get('master/schedule', [App\Http\Controllers\Admin\MasterScheduleController::class, 'index'])->middleware('permission:schedules.view')->name('master.schedule.index');
    Route::post('master/schedule/updateWorkHours', [App\Http\Controllers\Admin\MasterScheduleController::class, 'updateWorkHours'])->middleware('permission:schedules.update')->name('master.schedule.updateWorkHours');
    Route::post('master/schedule/updateLunchHours', [App\Http\Controllers\Admin\MasterScheduleController::class, 'updateLunchHours'])->middleware('permission:schedules.update')->name('master.schedule.updateLunchHours');
    Route::post('master/schedule/storeRedDay', [App\Http\Controllers\Admin\MasterScheduleController::class, 'storeRedDay'])->middleware('permission:schedules.update')->name('master.schedule.storeRedDay');
    Route::post('master/schedule/updateFixedBooking', [App\Http\Controllers\Admin\MasterScheduleController::class, 'updateFixedBooking'])->middleware('permission:schedules.update')->name('master.schedule.updateFixedBooking');
    Route::delete('master/schedule/redDay/{id}', [App\Http\Controllers\Admin\MasterScheduleController::class, 'destroyRedDay'])->middleware('permission:schedules.update')->name('master.schedule.destroyRedDay');
    Route::put('master/schedule/redDay/{id}', [App\Http\Controllers\Admin\MasterScheduleController::class, 'updateRedDay'])->middleware('permission:schedules.update')->name('master.schedule.updateRedDay');

    Route::get(
        'master/services',
        [App\Http\Controllers\Admin\MasterServiceController::class, 'index']
    )->middleware('permission:master_services.view')->name('master.services.index');

    Route::post(
        'master/services/{service}/toggle',
        [App\Http\Controllers\Admin\MasterServiceController::class, 'toggle']
    )->middleware('permission:master_services.update')->name('master.service.toggle');

    Route::put(
        'master/services/{service}',
        [App\Http\Controllers\Admin\MasterServiceController::class, 'update']
    )->middleware('permission:master_services.update')->name('master.service.update');
    
    Route::get('master/time-off', [App\Http\Controllers\Admin\MasterScheduleController::class, 'timeOff'])->middleware('permission:schedules.view')->name('master.time-off.index');
    Route::post('master/time-off/store', [App\Http\Controllers\Admin\MasterScheduleController::class, 'storeTimeOff'])->middleware('permission:schedules.update')->name('master.time-off.store');
    Route::post('master/time-off/{id}/update', [App\Http\Controllers\Admin\MasterScheduleController::class, 'updateTimeOff'])->middleware('permission:schedules.update')->name('master.time-off.update');
    Route::post('master/time-off/{id}/destroy', [App\Http\Controllers\Admin\MasterScheduleController::class, 'destroyTimeOff'])->middleware('permission:schedules.update')->name('master.time-off.destroy');
    Route::get(
        'activity-logs',
        [ActivityLogController::class, 'index']
    )->middleware('permission:activity_logs.view')->name('admin.activity-logs.index');
    // Только для администратора (super-admin)
    Route::group([], function () {
        Route::resource('calendarAllMasters', AppointmentControllerAllMasters::class)->only(['index', 'show', 'edit'])->middleware(['permission:appointments.view', 'scope.all']);
        Route::resource('calendarAllMasters', AppointmentControllerAllMasters::class)->only(['create', 'store'])->middleware(['permission:appointments.create', 'scope.all']);
        Route::resource('calendarAllMasters', AppointmentControllerAllMasters::class)->only(['update'])->middleware(['permission:appointments.update', 'scope.all']);
        Route::resource('calendarAllMasters', AppointmentControllerAllMasters::class)->only(['destroy'])->middleware(['permission:appointments.delete', 'scope.all']);
        Route::get('calendarListAllMasters', [AppointmentControllerAllMasters::class, 'calendarListAllMasters'])->middleware(['permission:appointments.view', 'scope.all'])->name('calendarListAllMasters');
        Route::get('master/{id}/calendar', [AppointmentController::class, 'masterCalendar'])->middleware('permission:appointments.view')->name('master.calendar');
        Route::get('master/{id}/calendar/list', [AppointmentController::class, 'masterCalendarList'])->middleware('permission:appointments.view')->name('master.calendar.list');

        Route::get('member/create', [MemberController::class, 'create'])->middleware(['permission:staff.create', 'permission:roles.manage'])->name('member.create');
        Route::post('member/store/{step}', [MemberController::class, 'store'])->middleware(['permission:staff.create', 'permission:roles.manage'])->name('member.store');
        Route::get('member/{id}/edit', [MemberController::class, 'edit'])->middleware('permission:staff.view')->name('member.edit');
        Route::put('member/{id}', [MemberController::class, 'update'])->middleware('permission:staff.update')->name('member.update');
        Route::put('member/{member}/schedule', [App\Http\Controllers\Admin\EmployeeScheduleController::class, 'updateSchedule'])->middleware(['permission:schedules.update', 'scope.all'])->name('member.schedule.update');
        Route::get('member/{member}/closures', [App\Http\Controllers\Admin\EmployeeScheduleController::class, 'index'])->middleware(['permission:schedules.view', 'scope.all'])->name('member.closures.index');
        Route::get('member/{member}/closures/events', [App\Http\Controllers\Admin\EmployeeScheduleController::class, 'events'])->middleware(['permission:schedules.view', 'scope.all'])->name('member.closures.events');
        Route::post('member/{member}/closures', [App\Http\Controllers\Admin\EmployeeScheduleController::class, 'store'])->middleware(['permission:schedules.update', 'scope.all'])->name('member.closures.store');
        Route::put('member/{member}/closures/{closure}', [App\Http\Controllers\Admin\EmployeeScheduleController::class, 'update'])->middleware(['permission:schedules.update', 'scope.all'])->name('member.closures.update');
        Route::delete('member/{member}/closures/{closure}', [App\Http\Controllers\Admin\EmployeeScheduleController::class, 'destroy'])->middleware(['permission:schedules.update', 'scope.all'])->name('member.closures.destroy');
        Route::delete('member/{id}', [MemberController::class, 'destroy'])->middleware('permission:staff.delete')->name('member.destroy');
        Route::post('admin/red-days/store', [App\Http\Controllers\Admin\MasterScheduleController::class, 'storeRedDayForMaster'])->middleware(['permission:schedules.update', 'scope.all'])->name('admin.red-days.store');

        Route::resource('settings', SettingsController::class)->only(['index'])->middleware('permission:settings.view');
        Route::resource('promo-codes', App\Http\Controllers\Admin\PromoCodeController::class)->only(['index'])->middleware('permission:promo_codes.view');
        Route::resource('promo-codes', App\Http\Controllers\Admin\PromoCodeController::class)->only(['store', 'update', 'destroy'])->middleware('permission:promo_codes.update');
        Route::post('settings/updateWorkHours', [SettingsController::class, 'updateWorkHours'])->middleware('permission:settings.update');
        Route::post('settings/updateLunchHours', [SettingsController::class, 'updateLunchHours'])->middleware('permission:settings.update');
        Route::post('settings/storeRedDay', [SettingsController::class, 'storeRedDay'])->middleware('permission:settings.update');
        Route::post('settings/updateFixedBooking', [SettingsController::class, 'updateFixedBooking'])->middleware('permission:settings.update');
        Route::post('settings/updateMainSettings', [SettingsController::class, 'updateMainSettings'])->middleware('permission:settings.update')->name('settings.main.update');
        Route::get('settings/red-days', [AjaxRedDayController::class, 'getRedDays'])->middleware('permission:settings.view')->name('settings.red-days.index');
        Route::delete('settings/red-days/{id}', [AjaxRedDayController::class, 'destroy'])->middleware('permission:settings.update')->name('settings.red-days.destroy');
        Route::put('settings/red-days/{id}', [AjaxRedDayController::class, 'update'])->middleware('permission:settings.update')->name('settings.red-days.update');
        Route::get('settings/main', [AjaxMainSettingController::class, 'mainSettings'])->middleware('permission:settings.view')->name('settings.main');
        Route::put('settings/booking-limit', [AjaxMainSettingController::class, 'updateLimitDays'])->middleware('permission:settings.update')->name('settings.booking-limit.update');
        Route::get('settings/fixed-booking', [AjaxFixedBookingController::class, 'getFixedBooking'])->middleware('permission:settings.view')->name('settings.fixed-booking');

        Route::get('admin/red-days', [App\Http\Controllers\Admin\MasterScheduleController::class, 'allRedDays'])->middleware(['permission:schedules.view', 'scope.all'])->name('admin.red-days.index');
        Route::get('admin/masters/schedule', [MemberController::class, 'allSchedules'])->middleware(['permission:schedules.view', 'scope.all'])->name('admin.masters.schedule');
        Route::post('admin/red-days/{id}/update', [App\Http\Controllers\Admin\MasterScheduleController::class, 'updateRedDayForMaster'])->middleware(['permission:schedules.update', 'scope.all'])->name('admin.red-days.update');
        Route::post('admin/red-days/{id}/destroy', [App\Http\Controllers\Admin\MasterScheduleController::class, 'destroyRedDayForMaster'])->middleware(['permission:schedules.update', 'scope.all'])->name('admin.red-days.destroy');

        Route::get('admin/masters/today', [App\Http\Controllers\Admin\MasterScheduleController::class, 'mastersToday'])->middleware(['permission:schedules.view', 'scope.all'])->name('admin.masters.today');
        Route::get('admin/masters/day/{date}', [App\Http\Controllers\Admin\MasterScheduleController::class, 'mastersToday'])->middleware(['permission:schedules.view', 'scope.all'])->name('admin.masters.day');

        Route::get('admin/appointments', [App\Http\Controllers\Admin\AppointmentController::class, 'allAppointments'])->middleware(['permission:appointments.view', 'scope.all'])->name('admin.appointments.index');
        Route::post('member/{id}/notification-recipients', [App\Http\Controllers\Admin\MemberController::class, 'updateNotificationRecipients'])->middleware('permission:staff.update')->name('member.notification-recipients.update');

        Route::get('admin/rooms', [App\Http\Controllers\Admin\RoomController::class, 'index'])->middleware('permission:rooms.view')->name('admin.rooms.index');
        Route::post('admin/rooms/store', [App\Http\Controllers\Admin\RoomController::class, 'store'])->middleware('permission:rooms.update')->name('admin.rooms.store');
        Route::post('admin/rooms/{id}/update', [App\Http\Controllers\Admin\RoomController::class, 'update'])->middleware('permission:rooms.update')->name('admin.rooms.update');
        Route::post('admin/rooms/{id}/destroy', [App\Http\Controllers\Admin\RoomController::class, 'destroy'])->middleware('permission:rooms.update')->name('admin.rooms.destroy');
        Route::post('admin/rooms/{id}/toggle', [App\Http\Controllers\Admin\RoomController::class, 'toggleActive'])->middleware('permission:rooms.update')->name('admin.rooms.toggle');
        Route::get('admin/rooms/today', [App\Http\Controllers\Admin\RoomController::class, 'today'])->middleware('permission:rooms.view')->name('admin.rooms.today');
        Route::get('admin/rooms/day/{date}', [App\Http\Controllers\Admin\RoomController::class, 'today'])->middleware('permission:rooms.view')->name('admin.rooms.day');
        Route::get(
            'admin/master-services',
            [AdminMasterServiceController::class, 'index']
        )->middleware(['permission:master_services.view', 'scope.all'])->name('admin.master-services.index');

        Route::post(
            'admin/master-services/{masterId}/services/{serviceId}/toggle',
            [AdminMasterServiceController::class, 'toggle']
        )->middleware(['permission:master_services.update', 'scope.all'])->name('admin.master-services.toggle');

        Route::put(
            'admin/master-services/{masterId}/services/{serviceId}',
            [AdminMasterServiceController::class, 'update']
        )->middleware(['permission:master_services.update', 'scope.all'])->name('admin.master-services.update');

        Route::get('admin/roles', [RoleController::class, 'index'])->middleware('permission:roles.manage')->name('admin.roles.index');
        Route::post('admin/roles', [RoleController::class, 'store'])->middleware('permission:roles.manage')->name('admin.roles.store');
        Route::put('admin/roles/{role}', [RoleController::class, 'update'])->middleware('permission:roles.manage')->name('admin.roles.update');
        Route::delete('admin/roles/{role}', [RoleController::class, 'destroy'])->middleware('permission:roles.manage')->name('admin.roles.destroy');
    });
});
