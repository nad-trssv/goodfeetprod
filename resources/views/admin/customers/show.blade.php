@section('title', $customer->full_name)
@php($activeTab = request('tab', 'overview'))
<x-dashboard-layout>
<div class="content">
  <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div><a class="text-decoration-none fs-9" href="{{ route('crm.customers.index') }}">← {{ __('crm.back') }}</a><h2 class="mt-2 mb-1">{{ $customer->full_name }}</h2><p class="text-body-tertiary mb-0">{{ __('crm.customer_card') }}</p></div>
    <a class="btn btn-outline-primary" href="{{ route('calendarList',['customer_id'=>$customer->id]) }}"><span class="fas fa-calendar-alt me-2"></span>{{ __('admin_customers.open_bookings') }}</a>
  </div>

  <div class="row g-4">
    <aside class="col-12 col-xl-4 col-xxl-3">
      <section class="card position-sticky" style="top:1rem"><div class="card-body p-4">
        <div class="text-center mb-4"><x-ui.avatar :name="$customer->full_name" :size="88" class="mb-3" /><h4 class="mb-2">{{ $customer->full_name }}</h4><span class="badge badge-phoenix badge-phoenix-{{ filled($customer->getRawOriginal('password')) ? 'success':'secondary' }}">{{ filled($customer->getRawOriginal('password')) ? __('admin_customers.account'):__('admin_customers.guest') }}</span></div>
        <div class="d-grid gap-3">
          <div><small class="text-body-tertiary d-block">Email</small><a class="text-break" href="mailto:{{ $customer->email }}">{{ $customer->email }}</a></div>
          <div><small class="text-body-tertiary d-block">{{ __('admin_customers.phone') }}</small><a href="tel:{{ $customer->phone }}">{{ $customer->phone }}</a></div>
          <div><small class="text-body-tertiary d-block">{{ __('admin_customers.first_seen') }}</small><strong>{{ $customer->created_at->format('d.m.Y H:i') }}</strong></div>
          @if($customer->account_registered_at)<div><small class="text-body-tertiary d-block">{{ __('admin_customers.registered_at') }}</small><strong>{{ $customer->account_registered_at->format('d.m.Y H:i') }}</strong></div>@endif
          <div><small class="text-body-tertiary d-block">{{ __('admin_customers.language') }}</small><strong>{{ strtoupper($customer->locale) }}</strong></div>
        </div>
      </div></section>
    </aside>

    <div class="col-12 col-xl-8 col-xxl-9">
      <nav class="card card-body p-2 mb-4" aria-label="{{ __('admin_customers.profile_sections') }}"><div class="nav nav-pills flex-column flex-sm-row gap-2" role="tablist">
        <button class="nav-link flex-sm-fill @if($activeTab==='overview') active @endif" data-bs-toggle="tab" data-bs-target="#customer-overview" type="button">{{ __('admin_customers.overview') }}</button>
        <button class="nav-link flex-sm-fill @if($activeTab==='crm') active @endif" data-bs-toggle="tab" data-bs-target="#customer-crm" type="button">{{ __('crm.profile') }}</button>
        <button class="nav-link flex-sm-fill @if($activeTab==='notes') active @endif" data-bs-toggle="tab" data-bs-target="#customer-notes" type="button">{{ __('crm.notes') }}</button>
        <button class="nav-link flex-sm-fill @if($activeTab==='consents') active @endif" data-bs-toggle="tab" data-bs-target="#customer-consents" type="button">{{ __('crm.consents') }}</button>
        <button class="nav-link flex-sm-fill @if($activeTab==='documents') active @endif" data-bs-toggle="tab" data-bs-target="#customer-documents" type="button">{{ __('crm.documents') }}</button>
        <button class="nav-link flex-sm-fill @if($activeTab==='appointments') active @endif" data-bs-toggle="tab" data-bs-target="#customer-appointments" type="button">{{ __('admin_customers.bookings') }} <span class="badge bg-body-secondary text-body ms-1">{{ (int)$summary->bookings_count }}</span></button>
        <button class="nav-link flex-sm-fill @if($activeTab==='preferences') active @endif" data-bs-toggle="tab" data-bs-target="#customer-preferences" type="button">{{ __('admin_customers.preferences') }}</button>
        @if(filled($customer->getRawOriginal('password')))<button class="nav-link flex-sm-fill @if($activeTab==='possible') active @endif" data-bs-toggle="tab" data-bs-target="#customer-possible" type="button">{{ __('admin_customers.possible_matches') }} @if($possibleCount)<span class="badge bg-warning text-dark ms-1">{{ $possibleCount }}</span>@endif</button>@endif
      </div></nav>

      <div class="tab-content">
        <section id="customer-overview" class="tab-pane fade @if($activeTab==='overview') show active @endif">
          @if(filled($customer->crmProfile?->important_warnings) || filled($customer->crmProfile?->contraindications))
            <div class="alert alert-danger mb-4" role="alert">
              @if(filled($customer->crmProfile?->important_warnings))<h5 class="alert-heading">{{ __('crm.warnings') }}</h5><div class="text-break">{{ $customer->crmProfile->important_warnings }}</div>@endif
              @if(filled($customer->crmProfile?->contraindications))<h5 class="alert-heading mt-3">{{ __('crm.contraindications') }}</h5><div class="text-break">{{ $customer->crmProfile->contraindications }}</div>@endif
            </div>
          @endif
          <div class="row g-3 mb-4">
            @foreach([
              [__('admin_customers.revenue'), number_format((float)$summary->revenue,2,',',' ').' €', 'success'],
              [__('admin_customers.bookings'), (int)$summary->bookings_count, 'primary'],
              [__('admin_customers.completed'), (int)$summary->completed_count, 'success'],
              [__('admin_customers.average_check'), number_format((float)$summary->average_check,2,',',' ').' €', 'info'],
              [__('admin_customers.no_show'), (int)$summary->no_show_count, 'danger'],
              [__('admin_customers.upcoming'), (int)$summary->upcoming_count, 'primary'],
            ] as [$label,$value,$tone])
              <div class="col-6 col-lg-4"><div class="card h-100 border-start border-3 border-{{ $tone }}"><div class="card-body p-3"><small class="text-body-tertiary d-block mb-1">{{ $label }}</small><strong class="fs-6 text-body-emphasis">{{ $value }}</strong></div></div></div>
            @endforeach
          </div>
          <section class="card"><div class="card-body p-4"><h4 class="mb-4">{{ __('admin_customers.activity') }}</h4><div class="row g-4">
            <div class="col-sm-6"><small class="text-body-tertiary d-block">{{ __('admin_customers.first_booking') }}</small><strong>{{ $summary->first_booking_at ? \Carbon\Carbon::parse($summary->first_booking_at)->format('d.m.Y H:i') : '—' }}</strong></div>
            <div class="col-sm-6"><small class="text-body-tertiary d-block">{{ __('admin_customers.last_booking') }}</small><strong>{{ $summary->last_booking_at ? \Carbon\Carbon::parse($summary->last_booking_at)->format('d.m.Y H:i') : '—' }}</strong></div>
            <div class="col-sm-6"><small class="text-body-tertiary d-block">{{ __('admin_customers.visit_frequency') }}</small><strong>{{ $visitFrequencyDays ? __('admin_customers.every_days',['days'=>$visitFrequencyDays]) : __('admin_customers.not_enough_data') }}</strong></div>
            <div class="col-sm-6"><small class="text-body-tertiary d-block">{{ __('admin_customers.cancelled') }}</small><strong>{{ (int)$summary->cancelled_count }}</strong></div>
          </div></div></section>
        </section>

        <section id="customer-crm" class="tab-pane fade @if($activeTab==='crm') show active @endif">
          <form method="POST" action="{{ route('crm.customers.update', $customer) }}" class="card"><div class="card-body p-4">@csrf @method('PUT')
            <div class="row g-4">
              <div class="col-12"><label class="form-label fw-semibold" for="crm-warnings">{{ __('crm.warnings') }}</label><textarea id="crm-warnings" class="form-control" name="important_warnings" rows="4" maxlength="10000" @cannot('crm.update') disabled @endcannot>{{ old('important_warnings', $customer->crmProfile?->important_warnings) }}</textarea></div>
              <div class="col-12"><label class="form-label fw-semibold" for="crm-contraindications">{{ __('crm.contraindications') }}</label><textarea id="crm-contraindications" class="form-control" name="contraindications" rows="4" maxlength="10000" @cannot('crm.update') disabled @endcannot>{{ old('contraindications', $customer->crmProfile?->contraindications) }}</textarea></div>
              <div class="col-12 col-lg-6"><label class="form-label" for="crm-master">{{ __('crm.preferred_master') }}</label><select id="crm-master" class="form-select" name="preferred_user_id" @cannot('crm.update') disabled @endcannot><option value="">{{ __('crm.not_selected') }}</option>@foreach($masters as $master)<option value="{{ $master->id }}" @selected((string)old('preferred_user_id',$customer->crmProfile?->preferred_user_id)===(string)$master->id)>{{ $master->name }}</option>@endforeach</select></div>
              <div class="col-12 col-lg-6"><label class="form-label">{{ __('crm.tags') }}</label><div class="d-flex flex-wrap gap-3 border rounded p-3">@forelse($tags as $tag)<label class="form-check"><input class="form-check-input" type="checkbox" name="tag_ids[]" value="{{ $tag->id }}" @checked(in_array($tag->id,old('tag_ids',$customer->crmTags->pluck('id')->all()))) @cannot('crm.update') disabled @endcannot><span class="form-check-label"><span class="badge text-white" style="background-color:{{ $tag->color }}">{{ $tag->name }}</span></span></label>@empty<span class="text-body-tertiary">{{ __('crm.not_selected') }}</span>@endforelse</div></div>
              <div class="col-12"><label class="form-label">{{ __('crm.preferred_services') }}</label><div class="row g-2 border rounded p-3" style="max-height:22rem;overflow:auto">@foreach($services as $service)@php($serviceName=$service->translations->firstWhere('locale',app()->getLocale())?->name ?? $service->name)<div class="col-12 col-md-6"><label class="form-check"><input class="form-check-input" type="checkbox" name="preferred_service_ids[]" value="{{ $service->id }}" @checked(in_array($service->id,old('preferred_service_ids',$customer->preferredServices->pluck('id')->all()))) @cannot('crm.update') disabled @endcannot><span class="form-check-label">{{ $serviceName }}</span></label></div>@endforeach</div></div>
            </div>
            @can('crm.update')<div class="text-end mt-4"><button class="btn btn-primary" type="submit">{{ __('crm.save') }}</button></div>@endcan
          </div></form>
        </section>

        <section id="customer-notes" class="tab-pane fade @if($activeTab==='notes') show active @endif">
          @can('crm.update')<form method="POST" action="{{ route('crm.customers.notes.store',$customer) }}" class="card card-body mb-4">@csrf<textarea class="form-control" name="body" rows="4" maxlength="5000" required placeholder="{{ __('crm.note_placeholder') }}">{{ old('body') }}</textarea><div class="d-flex justify-content-between align-items-center mt-3"><label class="form-check mb-0"><input class="form-check-input" type="checkbox" name="is_pinned" value="1"><span class="form-check-label">{{ __('crm.pin_note') }}</span></label><button class="btn btn-primary" type="submit">{{ __('crm.note_added') }}</button></div></form>@endcan
          <div class="d-grid gap-3">@forelse($customer->crmNotes as $note)<article class="card {{ $note->is_pinned ? 'border-warning':'' }}"><div class="card-body"><div class="d-flex justify-content-between gap-3"><div><strong>{{ $note->author?->name }}</strong><small class="text-body-tertiary ms-2">{{ $note->created_at->format('d.m.Y H:i') }}</small>@if($note->is_pinned)<span class="fas fa-thumbtack text-warning ms-2"></span>@endif</div>@can('crm.update')<form method="POST" action="{{ route('crm.customers.notes.destroy',[$customer,$note]) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" type="submit">{{ __('crm.delete') }}</button></form>@endcan</div><p class="mb-0 mt-3 text-break" style="white-space:pre-wrap">{{ $note->body }}</p></div></article>@empty<div class="card card-body text-body-tertiary">{{ __('crm.notes') }}: 0</div>@endforelse</div>
        </section>

        <section id="customer-consents" class="tab-pane fade @if($activeTab==='consents') show active @endif">
          @can('crm.update')<form method="POST" action="{{ route('crm.customers.consents.store',$customer) }}" class="card card-body mb-4">@csrf<div class="row g-3"><div class="col-md-4"><label class="form-label">{{ __('crm.consent_type') }}</label><select class="form-select" name="type">@foreach(['data_processing','marketing','photos','health_data','terms'] as $type)<option value="{{ $type }}">{{ __('crm.consent_'.$type) }}</option>@endforeach</select></div><div class="col-md-3"><label class="form-label">{{ __('crm.captured_at') }}</label><input class="form-control" type="datetime-local" name="captured_at" value="{{ now()->format('Y-m-d\TH:i') }}" required></div><div class="col-md-3"><label class="form-label">{{ __('crm.source') }}</label><input class="form-control" name="source" maxlength="100"></div><div class="col-md-2 d-flex align-items-end"><label class="form-check mb-2"><input type="hidden" name="is_granted" value="0"><input class="form-check-input" type="checkbox" name="is_granted" value="1" checked><span class="form-check-label">{{ __('crm.granted') }}</span></label></div><div class="col-12"><textarea class="form-control" name="note" rows="2" maxlength="1000" placeholder="{{ __('crm.note') }}"></textarea></div></div><div class="text-end mt-3"><button class="btn btn-primary" type="submit">{{ __('crm.save') }}</button></div></form>@endcan
          <div class="card overflow-hidden"><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th class="ps-4">{{ __('crm.consent_type') }}</th><th>{{ __('crm.captured_at') }}</th><th>{{ __('crm.source') }}</th><th>{{ __('crm.note') }}</th></tr></thead><tbody>@forelse($customer->consents as $consent)<tr><td class="ps-4"><span class="badge badge-phoenix badge-phoenix-{{ $consent->is_granted ? 'success':'danger' }}">{{ __('crm.consent_'.$consent->type) }} · {{ $consent->is_granted ? __('crm.granted'):__('crm.withdrawn') }}</span></td><td>{{ $consent->captured_at->format('d.m.Y H:i') }}</td><td>{{ $consent->source ?: '—' }}<small class="d-block text-body-tertiary">{{ $consent->recordedBy?->name }}</small></td><td>{{ $consent->note ?: '—' }}</td></tr>@empty<tr><td colspan="4" class="text-center py-5 text-body-tertiary">{{ __('crm.no_consents') }}</td></tr>@endforelse</tbody></table></div></div>
        </section>

        <section id="customer-documents" class="tab-pane fade @if($activeTab==='documents') show active @endif">
          @can('crm.documents')<form method="POST" enctype="multipart/form-data" action="{{ route('crm.customers.documents.store',$customer) }}" class="card card-body mb-4">@csrf<div class="row g-3 align-items-end"><div class="col-md-4"><label class="form-label">{{ __('crm.document_category') }}</label><select class="form-select" name="category">@foreach(['general','consent','photo','medical','other'] as $category)<option value="{{ $category }}">{{ __('crm.document_'.$category) }}</option>@endforeach</select></div><div class="col-md-6"><input class="form-control" type="file" name="file" accept=".pdf,.jpg,.jpeg,.png,.webp" required><small class="text-body-tertiary">{{ __('crm.document_hint') }}</small></div><div class="col-md-2"><button class="btn btn-primary w-100" type="submit">{{ __('crm.upload') }}</button></div></div></form>@endcan
          <div class="row g-3">@forelse($customer->documents as $document)<div class="col-12 col-md-6 col-xl-4"><article class="card h-100 overflow-hidden">@if(str_starts_with($document->mime_type,'image/'))<img src="{{ route('crm.documents.preview',$document) }}" alt="" loading="lazy" class="w-100 object-fit-cover" style="height:150px">@endif<div class="card-body"><strong class="d-block text-break">{{ $document->original_name }}</strong><small class="text-body-tertiary">{{ __('crm.document_'.$document->category) }} · {{ number_format($document->size/1024,0) }} KB</small><div class="d-flex gap-2 mt-3"><a class="btn btn-sm btn-outline-primary" href="{{ route('crm.documents.preview',$document) }}" target="_blank">{{ __('crm.preview') }}</a><a class="btn btn-sm btn-outline-secondary" href="{{ route('crm.documents.download',$document) }}">{{ __('crm.download') }}</a>@can('crm.documents')<form method="POST" action="{{ route('crm.documents.destroy',$document) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" type="submit">{{ __('crm.delete') }}</button></form>@endcan</div></div></article></div>@empty<div class="col-12"><div class="card card-body text-center text-body-tertiary">{{ __('crm.no_documents') }}</div></div>@endforelse</div>
        </section>

        <section id="customer-appointments" class="tab-pane fade @if($activeTab==='appointments') show active @endif">
          <div class="card overflow-hidden"><div class="table-responsive"><table class="table table-hover align-middle mb-0" style="min-width:850px"><thead><tr><th class="ps-4">{{ __('admin_customers.date') }}</th><th>{{ __('admin_customers.service') }}</th><th>{{ __('admin_customers.specialist') }}</th><th>{{ __('admin_customers.price') }}</th><th>{{ __('admin_customers.status') }}</th><th>{{ __('admin_customers.identity') }}</th><th></th></tr></thead><tbody>
          @forelse($appointments as $appointment)@php($serviceName=$appointment->service?->translations?->firstWhere('locale',app()->getLocale())?->name ?? $appointment->service?->name ?? '—')<tr><td class="ps-4 text-nowrap">{{ $appointment->appointment_start->format('d.m.Y H:i') }}</td><td>{{ $serviceName }}</td><td><div class="d-flex align-items-center gap-2"><x-ui.avatar :user="$appointment->user" :size="34" /><span>{{ $appointment->user?->name ?? '—' }}</span></div></td><td class="text-nowrap">{{ number_format((float)$appointment->price,2,',',' ') }} €</td><td><x-appointments.status :status="$appointment->status" /></td><td>@if($appointment->customer_identity_verified)<span class="badge badge-phoenix badge-phoenix-success">{{ __('admin_customers.account_booking') }}</span>@else<span class="badge badge-phoenix badge-phoenix-warning">{{ __('admin_customers.contact_match') }}</span>@endif</td><td class="pe-4 text-end"><a href="{{ route('calendar.show',$appointment) }}">{{ __('admin_customers.open') }}</a></td></tr>
          @empty<tr><td colspan="7" class="text-center py-5 text-body-tertiary">{{ __('admin_customers.no_bookings') }}</td></tr>@endforelse
          </tbody></table></div></div>
          @if($appointments->hasPages())<div class="mt-3">{{ $appointments->appends(['tab'=>'appointments'])->onEachSide(1)->links() }}</div>@endif
        </section>

        <section id="customer-preferences" class="tab-pane fade @if($activeTab==='preferences') show active @endif">
          <div class="row g-4"><div class="col-12 col-lg-6"><section class="card h-100"><div class="card-body p-4"><h4 class="mb-4">{{ __('admin_customers.favorite_services') }}</h4>
            @forelse($favoriteServices as $item)@php($serviceName=$item->service?->translations?->firstWhere('locale',app()->getLocale())?->name ?? $item->service?->name ?? '—')<div class="d-flex justify-content-between align-items-center gap-3 border-bottom py-3"><div class="d-flex align-items-center gap-3 min-w-0"><x-ui.service-image :service="$item->service" :width="44" :height="44" class="rounded" /><strong class="text-break">{{ $serviceName }}</strong></div><span class="badge badge-phoenix badge-phoenix-primary">{{ trans_choice('admin_customers.visit_count',$item->visits_count,['count'=>$item->visits_count]) }}</span></div>@empty<p class="text-body-tertiary">{{ __('admin_customers.no_completed') }}</p>@endforelse
          </div></section></div><div class="col-12 col-lg-6"><section class="card h-100"><div class="card-body p-4"><h4 class="mb-4">{{ __('admin_customers.favorite_masters') }}</h4>
            @forelse($favoriteMasters as $item)<div class="d-flex justify-content-between align-items-center gap-3 border-bottom py-3"><div class="d-flex align-items-center gap-3"><x-ui.avatar :user="$item->user" :size="44" /><strong>{{ $item->user?->name ?? '—' }}</strong></div><span class="badge badge-phoenix badge-phoenix-primary">{{ trans_choice('admin_customers.visit_count',$item->visits_count,['count'=>$item->visits_count]) }}</span></div>@empty<p class="text-body-tertiary">{{ __('admin_customers.no_completed') }}</p>@endforelse
          </div></section></div></div>
        </section>

        @if(filled($customer->getRawOriginal('password')))<section id="customer-possible" class="tab-pane fade @if($activeTab==='possible') show active @endif">
          <div class="alert alert-warning"><strong>{{ __('admin_customers.possible_title') }}</strong><div class="mt-1">{{ __('admin_customers.possible_help') }}</div></div>
          <div class="card overflow-hidden"><div class="table-responsive"><table class="table table-hover align-middle mb-0" style="min-width:850px"><thead><tr><th class="ps-4">{{ __('admin_customers.date') }}</th><th>{{ __('admin_customers.service') }}</th><th>{{ __('admin_customers.specialist') }}</th><th>{{ __('admin_customers.match_by') }}</th><th>{{ __('admin_customers.link_state') }}</th><th></th></tr></thead><tbody>
          @forelse($possibleAppointments as $appointment)@php($serviceName=$appointment->service?->translations?->firstWhere('locale',app()->getLocale())?->name ?? $appointment->service?->name ?? '—')<tr><td class="ps-4 text-nowrap">{{ $appointment->appointment_start->format('d.m.Y H:i') }}</td><td>{{ $serviceName }}</td><td><div class="d-flex align-items-center gap-2"><x-ui.avatar :user="$appointment->user" :size="34" /><span>{{ $appointment->user?->name ?? '—' }}</span></div></td><td><span class="badge badge-phoenix badge-phoenix-warning">{{ __('admin_customers.match_'.$appointment->identity_match) }}</span></td><td>{{ $appointment->already_linked ? __('admin_customers.linked_by_contacts') : __('admin_customers.needs_review') }}</td><td class="pe-4 text-end"><a href="{{ route('calendar.show',$appointment) }}">{{ __('admin_customers.open') }}</a></td></tr>
          @empty<tr><td colspan="6" class="text-center py-5 text-body-tertiary">{{ __('admin_customers.no_possible') }}</td></tr>@endforelse
          </tbody></table></div></div>
          @if($possibleAppointments?->hasPages())<div class="mt-3">{{ $possibleAppointments->appends(['tab'=>'possible'])->onEachSide(1)->links() }}</div>@endif
        </section>@endif
      </div>
    </div>
  </div>
  <x-dashboard-footer />
</div>
</x-dashboard-layout>
