@php
  $staff = auth()->user();
  $unread = (int) ($unreadNotificationCount ?? 0);
  $crmUnread = (int) ($unreadCrmChatCount ?? 0);
  $showCrm = $staff->hasPermission('crm.view') || $staff->hasPermission('crm.chat.view') || $staff->hasPermission('crm.settings');
  $showBookings = $staff->hasPermission('appointments.view') || $staff->hasPermission('appointments.create') || $staff->hasPermission('notifications.view') || $staff->hasPermission('reschedule_requests.view');
  $showManagement = $staff->hasPermission('services.view') || $staff->hasPermission('staff.view');
  $crmOpen = request()->routeIs('crm.*');
  $showAdministration = $staff->hasPermission('settings.view') || $staff->hasPermission('promo_codes.view') || $staff->hasPermission('rooms.view') || $staff->hasPermission('activity_logs.view') || $staff->hasPermission('work_time.view') || $staff->hasPermission('roles.manage') || ($staff->hasAllAppointmentsScope() && ($staff->hasPermission('appointments.view') || $staff->hasPermission('schedules.view')));
  $managementOpen = request()->routeIs('service.*', 'languages.*', 'fixedtime.*', 'member.*', 'admin.masters.schedule', 'admin.master-services.*');
  $profileOpen = request()->routeIs('profile.*', 'master.schedule.*', 'master.services.*', 'master.time-off.*');
  $administrationOpen = request()->routeIs('settings.*', 'promo-codes.*', 'admin.red-days.*', 'admin.appointments.*', 'admin.rooms.*', 'admin.activity-logs.*', 'admin.work-time.*', 'admin.roles.*', 'admin.system-health.*');
@endphp
<style>
  .navbar-vertical .admin-nav-group-toggle{padding-right:1.35rem!important}
  .navbar-vertical .admin-nav-group-toggle .nav-link-text{line-height:1.25;white-space:normal}
  .navbar-vertical .nav-group-chevron{flex:0 0 1.35rem;width:1.35rem;height:1.35rem;margin-left:.65rem!important;transition:transform .2s ease}
  .navbar-vertical .nav-group-chevron svg{width:1rem;height:1rem}
  .navbar-vertical [aria-expanded="true"] .nav-group-chevron{transform:rotate(180deg)}
  .navbar-vertical .admin-nav-group-children{margin-right:.8rem;padding-top:.2rem;padding-bottom:.35rem}
  .navbar-vertical .admin-nav-group-children .nav-link{padding-right:.85rem}
