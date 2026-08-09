@php
  $staff = auth()->user();
  $unread = (int) ($unreadNotificationCount ?? 0);
  $showBookings = $staff->hasPermission('appointments.view') || $staff->hasPermission('appointments.create') || $staff->hasPermission('notifications.view') || $staff->hasPermission('reschedule_requests.view');
  $showManagement = $staff->hasPermission('customers.view') || $staff->hasPermission('services.view') || $staff->hasPermission('staff.view');
  $showAdministration = $staff->hasPermission('settings.view') || $staff->hasPermission('promo_codes.view') || $staff->hasPermission('rooms.view') || $staff->hasPermission('activity_logs.view') || $staff->hasPermission('roles.manage') || ($staff->hasAllAppointmentsScope() && ($staff->hasPermission('appointments.view') || $staff->hasPermission('schedules.view')));
@endphp
<nav class="navbar navbar-vertical navbar-expand-lg">
  <div class="collapse navbar-collapse" id="navbarVerticalCollapse">
    <div class="navbar-vertical-content">
      <ul class="navbar-nav flex-column" id="navbarVerticalNav">
        @can('dashboard.view')
          <li class="nav-item"><div class="nav-item-wrapper"><a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }} label-1" href="{{ route('dashboard') }}"><div class="d-flex align-items-center"><span class="nav-link-icon"><span data-feather="pie-chart"></span></span><span class="nav-link-text-wrapper"><span class="nav-link-text">{{ __('admin_nav.dashboard') }}</span></span></div></a></div></li>
        @endcan

        @if($showBookings)
          <li class="nav-item">
            <p class="navbar-vertical-label">{{ __('admin_nav.appointments') }}</p><hr class="navbar-vertical-line">
            @can('appointments.create')<x-admin-nav-link route="calendar.create" icon="plus-circle" :label="__('admin_nav.new_appointment')" />@endcan
            @can('appointments.view')
              <x-admin-nav-link route="appointments.today" icon="clock" :label="__('admin_nav.today')" />
              <x-admin-nav-link route="calendar.index" icon="calendar" :label="__('admin_nav.calendar')" />
              <x-admin-nav-link route="calendarList" icon="list" :label="__('admin_nav.appointment_list')" />
            @endcan
            @can('notifications.view')
              <div class="nav-item-wrapper"><a class="nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }} label-1" href="{{ route('notifications.index') }}"><div class="d-flex align-items-center w-100"><span class="nav-link-icon"><span data-feather="bell"></span></span><span class="nav-link-text-wrapper flex-grow-1"><span class="nav-link-text">{{ __('admin_notifications.title') }}</span></span><span id="sidebar-notification-count" class="badge rounded-pill bg-danger ms-auto {{ $unread > 0 ? '' : 'd-none' }}">{{ $unread > 99 ? '99+' : $unread }}</span></div></a></div>
            @endcan
            @can('reschedule_requests.view')<x-admin-nav-link route="reschedule.index" icon="repeat" :label="__('customer.reschedule_requests')" />@endcan
          </li>
        @endif

        @if($showManagement)
          <li class="nav-item">
            <p class="navbar-vertical-label">{{ __('admin_nav.management') }}</p><hr class="navbar-vertical-line">
            @can('customers.view')<x-admin-nav-link route="admin.customers.index" icon="user-check" :label="__('admin_customers.title')" />@endcan
            @can('services.view')
              <x-admin-nav-link route="service.index" icon="clipboard" :label="__('admin_nav.services')" />
              @can('services.create')<x-admin-nav-link route="service.create" icon="plus" :label="__('admin_nav.add_service')" />@endcan
            @endcan
            @can('staff.view')<x-admin-nav-link route="member.index" icon="users" :label="__('admin_nav.staff')" />@endcan
            @can('staff.create')<x-admin-nav-link route="member.create" icon="user-plus" :label="__('admin_nav.add_employee')" />@endcan
            @if($staff->hasAllAppointmentsScope())
              @can('schedules.view')<x-admin-nav-link route="admin.masters.schedule" icon="calendar" :label="__('admin_nav.staff_schedules')" />@endcan
              @can('master_services.view')<x-admin-nav-link route="admin.master-services.index" icon="layers" :label="__('admin_nav.staff_services')" />@endcan
            @endif
          </li>
        @endif

        @if($staff->hasPermission('profile.view') || $staff->isServiceProvider())
          <li class="nav-item">
            <p class="navbar-vertical-label">{{ __('admin_nav.my_profile') }}</p><hr class="navbar-vertical-line">
            @can('profile.view')<x-admin-nav-link route="profile.index" icon="user" :label="__('admin_nav.profile')" />@endcan
            @if($staff->isServiceProvider())
              @can('schedules.view')<x-admin-nav-link route="master.schedule.index" icon="calendar" :label="__('admin_nav.my_schedule')" />@endcan
              @can('master_services.view')<x-admin-nav-link route="master.services.index" icon="clipboard" :label="__('admin_nav.my_services')" />@endcan
              @can('schedules.view')<x-admin-nav-link route="master.time-off.index" icon="calendar-x" :label="__('admin_nav.my_time_off')" />@endcan
            @endif
          </li>
        @endif

        @if($showAdministration)
          <li class="nav-item">
            <p class="navbar-vertical-label">{{ __('admin_nav.administration') }}</p><hr class="navbar-vertical-line">
            @can('settings.view')<x-admin-nav-link route="settings.index" icon="settings" :label="__('admin_nav.settings')" />@endcan
            @can('promo_codes.view')<x-admin-nav-link route="promo-codes.index" icon="tag" :label="__('promo.title')" />@endcan
            @if($staff->hasAllAppointmentsScope())
              @can('schedules.view')
                <x-admin-nav-link route="admin.red-days.index" icon="calendar-x" :label="__('admin_nav.closed_time')" />
                <x-admin-nav-link route="admin.masters.today" icon="bar-chart-2" :label="__('admin_nav.staff_load')" />
              @endcan
              @can('appointments.view')<x-admin-nav-link route="admin.appointments.index" icon="table" :label="__('admin_nav.all_appointments')" />@endcan
            @endif
            @can('rooms.view')
              <x-admin-nav-link route="admin.rooms.index" icon="home" :label="__('admin_nav.rooms')" />
              <x-admin-nav-link route="admin.rooms.today" icon="grid" :label="__('admin_nav.room_load')" />
            @endcan
            @can('activity_logs.view')<x-admin-nav-link route="admin.activity-logs.index" icon="activity" :label="__('admin_nav.activity_log')" />@endcan
            @can('roles.manage')<x-admin-nav-link route="admin.roles.index" icon="shield" :label="__('admin_roles.title')" />@endcan
          </li>
        @endif
      </ul>
    </div>
  </div>
  <div class="navbar-vertical-footer"><button class="btn navbar-vertical-toggle border-0 fw-semibold w-100 white-space-nowrap d-flex align-items-center"><span class="uil uil-left-arrow-to-left fs-8"></span><span class="uil uil-arrow-from-right fs-8"></span><span class="navbar-vertical-footer-text ms-2"></span></button></div>
</nav>
