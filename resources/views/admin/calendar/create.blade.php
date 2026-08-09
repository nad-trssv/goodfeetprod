@section('title', __('admin_appointment_create.title'))
<x-dashboard-layout>
<style>
  .quick-booking__step{border-left:4px solid var(--phoenix-primary);}
  .quick-booking__dates{display:grid;grid-template-columns:repeat(7,minmax(70px,1fr));gap:.5rem;overflow-x:auto;padding-bottom:.25rem;}
  .quick-booking__date{min-width:70px;border:1px solid var(--phoenix-border-color);border-radius:.75rem;background:var(--phoenix-body-bg);padding:.55rem .35rem;text-align:center;color:inherit;}
  .quick-booking__date:hover,.quick-booking__date.is-active{border-color:var(--phoenix-primary);box-shadow:0 0 0 2px color-mix(in srgb,var(--phoenix-primary) 18%,transparent);}
  .quick-booking__date.is-empty{text-decoration:line-through;opacity:.5;}
  .quick-booking__slots{display:grid;grid-template-columns:repeat(auto-fill,minmax(145px,1fr));gap:.65rem;}
  .quick-booking__slot{min-width:0;text-align:left;border:1px solid var(--phoenix-border-color);border-radius:.75rem;background:var(--phoenix-body-bg);padding:.7rem .8rem;color:inherit;}
  .quick-booking__slot:hover,.quick-booking__slot.is-selected{border-color:var(--phoenix-primary);background:var(--phoenix-primary-bg-subtle);box-shadow:0 0 0 2px color-mix(in srgb,var(--phoenix-primary) 16%,transparent);}
  .quick-booking__slot-master{display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-size:.72rem;color:var(--phoenix-tertiary-color);}
  .quick-booking__client-results{max-height:280px;overflow:auto;}
  .quick-booking__client-result{display:flex;width:100%;gap:.75rem;text-align:left;padding:.75rem;border:0;border-bottom:1px solid var(--phoenix-border-color);background:transparent;color:inherit;}
  .quick-booking__client-result:hover{background:var(--phoenix-tertiary-bg);}
  .quick-booking__avatar{width:38px;height:38px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;flex:0 0 auto;background:var(--phoenix-primary-bg-subtle);color:var(--phoenix-primary);font-weight:700;overflow:hidden;}
  .quick-booking__avatar img{width:100%;height:100%;object-fit:cover;}
  @media(max-width:767.98px){.quick-booking__dates{grid-template-columns:repeat(7,76px)}.quick-booking__slots{grid-template-columns:repeat(2,minmax(0,1fr));}.quick-booking__slot{padding:.65rem}.quick-booking__sticky{position:static!important;}}
