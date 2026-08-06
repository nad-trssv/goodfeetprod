@section('title', 'Расписание мастеров')

<x-dashboard-layout>
  <div class="content">
    <nav class="mb-3" aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('member.index') }}">Мастера</a></li>
        <li class="breadcrumb-item active">Расписание</li>
      </ol>
    </nav>

    <h2 class="mb-4">Расписание мастеров</h2>

    <div class="card">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-sm table-bordered fs-9 mb-0 align-middle">
            <thead class="bg-light">
              <tr>
                <th class="ps-3" style="min-width:150px">Мастер</th>
                <th class="text-center">Пн</th>
                <th class="text-center">Вт</th>
                <th class="text-center">Ср</th>
                <th class="text-center">Чт</th>
                <th class="text-center">Пт</th>
                <th class="text-center">Сб</th>
                <th class="text-center">Вс</th>
                <th class="text-center">Обед</th>
                <th class="text-center">Услуги</th>
                <th class="text-center"></th>
              </tr>
            </thead>
            <tbody>
              @foreach ($masters as $master)
              @php $s = $master->schedule; @endphp
              <tr>
                <td class="ps-3">
                  <div class="d-flex align-items-center gap-2">
                    <x-ui.avatar :user="$master" :size="32" />
                    <div>
                      <div class="fw-semibold">{{ $master->name }}</div>
                      <div class="text-muted fs-10">{{ $master->role->name }}</div>
                    </div>
                  </div>
                </td>
                @foreach(['monday','tuesday','wednesday','thursday','friday','saturday','sunday'] as $day)
                <td class="text-center">
                  @if($s && $s->{$day.'_start'} && $s->{$day.'_end'})
                    <span class="text-success fs-10 fw-semibold">
                      {{ substr($s->{$day.'_start'}, 0, 5) }}<br>{{ substr($s->{$day.'_end'}, 0, 5) }}
                    </span>
                  @else
                    <span class="text-muted fs-10">—</span>
                  @endif
                </td>
                @endforeach
                <td class="text-center">
                  @if($s && $s->lunch_start && $s->lunch_end)
                    <span class="fs-10">{{ substr($s->lunch_start, 0, 5) }}–{{ substr($s->lunch_end, 0, 5) }}</span>
                  @else
                    <span class="text-muted fs-10">—</span>
                  @endif
                </td>
                <td class="text-center" style="max-width:200px">
                  @forelse($master->services as $service)
                    <span class="badge bg-primary fs-10 me-1 mb-1">{{ $service->name }}</span>
                  @empty
                    <span class="text-muted fs-10">—</span>
                  @endforelse
                </td>
                <td class="text-center">
                  <a href="{{ route('member.edit', $master->id) }}" class="btn btn-sm btn-outline-primary">
                    <span class="fas fa-edit"></span>
                  </a>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <x-dashboard-footer />
  </div>
</x-dashboard-layout>
