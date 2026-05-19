<nav class="navbar navbar-vertical navbar-expand-lg">
    <div class="collapse navbar-collapse" id="navbarVerticalCollapse">
      <!-- scrollbar removed-->
      <div class="navbar-vertical-content">
        <ul class="navbar-nav flex-column" id="navbarVerticalNav">
          <li class="nav-item">
            <!-- parent pages-->
            <div class="nav-item-wrapper">
              <div class="parent-wrapper label-1">
                <div class="nav-item-wrapper">
                  <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }} label-1" href="{{ route('dashboard') }}" role="button" data-bs-toggle="" aria-expanded="false">
                    <div class="d-flex align-items-center"><span class="nav-link-icon"><span data-feather="pie-chart"></span></span><span class="nav-link-text-wrapper">
                      <span class="nav-link-text">Панель управления</span></span>
                    </div>
                  </a>
                </div>
              </div>
            </div>
          </li>
          <li class="nav-item">
            <p class="navbar-vertical-label">Приложения
            </p>
            <hr class="navbar-vertical-line" />
            <div class="nav-item-wrapper">
              <a class="nav-link {{ request()->routeIs('calendar.index') ? 'active' : '' }} label-1" href="{{ route('calendar.index') }}" role="button" data-bs-toggle="" aria-expanded="false">
                <div class="d-flex align-items-center"><span class="nav-link-icon"><span data-feather="calendar"></span></span><span class="nav-link-text-wrapper"><span class="nav-link-text">Календарь</span></span>
                </div>
              </a>
            </div>
            <div class="nav-item-wrapper">
              <a class="nav-link {{ request()->routeIs('calendarList') ? 'active' : '' }} label-1" href="{{ route('calendarList') }}" role="button" data-bs-toggle="" aria-expanded="false">
                <div class="d-flex align-items-center">
                  <span class="nav-link-icon">
                    <span data-feather="list"></span>
                  </span>
                  <span class="nav-link-text-wrapper">
                    <span class="nav-link-text">Записи</span>
                  </span>
                </div>
              </a>
            </div>
          </li>
          {{-- @can('is-superadmin')
            <li class="nav-item">
              <p class="navbar-vertical-label"> Общее
              </p>
              <hr class="navbar-vertical-line" />
                <div class="nav-item-wrapper">
                  <a class="nav-link {{ request()->routeIs('calendarAllMasters.index') ? 'active' : '' }} label-1" href="{{ route('calendarAllMasters.index') }}" role="button" data-bs-toggle="" aria-expanded="false">
                    <div class="d-flex align-items-center"><span class="nav-link-icon"><span data-feather="calendar"></span></span><span class="nav-link-text-wrapper"><span class="nav-link-text">Общий Календарь</span></span>
                    </div>
                  </a>
                </div>
                <div class="nav-item-wrapper">
                  <a class="nav-link {{ request()->routeIs('calendarListAllMasters') ? 'active' : '' }} label-1" href="{{ route('calendarListAllMasters') }}" role="button" data-bs-toggle="" aria-expanded="false">
                    <div class="d-flex align-items-center">
                      <span class="nav-link-icon">
                        <span data-feather="list"></span>
                      </span>
                      <span class="nav-link-text-wrapper">
                        <span class="nav-link-text">Все записи</span>
                      </span>
                    </div>
                  </a>
                </div>
            </li>
          @endcan --}}
          <li class="nav-item">
            <p class="navbar-vertical-label">
              Управление
            </p>
            <hr class="navbar-vertical-line" />
            <div class="nav-item-wrapper">
              <a class="nav-link dropdown-indicator label-1" href="#nv-project-management" role="button" data-bs-toggle="collapse" aria-expanded="false" aria-controls="nv-project-management">
                <div class="d-flex align-items-center">
                  <div class="dropdown-indicator-icon-wrapper">
                    <span class="fas fa-caret-right dropdown-indicator-icon"></span>
                  </div>
                  <span class="nav-link-icon">
                    <span data-feather="clipboard"></span>
                  </span>
                  <span class="nav-link-text">
                    Услуги
                  </span>
                </div>
              </a>
              <div class="parent-wrapper label-1">
                <ul class="nav collapse parent" data-bs-parent="#navbarVerticalCollapse" id="nv-project-management">
                  <li class="nav-item"><a class="nav-link" href="{{ route('service.index') }}">
                      <div class="d-flex align-items-center">
                        <span class="nav-link-text">Список</span>
                      </div>
                    </a>
                  </li>
                  <li class="nav-item"><a class="nav-link" href="{{ route('service.create') }}">
                      <div class="d-flex align-items-center">
                        <span class="nav-link-text">Создать</span>
                      </div>
                    </a>
                  </li>
                </ul>
              </div>
            </div>
            {{-- <div class="nav-item-wrapper">
              <a class="nav-link dropdown-indicator label-2" href="#nv-member-management" role="button" data-bs-toggle="collapse" aria-expanded="false" aria-controls="nv-member-management">
                <div class="d-flex align-items-center">
                  <div class="dropdown-indicator-icon-wrapper">
                    <span class="fas fa-caret-right dropdown-indicator-icon"></span>
                  </div>
                  <span class="nav-link-icon">
                    <span data-feather="users"></span>
                  </span>
                  <span class="nav-link-text">
                    Персонал
                  </span>
                </div>
              </a>
              <div class="parent-wrapper label-2">
                <ul class="nav collapse parent" data-bs-parent="#navbarVerticalCollapse" id="nv-member-management">
                  <li class="nav-item"><a class="nav-link" href="{{ route('member.index') }}">
                      <div class="d-flex align-items-center">
                        <span class="nav-link-text">Список</span>
                      </div>
                    </a>
                  </li>
                  <li class="nav-item"><a class="nav-link" href="{{ route('member.create') }}">
                      <div class="d-flex align-items-center">
                        <span class="nav-link-text">Создать</span>
                      </div>
                    </a>
                  </li>
                </ul>
              </div>
            </div> --}}
          </li>
          
            <li class="nav-item">
              <p class="navbar-vertical-label">
                Настройки
              </p>
              <hr class="navbar-vertical-line" />
              <li class="nav-item">
                <!-- parent pages-->
                <div class="nav-item-wrapper">

                  <div class="parent-wrapper label-1">
                    <div class="nav-item-wrapper">
                      <a class="nav-link {{ request()->routeIs('profile.index') ? 'active' : '' }} label-1" href="{{ route('profile.index') }}" role="button" data-bs-toggle="" aria-expanded="false">
                        <div class="d-flex align-items-center">
                          <span class="nav-link-icon">
                            <span class="fas fa-user"></span>
                          </span>
                          <span class="nav-link-text-wrapper">
                            <span class="nav-link-text">Профиль</span>
                          </span>
                        </div>
                      </a>
                    </div>
                  </div>
                  @can('is-superadmin')
                    <div class="parent-wrapper label-1">
                      <div class="nav-item-wrapper">
                        <a class="nav-link {{ request()->routeIs('settings.index') ? 'active' : '' }} label-1" href="{{ route('settings.index') }}" role="button" data-bs-toggle="" aria-expanded="false">
                          <div class="d-flex align-items-center">
                            <span class="nav-link-icon">
                              <span class="fas fa-cog"></span>
                            </span>
                            <span class="nav-link-text-wrapper">
                              <span class="nav-link-text">Настройки</span>
                            </span>
                          </div>
                        </a>
                      </div>
                    </div>
                  @endcan
                </div>
              </li>
            </li>
        </ul>
      </div>
    </div>
    <div class="navbar-vertical-footer">
      <button class="btn navbar-vertical-toggle border-0 fw-semibold w-100 white-space-nowrap d-flex align-items-center"><span class="uil uil-left-arrow-to-left fs-8"></span><span class="uil uil-arrow-from-right fs-8"></span><span class="navbar-vertical-footer-text ms-2">Collapsed View</span></button>
    </div>
  </nav>