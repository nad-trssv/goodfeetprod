@push('scripts')
<script>
$(function () {
  const root = document.querySelector('.booking-alternative');
  if (!root) return;

  const flow = root.dataset.bookingFlow;
  const services = @json($services);
  const today = @json($today);
  const limitDate = @json($limitDate);
  const createUrl = @json(route('booking.create'));
  const noSlotsText = @json(__('msg.booking_no_slots'));
  const minuteText = @json(__('msg.min'));
  const fromText = @json(__('msg.price_from'));
  const csrf = document.querySelector('meta[name="csrf-token"]').content;
  const showServiceImages = @json((bool)($settings['show_service_images'] ?? true));
  const showMasterImages = @json((bool)($settings['show_master_images'] ?? true));
  let selectedService = null;
  let selectedMaster = null;
  let busyDays = [];
  let selectedDate = null;
  let availabilityRequest = null;
  let availabilityTimer = null;
  let pendingAvailabilityRange = null;
  let slotsRequest = null;
  let effectiveRequest = null;
  let availabilityVersion = 0;
  const loadedAvailabilityRanges = new Set();

  const escapeHtml = value => $('<div>').text(value ?? '').html();
  const serviceById = id => services.find(service => String(service.id) === String(id));
  const serviceName = service => service?.translation?.name || service?.name || '';

  function setSelected(selector, element) {
    root.querySelectorAll(selector).forEach(item => item.classList.remove('is-selected'));
    element.classList.add('is-selected');
  }

  function updateSummary() {
    $('#altSummaryService').text(selectedService ? serviceName(selectedService) : '—');
    $('#altSummaryMaster').text(selectedMaster ? selectedMaster.name : '—');
    $('#altSummaryPrice').text(selectedService ? `${selectedService.price_can_change ? fromText + ' ' : ''}${selectedService.effective_price} €` : '—');
  }

  function unlock(id) { document.getElementById(id)?.classList.remove('is-locked'); }
  function lock(id) { document.getElementById(id)?.classList.add('is-locked'); }
  function scrollToPanel(id, mobileOnly = false) {
    if (mobileOnly && !window.matchMedia('(max-width: 767px)').matches) return;

    window.requestAnimationFrame(() => {
      document.getElementById(id)?.scrollIntoView({behavior: 'smooth', block: 'start'});
    });
  }

  function masterButton(master) {
    const image = showMasterImages ? `<img src="${escapeHtml(master.profile_photo_url)}" alt="${escapeHtml(master.name)}" width="84" height="84" loading="lazy" decoding="async" class="master-directory__photo">` : '';
    return `<button type="button" class="alt-master booking-option" data-master-id="${Number(master.id)}">${image}<span><strong>${escapeHtml(master.name)}</strong><small>${escapeHtml(master.professional_title || @json(__('msg.booking_view_services')))}</small></span><i class="fa-solid fa-chevron-right"></i></button>`;
  }

  function serviceButton(service) {
    const image = showServiceImages ? `<img src="${escapeHtml(service.image_url)}" alt="${escapeHtml(serviceName(service))}" width="192" height="128" loading="lazy" decoding="async" class="booking-option__image">` : '';
    return `<button type="button" class="alt-service booking-option" data-service-id="${Number(service.id)}">${image}<span><strong>${escapeHtml(serviceName(service))}</strong><small>${Number(service.effective_duration_minutes)} ${escapeHtml(minuteText)}</small></span><b>${service.price_can_change ? escapeHtml(fromText) + ' ' : ''}${escapeHtml(service.effective_price)} &euro;</b></button>`;
  }

  function chooseService(service, element) {
    selectedService = service;
    selectedMaster = flow === 'service-first' ? null : selectedMaster;
    setSelected('.alt-service', element);
    updateSummary();
    resetCalendar();

    if (flow === 'service-first') {
      $('#altMasters').html((service.users || []).map(masterButton).join(''));
      unlock('altMastersPanel');
      lock('altDatePanel');
      scrollToPanel('altMastersPanel', true);
    } else if (selectedMaster) {
      loadAvailability();
      scrollToPanel('altDatePanel');
    }
  }

  function chooseMaster(master, element) {
    selectedMaster = master;
    selectedService = flow === 'master-first' ? null : selectedService;
    setSelected('.alt-master', element);
    updateSummary();
    resetCalendar();

    if (flow === 'master-first') {
      const offered = services.filter(service => (service.users || []).some(user => String(user.id) === String(master.id)));
      $('#altServices').html(offered.map(serviceButton).join(''));
      unlock('altServicesPanel');
      lock('altDatePanel');
      scrollToPanel('altServicesPanel', true);
    } else if (selectedService) {
      loadAvailability();
      scrollToPanel('altDatePanel');
    }
  }

  root.addEventListener('click', event => {
    const serviceElement = event.target.closest('.alt-service');
    if (serviceElement) {
      const service = serviceById(serviceElement.dataset.serviceId);
      if (service) chooseService(service, serviceElement);
      return;
    }

    const masterElement = event.target.closest('.alt-master');
    if (masterElement) {
      const masterId = masterElement.dataset.masterId;
      const master = (selectedService?.users || @json($bookingMasters)).find(item => String(item.id) === String(masterId));
      if (master) chooseMaster(master, masterElement);
    }
  });

  function resetCalendar() {
    availabilityVersion++;
    availabilityRequest?.abort();
    window.clearTimeout(availabilityTimer);
    availabilityTimer = null;
    pendingAvailabilityRange = null;
    slotsRequest?.abort();
    effectiveRequest?.abort();
    selectedDate = null;
    loadedAvailabilityRanges.clear();
    try { $('#altCalendar').fullCalendar('destroy'); } catch (error) {}
    $('#altCalendar, #altSlots, #altDateName').empty();
  }

  function loadAvailability() {
    if (!selectedService || !selectedMaster) return;
    unlock('altDatePanel');
    renderCalendar();
  }

  function loadAvailabilityRange(start, end) {
    if (!selectedService || !selectedMaster) return;
    const rangeStart = moment.max(moment(start), moment(today)).format('YYYY-MM-DD');
    const rangeEnd = moment.min(moment(end).subtract(1, 'day'), moment(limitDate)).format('YYYY-MM-DD');
    if (rangeEnd < rangeStart) return;

    const rangeKey = `${selectedService.id}:${selectedMaster.id}:${rangeStart}:${rangeEnd}`;
    if (loadedAvailabilityRanges.has(rangeKey)) return;

    const version = availabilityVersion;
    const requestedService = String(selectedService.id);
    const requestedMaster = String(selectedMaster.id);
    loadedAvailabilityRanges.add(rangeKey);
    $('#altCalendar').addClass('is-loading-availability');

    availabilityRequest = $.ajax({
      url: @json(route('booking.busy-days')),
      type: 'POST',
      headers: {'X-CSRF-TOKEN': csrf},
      dataType: 'json',
      data: {
        service_id: selectedService.id,
        user_id: selectedMaster.id,
        range_start: rangeStart,
        range_end: rangeEnd
      },
      success(response) {
        if (version !== availabilityVersion || String(selectedService?.id) !== requestedService || String(selectedMaster?.id) !== requestedMaster) return;
        busyDays = [...new Set([...busyDays, ...(Array.isArray(response) ? response : [])])];
        busyDays.forEach(date => $(`#altCalendar td[data-date="${date}"]`).addClass('disabled-day'));
      },
      error(xhr, status) {
        if (status !== 'abort') loadedAvailabilityRanges.delete(rangeKey);
      },
      complete() {
        if (version === availabilityVersion) $('#altCalendar').removeClass('is-loading-availability');
      }
    });
  }

  function scheduleAvailabilityRange(start, end) {
    pendingAvailabilityRange = [start.clone(), end.clone()];
    window.clearTimeout(availabilityTimer);
    availabilityTimer = window.setTimeout(() => {
      availabilityTimer = null;
      if (slotsRequest && slotsRequest.readyState !== 4) return;
      const range = pendingAvailabilityRange;
      pendingAvailabilityRange = null;
      if (range) loadAvailabilityRange(range[0], range[1]);
    }, 700);
  }

  function renderCalendar() {
    function selectDate(dateString) {
      if (busyDays.includes(dateString) || dateString < today || dateString > limitDate) return;
      $('#altCalendar td').removeClass('selected-day');
      $(`#altCalendar td[data-date="${dateString}"]`).addClass('selected-day');
      renderSlots(dateString);
    }

    $('#altCalendar').empty().fullCalendar({
      header: {left: 'title', center: '', right: 'prev,next'},
      firstDay: 1,
      selectable: true,
      editable: false,
      validRange: {start: today, end: moment(limitDate).add(1, 'day').format('YYYY-MM-DD')},
      dayRender(date, cell) {
        if (busyDays.includes(date.format('YYYY-MM-DD'))) cell.addClass('disabled-day');
      },
      viewRender(view) {
        window.setTimeout(() => busyDays.forEach(date => $(`#altCalendar td[data-date="${date}"]`).addClass('disabled-day')), 0);
        scheduleAvailabilityRange(view.start, view.end);
      },
      dayClick(date) {
        selectDate(date.format('YYYY-MM-DD'));
      }
    });

    $('#altCalendar')
      .off('click.bookingDate', 'td.fc-day-top[data-date]')
      .on('click.bookingDate', 'td.fc-day-top[data-date]', function () {
        selectDate(this.dataset.date);
      });

  }

  function renderSlots(date) {
    selectedDate = date;
    slotsRequest?.abort();
    effectiveRequest?.abort();
    const requestedService = String(selectedService.id);
    const requestedMaster = String(selectedMaster.id);
    $('#altDateName').text(moment(date).format('D MMMM'));
    $('#altSlots').html('<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i></div>');

    slotsRequest = $.ajax({
      url: @json(route('booking.slots')),
      type: 'POST',
      headers: {'X-CSRF-TOKEN': csrf},
      dataType: 'json',
      data: {service_id: selectedService.id, user_id: selectedMaster.id, choose_date: date},
      success(slots) {
        if (selectedDate !== date || String(selectedService?.id) !== requestedService || String(selectedMaster?.id) !== requestedMaster) return;
        if (!Array.isArray(slots) || slots.length === 0) {
          if (!busyDays.includes(date)) busyDays.push(date);
          $(`#altCalendar td[data-date="${date}"]`).addClass('disabled-day').removeClass('selected-day');
          $('#altSlots').html(`<div class="booking-placeholder">${escapeHtml(noSlotsText)}</div>`);
          return;
        }

        $('#altSlots').html(slots.map(slot => `<button type="button" class="alt-slot" data-start="${escapeHtml(slot.start)}" data-end="${escapeHtml(slot.end)}">${escapeHtml(slot.start)}</button>`).join(''));
        $('.alt-slot').on('click', function () {
          const target = new URL(createUrl, window.location.origin);
          target.search = new URLSearchParams({start: this.dataset.start, end: this.dataset.end, user_id: selectedMaster.id, service_id: selectedService.id, choose_date: date}).toString();
          window.location.assign(target.toString());
        });
      },
      error(xhr, status) {
        if (status === 'abort' || selectedDate !== date || String(selectedService?.id) !== requestedService || String(selectedMaster?.id) !== requestedMaster) return;
        if (!busyDays.includes(date)) busyDays.push(date);
        $(`#altCalendar td[data-date="${date}"]`).addClass('disabled-day').removeClass('selected-day');
        $('#altSlots').html(`<div class="booking-placeholder">${escapeHtml(noSlotsText)}</div>`);
      },
      complete() {
        if (!pendingAvailabilityRange) return;
        const range = pendingAvailabilityRange;
        pendingAvailabilityRange = null;
        window.clearTimeout(availabilityTimer);
        availabilityTimer = null;
        loadAvailabilityRange(range[0], range[1]);
      }
    });

    effectiveRequest = $.ajax({
      url: @json(route('booking.service.effective')),
      type: 'POST',
      headers: {'X-CSRF-TOKEN': csrf},
      dataType: 'json',
      data: {service_id: selectedService.id, date},
      success(data) {
        if (selectedDate !== date || String(selectedService?.id) !== requestedService) return;
        $('#altSummaryPrice').text(`${data.price_can_change ? fromText + ' ' : ''}${data.price} €`);
      }
    });
  }

  @if($chooseService)
    if (flow === 'service-first') document.querySelector(`.alt-service[data-service-id="{{ $chooseService->id }}"]`)?.click();
  @endif
});
</script>
@endpush
