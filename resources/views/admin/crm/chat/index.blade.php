@section('title', __('crm.chat_title'))
<x-dashboard-layout>
<div class="content">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4"><div><h2 class="mb-1">{{ __('crm.chat_title') }}</h2><p class="text-body-tertiary mb-0">{{ __('crm.assignment_hint') }}</p></div>@can('crm.settings')<a class="btn btn-outline-primary" href="{{ route('crm.settings.index') }}"><span data-feather="settings" class="me-2"></span>{{ __('crm.settings') }}</a>@endcan</div>
  <nav class="nav nav-pills gap-2 mb-4">
    @foreach(['open'=>'open_conversations','closed'=>'closed_conversations','all'=>'all_conversations'] as $status=>$label)<a class="nav-link {{ request('status','open')===$status ? 'active':'' }}" href="{{ route('crm.chat.index',['status'=>$status]) }}">{{ __('crm.'.$label) }}</a>@endforeach
  </nav>
  <div class="card overflow-hidden"><div class="list-group list-group-flush">
    @forelse($conversations as $conversation)
      @php($last=$conversation->messages->first())
      <a class="list-group-item list-group-item-action p-3 p-lg-4" href="{{ route('crm.chat.show',$conversation) }}"><div class="d-flex gap-3 align-items-start"><x-ui.avatar :name="$conversation->customer?->full_name ?? $conversation->visitor_name" :size="46" /><div class="min-w-0 flex-grow-1"><div class="d-flex flex-wrap justify-content-between gap-2"><strong class="text-body-emphasis">{{ $conversation->customer?->full_name ?? $conversation->visitor_name }}</strong><small class="text-body-tertiary">{{ $conversation->last_message_at?->diffForHumans() }}</small></div><p class="text-body-secondary text-truncate mb-2">{{ $last?->body }}</p><div class="d-flex flex-wrap gap-2"><span class="badge badge-phoenix badge-phoenix-{{ $conversation->status==='open' ? 'success':'secondary' }}">{{ __('crm.'.($conversation->status==='open'?'open_conversations':'closed_conversations')) }}</span><span class="badge badge-phoenix badge-phoenix-info">{{ __('crm.assigned_to') }}: {{ $conversation->assignee?->name ?? __('crm.unassigned') }}</span>@if($conversation->customer)<span class="badge badge-phoenix badge-phoenix-primary">{{ __('admin_customers.account') }}</span>@endif</div></div></div></a>
    @empty<div class="p-6 text-center text-body-tertiary">{{ __('crm.no_conversations') }}</div>@endforelse
  </div></div>
  @if($conversations->hasPages())<div class="mt-3">{{ $conversations->onEachSide(1)->links() }}</div>@endif
  <x-dashboard-footer />
</div>
</x-dashboard-layout>
