@section('title', __('promo.title'))
<x-dashboard-layout>
<div class="content">
  <h2 class="mb-4">{{ __('promo.title') }}</h2>
  @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
  @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

  <div class="card mb-5"><div class="card-header"><h5 class="mb-0">{{ __('promo.create') }}</h5></div><div class="card-body">
    <form method="POST" action="{{ route('promo-codes.store') }}">@csrf
      @include('admin.promo-codes.partials.form', ['promo' => null])
      <button class="btn btn-primary mt-4" type="submit">{{ __('promo.create') }}</button>
    </form>
  </div></div>

  <div class="card"><div class="table-responsive"><table class="table align-middle mb-0">
    <thead><tr><th class="ps-3">{{ __('promo.code') }}</th><th>{{ __('promo.discount') }}</th><th>{{ __('promo.period') }}</th><th>{{ __('promo.limits') }}</th><th>{{ __('promo.used') }}</th><th>{{ __('promo.status') }}</th><th></th></tr></thead>
    <tbody>@forelse($promoCodes as $promo)
      <tr><td class="ps-3 fw-semibold">{{ $promo->code }}</td><td>{{ $promo->discount_type === 'percentage' ? $promo->discount_value.'%' : $promo->discount_value.' €' }}</td><td>{{ $promo->valid_from?->format('d.m.Y H:i') ?? '—' }} – {{ $promo->valid_until?->format('d.m.Y H:i') ?? '—' }}</td><td>{{ $promo->usage_limit ?? '∞' }} / {{ $promo->per_customer_limit ?? '∞' }}</td><td>{{ $promo->redemptions_count }}</td><td><span class="badge badge-phoenix badge-phoenix-{{ $promo->active ? 'success' : 'secondary' }}">{{ $promo->active ? __('promo.active') : __('promo.inactive') }}</span></td><td class="text-end"><button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#promo-edit-{{ $promo->id }}">{{ __('promo.edit') }}</button></td></tr>
      <tr class="collapse" id="promo-edit-{{ $promo->id }}"><td colspan="7"><div class="p-3"><form method="POST" action="{{ route('promo-codes.update', $promo) }}">@csrf @method('PUT') @include('admin.promo-codes.partials.form', ['promo' => $promo])<button class="btn btn-primary mt-4" type="submit">{{ __('promo.save') }}</button></form><form class="mt-2" method="POST" action="{{ route('promo-codes.destroy', $promo) }}">@csrf @method('DELETE')<button class="btn btn-outline-danger" type="submit">{{ __('promo.deactivate') }}</button></form></div></td></tr>
    @empty<tr><td class="text-center text-body-secondary py-5" colspan="7">{{ __('promo.empty') }}</td></tr>@endforelse</tbody>
  </table></div></div><div class="mt-3">{{ $promoCodes->links() }}</div>
</div>
</x-dashboard-layout>
