@section('title', __('admin_appointments.appointment_number', ['id' => $appointment->id]))
<x-dashboard-layout>
<div class="content">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div><a href="{{ route('calendarList') }}" class="text-decoration-none">{{ __('admin_appointments.back_all') }}</a><h2 class="mt-2 mb-1">{{ __('admin_appointments.appointment_number',['id'=>$appointment->id]) }}</h2><p class="text-body-tertiary mb-0">{{ $appointment->service->name }}</p></div>
    <div class="d-flex gap-2 align-items-center">@include('admin.calendar._status-actions',['appointment'=>$appointment])
      @if(auth()->user()->hasPermission('appointments.create'))<a href="{{ route('calendar.create',['repeat'=>$appointment->id]) }}" class="btn btn-outline-primary btn-sm"><span class="fas fa-rotate-right me-1"></span>{{ __('admin_appointment_create.repeat_booking') }}</a>@endif
      @unless(in_array($appointment->status,['completed','no_show','cancelled_by_client','cancelled_by_business','rescheduled'],true))<button id="destroyEvent" class="btn btn-outline-danger btn-sm">{{ __('admin_appointments.cancel') }}</button>@endunless
    </div>
  </div>
  @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
  @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
  <div class="row g-4 mb-4">
    <div class="col-12 col-xl-7"><section class="card h-100"><div class="card-body p-4"><h4 class="mb-4">{{ __('admin_appointments.details_title') }}</h4><div class="row g-3">
      <div class="col-sm-6"><small class="text-body-tertiary">{{ __('admin_appointments.date_time') }}</small><div class="fw-bold">{{ $appointment->appointment_start->translatedFormat('d F Y, H:i') }}–{{ $appointment->appointment_end->format('H:i') }}</div></div>
      <div class="col-sm-6"><small class="text-body-tertiary">{{ __('admin_appointments.employee_label') }}</small><div class="fw-bold">{{ $appointment->user->name }}</div></div>
      <div class="col-sm-6"><small class="text-body-tertiary">{{ __('admin_appointments.cost') }}</small><div class="fw-bold">{{ number_format((float)$appointment->price,2,',',' ') }} €</div></div>
      <div class="col-sm-6"><small class="text-body-tertiary">{{ __('admin_appointments.room') }}</small><div class="fw-bold">{{ $appointment->room?->name ?? __('admin_appointments.not_assigned') }}</div></div>
      <div class="col-12"><small class="text-body-tertiary">{{ __('admin_appointments.comment') }}</small><div>{{ $appointment->description ?: __('admin_appointments.no_comment') }}</div></div>
      @if($appointment->admin_notes)<div class="col-12"><small class="text-body-tertiary">{{ __('admin_appointment_create.admin_note') }}</small><div class="p-3 rounded bg-body-tertiary">{{ $appointment->admin_notes }}</div></div>@endif
      @if($appointment->series_uuid)<div class="col-12"><small class="text-body-tertiary">{{ __('admin_appointment_create.repeat_booking') }}</small><div>{{ $appointment->series_sequence }} / {{ $appointment->series_total }}</div></div>@endif
    </div></div></section></div>
    <div class="col-12 col-xl-5"><section class="card h-100"><div class="card-body p-4">
      <div class="d-flex justify-content-between"><h4>{{ __('admin_appointments.about_client') }}</h4><span class="badge badge-phoenix">{{ $appointment->customer ? __('admin_appointments.has_account') : __('admin_appointments.guest') }}</span></div>
      <h5>{{ trim($appointment->client_name.' '.$appointment->client_lastname) }}</h5>
      @if($appointment->client_phone)<div class="mt-3"><a href="tel:{{ $appointment->client_phone }}">{{ $appointment->client_phone }}</a></div>@endif
      @if($appointment->client_email)<div class="mt-2"><a href="mailto:{{ $appointment->client_email }}">{{ $appointment->client_email }}</a></div>@endif
      <p class="text-body-tertiary fs-9 mt-3 mb-0">{{ __('admin_appointments.snapshot_hint') }}</p>
    </div></section></div>
  </div>
  <div class="row g-4 mb-4">
    <div class="col-12 col-xl-7"><section class="card h-100"><div class="card-body p-4"><h4 class="mb-3">{{ __('admin_appointments.recent_client_appointments') }}</h4><div class="table-responsive"><table class="table table-sm align-middle"><thead><tr><th>{{ __('admin_settings.date') }}</th><th>{{ __('admin_appointments.service') }}</th><th>{{ __('admin_appointments.employee') }}</th><th>{{ __('admin_appointments.fields.status') }}</th><th></th></tr></thead><tbody>
      @forelse($customerAppointments as $item)<tr><td class="text-nowrap">{{ $item->appointment_start->format('d.m.Y H:i') }}</td><td>{{ $item->service->name }}</td><td>{{ $item->user->name }}</td><td><x-appointments.status :status="$item->status" /></td><td><a href="{{ route('calendar.show',$item) }}">{{ __('admin_appointments.open') }}</a></td></tr>@empty<tr><td colspan="5">{{ __('admin_appointments.no_other') }}</td></tr>@endforelse
    </tbody></table></div></div></section></div>
    <div class="col-12 col-xl-5"><section class="card h-100"><div class="card-body p-4"><h4 class="mb-3">{{ __('admin_appointments.message_client') }}</h4>
      @if($appointment->client_email)<form method="POST" action="{{ route('appointments.message.store',$appointment) }}">@csrf
        <div class="mb-3"><label class="form-label" for="subject">{{ __('admin_appointments.subject') }}</label><input id="subject" name="subject" class="form-control" maxlength="150" required value="{{ old('subject',__('admin_appointments.default_subject',['date'=>$appointment->appointment_start->format('d.m.Y H:i')])) }}"></div>
        <div class="mb-3"><label class="form-label" for="message">{{ __('admin_appointments.message') }}</label><textarea id="message" name="message" class="form-control" rows="6" maxlength="5000" required>{{ old('message') }}</textarea></div>
        <div class="fs-9 text-body-tertiary mb-3">{{ __('admin_appointments.recipient',['email'=>$appointment->client_email]) }}</div><button class="btn btn-primary">{{ __('admin_appointments.send_email') }}</button>
      </form>@else<p>{{ __('admin_appointments.no_email') }}</p>@endif
    </div></section></div>
  </div>
  <section class="card mb-4"><div class="card-body p-4"><h4 class="mb-4">{{ __('admin_appointments.history') }}</h4>
    @forelse($appointment->auditTrail as $audit)@php $actorName=$audit->actor?->name ?: trim(($audit->actor?->first_name??'').' '.($audit->actor?->last_name??'')) ?: __('admin_appointments.system'); @endphp
      <article class="border-start border-3 ps-3 pb-4"><div class="d-flex justify-content-between gap-2"><strong>{{ $audit->event === 'customer_message_sent' ? __('admin_appointments.email_sent') : __('admin_appointments.events.'.$audit->event) }}</strong><time class="text-body-tertiary fs-9">{{ $audit->created_at->format('d.m.Y H:i') }}</time></div><div class="text-body-tertiary fs-9">{{ $actorName }}</div>@if($audit->reason)<div>{{ __('admin_appointments.reason') }}: {{ $audit->reason }}</div>@endif @if($audit->event==='customer_message_sent')<div>{{ __('admin_appointments.email_subject') }}: {{ $audit->context['subject'] ?? '—' }}</div>@endif</article>
    @empty<p>{{ __('admin_appointments.empty') }}</p>@endforelse
  </div></section>
  <section class="card mb-5"><div class="card-body p-4"><h4 class="mb-3">{{ __('admin_appointments.recent_client_activity') }}</h4>
    @forelse($customerActivity as $activity)<div class="d-flex justify-content-between border-bottom py-2 gap-3"><div><a href="{{ route('calendar.show',$activity->appointment_id) }}">{{ __('admin_appointments.appointment_number',['id'=>$activity->appointment_id]) }}</a> · {{ $activity->event === 'customer_message_sent' ? __('admin_appointments.email_sent') : __('admin_appointments.events.'.$activity->event) }}</div><time class="text-nowrap fs-9">{{ $activity->created_at->format('d.m.Y H:i') }}</time></div>@empty<p>{{ __('admin_appointments.actions_empty') }}</p>@endforelse
  </div></section>
  <x-dashboard-footer />
</div>
@push('scripts')<script>document.getElementById('destroyEvent')?.addEventListener('click',async()=>{const reason=prompt(@json(__('admin_appointments.cancel_prompt')));if(!reason||reason.trim().length<3)return;const r=await fetch(@json(route('calendar.destroy',$appointment)),{method:'DELETE',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json','Content-Type':'application/json'},body:JSON.stringify({reason:reason.trim()})});if(r.ok)location.reload();else alert(@json(__('admin_appointments.cancel_failed')));});</script>@include('admin.calendar._status-script')@endpush
</x-dashboard-layout>