</style>
<div class="content">
  <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div><h2 class="mb-1">{{ __('admin_appointment_create.title') }}</h2><p class="text-body-tertiary mb-0">{{ __('admin_appointment_create.subtitle') }}</p></div>
    <a href="{{ route('calendar.index') }}" class="btn btn-outline-secondary btn-sm"><span class="fas fa-arrow-left me-2"></span>{{ __('admin_appointment_create.back') }}</a>
  </div>

  <form id="createAppointmentForm" autocomplete="off" novalidate>
    @csrf
    <input type="hidden" id="customerId" name="customer_id">
    <input type="hidden" id="appointmentStart" name="appointment_start">
    <input type="hidden" id="appointmentEnd" name="appointment_end">
    <input type="hidden" id="assignedMasterId" name="user_id">

    <div class="row g-4 align-items-start">
      <div class="col-12 col-xl-8">
        <section class="card quick-booking__step mb-4"><div class="card-body p-3 p-lg-4">
          <h4 class="mb-3">{{ __('admin_appointment_create.service_and_master') }}</h4>
          <div class="row g-3 align-items-end">
            <div class="col-12 col-lg-7"><label class="form-label" for="serviceSelect">{{ __('admin_appointment_create.service') }}</label><select class="form-select" id="serviceSelect" name="service_id" required><option value="">{{ __('admin_appointment_create.choose_service') }}</option>@foreach($services as $service)<option value="{{ $service['id'] }}">{{ $service['name'] }} · {{ __('admin_appointment_create.duration',['minutes'=>$service['duration_minutes']]) }}</option>@endforeach</select><div class="invalid-feedback" id="serviceError"></div></div>
            <div class="col-12 col-lg-5"><label class="form-label" for="masterSelect">{{ __('admin_appointment_create.master') }}</label><select class="form-select" id="masterSelect" disabled><option value="all">{{ __('admin_appointment_create.any_master') }}</option></select><div class="invalid-feedback" id="masterError"></div></div>
          </div>
          <div id="servicePreview" class="d-none align-items-center gap-3 mt-3 p-3 rounded-3 bg-body-tertiary"><img id="serviceImage" src="" alt="" width="68" height="52" class="rounded object-fit-cover flex-shrink-0"><div class="min-w-0"><strong id="serviceName" class="d-block text-break"></strong><span id="serviceMeta" class="text-body-tertiary fs-9"></span><div class="text-body-tertiary fs-10">{{ __('admin_appointment_create.server_price') }}</div></div></div>
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
          <div class="row g-3 mt-1">
            <div class="col-12 col-sm-6 col-xl-12"><label class="form-label" for="clientName">{{ __('admin_appointment_create.name') }}</label><input class="form-control" id="clientName" name="client_name" maxlength="190" required><div class="invalid-feedback" id="clientNameError"></div></div>
            <div class="col-12 col-sm-6 col-xl-12"><label class="form-label" for="clientLastname">{{ __('admin_appointment_create.lastname') }}</label><input class="form-control" id="clientLastname" name="client_lastname" maxlength="190"><div class="invalid-feedback" id="clientLastnameError"></div></div>
            <div class="col-12 col-sm-6 col-xl-12"><label class="form-label" for="clientPhone">{{ __('admin_appointment_create.phone') }}</label><input class="form-control" id="clientPhone" name="client_phone" type="tel" maxlength="32" required><div class="invalid-feedback" id="clientPhoneError"></div></div>
            <div class="col-12 col-sm-6 col-xl-12"><label class="form-label" for="clientEmail">{{ __('admin_appointment_create.email') }}</label><input class="form-control" id="clientEmail" name="client_email" type="email" maxlength="190"><div class="invalid-feedback" id="clientEmailError"></div></div>
            <div class="col-12"><label class="form-label" for="clientDescription">{{ __('admin_appointment_create.note') }}</label><textarea class="form-control" id="clientDescription" name="description" rows="3" maxlength="2000" placeholder="{{ __('admin_appointment_create.note_placeholder') }}"></textarea><div class="invalid-feedback" id="descriptionError"></div></div>
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
    'to'=>__('admin_appointment_create.to'),'loading'=>__('admin_appointment_create.loading'),'requestFailed'=>__('admin_appointment_create.request_failed'),
    'searchFailed'=>__('admin_appointment_create.search_failed'),'noCustomers'=>__('admin_appointment_create.no_customers'),'account'=>__('admin_appointment_create.account'),
    'guest'=>__('admin_appointment_create.guest'),'bookings'=>__('admin_appointment_create.bookings_count',['count'=>':count']),'notSelected'=>__('admin_appointment_create.not_selected'),
    'save'=>__('admin_appointment_create.save'),'saving'=>__('admin_appointment_create.saving'),'created'=>__('admin_appointment_create.created'),
    'validationFailed'=>__('admin_appointment_create.validation_failed'),'assignedMaster'=>__('admin_appointment_create.assigned_master',['name'=>':name']),
  ];
