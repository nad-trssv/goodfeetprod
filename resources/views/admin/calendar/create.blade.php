@section('title', __('admin_appointment_create.title'))
<x-dashboard-layout>
<style>
  .quick-booking__step{border-left:4px solid var(--phoenix-primary)}
  .quick-booking__dates{display:grid;grid-template-columns:repeat(7,minmax(70px,1fr));gap:.5rem;overflow-x:auto;padding-bottom:.25rem}
  .quick-booking__date{min-width:70px;border:1px solid var(--phoenix-border-color);border-radius:.75rem;background:var(--phoenix-body-bg);padding:.55rem .35rem;text-align:center;color:inherit}
  .quick-booking__date:hover,.quick-booking__date.is-active{border-color:var(--phoenix-primary);box-shadow:0 0 0 2px color-mix(in srgb,var(--phoenix-primary) 18%,transparent)}
  .quick-booking__date.is-empty{text-decoration:line-through;opacity:.5}
  .quick-booking__slots{display:grid;grid-template-columns:repeat(auto-fill,minmax(145px,1fr));gap:.65rem}
  .quick-booking__slot{min-width:0;text-align:left;border:1px solid var(--phoenix-border-color);border-radius:.75rem;background:var(--phoenix-body-bg);padding:.7rem .8rem;color:inherit}
  .quick-booking__slot:hover,.quick-booking__slot.is-selected{border-color:var(--phoenix-primary);background:var(--phoenix-primary-bg-subtle);box-shadow:0 0 0 2px color-mix(in srgb,var(--phoenix-primary) 16%,transparent)}
  .quick-booking__slot:disabled{opacity:.65}
  .quick-booking__slot-master{display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-size:.72rem;color:var(--phoenix-tertiary-color)}
  .quick-booking__client-results{max-height:280px;overflow:auto}
  .quick-booking__client-result{display:flex;width:100%;gap:.75rem;text-align:left;padding:.75rem;border:0;border-bottom:1px solid var(--phoenix-border-color);background:transparent;color:inherit}
  .quick-booking__client-result:hover{background:var(--phoenix-tertiary-bg)}
  .quick-booking__avatar{width:38px;height:38px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;flex:0 0 auto;background:var(--phoenix-primary-bg-subtle);color:var(--phoenix-primary);font-weight:700;overflow:hidden}
  .quick-booking__avatar img{width:100%;height:100%;object-fit:cover}
  @media(max-width:767.98px){.quick-booking__dates{grid-template-columns:repeat(7,76px)}.quick-booking__slots{grid-template-columns:repeat(2,minmax(0,1fr))}.quick-booking__slot{padding:.65rem}.quick-booking__sticky{position:static!important}}
