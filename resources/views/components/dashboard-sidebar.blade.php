<nav class="navbar navbar-vertical navbar-expand-lg">
    <div class="collapse navbar-collapse" id="navbarVerticalCollapse">
        <div class="navbar-vertical-content">
            <ul class="navbar-nav flex-column" id="navbarVerticalNav">

                {{-- Панель управления --}}
                <li class="nav-item">
                    <div class="nav-item-wrapper">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }} label-1"
                            href="{{ route('dashboard') }}" role="button">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span data-feather="pie-chart"></span></span>
                                <span class="nav-link-text-wrapper"><span class="nav-link-text">Панель
                                        управления</span></span>
                            </div>
                        </a>
                    </div>
                </li>

                {{-- Мои записи --}}
                <li class="nav-item">
                    <p class="navbar-vertical-label">Мои записи</p>
                    <hr class="navbar-vertical-line" />
                    <div class="nav-item-wrapper">
                        <a class="nav-link {{ request()->routeIs('calendar.index') ? 'active' : '' }} label-1"
                            href="{{ route('calendar.index') }}" role="button">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span data-feather="calendar"></span></span>
                                <span class="nav-link-text-wrapper"><span class="nav-link-text">Календарь</span></span>
                            </div>
                        </a>
                    </div>
                    <div class="nav-item-wrapper">
                        <a class="nav-link {{ request()->routeIs('calendarList') ? 'active' : '' }} label-1"
                            href="{{ route('calendarList') }}" role="button">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span data-feather="list"></span></span>
                                <span class="nav-link-text-wrapper"><span class="nav-link-text">Записи</span></span>
                            </div>
                        </a>
                    </div>
                    <div class="nav-item-wrapper">
                        <a class="nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }} label-1" href="{{ route('notifications.index') }}" role="button">
                            <div class="d-flex align-items-center w-100"><span class="nav-link-icon"><span data-feather="bell"></span></span><span class="nav-link-text-wrapper flex-grow-1"><span class="nav-link-text">{{ __('admin_notifications.title') }}</span></span><span id="sidebar-notification-count" class="badge rounded-pill bg-danger ms-auto {{ $unreadNotificationCount > 0 ? '' : 'd-none' }}">{{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}</span></div>
                        </a>
                    </div>
                    <div class="nav-item-wrapper">
                        <a class="nav-link {{ request()->routeIs('reschedule.*') ? 'active' : '' }} label-1" href="{{ route('reschedule.index') }}" role="button">
                            <div class="d-flex align-items-center"><span class="nav-link-icon"><span data-feather="repeat"></span></span><span class="nav-link-text-wrapper"><span class="nav-link-text">{{ __('customer.reschedule_requests') }}</span></span></div>
                        </a>
                    </div>
                </li>

                {{-- Управление --}}
                <li class="nav-item">
                    <p class="navbar-vertical-label">Управление</p>
                    <hr class="navbar-vertical-line" />

                    {{-- Услуги --}}
                    <div class="nav-item-wrapper">
                        <a class="nav-link dropdown-indicator label-1" href="#nv-services" role="button"
                            data-bs-toggle="collapse" aria-expanded="false">
                            <div class="d-flex align-items-center">
                                <div class="dropdown-indicator-icon-wrapper">
                                    <span class="fas fa-caret-right dropdown-indicator-icon"></span>
                                </div>
                                <span class="nav-link-icon"><span data-feather="clipboard"></span></span>
                                <span class="nav-link-text">Услуги</span>
                            </div>
                        </a>
                        <div class="parent-wrapper label-1">
                            <ul class="nav collapse parent" data-bs-parent="#navbarVerticalCollapse" id="nv-services">
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('service.index') }}">
                                        <div class="d-flex align-items-center"><span class="nav-link-text">Список</span>
                                        </div>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('service.create') }}">
                                        <div class="d-flex align-items-center"><span
                                                class="nav-link-text">Создать</span></div>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    {{-- Мастера — только админ --}}
                    @can('is-superadmin')
                        <div class="nav-item-wrapper">
                            <a class="nav-link dropdown-indicator label-1" href="#nv-masters" role="button"
                                data-bs-toggle="collapse" aria-expanded="false">
                                <div class="d-flex align-items-center">
                                    <div class="dropdown-indicator-icon-wrapper">
                                        <span class="fas fa-caret-right dropdown-indicator-icon"></span>
                                    </div>
                                    <span class="nav-link-icon"><span data-feather="users"></span></span>
                                    <span class="nav-link-text">Мастера</span>
                                </div>
                            </a>
                            <div class="parent-wrapper label-1">
                                <ul class="nav collapse parent" data-bs-parent="#navbarVerticalCollapse" id="nv-masters">
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('member.index') }}">
                                            <div class="d-flex align-items-center"><span class="nav-link-text">Список</span>
                                            </div>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('member.create') }}">
                                            <div class="d-flex align-items-center"><span
                                                    class="nav-link-text">Добавить</span></div>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="{{ route('admin.masters.schedule') }}">
                                            <div class="d-flex align-items-center"><span
                                                    class="nav-link-text">Расписание</span></div>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    @endcan
                </li>

                {{-- Мой профиль --}}
                <li class="nav-item">
                    <p class="navbar-vertical-label">Мой профиль</p>
                    <hr class="navbar-vertical-line" />
                    <div class="nav-item-wrapper">
                        <a class="nav-link {{ request()->routeIs('profile.index') ? 'active' : '' }} label-1"
                            href="{{ route('profile.index') }}" role="button">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span class="fas fa-user"></span></span>
                                <span class="nav-link-text-wrapper"><span class="nav-link-text">Профиль</span></span>
                            </div>
                        </a>
                    </div>
                    <div class="nav-item-wrapper">
                        <a class="nav-link {{ request()->routeIs('master.schedule.index') ? 'active' : '' }} label-1"
                            href="{{ route('master.schedule.index') }}" role="button">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span class="fas fa-calendar-alt"></span></span>
                                <span class="nav-link-text-wrapper"><span class="nav-link-text">Моё
                                        расписание</span></span>
                            </div>
                        </a>
                    </div>
                    <div class="nav-item-wrapper">
                        <a class="nav-link {{ request()->routeIs('master.services.index') ? 'active' : '' }} label-1"
                            href="{{ route('master.services.index') }}" role="button">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span class="fas fa-clipboard-list"></span></span>
                                <span class="nav-link-text-wrapper"><span class="nav-link-text">Мои
                                        услуги</span></span>
                            </div>
                        </a>
                    </div>
                    <div class="nav-item-wrapper">
                        <a class="nav-link {{ request()->routeIs('master.time-off.index') ? 'active' : '' }} label-1"
                            href="{{ route('master.time-off.index') }}" role="button">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span class="fas fa-calendar-times"></span></span>
                                <span class="nav-link-text-wrapper"><span class="nav-link-text">Мои закрытые
                                        окна</span></span>
                            </div>
                        </a>
                    </div>
                </li>

                {{-- Администрирование — только админ --}}
                @can('is-superadmin')
                    <li class="nav-item">
                        <p class="navbar-vertical-label">Администрирование</p>
                        <hr class="navbar-vertical-line" />
                        <div class="nav-item-wrapper">
                            <a class="nav-link {{ request()->routeIs('settings.index') ? 'active' : '' }} label-1"
                                href="{{ route('settings.index') }}" role="button">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon"><span class="fas fa-cog"></span></span>
                                    <span class="nav-link-text-wrapper"><span
                                            class="nav-link-text">Настройки</span></span>
                                </div>
                            </a>
                        </div>
                        <div class="nav-item-wrapper">
                            <a class="nav-link {{ request()->routeIs('admin.red-days.index') ? 'active' : '' }} label-1"
                                href="{{ route('admin.red-days.index') }}" role="button">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon"><span class="fas fa-calendar-times"></span></span>
                                    <span class="nav-link-text-wrapper"><span class="nav-link-text">Нерабочее
                                            время</span></span>
                                </div>
                            </a>
                        </div>
                        <div class="nav-item-wrapper">
                            <a class="nav-link {{ request()->routeIs('admin.appointments.index') ? 'active' : '' }} label-1"
                                href="{{ route('admin.appointments.index') }}" role="button">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon"><span class="fas fa-table-list"></span></span>
                                    <span class="nav-link-text-wrapper"><span class="nav-link-text">Все
                                            записи</span></span>
                                </div>
                            </a>
                        </div>
                        <div class="nav-item-wrapper">
                            <a class="nav-link {{ request()->routeIs('admin.masters.today') || request()->routeIs('admin.masters.day') ? 'active' : '' }} label-1"
                                href="{{ route('admin.masters.today') }}" role="button">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon"><span class="fas fa-chart-bar"></span></span> <span
                                        class="nav-link-text-wrapper"><span class="nav-link-text">Нагрузка
                                            мастеров</span></span>
                                </div>
                            </a>
                        </div>
                        <div class="nav-item-wrapper">
                            <a class="nav-link {{ request()->routeIs('admin.rooms.index') ? 'active' : '' }} label-1"
                                href="{{ route('admin.rooms.index') }}" role="button">
                                <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span class="fas fa-door-open"></span></span>
                                <span class="nav-link-text-wrapper"><span class="nav-link-text">Кабинеты</span></span>
                                </div>
                            </a>
                        </div>
                        <div class="nav-item-wrapper">
                            <a class="nav-link {{ request()->routeIs('admin.rooms.today') || request()->routeIs('admin.rooms.day') ? 'active' : '' }} label-1"
                                href="{{ route('admin.rooms.today') }}" role="button">
                                <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><span class="fas fa-door-open"></span></span>
                                <span class="nav-link-text-wrapper"><span class="nav-link-text">Нагрузка кабинетов</span></span>
                                </div>
                            </a>
                        </div>
                        <div class="nav-item-wrapper">
                            <a
                                class="nav-link
                                    {{ request()->routeIs('admin.activity-logs.*')
                                        ? 'active'
                                        : '' }}
                                    label-1"
                                href="{{ route('admin.activity-logs.index') }}"
                                role="button"
                            >
                                <div class="d-flex align-items-center">
                                <span class="nav-link-icon">
                                    <span class="fas fa-history"></span>
                                </span>

                                <span class="nav-link-text-wrapper">
                                    <span class="nav-link-text">
                                    Журнал действий
                                    </span>
                                </span>
                                </div>
                            </a>
                        </div>
                    </li>
                @endcan

            </ul>
        </div>
    </div>
    <div class="navbar-vertical-footer">
        <button
            class="btn navbar-vertical-toggle border-0 fw-semibold w-100 white-space-nowrap d-flex align-items-center">
            <span class="uil uil-left-arrow-to-left fs-8"></span>
            <span class="uil uil-arrow-from-right fs-8"></span>
            <span class="navbar-vertical-footer-text ms-2"></span>
        </button>
    </div>
</nav>
