@section('title', __('admin_staff.employee_card'))
<x-dashboard-layout>
<div class="content">
  <nav class="mb-3" aria-label="breadcrumb"><ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="{{ route('member.index') }}">{{ __('admin_staff.employees') }}</a></li><li class="breadcrumb-item active">{{ $member->name }}</li></ol></nav>
  <div id="successAlert" class="alert alert-success d-none">{{ __('admin_staff.employee_saved') }}</div>
  <div id="errorAlert" class="alert alert-danger d-none"><span id="errorText"></span></div>

  <div class="d-flex flex-column flex-md-row align-items-md-center gap-3 mb-4">
    <img id="photoPreview" src="{{ $member->profile_photo_path ? asset('storage/'.$member->profile_photo_path) : asset('assets/img/team/avatar.webp') }}" class="rounded-circle flex-shrink-0" style="width:96px;height:96px;object-fit:cover" alt="{{ $member->name }}">
    @php $currentVacation=$member->currentVacation(); @endphp<div class="min-w-0"><h2 class="mb-1 text-break">{{ $member->name }} @if($currentVacation)<span title="{{ __('admin_staff.vacation',['from'=>\Carbon\Carbon::parse($currentVacation->date)->format('d.m.Y'),'to'=>$currentVacation->endDate()->format('d.m.Y')]) }}">🌴</span>@endif</h2><div class="text-body-tertiary">{{ $member->professionalTitle() ?: $member->role?->displayName() }} · {{ $member->email }}</div>@if($currentVacation)<div class="text-success">🌴 {{ __('admin_staff.on_vacation',['from'=>\Carbon\Carbon::parse($currentVacation->date)->format('d.m.Y'),'to'=>$currentVacation->endDate()->format('d.m.Y')]) }}</div>@endif</div>
  </div>

  <ul class="nav nav-tabs flex-nowrap overflow-x-auto mb-4" id="employeeTabs" role="tablist">
    <li class="nav-item"><button class="nav-link active text-nowrap" data-bs-toggle="tab" data-bs-target="#main" type="button">{{ __('admin_staff.main') }}</button></li>
    <li class="nav-item"><button class="nav-link text-nowrap" data-bs-toggle="tab" data-bs-target="#statistics" type="button">{{ __('admin_staff.statistics') }}</button></li>
    <li class="nav-item"><button class="nav-link text-nowrap" data-bs-toggle="tab" data-bs-target="#services" type="button">{{ __('admin_staff.services') }}</button></li>
    <li class="nav-item"><button class="nav-link text-nowrap" data-bs-toggle="tab" data-bs-target="#work-calendar" type="button">{{ __('admin_staff.work_calendar') }}</button></li>
  </ul>

  <div class="tab-content">
    <section class="tab-pane fade show active" id="main">
      <div class="card"><div class="card-body p-4">
        <div class="row g-4">
          <div class="col-12 col-xl-8">
            <h4 class="mb-3">{{ __('admin_staff.employee_data') }}</h4>
            <div class="row g-3">
              <div class="col-12 col-md-6"><label class="form-label" for="name">{{ __('admin_staff.name') }}</label><input class="form-control" id="name" value="{{ $member->name }}"></div>
              <div class="col-12 col-md-6"><label class="form-label" for="username">{{ __('admin_staff.username') }}</label><input class="form-control" id="username" value="{{ $member->username }}"></div>
              <div class="col-12 col-md-6"><label class="form-label" for="phone">{{ __('admin_staff.phone') }}</label><input class="form-control" id="phone" value="{{ $member->phone }}"></div>
              <div class="col-12 col-md-6"><label class="form-label" for="email">{{ __('admin_staff.email') }}</label><input class="form-control" id="email" type="email" value="{{ $member->email }}"></div>
              <div class="col-12 col-md-6"><label class="form-label" for="role_id">{{ __('admin_staff.role') }}</label><select class="form-select" id="role_id" @disabled(!auth()->user()->hasPermission('roles.manage'))>@foreach($roles as $role)<option value="{{ $role->id }}" @selected($member->role_id === $role->id)>{{ $role->displayName() }}</option>@endforeach</select>@cannot('roles.manage')<input type="hidden" id="role_id_locked" value="{{ $member->role_id }}">@endcannot</div>
              <div class="col-12 col-md-6"><label class="form-label" for="date_birthday">{{ __('admin_staff.birthday') }}</label><input class="form-control" id="date_birthday" type="date" value="{{ $member->date_birthday?->format('Y-m-d') }}"></div>
              <div class="col-12 col-md-6"><label class="form-label" for="employment_started_at">{{ __('admin_staff.employment_started_at') }}</label><input class="form-control" id="employment_started_at" type="date" value="{{ $member->employment_started_at?->format('Y-m-d') }}"><div class="form-text">{{ __('admin_staff.employment_hint') }}</div></div>
              <div class="col-12 col-md-6"><label class="form-label" for="locale">{{ __('admin_nav.interface_language') }}</label><select class="form-select" id="locale">@foreach(config('supported_locales') as $localeCode => $language)<option value="{{ $localeCode }}" @selected(($member->locale ?? config('app.locale')) === $localeCode)>{{ $language }}</option>@endforeach</select><div class="form-text">{{ __('admin_nav.employee_language_hint') }}</div></div>
              <div class="col-12 col-md-6"><label class="form-label" for="photoInput">{{ __('admin_staff.photo') }}</label><input class="form-control" id="photoInput" type="file" accept="image/jpeg,image/png,image/webp"><div class="form-text">{{ __('admin_staff.photo_hint') }}</div></div>
              <div class="col-12">
                <label class="form-label">{{ __('admin_staff.localized_titles') }}</label>
                <ul class="nav nav-pills gap-1 mb-2">@foreach(app(\App\Services\Localization\SiteLocaleRegistry::class)->installedLabels() as $locale => $label)<li class="nav-item"><button class="nav-link py-1 px-3 {{ $loop->first ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#title-{{ $locale }}" type="button">{{ $label }}</button></li>@endforeach</ul>
                <div class="tab-content">@foreach(app(\App\Services\Localization\SiteLocaleRegistry::class)->installedLabels() as $locale => $label)<div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="title-{{ $locale }}"><input class="form-control professional-title" data-locale="{{ $locale }}" maxlength="120" value="{{ $member->professional_titles[$locale] ?? '' }}"></div>@endforeach</div>
              </div>
            </div>
          </div>
          <div class="col-12 col-xl-4">
            <h4 class="mb-3">{{ __('admin_staff.change_password') }}</h4>
            <div class="mb-3"><label class="form-label" for="password">{{ __('admin_staff.new_password') }}</label><input class="form-control" id="password" type="password" autocomplete="new-password"></div>
            <div class="mb-4"><label class="form-label" for="password_confirmation">{{ __('admin_staff.repeat_password') }}</label><input class="form-control" id="password_confirmation" type="password" autocomplete="new-password"></div>
            @can('staff.update')<button class="btn btn-primary w-100" id="saveBtn" type="button"><span class="fas fa-save me-1"></span>{{ __('admin_staff.save_main') }}</button>@endcan
          </div>
        </div>
      </div></div>

      <div class="card mt-4"><div class="card-body p-4"><h4 class="mb-1">{{ __('admin_staff.notification_recipients') }}</h4><p class="text-body-tertiary">{{ __('admin_staff.notification_recipients_hint') }}</p>
        <form method="POST" action="{{ route('member.notification-recipients.update', $member) }}">@csrf
          @php $recipientIds = $member->notificationRecipientsUsers->pluck('id')->push($member->id)->unique()->all(); @endphp
          <div class="row g-2">@foreach($admins as $admin)<div class="col-12 col-lg-6"><label class="border rounded p-3 d-flex align-items-center gap-2 w-100"><input class="form-check-input mt-0" type="checkbox" name="recipients[]" value="{{ $admin->id }}" @checked(in_array($admin->id, $recipientIds)) @disabled($admin->id === $member->id)><x-ui.avatar :user="$admin" :size="32" /><span class="min-w-0"><strong class="d-block text-break">{{ $admin->name }}</strong><small class="text-body-tertiary text-break">{{ $admin->email }}</small></span></label></div>@endforeach</div>
          <input type="hidden" name="recipients[]" value="{{ $member->id }}">@can('staff.update')<button class="btn btn-outline-primary mt-3" type="submit">{{ __('admin_staff.save_recipients') }}</button>@endcan
        </form>
      </div></div>
    </section>

    <section class="tab-pane fade" id="statistics">
      <div class="row g-3">
        @foreach([
          [__('admin_staff.current_month_revenue'), number_format($statistics['current_month_revenue'], 2, ',', ' ').' €'],
          [__('admin_staff.average_monthly_revenue'), number_format($statistics['average_monthly_revenue'], 2, ',', ' ').' €'],
          [__('admin_staff.current_month_appointments'), $statistics['current_month_appointments']],
          [__('admin_staff.completed_appointments'), $statistics['completed_appointments']],
          [__('admin_staff.active_services'), $statistics['active_services']],
          [__('admin_staff.average_check'), number_format($statistics['average_check'], 2, ',', ' ').' €'],
          [__('admin_staff.employment_tenure'), $statistics['employment_tenure']],
        ] as [$label, $value])
          <div class="col-12 col-sm-6 col-xl-4"><div class="card h-100"><div class="card-body"><div class="text-body-tertiary mb-2">{{ $label }}</div><div class="fs-5 fw-bold text-body-emphasis">{{ $value }}</div></div></div></div>
        @endforeach
      </div>
      <p class="text-body-tertiary mt-3">{{ __('admin_staff.revenue_note',['date'=>$statistics['employment_date']->format('d.m.Y')]) }}</p>
    </section>

    <section class="tab-pane fade" id="services">
      @include('admin.master-services._service-list', ['master' => $member, 'catalogUrl' => route('member.edit', $member).'#services', 'catalogMasterParameter' => false])
    </section>

    <section class="tab-pane fade" id="work-calendar">
      @php $schedule = $member->schedule; $days = collect(['monday','tuesday','wednesday','thursday','friday','saturday','sunday'])->mapWithKeys(fn($day)=>[$day=>__('admin_staff.'.$day)]); @endphp
      <div class="row g-4">
        <div class="col-12 col-xl-7"><div class="card h-100"><div class="card-body"><h4 class="mb-3">{{ __('admin_staff.standard_week') }}</h4><form method="POST" action="{{ route('member.schedule.update', $member) }}">@csrf @method('PUT')<div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>{{ __('admin_staff.day') }}</th><th>{{ __('admin_staff.start') }}</th><th>{{ __('admin_staff.end') }}</th><th>{{ __('admin_staff.day_off') }}</th></tr></thead><tbody>@foreach($days as $day => $label)@php $off=!$schedule?->{$day.'_start'} || !$schedule?->{$day.'_end'}; @endphp<tr><th>{{ $label }}</th><td><input class="form-control form-control-sm schedule-time" name="{{ $day }}_start" type="time" value="{{ $schedule?->{$day.'_start'} ? substr($schedule->{$day.'_start'},0,5) : '' }}" @disabled($off)></td><td><input class="form-control form-control-sm schedule-time" name="{{ $day }}_end" type="time" value="{{ $schedule?->{$day.'_end'} ? substr($schedule->{$day.'_end'},0,5) : '' }}" @disabled($off)></td><td><input class="form-check-input day-off" name="{{ $day }}_off" type="checkbox" value="1" data-day="{{ $day }}" @checked($off)></td></tr>@endforeach</tbody></table></div><div class="row g-3 mt-3"><div class="col-6"><label class="form-label">{{ __('admin_staff.lunch_from') }}</label><input class="form-control" name="lunch_start" type="time" value="{{ $schedule?->lunch_start ? substr($schedule->lunch_start,0,5) : '' }}"></div><div class="col-6"><label class="form-label">{{ __('admin_staff.lunch_until') }}</label><input class="form-control" name="lunch_end" type="time" value="{{ $schedule?->lunch_end ? substr($schedule->lunch_end,0,5) : '' }}"></div></div>@can('schedules.update')<button class="btn btn-primary mt-3" type="submit">{{ __('admin_staff.save_hours') }}</button>@endcan</form></div></div></div>
        <div class="col-12 col-xl-5"><div class="card h-100"><div class="card-body"><h4 class="mb-3">{{ __('admin_staff.exceptions') }}</h4><div class="row g-2 mb-4"><div class="col-6"><div class="border rounded p-2"><small class="text-body-tertiary d-block">{{ __('admin_staff.future') }}</small><strong>{{ __('admin_staff.days_windows',['days'=>$workCalendar['future_days_count'],'windows'=>$workCalendar['future_hours_count']]) }}</strong></div></div><div class="col-6"><div class="border rounded p-2"><small class="text-body-tertiary d-block">{{ __('admin_staff.this_month') }}</small><strong>{{ __('admin_staff.days_windows',['days'=>$workCalendar['month_days_count'],'windows'=>$workCalendar['month_hours_count']]) }}</strong></div></div></div>
          <h6>{{ __('admin_staff.personal_days') }}</h6>@forelse($workCalendar['personal_days'] as $item)<div class="border-bottom py-2"><strong>{{ \Carbon\Carbon::parse($item->date)->format('d.m.Y') }}</strong> · {{ $item->name }}@if($item->description)<div class="text-body-tertiary">{{ $item->description }}</div>@endif</div>@empty<p class="text-body-tertiary">{{ __('admin_staff.no_personal_days') }}</p>@endforelse
          <h6 class="mt-4">{{ __('admin_staff.personal_hours') }}</h6>@forelse($workCalendar['personal_hours'] as $item)<div class="border-bottom py-2"><strong>{{ \Carbon\Carbon::parse($item->date)->format('d.m.Y') }}</strong> · {{ substr($item->start_time,0,5) }}–{{ substr($item->end_time,0,5) }} · {{ $item->name }}</div>@empty<p class="text-body-tertiary">{{ __('admin_staff.no_personal_hours') }}</p>@endforelse
          <h6 class="mt-4">{{ __('admin_staff.company_days') }}</h6>@forelse($workCalendar['company_days'] as $item)<div class="border-bottom py-2"><strong>{{ \Carbon\Carbon::parse($item->date)->format('d.m.Y') }}</strong> · {{ $item->name }}@if(!$item->full_day) · {{ substr($item->start_time,0,5) }}–{{ substr($item->end_time,0,5) }}@endif</div>@empty<p class="text-body-tertiary">{{ __('admin_staff.no_company_days') }}</p>@endforelse
          <a class="btn btn-outline-primary w-100 mt-4" href="{{ route('member.closures.index', $member) }}">{{ __('admin_staff.open_exceptions') }}</a>
        </div></div></div>
      </div>
      <div class="alert alert-light border mt-4">{{ __('admin_staff.calendar_info') }}</div>
    </section>
  </div>
  <x-dashboard-footer />
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.day-off').forEach(box => box.addEventListener('change', function(){document.querySelectorAll(`[name^="${this.dataset.day}_"]`).forEach(input => {input.disabled=this.checked;if(!this.checked && !input.value)input.value=input.name.endsWith('_start')?'09:00':'18:00';});}));
  const hash = window.location.hash;
  if (hash) { const trigger = document.querySelector(`[data-bs-target="${hash}"]`); if (trigger) bootstrap.Tab.getOrCreateInstance(trigger).show(); }
  document.querySelectorAll('#employeeTabs [data-bs-toggle="tab"]').forEach(button => button.addEventListener('shown.bs.tab', event => history.replaceState(null, '', event.target.dataset.bsTarget)));
  document.getElementById('photoInput')?.addEventListener('change', function(){ const file=this.files[0]; if(file) document.getElementById('photoPreview').src=URL.createObjectURL(file); });
  document.getElementById('saveBtn')?.addEventListener('click', async function(){
    const data = new FormData(); data.append('_method','PUT');
    ['name','username','email','phone','date_birthday','employment_started_at','locale'].forEach(id => data.append(id, document.getElementById(id).value));
    data.append('role_id', document.getElementById('role_id').value || document.getElementById('role_id_locked')?.value || '');
    document.querySelectorAll('.professional-title').forEach(input => data.append(`professional_titles[${input.dataset.locale}]`, input.value));
    if(document.getElementById('password').value){data.append('password',document.getElementById('password').value);data.append('password_confirmation',document.getElementById('password_confirmation').value);}
    const photo=document.getElementById('photoInput').files[0];if(photo)data.append('photo',photo);
    try { const response=await fetch(@json(route('member.update',$member)),{method:'POST',headers:{'Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},body:data}); const payload=await response.json(); if(!response.ok)throw payload; document.getElementById('successAlert').classList.remove('d-none'); document.getElementById('errorAlert').classList.add('d-none'); window.scrollTo({top:0,behavior:'smooth'}); }
    catch(error){document.getElementById('errorText').textContent=error.message || Object.values(error.errors || {}).flat().join(' ');document.getElementById('errorAlert').classList.remove('d-none');window.scrollTo({top:0,behavior:'smooth'});}
  });
});
</script>
@endpush
</x-dashboard-layout>
