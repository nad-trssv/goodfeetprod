@section('title', 'Запись #'.$appointment->id)
<x-dashboard-layout>
<div class="content">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div><a href="{{ route('calendarList') }}" class="text-decoration-none">← Все записи</a><h2 class="mt-2 mb-1">Запись #{{ $appointment->id }}</h2><p class="text-body-tertiary mb-0">{{ $appointment->service->name }}</p></div>
    <div class="d-flex gap-2 align-items-center">@include('admin.calendar._status-actions',['appointment'=>$appointment])
      @if(auth()->user()->hasPermission('appointments.create'))<a href="{{ route('calendar.create',['repeat'=>$appointment->id]) }}" class="btn btn-outline-primary btn-sm"><span class="fas fa-rotate-right me-1"></span>{{ __('admin_appointment_create.repeat_booking') }}</a>@endif
      @unless(in_array($appointment->status,['completed','no_show','cancelled_by_client','cancelled_by_business','rescheduled'],true))<button id="destroyEvent" class="btn btn-outline-danger btn-sm">Отменить</button>@endunless
    </div>
  </div>
  @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
  @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
  <div class="row g-4 mb-4">
    <div class="col-12 col-xl-7"><section class="card h-100"><div class="card-body p-4"><h4 class="mb-4">Детали записи</h4><div class="row g-3">
      <div class="col-sm-6"><small class="text-body-tertiary">Дата и время</small><div class="fw-bold">{{ $appointment->appointment_start->translatedFormat('d F Y, H:i') }}–{{ $appointment->appointment_end->format('H:i') }}</div></div>
      <div class="col-sm-6"><small class="text-body-tertiary">Мастер</small><div class="fw-bold">{{ $appointment->user->name }}</div></div>
      <div class="col-sm-6"><small class="text-body-tertiary">Стоимость</small><div class="fw-bold">{{ number_format((float)$appointment->price,2,',',' ') }} €</div></div>
      <div class="col-sm-6"><small class="text-body-tertiary">Кабинет</small><div class="fw-bold">{{ $appointment->room?->name ?? 'Не назначен' }}</div></div>
      <div class="col-12"><small class="text-body-tertiary">Комментарий</small><div>{{ $appointment->description ?: 'Комментария нет' }}</div></div>
      @if($appointment->admin_notes)<div class="col-12"><small class="text-body-tertiary">{{ __('admin_appointment_create.admin_note') }}</small><div class="p-3 rounded bg-body-tertiary">{{ $appointment->admin_notes }}</div></div>@endif
      @if($appointment->series_uuid)<div class="col-12"><small class="text-body-tertiary">{{ __('admin_appointment_create.repeat_booking') }}</small><div>{{ $appointment->series_sequence }} / {{ $appointment->series_total }}</div></div>@endif
    </div></div></section></div>
    <div class="col-12 col-xl-5"><section class="card h-100"><div class="card-body p-4">
      <div class="d-flex justify-content-between"><h4>О клиенте</h4><span class="badge badge-phoenix">{{ $appointment->customer ? 'Есть аккаунт' : 'Гость' }}</span></div>
      <h5>{{ trim($appointment->client_name.' '.$appointment->client_lastname) }}</h5>
      @if($appointment->client_phone)<div class="mt-3"><a href="tel:{{ $appointment->client_phone }}">{{ $appointment->client_phone }}</a></div>@endif
      @if($appointment->client_email)<div class="mt-2"><a href="mailto:{{ $appointment->client_email }}">{{ $appointment->client_email }}</a></div>@endif
      <p class="text-body-tertiary fs-9 mt-3 mb-0">Контакты сохранены как снимок записи и не меняются вместе с профилем клиента.</p>
    </div></section></div>
  </div>
  <div class="row g-4 mb-4">
    <div class="col-12 col-xl-7"><section class="card h-100"><div class="card-body p-4"><h4 class="mb-3">Последние записи клиента</h4><div class="table-responsive"><table class="table table-sm align-middle"><thead><tr><th>Дата</th><th>Услуга</th><th>Мастер</th><th>Статус</th><th></th></tr></thead><tbody>
      @forelse($customerAppointments as $item)<tr><td class="text-nowrap">{{ $item->appointment_start->format('d.m.Y H:i') }}</td><td>{{ $item->service->name }}</td><td>{{ $item->user->name }}</td><td><x-appointments.status :status="$item->status" /></td><td><a href="{{ route('calendar.show',$item) }}">Открыть</a></td></tr>@empty<tr><td colspan="5">Других записей не найдено.</td></tr>@endforelse
    </tbody></table></div></div></section></div>
    <div class="col-12 col-xl-5"><section class="card h-100"><div class="card-body p-4"><h4 class="mb-3">Написать клиенту</h4>
      @if($appointment->client_email)<form method="POST" action="{{ route('appointments.message.store',$appointment) }}">@csrf
        <div class="mb-3"><label class="form-label" for="subject">Тема</label><input id="subject" name="subject" class="form-control" maxlength="150" required value="{{ old('subject','Ваша запись на '.$appointment->appointment_start->format('d.m.Y H:i')) }}"></div>
        <div class="mb-3"><label class="form-label" for="message">Сообщение</label><textarea id="message" name="message" class="form-control" rows="6" maxlength="5000" required>{{ old('message') }}</textarea></div>
        <div class="fs-9 text-body-tertiary mb-3">Получатель: {{ $appointment->client_email }}</div><button class="btn btn-primary">Отправить письмо</button>
      </form>@else<p>У клиента не указан email.</p>@endif
    </div></section></div>
  </div>
  <section class="card mb-4"><div class="card-body p-4"><h4 class="mb-4">{{ __('admin_appointments.history') }}</h4>
    @forelse($appointment->auditTrail as $audit)@php $actorName=$audit->actor?->name ?: trim(($audit->actor?->first_name??'').' '.($audit->actor?->last_name??'')) ?: 'Система'; @endphp
      <article class="border-start border-3 ps-3 pb-4"><div class="d-flex justify-content-between gap-2"><strong>{{ $audit->event === 'customer_message_sent' ? 'Клиенту отправлено письмо' : __('admin_appointments.events.'.$audit->event) }}</strong><time class="text-body-tertiary fs-9">{{ $audit->created_at->format('d.m.Y H:i') }}</time></div><div class="text-body-tertiary fs-9">{{ $actorName }}</div>@if($audit->reason)<div>Причина: {{ $audit->reason }}</div>@endif @if($audit->event==='customer_message_sent')<div>Тема письма: {{ $audit->context['subject'] ?? '—' }}</div>@endif</article>
    @empty<p>История пока пуста.</p>@endforelse
  </div></section>
  <section class="card mb-5"><div class="card-body p-4"><h4 class="mb-3">Последние действия клиента</h4>
    @forelse($customerActivity as $activity)<div class="d-flex justify-content-between border-bottom py-2 gap-3"><div><a href="{{ route('calendar.show',$activity->appointment_id) }}">Запись #{{ $activity->appointment_id }}</a> · {{ $activity->event === 'customer_message_sent' ? 'Клиенту отправлено письмо' : __('admin_appointments.events.'.$activity->event) }}</div><time class="text-nowrap fs-9">{{ $activity->created_at->format('d.m.Y H:i') }}</time></div>@empty<p>Действий пока нет.</p>@endforelse
  </div></section>
  <x-dashboard-footer />
</div>
@push('scripts')<script>document.getElementById('destroyEvent')?.addEventListener('click',async()=>{const reason=prompt('Укажите причину отмены');if(!reason||reason.trim().length<3)return;const r=await fetch(@json(route('calendar.destroy',$appointment)),{method:'DELETE',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json','Content-Type':'application/json'},body:JSON.stringify({reason:reason.trim()})});if(r.ok)location.reload();else alert('Не удалось отменить запись.');});</script>@include('admin.calendar._status-script')@endpush
</x-dashboard-layout>