</style>
<nav class="navbar navbar-vertical navbar-expand-lg">
  <div class="collapse navbar-collapse" id="navbarVerticalCollapse">
    <div class="navbar-vertical-content">
      <ul class="navbar-nav flex-column" id="navbarVerticalNav">
        @can('dashboard.view')
          <li class="nav-item"><div class="nav-item-wrapper"><a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }} label-1" href="{{ route('dashboard') }}"><div class="d-flex align-items-center"><span class="nav-link-icon"><span data-feather="pie-chart"></span></span><span class="nav-link-text-wrapper"><span class="nav-link-text">{{ __('admin_nav.dashboard') }}</span></span></div></a></div></li>
        @endcan
        @can('dashboard.full')
          <li class="nav-item"><x-admin-nav-link route="admin.dashboard.full" icon="bar-chart-2" :label="__('admin_nav.full_dashboard')" /></li>
        @endcan

        @if($showBookings)
          <li class="nav-item">
            <p class="navbar-vertical-label">{{ __('admin_nav.appointments') }}</p><hr class="navbar-vertical-line">
            @can('appointments.create')<x-admin-nav-link route="calendar.create" icon="plus-circle" :label="__('admin_nav.new_appointment')" />@endcan
            @can('appointments.view')
              <x-admin-nav-link route="appointments.today" icon="clock" :label="__('admin_nav.today')" />
              <x-admin-nav-link route="calendar.index" icon="calendar" :label="__('admin_nav.calendar')" />
              <x-admin-nav-link route="calendarList" icon="list" :label="__('admin_nav.appointment_list')" />
              <x-admin-nav-link route="dashboard.appointments.unresolved" icon="alert-circle" :label="__('admin_nav.unresolved_appointments')" />
            @endcan
            @can('notifications.view')
              <div class="nav-item-wrapper"><a class="nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }} label-1" href="{{ route('notifications.index') }}"><div class="d-flex align-items-center w-100"><span class="nav-link-icon"><span data-feather="bell"></span></span><span class="nav-link-text-wrapper flex-grow-1"><span class="nav-link-text">{{ __('admin_nav.notifications') }}</span></span><span id="sidebar-notification-count" class="badge rounded-pill bg-danger ms-auto {{ $unread > 0 ? '' : 'd-none' }}">{{ $unread > 99 ? '99+' : $unread }}</span></div></a></div>
            @endcan
            @can('reschedule_requests.view')<x-admin-nav-link route="reschedule.index" icon="repeat" :label="__('admin_nav.reschedule_requests')" />@endcan
          </li>
        @endif

        @if($showCrm)
          <x-admin-nav-group id="adminNavCrm" icon="message-circle" :label="__('admin_nav.crm')" :open="$crmOpen" badge-id="sidebar-crm-group-count" :badge-count="$crmUnread">
            @can('crm.view')
              <x-admin-nav-link route="crm.customers.index" icon="users" :label="__('admin_nav.crm_customers')" />
              <x-admin-nav-link route="dashboard.customers.lost" icon="user-x" :label="__('admin_nav.lost_customers')" />
            @endcan
            @can('crm.chat.view')
              <div class="nav-item-wrapper"><a class="nav-link {{ request()->routeIs('crm.chat.*') ? 'active' : '' }} label-1" href="{{ route('crm.chat.index') }}"><div class="d-flex align-items-center w-100"><span class="nav-link-icon"><span data-feather="message-square"></span></span><span class="nav-link-text-wrapper flex-grow-1"><span class="nav-link-text">{{ __('admin_nav.crm_chat') }}</span></span><span id="sidebar-crm-chat-count" class="badge rounded-pill bg-danger ms-auto {{ $crmUnread > 0 ? '' : 'd-none' }}">{{ $crmUnread > 99 ? '99+' : $crmUnread }}</span></div></a></div>
            @endcan
            @if($staff->hasAllAppointmentsScope()) @can('crm.settings')<x-admin-nav-link route="crm.ratings.index" icon="star" :label="__('admin_nav.crm_ratings')" />@endcan @endif
            @if($staff->hasAllAppointmentsScope()) @can('crm.settings')<x-admin-nav-link route="crm.settings.index" icon="sliders" :label="__('admin_nav.crm_settings')" />@endcan @endif
          </x-admin-nav-group>
        @endif

        @if($showManagement)
          <x-admin-nav-group id="adminNavManagement" icon="briefcase" :label="__('admin_nav.management')" :open="$managementOpen">
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
          </x-admin-nav-group>
        @endif

        @if($staff->hasPermission('profile.view') || $staff->isServiceProvider())
          <x-admin-nav-group id="adminNavProfile" icon="user" :label="__('admin_nav.my_profile')" :open="$profileOpen">
            @can('profile.view')<x-admin-nav-link route="profile.index" icon="user" :label="__('admin_nav.profile')" />@endcan
            @if($staff->isServiceProvider())
              @can('schedules.view')<x-admin-nav-link route="master.schedule.index" icon="calendar" :label="__('admin_nav.my_schedule')" />@endcan
              @can('master_services.view')<x-admin-nav-link route="master.services.index" icon="clipboard" :label="__('admin_nav.my_services')" />@endcan
              @can('schedules.view')<x-admin-nav-link route="master.time-off.index" icon="calendar-x" :label="__('admin_nav.my_time_off')" />@endcan
            @endif
          </x-admin-nav-group>
        @endif

        @if($showAdministration)
          <x-admin-nav-group id="adminNavAdministration" icon="settings" :label="__('admin_nav.administration')" :open="$administrationOpen">
            @can('settings.view')<x-admin-nav-link route="settings.index" icon="settings" :label="__('admin_nav.settings')" />@endcan
            @if($staff->role?->resolvedSlug() === 'super-admin')<x-admin-nav-link route="settings.localization.translations.index" icon="globe" :label="__('admin_nav.localization')" />@endif
            @can('promo_codes.view')<x-admin-nav-link route="promo-codes.index" icon="tag" :label="__('admin_nav.promo_codes')" />@endcan
            @if($staff->hasAllAppointmentsScope())
              @can('schedules.view')
                <x-admin-nav-link route="admin.red-days.index" icon="x-circle" :label="__('admin_nav.closed_time')" />
                <x-admin-nav-link route="admin.masters.today" icon="bar-chart-2" :label="__('admin_nav.staff_load')" />
              @endcan
              @can('appointments.view')<x-admin-nav-link route="admin.appointments.index" icon="table" :label="__('admin_nav.all_appointments')" />@endcan
            @endif
            @can('rooms.view')
              <x-admin-nav-link route="admin.rooms.index" icon="home" :label="__('admin_nav.rooms')" />
              <x-admin-nav-link route="admin.rooms.today" icon="grid" :label="__('admin_nav.room_load')" />
            @endcan
            @can('activity_logs.view')<x-admin-nav-link route="admin.activity-logs.index" icon="activity" :label="__('admin_nav.activity_log')" />@endcan
            @can('work_time.view')<x-admin-nav-link route="admin.work-time.index" icon="clock" :label="__('admin_nav.work_time_report')" />@endcan
            @if($staff->role?->resolvedSlug() === 'super-admin')<x-admin-nav-link route="admin.system-health.index" icon="heart" :label="__('admin_nav.system_health')" />@endif
            @can('roles.manage')<x-admin-nav-link route="admin.roles.index" icon="shield" :label="__('admin_nav.roles')" />@endcan
          </x-admin-nav-group>
        @endif
        <li class="nav-item d-lg-none mt-3"><div class="nav-item-wrapper"><a class="nav-link {{ request()->routeIs('technical-support.*') ? 'active' : '' }} label-1" href="{{ route('technical-support.create') }}"><div class="d-flex align-items-center"><span class="nav-link-icon"><span data-feather="life-buoy"></span></span><span class="nav-link-text-wrapper"><span class="nav-link-text">{{ __('admin_nav.technical_support') }}</span></span></div></a></div></li>
      </ul>
    </div>
  </div>
  <div class="navbar-vertical-footer px-2 gap-1">
    <a class="btn btn-phoenix-secondary flex-grow-1 min-w-0 white-space-nowrap d-flex align-items-center justify-content-center {{ request()->routeIs('technical-support.*') ? 'active' : '' }}" href="{{ route('technical-support.create') }}" title="{{ __('admin_nav.technical_support') }}"><span data-feather="life-buoy" style="width:17px;height:17px"></span><span class="navbar-vertical-footer-text ms-2">{{ __('admin_nav.technical_support') }}</span></a>
    <button class="btn navbar-vertical-toggle border-0 fw-semibold flex-shrink-0 white-space-nowrap d-flex align-items-center px-2" type="button" aria-label="Toggle sidebar"><span class="uil uil-left-arrow-to-left fs-8"></span><span class="uil uil-arrow-from-right fs-8"></span></button>
  </div>
</nav>
