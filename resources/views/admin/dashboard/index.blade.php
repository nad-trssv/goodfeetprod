@section('title', 'Dashboard')
  @push('styles')
  @endpush
  <x-dashboard-layout>
    <div class="content">
      <div class="d-flex mb-5 pt-8" id="scrollspyStats"><span class="fa-stack me-2 ms-n1"><svg class="svg-inline--fa fa-circle fa-stack-2x text-primary" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="circle" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><path fill="currentColor" d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512z"></path></svg><!-- <i class="fas fa-circle fa-stack-2x text-primary"></i> Font Awesome fontawesome.com --><svg class="svg-inline--fa fa-percent fa-inverse fa-stack-1x text-primary-subtle" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="percent" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" data-fa-i2svg=""><path fill="currentColor" d="M374.6 118.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-320 320c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l320-320zM128 128A64 64 0 1 0 0 128a64 64 0 1 0 128 0zM384 384a64 64 0 1 0 -128 0 64 64 0 1 0 128 0z"></path></svg><!-- <i class="fa-inverse fa-stack-1x text-primary-subtle fas fa-percentage"></i> Font Awesome fontawesome.com --></span>
        <div class="col">
          <h3 class="mb-0 text-primary position-relative fw-bold"><span class="bg-body pe-2">В этом месяце</span><span class="border border-primary position-absolute top-50 translate-middle-y w-100 start-0 z-n1"></span></h3>
          <p class="mb-0">Здесь вы можете увидеть статистику за текущий месяц</p>
        </div>
      </div>
      <div class="px-3 mb-5">
        <div class="row justify-content-start">
          <div class="col-6 col-md-4 col-xxl-2 text-center border-translucent border-start-xxl border-end-xxl-0 border-bottom-xxl-0 border-end border-bottom pb-4 pb-xxl-0 ">
            <i class="fa-solid fa-users text-primary fs-5 lh-1"></i>
            <h1 class="fs-5 pt-3">{{ $stats['clients'] }}</h1>
            <p class="fs-9 mb-0">Клиентов</p>
          </div>
          <div class="col-6 col-md-4 col-xxl-2 text-center border-translucent border-start-xxl border-end-xxl-0 border-bottom-xxl-0 border-end border-bottom pb-4 pb-xxl-0 ">
            <i class="fa-solid fa-calendar-xmark text-warning fs-5 lh-1"></i>
            <h1 class="fs-5 pt-3">{{ $stats['redDays'] }}</h1>
            <p class="fs-9 mb-0">Нерабочих дня</p>
          </div>
          <div class="col-6 col-md-4 col-xxl-2 text-center border-translucent border-start-xxl border-end-xxl-0 border-bottom-xxl-0 border-end border-bottom pb-4 pb-xxl-0 ">
            <span class="uil uil-wallet text-success fs-5 lh-1"></span>
            <h1 class="fs-5 pt-3">{{ $stats['salary'] }} &euro;</h1>
            <p class="fs-9 mb-0">Расчетная прибыль</p>
          </div>
          <div class="col-6 col-md-4 col-xxl-2 text-center border-translucent border-start-xxl border-end-xxl-0 border-bottom-xxl-0 border-end border-bottom pb-4 pb-xxl-0 ">
            <span class="stat_icons">
              <i class="uil fs-5 lh-1 uil-chart-growth text-primary"></i>
              <i class="fas fa-users text-warning icon_top"></i>
            </span>
            <h1 class="fs-5 pt-3 @if ($stats['clientsDifference'] > 0) text-success @endif">{{ $stats['clientsDifference'] }}</h1>
            <p class="fs-9 mb-0">Статистика клиентов в сравнении с прошлым месяцем</p>
          </div>
          <div class="col-6 col-md-4 col-xxl-2 text-center border-translucent border-start-xxl border-end-xxl-0 border-bottom-xxl-0 border-end border-bottom pb-4 pb-xxl-0 ">
            <span class="stat_icons">
              <i class="uil fs-5 lh-1 uil-chart-line text-primary"></i>
              <i class="fas fa-coins text-warning icon_top"></i>
            </span>
            <h1 class="fs-5 pt-3 @if ($stats['salaryDifference'] > 0) text-success @endif">{{ $stats['salaryDifference'] }} &euro;</h1>
            <p class="fs-9 mb-0">Статистика зарплаты в сравнении с прошлым месяцем</p>
          </div>
        </div>
      </div>
      <div class="mx-lg-n4 mt-3">
        <div class="row g-3">
          <div class="col-12 col-xl-6 col-xxl-8">
            <div class="card h-100">
              <div class="card-body">
                <div class="card-title mb-1">
                  <h3 class="text-body-emphasis">Активность</h3>
                </div>
                @if($appointments)
                  <p class="text-body-tertiary mb-4">На сегодня записаны {{ count($appointments) }} клиентов</p>
                @endif
                <div class="timeline-vertical timeline-with-details">
                  @if($appointments)
                    @foreach ($appointments as $event)
                      <div class="timeline-item position-relative event-item" 
                      data-start="{{ $event['start'] }}" 
                      data-end="{{ $event['end'] }}">
                        <div class="row mt-2 pt-2">
                          <div class="col-12 col-md-auto d-flex">
                            <div class="timeline-item-date order-1 order-md-0 me-md-4">
                              <p class="fs-10 fw-semibold text-body-tertiary text-opacity-85 text-end text-uppercase event-item">
                                {{ \Carbon\Carbon::parse($event['start'])->locale(App::getLocale())->translatedFormat('d M, Y') }}
                                <br class="d-none d-md-block" /> 
                                {{ \Carbon\Carbon::parse($event['start'])->format('H:i') }}
                              </p>
                            </div>
                            <div class="timeline-item-bar position-md-relative me-3 me-md-0">
                              <div class="icon-item icon-item-sm rounded-7 shadow-none dashboard_whatToday_icon" style="@if ($event['textColor']) background-color: {{ $event['textColor'] }} !important; @endif">
                                <span class="dashboard_whatToday_icon_svg fa-solid fa-chess text-primary-dark fs-10"></span>
                              </div>
                              @if (!$loop->last)
                              <span class="timeline-bar border-end border-dashed"></span>
                              @endif
                            </div>
                          </div>
                          <div class="col">
                            <div class="timeline-item-content ps-6 ps-md-3">
                              <h5 class="fs-9 lh-sm">{{ $event['title'] }} ( <span>{{ $event['price'] }} </span>&euro; )</h5>
                              <p class="fs-9 mb-0 event-item">Клиент: <a class="fw-semibold" href="#!">{{ $event['client_lastname'] }}, {{ $event['client_name'] }}</a></p>
                              <p class="fs-9 mb-0 event-item">Мастер: <a class="fw-semibold" href="#!">{{ $event['master'] }}</a></p>
                              <p class="fs-9 event-item">Номер клиента: <a class="fw-semibold" href="#!">{{ $event['client_phone'] }}</a></p>
                              @if ($event['description'])
                                <p class="fs-9 text-body-secondary mb-5 event-item">{{ $event['description'] }}</p>                                
                              @endif
                            </div>
                          </div>
                        </div>
                      </div>
                    @endforeach
                  @else
                    <p class="text-body-tertiary mb-4">На сегодня у вас 0 клиентов</p>
                  @endif
                </div>
              </div>
            </div>
          </div>
          <div class="col-12 col-xl-6 col-xxl-4">
              {{-- <div class="card h-100">
                <div class="card-body">
                  <div class="row g-0">
                    <div class="col-6 border-1 border-bottom border-translucent border-end py-2"> <a class="btn btn-link ps-2 fs-8 text-body-secondary text-primary-hover fw-semibold d-flex flex-column d-xxl-inline-block" href="#!"><svg class="svg-inline--fa fa-user-group me-2 mb-2 mb-xxl-0" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="user-group" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512" data-fa-i2svg=""><path fill="currentColor" d="M96 128a128 128 0 1 1 256 0A128 128 0 1 1 96 128zM0 482.3C0 383.8 79.8 304 178.3 304h91.4C368.2 304 448 383.8 448 482.3c0 16.4-13.3 29.7-29.7 29.7H29.7C13.3 512 0 498.7 0 482.3zM609.3 512H471.4c5.4-9.4 8.6-20.3 8.6-32v-8c0-60.7-27.1-115.2-69.8-151.8c2.4-.1 4.7-.2 7.1-.2h61.4C567.8 320 640 392.2 640 481.3c0 17-13.8 30.7-30.7 30.7zM432 256c-31 0-59-12.6-79.3-32.9C372.4 196.5 384 163.6 384 128c0-26.8-6.6-52.1-18.3-74.3C384.3 40.1 407.2 32 432 32c61.9 0 112 50.1 112 112s-50.1 112-112 112z"></path></svg><!-- <span class="fa-solid fa-user-group me-2 mb-2 mb-xxl-0"></span> Font Awesome fontawesome.com -->Followers</a></div>
                    <div class="col-6 border-1 border-bottom border-translucent py-2"><a class="btn btn-link fs-8 text-body-secondary text-primary-hover fw-semibold d-flex flex-column d-xxl-inline-block" href="#!"><svg class="svg-inline--fa fa-users me-2 mb-2 mb-xxl-0" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="users" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512" data-fa-i2svg=""><path fill="currentColor" d="M144 0a80 80 0 1 1 0 160A80 80 0 1 1 144 0zM512 0a80 80 0 1 1 0 160A80 80 0 1 1 512 0zM0 298.7C0 239.8 47.8 192 106.7 192h42.7c15.9 0 31 3.5 44.6 9.7c-1.3 7.2-1.9 14.7-1.9 22.3c0 38.2 16.8 72.5 43.3 96c-.2 0-.4 0-.7 0H21.3C9.6 320 0 310.4 0 298.7zM405.3 320c-.2 0-.4 0-.7 0c26.6-23.5 43.3-57.8 43.3-96c0-7.6-.7-15-1.9-22.3c13.6-6.3 28.7-9.7 44.6-9.7h42.7C592.2 192 640 239.8 640 298.7c0 11.8-9.6 21.3-21.3 21.3H405.3zM224 224a96 96 0 1 1 192 0 96 96 0 1 1 -192 0zM128 485.3C128 411.7 187.7 352 261.3 352H378.7C452.3 352 512 411.7 512 485.3c0 14.7-11.9 26.7-26.7 26.7H154.7c-14.7 0-26.7-11.9-26.7-26.7z"></path></svg><!-- <span class="fa-solid fa-users me-2 mb-2 mb-xxl-0"></span> Font Awesome fontawesome.com -->Communities</a></div>
                    <div class="col-6 border-1 border-bottom border-translucent border-end py-2"><a class="btn btn-link ps-2 fs-8 text-body-secondary text-primary-hover fw-semibold d-flex flex-column d-xxl-inline-block" href="#!"><svg class="svg-inline--fa fa-photo-film me-2 mb-2 mb-xxl-0" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="photo-film" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512" data-fa-i2svg=""><path fill="currentColor" d="M256 0H576c35.3 0 64 28.7 64 64V288c0 35.3-28.7 64-64 64H256c-35.3 0-64-28.7-64-64V64c0-35.3 28.7-64 64-64zM476 106.7C471.5 100 464 96 456 96s-15.5 4-20 10.7l-56 84L362.7 169c-4.6-5.7-11.5-9-18.7-9s-14.2 3.3-18.7 9l-64 80c-5.8 7.2-6.9 17.1-2.9 25.4s12.4 13.6 21.6 13.6h80 48H552c8.9 0 17-4.9 21.2-12.7s3.7-17.3-1.2-24.6l-96-144zM336 96a32 32 0 1 0 -64 0 32 32 0 1 0 64 0zM64 128h96V384v32c0 17.7 14.3 32 32 32H320c17.7 0 32-14.3 32-32V384H512v64c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V192c0-35.3 28.7-64 64-64zm8 64c-8.8 0-16 7.2-16 16v16c0 8.8 7.2 16 16 16H88c8.8 0 16-7.2 16-16V208c0-8.8-7.2-16-16-16H72zm0 104c-8.8 0-16 7.2-16 16v16c0 8.8 7.2 16 16 16H88c8.8 0 16-7.2 16-16V312c0-8.8-7.2-16-16-16H72zm0 104c-8.8 0-16 7.2-16 16v16c0 8.8 7.2 16 16 16H88c8.8 0 16-7.2 16-16V416c0-8.8-7.2-16-16-16H72zm336 16v16c0 8.8 7.2 16 16 16h16c8.8 0 16-7.2 16-16V416c0-8.8-7.2-16-16-16H424c-8.8 0-16 7.2-16 16z"></path></svg><!-- <span class="fa-solid fa-photo-film me-2 mb-2 mb-xxl-0"></span> Font Awesome fontawesome.com -->Media Files</a></div>
                    <div class="col-6 border-1 border-bottom border-translucent py-2"><a class="btn btn-link fs-8 text-body-secondary text-primary-hover fw-semibold d-flex flex-column d-xxl-inline-block" href="#!"><svg class="svg-inline--fa fa-calendar-days me-2 mb-2 mb-xxl-0" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="calendar-days" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" data-fa-i2svg=""><path fill="currentColor" d="M128 0c17.7 0 32 14.3 32 32V64H288V32c0-17.7 14.3-32 32-32s32 14.3 32 32V64h48c26.5 0 48 21.5 48 48v48H0V112C0 85.5 21.5 64 48 64H96V32c0-17.7 14.3-32 32-32zM0 192H448V464c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V192zm64 80v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V272c0-8.8-7.2-16-16-16H80c-8.8 0-16 7.2-16 16zm128 0v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V272c0-8.8-7.2-16-16-16H208c-8.8 0-16 7.2-16 16zm144-16c-8.8 0-16 7.2-16 16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V272c0-8.8-7.2-16-16-16H336zM64 400v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V400c0-8.8-7.2-16-16-16H80c-8.8 0-16 7.2-16 16zm144-16c-8.8 0-16 7.2-16 16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V400c0-8.8-7.2-16-16-16H208zm112 16v32c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V400c0-8.8-7.2-16-16-16H336c-8.8 0-16 7.2-16 16z"></path></svg><!-- <span class="fa-solid fa-calendar-days me-2 mb-2 mb-xxl-0"></span> Font Awesome fontawesome.com -->Events</a></div>
                    <div class="col-6 border-1 border-end border-translucent py-2"><a class="btn btn-link ps-2 fs-8 text-body-secondary text-primary-hover fw-semibold d-flex flex-column d-xxl-inline-block" href="#!"><svg class="svg-inline--fa fa-dice me-2 mb-2 mb-xxl-0" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="dice" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512" data-fa-i2svg=""><path fill="currentColor" d="M274.9 34.3c-28.1-28.1-73.7-28.1-101.8 0L34.3 173.1c-28.1 28.1-28.1 73.7 0 101.8L173.1 413.7c28.1 28.1 73.7 28.1 101.8 0L413.7 274.9c28.1-28.1 28.1-73.7 0-101.8L274.9 34.3zM200 224a24 24 0 1 1 48 0 24 24 0 1 1 -48 0zM96 200a24 24 0 1 1 0 48 24 24 0 1 1 0-48zM224 376a24 24 0 1 1 0-48 24 24 0 1 1 0 48zM352 200a24 24 0 1 1 0 48 24 24 0 1 1 0-48zM224 120a24 24 0 1 1 0-48 24 24 0 1 1 0 48zm96 328c0 35.3 28.7 64 64 64H576c35.3 0 64-28.7 64-64V256c0-35.3-28.7-64-64-64H461.7c11.6 36 3.1 77-25.4 105.5L320 413.8V448zM480 328a24 24 0 1 1 0 48 24 24 0 1 1 0-48z"></path></svg><!-- <span class="fa-solid fa-dice me-2 mb-2 mb-xxl-0"></span> Font Awesome fontawesome.com -->Games</a></div>
                    <div class="col-6 border-1 py-2"><a class="btn btn-link fs-8 text-body-secondary text-primary-hover fw-semibold d-flex flex-column d-xxl-inline-block" href="#!"><svg class="svg-inline--fa fa-user-gear me-2 mb-2 mb-xxl-0" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="user-gear" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512" data-fa-i2svg=""><path fill="currentColor" d="M224 0a128 128 0 1 1 0 256A128 128 0 1 1 224 0zM178.3 304h91.4c11.8 0 23.4 1.2 34.5 3.3c-2.1 18.5 7.4 35.6 21.8 44.8c-16.6 10.6-26.7 31.6-20 53.3c4 12.9 9.4 25.5 16.4 37.6s15.2 23.1 24.4 33c15.7 16.9 39.6 18.4 57.2 8.7v.9c0 9.2 2.7 18.5 7.9 26.3H29.7C13.3 512 0 498.7 0 482.3C0 383.8 79.8 304 178.3 304zM436 218.2c0-7 4.5-13.3 11.3-14.8c10.5-2.4 21.5-3.7 32.7-3.7s22.2 1.3 32.7 3.7c6.8 1.5 11.3 7.8 11.3 14.8v17.7c0 7.8 4.8 14.8 11.6 18.7c6.8 3.9 15.1 4.5 21.8 .6l13.8-7.9c6.1-3.5 13.7-2.7 18.5 2.4c7.6 8.1 14.3 17.2 20.1 27.2s10.3 20.4 13.5 31c2.1 6.7-1.1 13.7-7.2 17.2l-14.4 8.3c-6.5 3.7-10 10.9-10 18.4s3.5 14.7 10 18.4l14.4 8.3c6.1 3.5 9.2 10.5 7.2 17.2c-3.3 10.6-7.8 21-13.5 31s-12.5 19.1-20.1 27.2c-4.8 5.1-12.5 5.9-18.5 2.4l-13.8-7.9c-6.7-3.9-15.1-3.3-21.8 .6c-6.8 3.9-11.6 10.9-11.6 18.7v17.7c0 7-4.5 13.3-11.3 14.8c-10.5 2.4-21.5 3.7-32.7 3.7s-22.2-1.3-32.7-3.7c-6.8-1.5-11.3-7.8-11.3-14.8V467.8c0-7.9-4.9-14.9-11.7-18.9c-6.8-3.9-15.2-4.5-22-.6l-13.5 7.8c-6.1 3.5-13.7 2.7-18.5-2.4c-7.6-8.1-14.3-17.2-20.1-27.2s-10.3-20.4-13.5-31c-2.1-6.7 1.1-13.7 7.2-17.2l14-8.1c6.5-3.8 10.1-11.1 10.1-18.6s-3.5-14.8-10.1-18.6l-14-8.1c-6.1-3.5-9.2-10.5-7.2-17.2c3.3-10.6 7.7-21 13.5-31s12.5-19.1 20.1-27.2c4.8-5.1 12.4-5.9 18.5-2.4l13.6 7.8c6.8 3.9 15.2 3.3 22-.6c6.9-3.9 11.7-11 11.7-18.9V218.2zm92.1 133.5a48.1 48.1 0 1 0 -96.1 0 48.1 48.1 0 1 0 96.1 0z"></path></svg><!-- <span class="fa-solid fa-user-gear me-2 mb-2 mb-xxl-0"></span> Font Awesome fontawesome.com -->Settings </a></div>
                  </div>
                </div>
              </div> --}}
          </div>
        </div>
      </div>
      
      <div class="mx-lg-n4 mt-3 mb-3">
        <div class="row g-3">
          <div class="col-12 col-xl-4 col-xxl-4">
            <div class="card h-100">
              <div class="card-body">
                <div class="card-title mb-1 d-flex pb-4 border-bottom border-dashed align-items-end">
                  <h3 class="flex-1 mb-0">События</h3><a class="fw-bold fs-9" href="#!">Все события</a>
                </div>
                @if ($events->isNotEmpty())
                  @foreach ($events as $event)
                    <div class="py-3">
                      <div class="d-flex flex-between-center">
                        <p class="text-warning fs-10 mb-0 fw-bold mb-1 text-uppercase">
                          @if ($event['start_time'] !== null && $event['end_time'] !== null)
                            {{ \Carbon\Carbon::parse($event['date'] . ' ' . $event['start_time'])->translatedFormat('D, M d H:i') }} 
                            - 
                            {{ \Carbon\Carbon::parse($event['date'] . ' ' . $event['end_time'])->translatedFormat('H:i') }}    
                            @else
                            {{ \Carbon\Carbon::parse($event['date'] . ' ' . $event['start_time'])->translatedFormat('D, M d') }}
                          @endif
                        </p>
                      </div>
                      <a class="text-primary-hover text-body-highlight fw-bold mb-2 line-clamp-1 me-5 lh-base" href="#!">
                        @if ($event['type'] === 'redday' )
                          <i class="far fa-calendar-times text-danger"></i>
                        @elseif ($event['type'] === 'event' )
                        <i class="far fa-bell text-success"></i>
                        @endif
                        {{ $event['name'] }}
                      </a>
                      @if ($event['user_id'])
                        <p class="text-body-secondary fs-9 mb-2">Именинник: 
                          <a class="fw-bold text-primary" href="#!">
                            {{ $event->celebrant->name }}
                          </a>
                        </p>
                      @endif
                      @if ($event['organized_by'])
                        <p class="text-body-secondary fs-9 mb-2">Организатор: <a class="fw-bold text-primary" href="#!">{{ $event->organizer->name }}</a></p>
                      @endif
                      <p class="fs-10 text-body-tertiary text-opacity-85">{{ $event['description'] }} </p>
                      @if ($event['start_time'] !== null)
                        <p class="fs-9 text-body-tertiary fw-bold mb-1"><span class="fa-solid fa-clock text-body-secondary me-1"></span>
                          {{ $event['start_time'] }} 
                          @if ($event['end_time'] !== null)
                            - {{ $event['end_time'] }} 
                          @endif
                        </p>
                      @endif
                    </div>
                  @endforeach
                @else
                  <p class="text-body-tertiary my-4">На сегодня нет мероприятий!</p>
                @endif
              </div>
            </div>
          </div>
          <div class="col-12 col-xl-8 col-xxl-8">
            <div class="card h-100">
              <div class="card-body">
                <div class="row g-0">
                  <div class="quickcode-chart-by-day" style="min-height:300px"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="row col-12 my-6">
        <div class="col-6">
          <div class="quickcode-chart-by-month-sales" style="min-height:300px"></div>
        </div>
        <div class="col-6">
          <div class="quickcode-chart-by-month-clients" style="min-height:300px"></div>
        </div>
      </div>
      <div class="row col-12 my-6">
        <div class="col-12">
          <div class="quickcode-gauge-by-day" style="min-height:300px"></div>
        </div>
      </div>
      <x-dashboard-footer />
    </div>
    @push('scripts')
      <script>
        //Activity today
        const items = document.querySelectorAll('.event-item');
        const now = new Date();
        items.forEach((item) => {
            const start = new Date(item.dataset.start);
            const end = new Date(item.dataset.end);
        
            if (now >= start && now <= end) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        });

    // CHarts
    const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--phoenix-primary-rgb').trim();
    // Chart By Day
    var chartContainerByDay = document.querySelector('.quickcode-chart-by-day');
    var chartDataByDay = @json($chartDataByDay);
    var myChartByDay = echarts.init(chartContainerByDay);
    var optionByDay = {
        title: {
            text: 'Общие продажи и количество записей по дням',
            left: 'center',
            textStyle: {
                color: `rgba(${primaryColor}, 1)`,
            },
        },
        tooltip: {
            trigger: 'axis',
            axisPointer: {
                type: 'shadow'
            },
        },
        legend: {
          data: ['Общие продажи', 'Количество записей'],
          top: '10%'
        },
        xAxis: {
            type: 'category',
            data: chartDataByDay.labels 
        },
        yAxis: [
            {
                type: 'value',
                name: 'Общие продажи',
                position: 'left',
                axisLabel: {
                    formatter: '{value} €' 
                }
            },
            {
                type: 'value',
                name: 'Количество записей',
                position: 'right'
            }
        ],
        series: [
            {
                name: 'Общие продажи',
                type: 'line',
                smooth: true,
                data: chartDataByDay.data, 
                lineStyle: {
                    color: 'rgba(255, 158, 68, 1)'
                },
                areaStyle: {
                    color: new echarts.graphic.LinearGradient(
                        0, 0, 0, 1,
                        [
                          { offset: 0, color: 'rgba(255, 148, 0, 0.8)' },
                            { offset: 1, color: 'rgba(255, 70, 131, 0.2)' }
                        ]
                    )
                },
                itemStyle: {
                    color: '#ef8b00'
                }
            },
            {
                name: 'Количество записей',
                type: 'bar', 
                yAxisIndex: 1, 
                data: chartDataByDay.counts,
                itemStyle: {
                    color: new echarts.graphic.LinearGradient(
                        0, 0, 0, 1,
                        [
                            { offset: 0, color: 'rgba(137, 110, 181, 0.8)' },
                            { offset: 1, color: 'rgba(190, 178, 211, 0.2)' }
                        ]
                    )
                }
            }
        ]
    };
    // Chart By Month
    var chartDataByMonth = @json($chartDataByMonth);

    //Sales
    var chartContainerByMonthSales = document.querySelector('.quickcode-chart-by-month-sales');
    var myChartByMonthSales = echarts.init(chartContainerByMonthSales);
    var optionByMonthSales = {
        title: {
            text: 'Общие продажи за месяц',
            left: 'center',
            textStyle: {
                color: `rgba(${primaryColor}, 1)`,
            },
        },
        tooltip: {
            trigger: 'axis',
        },
        legend: {
          data: ['Продажи'],
          top: '10%'
        },
        xAxis: {
            type: 'category',
            data: chartDataByMonth.labels 
        },
        yAxis: [
            {
                type: 'value',
                name: 'Продажи',
                position: 'left',
                axisLabel: {
                    formatter: '{value} €'
                }
            },
        ],
        series: [
            {
                name: 'Продажи',
                type: 'line',
                smooth: true,
                data: chartDataByMonth.data, 
                lineStyle: {
                    color: 'rgba(255, 158, 68, 1)'
                },
                areaStyle: {
                    color: new echarts.graphic.LinearGradient(
                        0, 0, 0, 1,
                        [
                            { offset: 0, color: 'rgba(255, 148, 0, 0.8)' },
                            { offset: 1, color: 'rgba(255, 70, 131, 0.2)' }
                        ]
                    )
                },
                itemStyle: {
                    color: '#ef8b00'
                }
            },
        ]
    };

    //Clients
    var chartContainerByMonthClients = document.querySelector('.quickcode-chart-by-month-clients');
    var myChartByMonthClients = echarts.init(chartContainerByMonthClients);
    var optionByMonthClients = {
        title: {
            text: 'Общее количество клиентов за месяц',
            left: 'center',
            textStyle: {
                color: `rgba(${primaryColor}, 1)`,
            },
        },
        tooltip: {
            trigger: 'axis',
        },
        legend: {
          data: ['Клиенты'],
          top: '10%'
        },
        xAxis: {
            type: 'category',
            data: chartDataByMonth.labels 
        },
        yAxis: [
            {
                type: 'value',
                name: 'Клиенты',
                position: 'left',
            },
        ],
        series: [
            {
                name: 'Клиенты',
                type: 'bar',
                data: chartDataByMonth.counts, 
                itemStyle: {
                    color: new echarts.graphic.LinearGradient(
                        0, 0, 0, 1,
                        [
                            { offset: 0, color: 'rgba(137, 110, 181, 0.8)' },
                            { offset: 1, color: 'rgba(190, 178, 211, 0.2)' }
                        ]
                    )
                }
            },
        ]
    };

    //gauge
    var chartGauge = document.querySelector('.quickcode-gauge-by-day');
    var myChartGauge = echarts.init(chartGauge);
    var optionGauge = {
        title: {
            text: 'Активность на сегодняшний день',
            left: 'center',
            textStyle: {
                color: `rgba(${primaryColor}, 1)`,
            },
        },
        tooltip: {
          trigger: 'axis',
          padding: [7, 10],
          backgroundColor: `rgba(${primaryColor}, 1)`,
          borderColor: `rgba(${primaryColor}, 1)`,
          textStyle: { color: `rgba(${primaryColor}, 1)` },
          borderWidth: 1,
          formatter: params => tooltipFormatter(params),
          transitionDuration: 0,
          axisPointer: {
            type: 'none'
          }
        },
        series: [
          {
            type: 'gauge',
            center: ['50%', '60%'],
            radius: '100%',
            startAngle: 180,
            endAngle: 0,
            progress: {
              show: true,
              width: 18,
              itemStyle: {
                color: '#a088c2',
                shadowColor: `#c6bcd5`,
              }
            },
            itemStyle: {
              color: `rgba(${primaryColor}, 1)`,
              shadowColor: `rgba(${primaryColor}, 1)`,
              shadowBlur: 10,
              shadowOffsetX: 2,
              shadowOffsetY: 2
            },
            axisLine: {
              lineStyle: {
                width: 18,
                color: [[1, '#cfd1d9']]
              }
            },
            axisTick: {
              show: false
            },
            splitLine: {
              lineStyle: {
                width: 2,
                color: `#cfd1d9` 
              }
            },
            axisLabel: {
              distance: 25,
              color: `#acaeb9` //цвет текста потом поменять в зависимости от темы (10 20 30)
            },
            anchor: {
              show: true,
              showAbove: true,
              size: 25,
              itemStyle: {
                color: `rgba(${primaryColor}, 1)`
              }
            },
            title: {
              show: false
            },
            detail: {
              valueAnimation: true,
              fontSize: 80,
              offsetCenter: [0, '70%']
            },
            data: [
              {
                value: @json($activity),
                detail: {
                  fontSize: 30,
                  color: `rgba(${primaryColor}, 1)`,
                  offsetCenter: [0, '40%'],
                  formatter: '{value}%'
                }
              }
            ]
          }
        ]
    };

      myChartByDay.setOption(optionByDay);
      myChartByMonthSales.setOption(optionByMonthSales);
      myChartByMonthClients.setOption(optionByMonthClients);
    </script>
    @endpush
  </x-dashboard-layout>
