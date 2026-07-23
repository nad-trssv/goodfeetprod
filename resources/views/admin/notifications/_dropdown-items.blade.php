@forelse($notifications as $item)
  @php($data = $item->data)
  <form method="POST" action="{{ route('notifications.read', $item->id) }}">@csrf
    <button class="dropdown-item text-wrap border-bottom py-3" type="submit">
      <span class="d-block fw-semibold">{{ __('admin_notifications.events.'.($data['event'] ?? 'booking_created')) }}</span>
      <small class="d-block text-body-secondary">{{ $data['client_name'] ?? '' }} · {{ $data['service_name'] ?? '' }}</small>
      <small class="text-body-tertiary">{{ $item->created_at->diffForHumans() }}</small>
    </button>
  </form>
@empty
  <div class="p-4 text-center text-body-secondary">{{ __('admin_notifications.empty') }}</div>
@endforelse