</style>
<div class="content">
  <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div><h2 class="mb-1">{{ __('admin_appointment_create.title') }}</h2><p class="text-body-tertiary mb-0">{{ __('admin_appointment_create.subtitle') }}</p></div>
    <a href="{{ route('calendar.index') }}" class="btn btn-outline-secondary btn-sm"><span class="fas fa-arrow-left me-2"></span>{{ __('admin_appointment_create.back') }}</a>
  </div>

  @if($repeatAppointment)<div class="alert alert-subtle-info mb-4"><span class="fas fa-rotate-right me-2"></span>{{ __('admin_appointment_create.repeated_from', ['id'=>$repeatAppointment['id']]) }}</div>@endif

  <form id="createAppointmentForm" autocomplete="off" novalidate>
    @csrf
    <input type="hidden" id="customerId" name="customer_id">
    <input type="hidden" id="appointmentStart" name="appointment_start">
    <input type="hidden" id="appointmentEnd" name="appointment_end">
    <input type="hidden" id="assignedMasterId" name="user_id">
    <input type="hidden" id="slotHoldToken" name="slot_hold_token">

    <div class="row g-4 align-items-start">
      <div class="col-12 col-xl-8">
        <section class="card quick-booking__step mb-4"><div class="card-body p-3 p-lg-4">
          <h4 class="mb-3">{{ __('admin_appointment_create.service_and_master') }}</h4>
          <div class="row g-3 align-items-end">
            <div class="col-12 col-lg-7"><label class="form-label" for="serviceSelect">{{ __('admin_appointment_create.service') }}</label><select class="form-select" id="serviceSelect" name="service_id" required><option value="">{{ __('admin_appointment_create.choose_service') }}</option>@foreach($services as $service)<option value="{{ $service['id'] }}">{{ $service['name'] }} · {{ __('admin_appointment_create.duration',['minutes'=>$service['duration_minutes']]) }}</option>@endforeach</select><div class="invalid-feedback" id="serviceError"></div></div>
            <div class="col-12 col-lg-5"><label class="form-label" for="masterSelect">{{ __('admin_appointment_create.master') }}</label><select class="form-select" id="masterSelect" disabled><option value="all">{{ __('admin_appointment_create.any_master') }}</option></select><div class="form-text">{{ __('admin_appointment_create.any_master_help') }}</div><div class="invalid-feedback" id="masterError"></div></div>
          </div>
          <div id="servicePreview" class="d-none align-items-center gap-3 mt-3 p-3 rounded-3 bg-body-tertiary"><img id="serviceImage" src="" alt="" width="68" height="52" loading="lazy" class="rounded object-fit-cover flex-shrink-0"><div class="min-w-0"><strong id="serviceName" class="d-block text-break"></strong><span id="serviceMeta" class="text-body-tertiary fs-9"></span><div class="text-body-tertiary fs-10">{{ __('admin_appointment_create.server_price') }}</div></div></div>
        </div></section>

        <section class="card quick-booking__step"><div class="card-body p-3 p-lg-4">
          <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-3"><div><h4 class="mb-1">{{ __('admin_appointment_create.date_and_time') }}</h4><p class="text-body-tertiary fs-9 mb-0">{{ __('admin_appointment_create.nearby_help') }}</p></div><div><label class="form-label mb-1" for="bookingDate">{{ __('admin_appointment_create.date') }}</label><input class="form-control" type="date" id="bookingDate" value="{{ $selectedDate }}" min="{{ today()->toDateString() }}"></div></div>
          <div id="dateStrip" class="quick-booking__dates mb-4" aria-label="{{ __('admin_appointment_create.date') }}"></div>
          <div id="availabilityHint" class="text-center text-body-tertiary py-5">{{ __('admin_appointment_create.choose_parameters') }}</div>
          <div id="availabilityLoading" class="text-center py-5 d-none"><span class="spinner-border spinner-border-sm text-primary me-2" aria-hidden="true"></span>{{ __('admin_appointment_create.loading') }}</div>
          <div id="availabilityContent" class="d-none">
            <div id="recommendationsSection" class="mb-4"><h5 class="mb-1">{{ __('admin_appointment_create.nearby') }}</h5><div id="recommendations" class="quick-booking__slots mt-3"></div></div>
            <div class="d-flex justify-content-between align-items-center gap-2 mb-3"><h5 class="mb-0">{{ __('admin_appointment_create.slots') }}</h5><span id="selectedDateLabel" class="badge badge-phoenix badge-phoenix-primary"></span></div>
            <div id="slots" class="quick-booking__slots"></div>
            <div id="slotsEmpty" class="alert alert-subtle-secondary text-center d-none mb-0">{{ __('admin_appointment_create.no_slots') }}</div>
          </div>
          <div id="availabilityError" class="alert alert-danger d-none mt-3"></div>
          <div id="holdStatus" class="alert alert-subtle-warning d-none mt-3 mb-0"></div>
        </div></section>
      </div>

      <div class="col-12 col-xl-4" id="clientCard"><div class="quick-booking__sticky position-sticky" style="top:1rem">
        <section class="card quick-booking__step mb-4"><div class="card-body p-3 p-lg-4">
          <h4 class="mb-3">{{ __('admin_appointment_create.client') }}</h4>
          <label class="form-label" for="customerSearch">{{ __('admin_appointment_create.find_client') }}</label>
          <div class="input-group"><span class="input-group-text"><span class="fas fa-search"></span></span><input class="form-control" type="search" id="customerSearch" placeholder="{{ __('admin_appointment_create.client_search_placeholder') }}" maxlength="100"><button class="btn btn-outline-secondary" id="newCustomerButton" type="button">{{ __('admin_appointment_create.new_client') }}</button></div>
          <div id="customerSearchHint" class="form-text">{{ __('admin_appointment_create.search_hint') }}</div>
          <div id="customerResults" class="quick-booking__client-results border rounded-3 mt-2 d-none"></div>
          <div id="selectedCustomer" class="alert alert-subtle-success d-none mt-3 mb-3"></div>
          <div id="duplicateWarning" class="alert alert-subtle-warning d-none mt-3 mb-0"><strong class="d-block mb-1">{{ __('admin_appointment_create.duplicate_title') }}</strong><span class="fs-9">{{ __('admin_appointment_create.duplicate_help') }}</span><div id="duplicateCustomers" class="mt-2"></div></div>

          <div class="row g-3 mt-1">
            <div class="col-12 col-sm-6 col-xl-12"><label class="form-label" for="clientName">{{ __('admin_appointment_create.name') }}</label><input class="form-control" id="clientName" name="client_name" maxlength="190" required><div class="invalid-feedback" id="clientNameError"></div></div>
            <div class="col-12 col-sm-6 col-xl-12"><label class="form-label" for="clientLastname">{{ __('admin_appointment_create.lastname') }}</label><input class="form-control" id="clientLastname" name="client_lastname" maxlength="190"><div class="invalid-feedback" id="clientLastnameError"></div></div>
            <div class="col-12 col-sm-6 col-xl-12"><label class="form-label" for="clientPhone">{{ __('admin_appointment_create.phone') }}</label><input class="form-control" id="clientPhone" name="client_phone" type="tel" maxlength="32" required><div class="invalid-feedback" id="clientPhoneError"></div></div>
            <div class="col-12 col-sm-6 col-xl-12"><label class="form-label" for="clientEmail">{{ __('admin_appointment_create.email') }}</label><input class="form-control" id="clientEmail" name="client_email" type="email" maxlength="190"><div class="invalid-feedback" id="clientEmailError"></div></div>
            <div class="col-12 col-sm-6 col-xl-12">
              <label class="form-label" for="clientLocale">{{ __('admin_appointment_create.client_language') }}</label>
              <select class="form-select" id="clientLocale" name="client_locale">
                <option value="">{{ __('admin_appointment_create.client_language_auto') }}</option>
                @foreach($siteLocales as $code => $label)<option value="{{ $code }}">{{ $label }} ({{ strtoupper($code) }})</option>@endforeach
              </select>
              <div class="form-text">{{ __('admin_appointment_create.client_language_hint') }}</div>
              <div class="invalid-feedback"></div>
            </div>
            <div class="col-12"><label class="form-label" for="clientDescription">{{ __('admin_appointment_create.customer_note') }}</label><textarea class="form-control" id="clientDescription" name="description" rows="2" maxlength="2000" placeholder="{{ __('admin_appointment_create.note_placeholder') }}"></textarea><div class="invalid-feedback" id="descriptionError"></div></div>
            <div class="col-12"><label class="form-label" for="adminNotes">{{ __('admin_appointment_create.admin_note') }}</label><textarea class="form-control" id="adminNotes" name="admin_notes" rows="2" maxlength="5000" placeholder="{{ __('admin_appointment_create.admin_note_placeholder') }}"></textarea><div class="form-text">{{ __('admin_appointment_create.admin_note_help') }}</div><div class="invalid-feedback" id="adminNotesError"></div></div>
          </div>
        </div></section>

        <section class="card mb-4"><div class="card-body p-3 p-lg-4">
          <div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" id="repeatEnabled"><label class="form-check-label fw-semibold" for="repeatEnabled">{{ __('admin_appointment_create.repeat_booking') }}</label></div>
          <p class="text-body-tertiary fs-9 mt-1 mb-3">{{ __('admin_appointment_create.repeat_help') }}</p>
          <div id="repeatOptions" class="row g-2 d-none">
            <div class="col-6"><label class="form-label" for="repeatCount">{{ __('admin_appointment_create.repeat_count') }}</label><input class="form-control" id="repeatCount" name="repeat_count" type="number" min="2" max="12" value="4" disabled></div>
            <div class="col-6"><label class="form-label" for="repeatInterval">{{ __('admin_appointment_create.repeat_interval') }}</label><select class="form-select" id="repeatInterval" name="repeat_interval_weeks" disabled>@for($week=1;$week<=8;$week++)<option value="{{ $week }}">{{ trans_choice('admin_appointment_create.weeks', $week, ['count'=>$week]) }}</option>@endfor</select></div>
          </div>
        </div></section>

        <section class="card"><div class="card-body p-3 p-lg-4"><h5 class="mb-3">{{ __('admin_appointment_create.summary') }}</h5><div id="bookingSummary" class="text-body-tertiary mb-3">{{ __('admin_appointment_create.not_selected') }}</div><div id="formError" class="alert alert-danger d-none"></div><button class="btn btn-primary w-100" id="saveButton" type="submit" disabled><span class="fas fa-check me-2"></span>{{ __('admin_appointment_create.save') }}</button></div></section>
      </div></div>
    </div>
  </form>
  <x-dashboard-footer />