@endphp
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const services = @json($services);
  const currentUserId = @json((int)$currentUserId);
  const canChooseMaster = @json((bool)$canChooseMaster);
  const locale = @json(app()->getLocale());
  const csrf = document.querySelector('meta[name="csrf-token"]').content;
  const urls = { availability: @json(route('calendar.availability')), customers: @json(route('calendar.customers.search')), store: @json(route('calendar.store')) };
  const labels = @json($quickBookingLabels);
  const el = id => document.getElementById(id);
  let selectedSlot = null;
  let selectedCustomerId = null;
  let availabilityController = null;
  let customerSearchTimer = null;

  const escapeHtml = value => String(value ?? '').replace(/[&<>'"]/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[char]));
  const serviceById = id => services.find(service => String(service.id) === String(id));
  const initials = name => String(name || '').trim().split(/\s+/).slice(0,2).map(word => word.charAt(0).toUpperCase()).join('') || '—';
  const avatar = (name, src) => `<span class="quick-booking__avatar">${src ? `<img src="${escapeHtml(src)}" alt="${escapeHtml(name)}">` : escapeHtml(initials(name))}</span>`;
  const displayDate = date => new Intl.DateTimeFormat(locale,{weekday:'short',day:'2-digit',month:'short'}).format(new Date(date+'T12:00:00'));
  const money = value => new Intl.NumberFormat(locale,{style:'currency',currency:'EUR'}).format(Number(value || 0));

  function clearSelectedSlot() {
    selectedSlot = null;
    el('appointmentStart').value = '';
    el('appointmentEnd').value = '';
    el('assignedMasterId').value = '';
    document.querySelectorAll('.quick-booking__slot').forEach(button => button.classList.remove('is-selected'));
    renderSummary();
  }

  function populateMasters() {
    const service = serviceById(el('serviceSelect').value);
    el('masterSelect').innerHTML = canChooseMaster ? `<option value="all">${escapeHtml(labels.anyMaster)}</option>` : '';
    el('masterSelect').disabled = !service;
    if (!service) { el('servicePreview').classList.add('d-none'); clearSelectedSlot(); return; }
    service.users.forEach(user => {
      const option = new Option(user.name + (Number(user.id) === currentUserId ? ` (${labels.currentMaster})` : ''), user.id);
      el('masterSelect').add(option);
    });
    el('masterSelect').value = service.users.some(user => Number(user.id) === currentUserId) ? String(currentUserId) : (canChooseMaster ? 'all' : '');
    el('serviceImage').src = service.image_url;
    el('serviceImage').alt = service.name;
    el('serviceName').textContent = service.name;
    el('serviceMeta').textContent = `${labels.duration.replace(':minutes',service.duration_minutes)} · ${money(service.price)}`;
    el('servicePreview').classList.remove('d-none'); el('servicePreview').classList.add('d-flex');
    clearSelectedSlot();
    loadAvailability();
  }

  function slotButton(slot, recommended = false) {
    return `<button type="button" class="quick-booking__slot" data-date="${slot.date}" data-start="${slot.start}" data-end="${slot.end}" data-user-id="${slot.user_id}" data-master-name="${escapeHtml(slot.master_name)}">
      <strong>${recommended ? `${escapeHtml(displayDate(slot.date))} · ` : ''}${escapeHtml(slot.start)}</strong> <small>${escapeHtml(labels.to)} ${escapeHtml(slot.end)}</small>
      <span class="quick-booking__slot-master">${escapeHtml(slot.master_name)}</span></button>`;
  }

  function renderAvailability(data) {
    el('availabilityLoading').classList.add('d-none');
    el('availabilityContent').classList.remove('d-none');
    el('selectedDateLabel').textContent = displayDate(data.date);
    el('slots').innerHTML = data.slots.map(slot => slotButton(slot)).join('');
    el('slotsEmpty').classList.toggle('d-none', data.slots.length > 0);
    el('recommendations').innerHTML = data.recommendations.map(slot => slotButton(slot,true)).join('');
    el('recommendationsSection').classList.toggle('d-none', data.recommendations.length === 0);
    el('dateStrip').innerHTML = data.calendar_days.map(day => `<button type="button" class="quick-booking__date ${day.date === data.date ? 'is-active' : ''} ${day.available === false ? 'is-empty' : ''}" data-date="${day.date}"><small class="d-block text-body-tertiary">${escapeHtml(day.weekday)}</small><strong class="d-block fs-7">${escapeHtml(day.day)}</strong><small>${escapeHtml(day.month)}</small></button>`).join('');
    const service = serviceById(el('serviceSelect').value);
    if (service) el('serviceMeta').textContent = `${labels.duration.replace(':minutes',data.duration_minutes)} · ${money(data.price)}`;
  }

  async function loadAvailability() {
    const serviceId = el('serviceSelect').value;
    if (!serviceId || !el('bookingDate').value || el('masterSelect').disabled) return;
    clearSelectedSlot();
    availabilityController?.abort();
    availabilityController = new AbortController();
    el('availabilityHint').classList.add('d-none'); el('availabilityError').classList.add('d-none'); el('availabilityContent').classList.add('d-none'); el('availabilityLoading').classList.remove('d-none');
    try {
      const response = await fetch(urls.availability,{method:'POST',signal:availabilityController.signal,headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify({service_id:Number(serviceId),user_id:el('masterSelect').value,date:el('bookingDate').value})});
      if (!response.ok) throw new Error((await response.json().catch(()=>({}))).message || labels.requestFailed);
      renderAvailability(await response.json());
    } catch (error) {
      if (error.name === 'AbortError') return;
      el('availabilityLoading').classList.add('d-none'); el('availabilityError').textContent = error.message || labels.requestFailed; el('availabilityError').classList.remove('d-none');
    }
  }

  function selectSlot(button) {
    document.querySelectorAll('.quick-booking__slot').forEach(item => item.classList.remove('is-selected'));
    button.classList.add('is-selected');
    selectedSlot = {date:button.dataset.date,start:button.dataset.start,end:button.dataset.end,userId:Number(button.dataset.userId),masterName:button.dataset.masterName};
    el('appointmentStart').value = `${selectedSlot.date} ${selectedSlot.start}`;
    el('appointmentEnd').value = `${selectedSlot.date} ${selectedSlot.end}`;
    el('assignedMasterId').value = selectedSlot.userId;
    el('bookingDate').value = selectedSlot.date;
    renderSummary();
    if (window.innerWidth < 1200) el('clientCard').scrollIntoView({behavior:'smooth',block:'start'});
  }

  function renderSummary() {
    if (!selectedSlot) el('bookingSummary').textContent = labels.notSelected;
    else {
      const service = serviceById(el('serviceSelect').value);
      el('bookingSummary').innerHTML = `<strong class="d-block text-body-emphasis">${escapeHtml(service?.name)}</strong><span class="d-block">${escapeHtml(displayDate(selectedSlot.date))}, ${escapeHtml(selectedSlot.start)}–${escapeHtml(selectedSlot.end)}</span><span class="d-block text-body-tertiary fs-9">${escapeHtml(labels.assignedMaster.replace(':name',selectedSlot.masterName))}</span>`;
    }
    refreshSubmit();
  }

  function refreshSubmit() { el('saveButton').disabled = !selectedSlot || !el('clientName').value.trim() || !el('clientPhone').value.trim(); }

  function clearCustomer() {
    selectedCustomerId = null; el('customerId').value = ''; el('selectedCustomer').classList.add('d-none'); el('selectedCustomer').textContent = '';
  }

  async function searchCustomers() {
    const query = el('customerSearch').value.trim();
    if (query.length < 2) { el('customerResults').classList.add('d-none'); return; }
    try {
      const response = await fetch(`${urls.customers}?query=${encodeURIComponent(query)}`,{headers:{'Accept':'application/json'}});
      if (!response.ok) throw new Error(labels.searchFailed);
      const data = await response.json();
      el('customerResults').innerHTML = data.customers.length ? data.customers.map(customer => `<button type="button" class="quick-booking__client-result" data-customer='${escapeHtml(JSON.stringify(customer))}'>${avatar(customer.name,null)}<span class="min-w-0"><strong class="d-block text-break">${escapeHtml(customer.name)}</strong><small class="d-block text-body-tertiary text-break">${escapeHtml(customer.email)} · ${escapeHtml(customer.phone)}</small><small class="badge badge-phoenix badge-phoenix-${customer.has_account?'success':'secondary'} mt-1">${customer.has_account?labels.account:labels.guest} · ${escapeHtml(labels.bookings.replace(':count',customer.appointments_count))}</small></span></button>`).join('') : `<div class="p-3 text-body-tertiary fs-9">${escapeHtml(labels.noCustomers)}</div>`;
      el('customerResults').classList.remove('d-none');
    } catch { el('customerResults').innerHTML = `<div class="p-3 text-danger fs-9">${escapeHtml(labels.searchFailed)}</div>`; el('customerResults').classList.remove('d-none'); }
  }

  function chooseCustomer(customer) {
    selectedCustomerId = customer.id; el('customerId').value = customer.id;
    const names = String(customer.name || '').trim().split(/\s+/); el('clientName').value = names.shift() || ''; el('clientLastname').value = names.join(' ');
    el('clientPhone').value = customer.phone || ''; el('clientEmail').value = customer.email || '';
    el('selectedCustomer').innerHTML = `<strong>${escapeHtml(customer.name)}</strong><span class="d-block fs-9">${escapeHtml(customer.email)} · ${escapeHtml(customer.phone)}</span>`;
    el('selectedCustomer').classList.remove('d-none'); el('customerResults').classList.add('d-none'); el('customerSearch').value = '';
    refreshSubmit();
  }

  function clearErrors() { document.querySelectorAll('.is-invalid').forEach(item=>item.classList.remove('is-invalid')); document.querySelectorAll('.invalid-feedback').forEach(item=>item.textContent=''); el('formError').classList.add('d-none'); }
  const errorTargets = {service_id:'serviceSelect',user_id:'masterSelect',client_name:'clientName',client_lastname:'clientLastname',client_phone:'clientPhone',client_email:'clientEmail',description:'clientDescription',appointment_start:'bookingDate',appointment_end:'bookingDate'};

  el('serviceSelect').addEventListener('change', populateMasters);
  el('masterSelect').addEventListener('change', () => { clearSelectedSlot(); loadAvailability(); });
  el('bookingDate').addEventListener('change', loadAvailability);
  el('dateStrip').addEventListener('click', event => { const button=event.target.closest('[data-date]'); if(!button)return; el('bookingDate').value=button.dataset.date; loadAvailability(); });
  document.addEventListener('click', event => { const button=event.target.closest('.quick-booking__slot'); if(button)selectSlot(button); });
  el('customerSearch').addEventListener('input',()=>{clearTimeout(customerSearchTimer);customerSearchTimer=setTimeout(searchCustomers,250);});
  el('customerResults').addEventListener('click',event=>{const button=event.target.closest('[data-customer]');if(button)chooseCustomer(JSON.parse(button.dataset.customer));});
  el('newCustomerButton').addEventListener('click',()=>{clearCustomer();el('customerResults').classList.add('d-none');el('customerSearch').value='';['clientName','clientLastname','clientPhone','clientEmail'].forEach(id=>el(id).value='');el('clientName').focus();refreshSubmit();});
  ['clientName','clientLastname','clientPhone','clientEmail'].forEach(id=>el(id).addEventListener('input',event=>{if(event.isTrusted&&selectedCustomerId)clearCustomer();refreshSubmit();}));

  el('createAppointmentForm').addEventListener('submit', async event => {
    event.preventDefault(); clearErrors(); if (!selectedSlot) return renderSummary();
    const button=el('saveButton'); button.disabled=true; button.innerHTML=`<span class="spinner-border spinner-border-sm me-2"></span>${escapeHtml(labels.saving)}`;
    const payload=Object.fromEntries(new FormData(event.currentTarget).entries()); payload.service_id=Number(payload.service_id); payload.user_id=Number(payload.user_id); if(!payload.customer_id)delete payload.customer_id;
    try {
      const response=await fetch(urls.store,{method:'POST',headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify(payload)}); const data=await response.json().catch(()=>({}));
      if(!response.ok){const errors=data.errors||{};Object.entries(errors).forEach(([field,messages])=>{const target=el(errorTargets[field]);if(target){target.classList.add('is-invalid');const feedback=target.parentElement.querySelector('.invalid-feedback');if(feedback)feedback.textContent=Array.isArray(messages)?messages[0]:messages;}});throw new Error(Object.values(errors).flat()[0]||data.message||labels.validationFailed);}
      if(window.Swal) await Swal.fire({icon:'success',title:labels.created,timer:1200,showConfirmButton:false});
      window.location.href=data.redirect_url||@json(route('calendar.index'));
    } catch(error){el('formError').textContent=error.message||labels.validationFailed;el('formError').classList.remove('d-none');button.disabled=false;button.innerHTML=`<span class="fas fa-check me-2"></span>${escapeHtml(labels.save)}`;}
  });
});
</script>
@endpush
</x-dashboard-layout>
