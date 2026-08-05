@section('title', 'Services index')
@push('styles')
    <link href="{{ asset('public/vendors/glightbox/glightbox.min.css') }}" rel="stylesheet">
@endpush
  <x-dashboard-layout>
    <div class="content">
      <div class="col-12 col-xxl-8 px-0 bg-body">
        <div class="px-4 px-lg-6 pt-6 pb-9">
          <div class="mb-5">
            <div class="d-flex justify-content-between">
              <div class="d-flex justify-content-start">
                <h2 class="text-body-emphasis fw-bolder mb-2">{{  $title  }} - {{  $client  }}</h2>
              </div>
              <div class="d-flex justify-content-end">
                @unless(in_array($appointment['status'], ['cancelled_by_client', 'cancelled_by_business', 'rescheduled'], true))
                  <button id="destroyEvent" data-event="{{ $appointment['id'] }}" class="btn btn-warning fw-regular fs-8">{{ __('admin_appointments.cancel') }}</button>
                @endunless
              </div>
            </div>
          </div>
          <div class="row gx-0 gx-sm-5 gy-8 mb-8">
            <div class="col-12">
              <div class="mb-4 mb-xl-7">
                <div class="row gx-0 gx-sm-7">
                  <div class="col-12 col-md-6">
                    <table class="lh-sm mb-4 mb-sm-0 mb-xl-4">
                      <tbody>
                        <tr>
                          <td class="py-1" colspan="2">
                            <div class="d-flex"><svg class="svg-inline--fa fa-earth-americas me-2 text-body-tertiary fs-9" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="earth-americas" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" data-fa-i2svg=""><path fill="currentColor" d="M57.7 193l9.4 16.4c8.3 14.5 21.9 25.2 38 29.8L163 255.7c17.2 4.9 29 20.6 29 38.5v39.9c0 11 6.2 21 16 25.9s16 14.9 16 25.9v39c0 15.6 14.9 26.9 29.9 22.6c16.1-4.6 28.6-17.5 32.7-33.8l2.8-11.2c4.2-16.9 15.2-31.4 30.3-40l8.1-4.6c15-8.5 24.2-24.5 24.2-41.7v-8.3c0-12.7-5.1-24.9-14.1-33.9l-3.9-3.9c-9-9-21.2-14.1-33.9-14.1H257c-11.1 0-22.1-2.9-31.8-8.4l-34.5-19.7c-4.3-2.5-7.6-6.5-9.2-11.2c-3.2-9.6 1.1-20 10.2-24.5l5.9-3c6.6-3.3 14.3-3.9 21.3-1.5l23.2 7.7c8.2 2.7 17.2-.4 21.9-7.5c4.7-7 4.2-16.3-1.2-22.8l-13.6-16.3c-10-12-9.9-29.5 .3-41.3l15.7-18.3c8.8-10.3 10.2-25 3.5-36.7l-2.4-4.2c-3.5-.2-6.9-.3-10.4-.3C163.1 48 84.4 108.9 57.7 193zM464 256c0-36.8-9.6-71.4-26.4-101.5L412 164.8c-15.7 6.3-23.8 23.8-18.5 39.8l16.9 50.7c3.5 10.4 12 18.3 22.6 20.9l29.1 7.3c1.2-9 1.8-18.2 1.8-27.5zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256z"></path></svg><!-- <span class="fa-solid fa-earth-americas me-2 text-body-tertiary fs-9"></span> Font Awesome fontawesome.com -->
                              <h5 class="text-body">{{  $title  }}</h5>
                            </div>
                          </td>
                        </tr>
                        <tr>
                          <td class="align-top py-1">
                            <div class="d-flex"><svg class="svg-inline--fa fa-credit-card me-2 text-body-tertiary fs-9" aria-hidden="true" focusable="false" data-prefix="far" data-icon="credit-card" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" data-fa-i2svg=""><path fill="currentColor" d="M512 80c8.8 0 16 7.2 16 16v32H48V96c0-8.8 7.2-16 16-16H512zm16 144V416c0 8.8-7.2 16-16 16H64c-8.8 0-16-7.2-16-16V224H528zM64 32C28.7 32 0 60.7 0 96V416c0 35.3 28.7 64 64 64H512c35.3 0 64-28.7 64-64V96c0-35.3-28.7-64-64-64H64zm56 304c-13.3 0-24 10.7-24 24s10.7 24 24 24h48c13.3 0 24-10.7 24-24s-10.7-24-24-24H120zm128 0c-13.3 0-24 10.7-24 24s10.7 24 24 24H360c13.3 0 24-10.7 24-24s-10.7-24-24-24H248z"></path></svg><!-- <span class="fa-regular fa-credit-card me-2 text-body-tertiary fs-9"></span> Font Awesome fontawesome.com -->
                              <h5 class="text-body mb-0 text-nowrap">Цена : </h5>
                            </div>
                          </td>
                          <td class="fw-bold ps-1 py-1 text-body-highlight">
                                @if ($appointment->service['price_can_change'])
                                    <span>От </span>
                                @endif
                                {{ $appointment['price'] }}
                                <span>&euro;</span>
                          </td>
                        </tr>
                        <tr>
                          <td class="align-top py-1">
                            <div class="d-flex">
                              <span class="fa-regular fa-user me-2 text-body-tertiary fs-9"></span>
                              <h5 class="text-body mb-0 text-nowrap">Мастер : </h5>
                            </div>
                          </td>
                          <td class="fw-bold ps-1 py-1 text-body-highlight">
                              {{ $appointment->user['name'] }}
                          </td>
                        </tr>
                        <tr>
                          <td class="align-top py-1">
                            <div class="d-flex">
                              <h5 class="text-body mb-0 text-nowrap mt-4">Данные клиента</h5>
                            </div>
                          </td>
                        </tr>
                        <tr>
                          <td class="align-top py-1">
                            <div class="d-flex">
                              <span class="fa-regular fa-user me-2 text-body-tertiary fs-9"></span>
                              <h5 class="text-body mb-0 text-nowrap">Имя : </h5>
                            </div>
                          </td>
                          <td class="fw-bold ps-1 py-1 text-body-highlight">
                              {{ $client }}
                          </td>
                        </tr>
                        <tr>
                          <td class="align-top py-1">
                            <div class="d-flex">
                                <span class="fa-solid fa-phone me-2 text-body-tertiary fs-9"></span>
                                <h5 class="text-body mb-0 text-nowrap">Номер телефона:</h5>
                            </div>
                          </td>
                          <td class="ps-1 py-1">{{  $appointment['client_phone']  }}</td>
                        </tr>
                        <tr>
                          <td class="align-top py-1">
                            <div class="d-flex">
                                <span class="fa-solid fa-envelope me-2 text-body-tertiary fs-9"></span>
                                <h5 class="text-body mb-0 text-nowrap">Email:</h5>
                            </div>
                          </td>
                          <td class="ps-1 py-1">{{  $appointment['client_email']  }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                  <div class="col-12 col-md-6">
                    <table class="lh-sm">
                      <tbody>
                        <tr>
                          <td class="align-top py-1 text-body text-nowrap fw-bold">Начало : </td>
                          <td class="text-body-tertiary text-opacity-85 fw-semibold ps-3">{{  $appointment['appointment_start']  }}</td>
                        </tr>
                        <tr>
                          <td class="align-top py-1 text-body text-nowrap fw-bold">Конец :</td>
                          <td class="text-body-tertiary text-opacity-85 fw-semibold ps-3">{{  $appointment['appointment_end']  }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <h3 class="text-body-emphasis mb-4">Дополнительное сообщение</h3>
          <p class="text-body-secondary mb-4">
            @if ($appointment['description'] )
                {{ $appointment['description'] }}
            @else
                Сообщений нет
            @endif
          </p>
        </div>
      </div>

      <div class="row g-2 g-sm-3">
        @foreach ($appointment->media as $item)
        <div class="col-6 col-md-4 col-lg-3"><a href="{{ asset('public/storage/'.$item['photo_path']) }}" class="img-fluid rounded-2 d-block h-100 overflow-hidden" data-gallery="default-gallery"><img class="h-100 w-100 object-fit-cover" src="{{ asset('public/storage/'.$item['photo_path']) }}" alt="{{ asset('public/storage/'.$item['photo_path']) }}"></a></div>
        @endforeach
      </div>

      <x-dashboard-footer />
    </div>
    @push('scripts')
    <script src="{{ asset('public/vendors/glightbox/glightbox.min.js') }}"></script>
    <script>
      
      $(document).ready(function() {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    }
                });
                $('#destroyEvent').on('click', function() {
                  var eventId = $(this).data('event');
                  var reason = window.prompt(@json(__('admin_appointments.cancel_reason')));
                  if (reason === null) return;
                  reason = reason.trim();
                  if (reason.length < 3) {
                    Swal.fire({ icon: 'error', text: @json(__('admin_appointments.reason_required')) });
                    return;
                  }
                    $.ajax({
                      url: "{{ route('calendar.destroy', '') }}"+'/'+eventId,
                      type: "DELETE",
                      data: { reason: reason },
                      dataType: "json",
                      success: function(response) {
                          Swal.fire({
                            position: "top-end",
                            icon: "success",
                            title: @json(__('admin_appointments.cancelled')),
                            showConfirmButton: false,
                            iconColor: '#1463b8',
                            timerProgressBar: true,
                            timer: 1500,
                          });
                          Swal.fire({
                            icon: "success",
                            title: @json(__('admin_appointments.cancelled')),
                            text: "Вы будете перенаправлены ко всем записям через 3 секунды.",
                            showConfirmButton: true,
                            confirmButtonText: "ОК",
                            iconColor: '#1463b8',
                            timer: 3000,
                            timerProgressBar: true,
                            allowOutsideClick: false
                          }).then((result) => {
                            if (result.isConfirmed) {
                              window.location.href = "{{ route('calendarList') }}";
                            }
                          });

                          setTimeout(() => {
                            window.location.href = "{{ route('calendarList') }}";
                          }, 3000);
                      },
                      error: function(err) {
                          console.log('Ошибка при удалении события:', err);
                      }
                  });
                });
    });
    </script>
    @endpush
  </x-dashboard-layout>