</div>

@php
  $quickBookingLabels = [
    'anyMaster'=>__('admin_appointment_create.any_master'),'currentMaster'=>__('admin_appointment_create.current_master'),'duration'=>__('admin_appointment_create.duration',['minutes'=>':minutes']),
    'to'=>__('admin_appointment_create.to'),'requestFailed'=>__('admin_appointment_create.request_failed'),'searchFailed'=>__('admin_appointment_create.search_failed'),
    'noCustomers'=>__('admin_appointment_create.no_customers'),'account'=>__('admin_appointment_create.account'),'guest'=>__('admin_appointment_create.guest'),
    'bookings'=>__('admin_appointment_create.bookings_count',['count'=>':count']),'notSelected'=>__('admin_appointment_create.not_selected'),
    'save'=>__('admin_appointment_create.save'),'saving'=>__('admin_appointment_create.saving'),'created'=>__('admin_appointment_create.created'),
    'seriesCreated'=>__('admin_appointment_create.series_created',['count'=>':count']),'validationFailed'=>__('admin_appointment_create.validation_failed'),
    'assignedMaster'=>__('admin_appointment_create.assigned_master',['name'=>':name']),'holdActive'=>__('admin_appointment_create.hold_active',['time'=>':time']),
    'holdExpired'=>__('admin_appointment_create.hold_expired'),'holdConflict'=>__('admin_appointment_create.hold_conflict'),
    'useCustomer'=>__('admin_appointment_create.use_customer'),'duplicateReasons'=>['email'=>__('admin_appointment_create.duplicate_email'),'phone'=>__('admin_appointment_create.duplicate_phone'),'name'=>__('admin_appointment_create.duplicate_name')],
    'occurrences'=>__('admin_appointment_create.occurrences',['count'=>':count']),
  ];
