@section('title', __('admin_notifications.title'))
<x-dashboard-layout>
  <div class="content">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
      <h2 class="mb-0">{{ __('admin_notifications.title') }}</h2>
      @if(auth()->user()->unreadNotifications()->exists())
        <form method="POST" action="{{ route('notifications.read-all') }}">@csrf<button class="btn btn-outline-primary" type="submit">{{ __('admin_notifications.mark_all_read') }}</button></form>
      @endif
    </div>
    <form class="card card-body mb-4" method="GET">
      <div class="row g-3 align-items-end">
        <div class="col-sm-6 col-lg-4"><label class="form-label" for="event">{{ __('admin_notifications.type') }}</label><select class="form-select" id="event" name="event"><option value="">{{ __('admin_notifications.all_types') }}</option>@foreach(['booking_created','cancelled_by_client','cancelled_by_business','reschedule_requested'] as $event)<option value="{{ $event }}" @selected(request('event') === $event)>{{ __('admin_notifications.events.'.$event) }}</option>@endforeach</select></div>
        <div class="col-auto"><div class="form-check"><input class="form-check-input" id="unread" name="unread" type="checkbox" value="1" @checked(request()->boolean('unread'))><label class="form-check-label" for="unread">{{ __('admin_notifications.only_unread') }}</label></div></div>
        <div class="col-auto"><button class="btn btn-primary" type="submit">{{ __('admin_notifications.filter') }}</button></div>
      </div>
    </form>
    <div class="card"><div class="list-group list-group-flush">
      @forelse($notifications as $item)
        @php($data = $item->data)
        <form method="POST" action="{{ route('notifications.read', $item->id) }}">@csrf
          <button class="list-group-item list-group-item-action text-start p-3 {{ $item->read_at ? '' : 'bg-primary-subtle' }}" type="submit">
            <span class="d-flex justify-content-between gap-3"><span><span class="fw-semibold">{{ __('admin_notifications.events.'.($data['event'] ?? 'booking_created')) }}</span><span class="d-block text-body-secondary fs-9">{{ $data['client_name'] ?? '' }} · {{ $data['service_name'] ?? '' }} · {{ $data['master_name'] ?? '' }}</span></span><time class="text-nowrap fs-9 text-body-secondary">{{ $item->created_at->diffForHumans() }}</time></span>
          </button>
        </form>
      @empty<div class="p-5 text-center text-body-secondary">{{ __('admin_notifications.empty') }}</div>@endforelse
    </div></div>
    <div class="mt-3">{{ $notifications->links() }}</div>
  </div>
</x-dashboard-layout>
