@section('title', 'Мои услуги')

<x-dashboard-layout>
  <div class="content">
    <nav class="mb-3" aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item active">Мои услуги</li>
      </ol>
    </nav>

    <div id="successAlert" class="alert alert-success d-none" role="alert">
      <span class="fas fa-check-circle me-2"></span><span id="successText"></span>
    </div>

    <div class="row mb-4 align-items-center">
      <div class="col">
        <h2 class="mb-0">Мои услуги</h2>
        <p class="text-muted fs-9 mt-1">Выберите услуги которые вы оказываете</p>
      </div>
    </div>

    <div class="row g-3">
      @foreach ($allServices as $service)
      @php $isAttached = in_array($service->id, $myServiceIds); @endphp
      <div class="col-12 col-md-6 col-xl-4">
        <div class="card h-100 service-card {{ $isAttached ? 'border-success' : '' }}" id="service_card_{{ $service->id }}">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <h6 class="mb-0 fw-semibold">{{ $service->name }}</h6>
              <span class="badge {{ $isAttached ? 'bg-success' : 'bg-secondary' }} fs-10" id="badge_{{ $service->id }}">
                {{ $isAttached ? 'Активна' : 'Не активна' }}
              </span>
            </div>
            <div class="d-flex gap-3 mb-3">
              <span class="text-muted fs-10">
                <span class="fas fa-clock me-1"></span>{{ $service->duration_minutes }} мин
              </span>
              <span class="text-muted fs-10">
                <span class="fas fa-euro-sign me-1"></span>{{ $service->price }} €
              </span>
            </div>
            @if($service->short_description)
            <p class="text-muted fs-10 mb-3">{{ Str::limit($service->short_description, 80) }}</p>
            @endif
            <button class="btn btn-sm w-100 toggle-service {{ $isAttached ? 'btn-outline-danger' : 'btn-success' }}"
                    data-id="{{ $service->id }}"
                    data-name="{{ $service->name }}">
              <span class="fas {{ $isAttached ? 'fa-minus-circle' : 'fa-plus-circle' }} me-1"></span>
              <span class="btn-text">{{ $isAttached ? 'Отключить' : 'Подключить' }}</span>
            </button>
          </div>
        </div>
      </div>
      @endforeach
    </div>

    <x-dashboard-footer />
  </div>

  @push('scripts')
  <script>
    $(document).ready(function () {
      $(document).on('click', '.toggle-service', function () {
        const btn = $(this);
        const serviceId = btn.data('id');
        const serviceName = btn.data('name');

        btn.prop('disabled', true).html('<span class="fas fa-spinner fa-spin me-1"></span>...');

        $.ajax({
          url: '/master/services/' + serviceId + '/toggle',
          type: 'POST',
          headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
          success: function (response) {
            const card = $('#service_card_' + serviceId);
            const badge = $('#badge_' + serviceId);

            if (response.attached) {
              card.addClass('border-success');
              badge.removeClass('bg-secondary').addClass('bg-success').text('Активна');
              btn.removeClass('btn-success').addClass('btn-outline-danger');
              btn.html('<span class="fas fa-minus-circle me-1"></span><span class="btn-text">Отключить</span>');
              showSuccess('Услуга "' + serviceName + '" добавлена!');
            } else {
              card.removeClass('border-success');
              badge.removeClass('bg-success').addClass('bg-secondary').text('Не активна');
              btn.removeClass('btn-outline-danger').addClass('btn-success');
              btn.html('<span class="fas fa-plus-circle me-1"></span><span class="btn-text">Подключить</span>');
              showSuccess('Услуга "' + serviceName + '" отключена!');
            }

            btn.prop('disabled', false);
          },
          error: function () {
            btn.prop('disabled', false);
            btn.html('<span class="fas fa-exclamation-circle me-1"></span>Ошибка');
          }
        });
      });

      function showSuccess(msg) {
        $('#successText').text(msg);
        $('#successAlert').removeClass('d-none');
        setTimeout(() => $('#successAlert').addClass('d-none'), 3000);
      }
    });
  </script>
  @endpush
</x-dashboard-layout>