@endphp
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const services = @json($services);
  const repeatAppointment = @json($repeatAppointment);
  const currentUserId = @json((int)$currentUserId);
  const canChooseMaster = @json((bool)$canChooseMaster);
  const locale = @json(app()->getLocale());
  const csrf = document.querySelector('meta[name="csrf-token"]').content;
  const urls = {
    availability:@json(route('calendar.availability')), customers:@json(route('calendar.customers.search')),
    duplicates:@json(route('calendar.customers.duplicates')), store:@json(route('calendar.store')),
    hold:@json(route('calendar.slot-holds.store')), renew:@json(route('calendar.slot-holds.renew',['token'=>'__TOKEN__'])),
    release:@json(route('calendar.slot-holds.destroy',['token'=>'__TOKEN__']))
  };
  const labels = @json($quickBookingLabels);
  const el = id => document.getElementById(id);
  let selectedSlot = null, selectedCustomerId = null, holdToken = null, holdExpiresAt = null;
  let availabilityController = null, customerSearchTimer = null, duplicateTimer = null, renewTimer = null, countdownTimer = null;

  const escapeHtml = value => String(value ?? '').replace(/[&<>'"]/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[char]));
  const serviceById = id => services.find(service => String(service.id) === String(id));
  const initials = name => String(name || '').trim().split(/\s+/).slice(0,2).map(word => word.charAt(0).toUpperCase()).join('') || '—';
  const avatar = (name, src) => `<span class="quick-booking__avatar">${src ? `<img src="${escapeHtml(src)}" alt="${escapeHtml(name)}">` : escapeHtml(initials(name))}</span>`;
  const displayDate = date => new Intl.DateTimeFormat(locale,{weekday:'short',day:'2-digit',month:'short'}).format(new Date(date+'T12:00:00'));
  const money = value => new Intl.NumberFormat(locale,{style:'currency',currency:'EUR'}).format(Number(value || 0));
  const tokenUrl = (template, token) => template.replace('__TOKEN__', encodeURIComponent(token));

  async function releaseHold() {
    const token = holdToken;
    holdToken = null; holdExpiresAt = null; el('slotHoldToken').value = ''; el('holdStatus').classList.add('d-none');
    clearInterval(renewTimer); clearInterval(countdownTimer);
    if (token) fetch(tokenUrl(urls.release, token), {method:'DELETE',headers:{'Accept':'application/json','X-CSRF-TOKEN':csrf},keepalive:true}).catch(()=>{});
  }

  function clearSelectedSlot(release = true) {
    if (release) releaseHold();
    selectedSlot = null; el('appointmentStart').value = ''; el('appointmentEnd').value = ''; el('assignedMasterId').value = '';
    document.querySelectorAll('.quick-booking__slot').forEach(button => button.classList.remove('is-selected'));
    renderSummary();
  }

  function populateMasters(preferredMaster = null) {
    const service = serviceById(el('serviceSelect').value);
    el('masterSelect').innerHTML = canChooseMaster ? `<option value="all">${escapeHtml(labels.anyMaster)}</option>` : '';
    el('masterSelect').disabled = !service;
    if (!service) { el('servicePreview').classList.add('d-none'); clearSelectedSlot(); return; }
    service.users.forEach(user => el('masterSelect').add(new Option(user.name + (Number(user.id) === currentUserId ? ` (${labels.currentMaster})` : ''), user.id)));
    const allowedPreferred = service.users.some(user => Number(user.id) === Number(preferredMaster));
    el('masterSelect').value = allowedPreferred ? String(preferredMaster) : (canChooseMaster ? 'all' : String(currentUserId));
    el('serviceImage').src = service.image_url; el('serviceImage').alt = service.name; el('serviceName').textContent = service.name;
    el('serviceMeta').textContent = `${labels.duration.replace(':minutes',service.duration_minutes)} · ${money(service.price)}`;
    el('servicePreview').classList.remove('d-none'); el('servicePreview').classList.add('d-flex'); clearSelectedSlot(); loadAvailability();
  }

  function slotButton(slot, recommended = false) {
    return `<button type="button" class="quick-booking__slot" data-date="${slot.date}" data-start="${slot.start}" data-end="${slot.end}" data-user-id="${slot.user_id}" data-master-name="${escapeHtml(slot.master_name)}"><strong>${recommended ? `${escapeHtml(displayDate(slot.date))} · ` : ''}${escapeHtml(slot.start)}</strong> <small>${escapeHtml(labels.to)} ${escapeHtml(slot.end)}</small><span class="quick-booking__slot-master">${escapeHtml(slot.master_name)}</span></button>`;
  }

  function renderAvailability(data) {
    el('availabilityLoading').classList.add('d-none'); el('availabilityContent').classList.remove('d-none'); el('selectedDateLabel').textContent = displayDate(data.date);
    el('slots').innerHTML = data.slots.map(slot => slotButton(slot)).join(''); el('slotsEmpty').classList.toggle('d-none', data.slots.length > 0);
    el('recommendations').innerHTML = data.recommendations.map(slot => slotButton(slot,true)).join(''); el('recommendationsSection').classList.toggle('d-none', data.recommendations.length === 0);
    el('dateStrip').innerHTML = data.calendar_days.map(day => `<button type="button" class="quick-booking__date ${day.date === data.date ? 'is-active' : ''} ${day.available === false ? 'is-empty' : ''}" data-date="${day.date}"><small class="d-block text-body-tertiary">${escapeHtml(day.weekday)}</small><strong class="d-block fs-7">${escapeHtml(day.day)}</strong><small>${escapeHtml(day.month)}</small></button>`).join('');
    const service = serviceById(el('serviceSelect').value); if (service) el('serviceMeta').textContent = `${labels.duration.replace(':minutes',data.duration_minutes)} · ${money(data.price)}`;
  }

  async function loadAvailability() {
    const serviceId = el('serviceSelect').value;
    if (!serviceId || !el('bookingDate').value || el('masterSelect').disabled) return;
    clearSelectedSlot(); availabilityController?.abort(); availabilityController = new AbortController();
    el('availabilityHint').classList.add('d-none'); el('availabilityError').classList.add('d-none'); el('availabilityContent').classList.add('d-none'); el('availabilityLoading').classList.remove('d-none');
    try {
      const response = await fetch(urls.availability,{method:'POST',signal:availabilityController.signal,headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify({service_id:Number(serviceId),user_id:el('masterSelect').value,date:el('bookingDate').value})});
      if (!response.ok) throw new Error((await response.json().catch(()=>({}))).message || labels.requestFailed); renderAvailability(await response.json());
    } catch (error) { if(error.name==='AbortError')return;el('availabilityLoading').classList.add('d-none');el('availabilityError').textContent=error.message||labels.requestFailed;el('availabilityError').classList.remove('d-none'); }
  }

  function startHoldTimers() {
    clearInterval(renewTimer); clearInterval(countdownTimer);
    const draw = () => { const seconds=Math.max(0,Math.floor((new Date(holdExpiresAt)-Date.now())/1000));if(seconds<=0){el('holdStatus').textContent=labels.holdExpired;clearSelectedSlot(false);return;}const time=`${String(Math.floor(seconds/60)).padStart(2,'0')}:${String(seconds%60).padStart(2,'0')}`;el('holdStatus').textContent=labels.holdActive.replace(':time',time);el('holdStatus').classList.remove('d-none'); };
    draw(); countdownTimer=setInterval(draw,1000);
    renewTimer=setInterval(async()=>{if(!holdToken)return;try{const response=await fetch(tokenUrl(urls.renew,holdToken),{method:'PATCH',headers:{'Accept':'application/json','X-CSRF-TOKEN':csrf}});if(!response.ok)throw new Error();holdExpiresAt=(await response.json()).expires_at;}catch{el('holdStatus').textContent=labels.holdExpired;clearSelectedSlot(false);loadAvailability();}},60000);
  }

  async function selectSlot(button) {
    document.querySelectorAll('.quick-booking__slot').forEach(item=>item.disabled=true); el('availabilityError').classList.add('d-none');
    const candidate={date:button.dataset.date,start:button.dataset.start,end:button.dataset.end,userId:Number(button.dataset.userId),masterName:button.dataset.masterName};
    try {
      const response=await fetch(urls.hold,{method:'POST',headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify({token:holdToken,service_id:Number(el('serviceSelect').value),user_id:candidate.userId,appointment_start:`${candidate.date} ${candidate.start}`,appointment_end:`${candidate.date} ${candidate.end}`})});
      const data=await response.json().catch(()=>({}));if(!response.ok)throw new Error(data.message||labels.holdConflict);
      holdToken=data.token;holdExpiresAt=data.expires_at;el('slotHoldToken').value=holdToken;selectedSlot=candidate;
      document.querySelectorAll('.quick-booking__slot').forEach(item=>{item.classList.remove('is-selected');item.disabled=false});button.classList.add('is-selected');
      el('appointmentStart').value=`${candidate.date} ${candidate.start}`;el('appointmentEnd').value=`${candidate.date} ${candidate.end}`;el('assignedMasterId').value=candidate.userId;el('bookingDate').value=candidate.date;
      startHoldTimers();renderSummary();if(window.innerWidth<1200)el('clientCard').scrollIntoView({behavior:'smooth',block:'start'});
    } catch(error){document.querySelectorAll('.quick-booking__slot').forEach(item=>item.disabled=false);el('availabilityError').textContent=error.message||labels.holdConflict;el('availabilityError').classList.remove('d-none');loadAvailability();}
  }

  function renderSummary() {
    if(!selectedSlot)el('bookingSummary').textContent=labels.notSelected;else{const service=serviceById(el('serviceSelect').value);let repeat='';if(el('repeatEnabled').checked)repeat=`<span class="d-block text-body-tertiary fs-9">${escapeHtml(labels.occurrences.replace(':count',el('repeatCount').value))}</span>`;el('bookingSummary').innerHTML=`<strong class="d-block text-body-emphasis">${escapeHtml(service?.name)}</strong><span class="d-block">${escapeHtml(displayDate(selectedSlot.date))}, ${escapeHtml(selectedSlot.start)}–${escapeHtml(selectedSlot.end)}</span><span class="d-block text-body-tertiary fs-9">${escapeHtml(labels.assignedMaster.replace(':name',selectedSlot.masterName))}</span>${repeat}`;} refreshSubmit();
  }
  function refreshSubmit(){el('saveButton').disabled=!selectedSlot||!el('clientName').value.trim()||!el('clientPhone').value.trim()}
  function clearCustomer(){selectedCustomerId=null;el('customerId').value='';el('selectedCustomer').classList.add('d-none');el('selectedCustomer').textContent=''}

  async function searchCustomers(){const query=el('customerSearch').value.trim();if(query.length<2){el('customerResults').classList.add('d-none');return}try{const response=await fetch(`${urls.customers}?query=${encodeURIComponent(query)}`,{headers:{'Accept':'application/json'}});if(!response.ok)throw new Error();const data=await response.json();el('customerResults').innerHTML=data.customers.length?data.customers.map(customer=>customerButton(customer)).join(''):`<div class="p-3 text-body-tertiary fs-9">${escapeHtml(labels.noCustomers)}</div>`;el('customerResults').classList.remove('d-none')}catch{el('customerResults').innerHTML=`<div class="p-3 text-danger fs-9">${escapeHtml(labels.searchFailed)}</div>`;el('customerResults').classList.remove('d-none')}}
  function customerButton(customer){return `<button type="button" class="quick-booking__client-result" data-customer='${escapeHtml(JSON.stringify(customer))}'>${avatar(customer.name,null)}<span class="min-w-0"><strong class="d-block text-break">${escapeHtml(customer.name)}</strong><small class="d-block text-body-tertiary text-break">${escapeHtml(customer.email)} · ${escapeHtml(customer.phone)}</small><small class="badge badge-phoenix badge-phoenix-${customer.has_account?'success':'secondary'} mt-1">${customer.has_account?labels.account:labels.guest} · ${escapeHtml(labels.bookings.replace(':count',customer.appointments_count))}</small></span></button>`}
  function chooseCustomer(customer){selectedCustomerId=customer.id||null;el('customerId').value=customer.id||'';const names=String(customer.name||'').trim().split(/\s+/);el('clientName').value=customer.first_name||names.shift()||'';el('clientLastname').value=customer.last_name||names.join(' ');el('clientPhone').value=customer.phone||'';el('clientEmail').value=customer.email||'';el('clientLocale').value=customer.locale||'';el('selectedCustomer').innerHTML=`<strong>${escapeHtml(customer.name)}</strong><span class="d-block fs-9">${escapeHtml(customer.email)} · ${escapeHtml(customer.phone)}</span>`;el('selectedCustomer').classList.remove('d-none');el('customerResults').classList.add('d-none');el('duplicateWarning').classList.add('d-none');el('customerSearch').value='';refreshSubmit()}

  async function checkDuplicates(){if(selectedCustomerId)return;const payload={first_name:el('clientName').value,last_name:el('clientLastname').value,email:el('clientEmail').value,phone:el('clientPhone').value};if(!payload.email&&!payload.phone&&`${payload.first_name} ${payload.last_name}`.trim().length<4){el('duplicateWarning').classList.add('d-none');return}try{const response=await fetch(urls.duplicates,{method:'POST',headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify(payload)});if(!response.ok)return;const data=await response.json();el('duplicateCustomers').innerHTML=data.customers.map(customer=>`<button type="button" class="btn btn-sm btn-outline-warning d-flex align-items-center justify-content-between gap-2 w-100 mt-2 text-start" data-duplicate='${escapeHtml(JSON.stringify(customer))}'><span><strong class="d-block">${escapeHtml(customer.name)}</strong><small>${escapeHtml(customer.reasons.map(reason=>labels.duplicateReasons[reason]).join(', '))}</small></span><span>${escapeHtml(labels.useCustomer)}</span></button>`).join('');el('duplicateWarning').classList.toggle('d-none',data.customers.length===0)}catch{}}

  function clearErrors(){document.querySelectorAll('.is-invalid').forEach(item=>item.classList.remove('is-invalid'));document.querySelectorAll('.invalid-feedback').forEach(item=>item.textContent='');el('formError').classList.add('d-none')}
  const errorTargets={service_id:'serviceSelect',user_id:'masterSelect',client_name:'clientName',client_lastname:'clientLastname',client_phone:'clientPhone',client_email:'clientEmail',client_locale:'clientLocale',description:'clientDescription',admin_notes:'adminNotes',appointment_start:'bookingDate',appointment_end:'bookingDate',slot_hold_token:'bookingDate'};

  el('serviceSelect').addEventListener('change',()=>populateMasters());el('masterSelect').addEventListener('change',loadAvailability);el('bookingDate').addEventListener('change',loadAvailability);
  el('dateStrip').addEventListener('click',event=>{const button=event.target.closest('[data-date]');if(button){el('bookingDate').value=button.dataset.date;loadAvailability()}});
  document.addEventListener('click',event=>{const slot=event.target.closest('.quick-booking__slot');if(slot)selectSlot(slot);const customer=event.target.closest('[data-customer]');if(customer)chooseCustomer(JSON.parse(customer.dataset.customer));const duplicate=event.target.closest('[data-duplicate]');if(duplicate)chooseCustomer(JSON.parse(duplicate.dataset.duplicate))});
  el('customerSearch').addEventListener('input',()=>{clearTimeout(customerSearchTimer);customerSearchTimer=setTimeout(searchCustomers,250)});
  el('newCustomerButton').addEventListener('click',()=>{clearCustomer();el('customerResults').classList.add('d-none');el('duplicateWarning').classList.add('d-none');el('customerSearch').value='';['clientName','clientLastname','clientPhone','clientEmail'].forEach(id=>el(id).value='');el('clientName').focus();refreshSubmit()});
  ['clientName','clientLastname','clientPhone','clientEmail'].forEach(id=>el(id).addEventListener('input',event=>{if(event.isTrusted&&selectedCustomerId)clearCustomer();clearTimeout(duplicateTimer);duplicateTimer=setTimeout(checkDuplicates,450);refreshSubmit()}));
  el('repeatEnabled').addEventListener('change',()=>{const enabled=el('repeatEnabled').checked;el('repeatOptions').classList.toggle('d-none',!enabled);el('repeatCount').disabled=!enabled;el('repeatInterval').disabled=!enabled;renderSummary()});el('repeatCount').addEventListener('input',renderSummary);

  el('createAppointmentForm').addEventListener('submit',async event=>{event.preventDefault();clearErrors();if(!selectedSlot)return renderSummary();const button=el('saveButton');button.disabled=true;button.innerHTML=`<span class="spinner-border spinner-border-sm me-2"></span>${escapeHtml(labels.saving)}`;const payload=Object.fromEntries(new FormData(event.currentTarget).entries());payload.service_id=Number(payload.service_id);payload.user_id=Number(payload.user_id);if(!payload.customer_id)delete payload.customer_id;try{const response=await fetch(urls.store,{method:'POST',headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify(payload)});const data=await response.json().catch(()=>({}));if(!response.ok){const errors=data.errors||{};Object.entries(errors).forEach(([field,messages])=>{const target=el(errorTargets[field]);if(target){target.classList.add('is-invalid');const feedback=target.parentElement.querySelector('.invalid-feedback');if(feedback)feedback.textContent=Array.isArray(messages)?messages[0]:messages}});throw new Error(Object.values(errors).flat()[0]||data.message||labels.validationFailed)}holdToken=null;const title=data.created_count>1?labels.seriesCreated.replace(':count',data.created_count):labels.created;if(window.Swal)await Swal.fire({icon:'success',title,timer:1400,showConfirmButton:false});window.location.href=data.redirect_url||@json(route('calendar.index'))}catch(error){el('formError').textContent=error.message||labels.validationFailed;el('formError').classList.remove('d-none');button.disabled=false;button.innerHTML=`<span class="fas fa-check me-2"></span>${escapeHtml(labels.save)}`}});

  if(repeatAppointment){el('serviceSelect').value=String(repeatAppointment.service_id);populateMasters(repeatAppointment.user_id);chooseCustomer({id:repeatAppointment.customer_id,name:repeatAppointment.customer_name,email:repeatAppointment.client_email,phone:repeatAppointment.client_phone,locale:repeatAppointment.client_locale,has_account:repeatAppointment.has_account,appointments_count:repeatAppointment.appointments_count,first_name:repeatAppointment.client_name,last_name:repeatAppointment.client_lastname});el('clientDescription').value=repeatAppointment.description||'';el('adminNotes').value=repeatAppointment.admin_notes||''}
});
</script>
@endpush
</x-dashboard-layout>
